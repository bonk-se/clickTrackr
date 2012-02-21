/**
 * clickTrackr database setup
 *
 * @package clickTrackr
 * @version 0.6.1
 *
 * @link http://bonk.se/clickTrackr
 * @author miken@bonk.se
 */

/**
 * Mapping of all tracked hosts to hostId
 */
CREATE TABLE IF NOT EXISTS clickTrackr_hosts (
	id smallint unsigned not null auto_increment,
	host varchar(200) not null,
	PRIMARY KEY (id, host)
) ENGINE=InnoDB
 DEFAULT CHARSET=utf8
 COMMENT='Mapping of all known hosts to id';

/**
 * All paths mapped to a unique id
 * Paths are not connected to a hostId, just to save the amount of possible paths
 */
CREATE TABLE IF NOT EXISTS clickTrackr_paths (
	id smallint unsigned not null auto_increment,
	path varchar(200) not null,
	PRIMARY KEY (id, path)
) ENGINE=InnoDB
 DEFAULT CHARSET=utf8
 COMMENT='Mapping of all paths to id';

/**
 * When you have a lot of traffic on your sites, you might want to reconsider
 * having one table for each host and remove hostId from here.
 * Rotating this table on a regular basis is almost required.
 * 
 * A date/timestamp-column can be added (to the PK as well) to see if clicks
 * changes over time of day, but I recommend using rotation instead. 
 */
CREATE TABLE IF NOT EXISTS clickTrackr_log (
	hostId smallint unsigned not null COMMENT 'Refers to clickTrackr_hosts.id',
	pathId smallint unsigned not null COMMENT 'Refers to clickTrackr_paths.id',
	x smallint unsigned not null default 0,
	y smallint unsigned not null default 0,
	clicks smallint unsigned not null default 0,
	PRIMARY KEY (hostId, pathId, x, y)
) ENGINE=InnoDB
 DEFAULT CHARSET=utf8
 COMMENT='All tracked clicks';
