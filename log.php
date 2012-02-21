<?php
/**
 * clickTrackr Logger
 * Receives a request from the client, returns the code for a 1x1-gif and closes the connection.
 * Then the data is parsed and stored in a MySQL database.
 *
 * If you have a lot of clicks to track and don't have the database-performance for it,
 * I strongly suggests that you get this data by parsing the access log instead (and set
 * the bCT.conf.url-parameter to a static gif).
 *
 * @package clickTrackr
 * @version 0.6.1
 *
 * @uses MySQL
 * @uses Memcached
 *
 * @link http://bonk.se/clickTrackr
 * @author miken@bonk.se
 */

// Include configuration
require('config.php');

/* Filter input data */
$pos = (!empty($_GET['p'])) ? json_decode($_GET['p']) : null;
$url = (!empty($_GET['u'])) ? filter_var(urldecode($_GET['u']), FILTER_VALIDATE_URL) : null;
if (!$url) {
	/* No URL was sent, fallback to referer */
	$url = filter_var($_SERVER['HTTP_REFERER'], FILTER_VALIDATE_URL);
}

/* Let this script run, even if connection is closed */
ignore_user_abort(true);

/* Output transparent gif and close connection */
$response = base64_decode('R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
header('Cache-Control: no-cache');
header('Content-type: image/gif');
header('Content-length: '.mb_strlen($response));
header('Connection: close');
echo $response;
flush();
/*
 * Connection to the client should be closed (if your server supports implicit flush)
 */

/* Start parsing the data and storing it */
if (!empty($pos) && is_array($pos) && $url) {
	$url = parse_url($url);
	if (!$url || empty($url)) {
		exit();
	}
	$path =& $url['path'];
	$host = str_replace('www.', '', $url['host']);
	unset($url);

	$hostId = null;
	$pathId = null;

	/*
	 * Host checks
	 */
	if (!empty($knownHosts)) {
		/* Known hosts */
		if (isset($knownHosts[$host])) {
			$hostId = $knownHosts[$host];
		}
	} elseif (!empty($blockedHosts) && isset($blockedHosts[$host])) {
		/* Blocked hosts */
		if ($blockedHosts[$host]) {
			error_log('clickTrackr - Unwanted request from: '.$url);
		}
		exit();
	}
	if (!$hostId && false === $addUnknownHosts) {
		/* Unknown host */
		error_log('clickTrackr - Unknown host: '.$url);
		exit();
	}

	/* Get hostId and pathId from memcache */
	if (!empty($memcacheServers)) {
		$memcache = new Memcached();
		foreach ($memcacheServers as $server) {
			$res = $memcache->addServer($server[0], $server[1]);
			if (!$res) {
				error_log('clickTrackr - Memcached->addServer failed: '.$server[0].':'.$server[1]);
			}
		}
		$memcache->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);

		$mcKeyHost = 'bct_host_'.$host;
		$mcKeyPath = 'bct_path_'.$path;
		$mcKeys = array('p', $mcKeyPath);
		if (!$hostId) {
			$mcKeys['h'] = $mcKeyHost;
		}
		$res = $memcache->getMulti($mcKeys);
		if (!empty($res)) {
			$hostId = (isset($res['h'])) ? intval($res['h']) : $hostId;
			$pathId = (isset($res['p'])) ? intval($res['p']) : $pathId;
		}
	}

	/* Connect to MySQL-server */
	$db = @mysql_connect($mysql['host'], $mysql['user'], $mysql['pass']);
	if (!$db) {
		error_log('clickTrackr - Failed connecting: ('.mysql_errno().') '.mysql_error());
		exit();
	}
	if (!@mysql_select_db($mysql['db'], $db)) {
		error_log('clickTrackr - Failed db-select: ('.mysql_errno().') '.mysql_error());
		exit();
	}

	/* host and hostId */
	if (!$hostId) {
		$hostEscaped = mysql_real_escape_string($host, $db);
		$sql = 'SELECT id FROM clickTrackr_hosts WHERE host = "'.$hostEscaped.'"';
		$res = mysql_query($sql);
		if ($res) {
			list($hostId) = mysql_fetch_row($res);
			if ($hostId) {
				$hostId = intval($hostId);
			} elseif ($addUnknownHosts) {
				mysql_query('INSERT INTO clickTrackr_hosts SET host = "'.$hostEscaped.'"');
				$hostId = (int)mysql_insert_id();
			} else {
				error_log('clickTrackr - Host not found in DB: '.$host);
				exit();
			}
		}
		if (!$hostId) {
			error_log('clickTrackr - Failed getting hostId from DB: '.$host);
			exit();
		} elseif ($memcacheServers) {
			$memcache->add($mcKeyHost, $hostId, 0);
		}
	}

	/* path and pathId */
	if (!$pathId) {
		$pathEscaped = mysql_real_escape_string($path, $db);
		$sql = 'SELECT id FROM paths WHERE path = "'.$pathEscaped.'"';
		$res = mysql_query($sql);
		if ($res) {
			list($pathId) = mysql_fetch_row($res);
			if ($pathId) {
				$pathId = intval($pathId);
			} else {
				mysql_query('INSERT INTO clickTrackr_paths SET path = "'.$pathEscaped.'"');
				$pathId = (int)mysql_insert_id();
			}
		}
		if (!$pathId) {
			error_log('clickTrackr - Failed get/set pathId from DB: '.$path);
			exit();
		} elseif ($memcacheServers) {
			$memcache->add($mcKeyPath, $pathId, 0);
		}
	}

	/* Insert all tracking points */
	$sqlPre = 'INSERT INTO clickTrackr_log SET hostId = '.$hostId.', pathId = '.$pathId.', ';
	$sqlPost = ', clicks = 1 ON DUPLICATE KEY UPDATE clicks = clicks + 1';
	foreach ($pos as $p) {
		$x = intval($p[0]);
		$y = intval($p[1]);
		if (0 > $x || 0 > $y || 65535 < $x || 65535 < $y) {
			/* Ignore unsavable values */
			continue;
		}
		$res = mysql_query($sqlPre.'x = '.$x.', y = '.$y . $sqlPost);
		if (!$res) {
			error_log('clickTrackr query error: ('.mysql_errno().') '.mysql_error());
		}
	}
	mysql_close($db);
}

//EOF