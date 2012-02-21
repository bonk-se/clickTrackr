<?php
/**
 * clickTrackr Logger config
 * Configuration for your version of clickTrackr
 *
 * @package clickTrackr
 * @version 0.6.1
 *
 * @link http://bonk.se/clickTrackr
 * @author miken@bonk.se
 */

/**
 * When this is enabled, new unregistered hosts will be added and tracked.
 * Will ignore data from unknown hosts when disabled.
 * @var bool
 */
$addUnknownHosts = true;

/**
 * Possibility to ignore request from given hostnames.
 * If you get a lot of requests from hosts that you do not want to track
 * or get error messages from, just add them here.
 * @var array|null   host => (bool) log requests
 */
$blockedHosts = array(
	'live.se' => false,
	'bing.com' => true, // unwanted requests are logged
	'google.com' => false,
);

/**
 * List of known hosts (overrides $addUnknownHosts and $blockedHosts).
 * If you have a fixed amount of hosts and don't need to add new ones
 * automatically, it's a perfomance boost to have them specified here.
 * No memcache- or db-lookup has to be made to match host to hostId.
 *
 * Note! If knownHosts is specified and doesn't match anything, the script
 * will not track anything.
 *
 * @example $knownHosts = array('bonk.se' => 1);
 * @var array|null   Array of (string) host => (int) hostId or null
 */
$knownHosts = null;

/**
 * Memcache servers ("null" disables memcache features)
 * @link http://tangent.org/552/libmemcached.html libmemcached client
 * @link http://memcached.org/ memcached server
 * @var array|null
 */
$memcacheServers = array(
	array('localhost', 11211),
);

/**
 * Write each logged click to syslog instead of MySQL.
 * Log rows have the following format: clickTrackr - {"host", "path", x, y, timestamp}
 *
 * @var bool
 */
$useSyslog = false;

/**
 * MySQL settings - Only used if $useSyslog is false.
 * @var array
 */
$mysql = array(
	'host' => 'localhost',
	'user' => 'clickTrackr',
	'pass' => 'some-good-password',
	'db' => 'clickTrackr',
);
