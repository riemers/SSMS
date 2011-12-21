SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


DROP TABLE IF EXISTS `calendar`;
CREATE TABLE IF NOT EXISTS `calendar` (
  `datefield` date DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `config`;
CREATE TABLE IF NOT EXISTS `config` (
  `setting` varchar(30) NOT NULL,
  `config` varchar(512) NOT NULL,
  `shortname` varchar(512) NOT NULL,
  `description` varchar(512) NOT NULL,
  UNIQUE KEY `setting` (`setting`,`config`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `Crashes`;
CREATE TABLE IF NOT EXISTS `Crashes` (
  `indexnum` int(11) NOT NULL AUTO_INCREMENT,
  `timedate` int(11) NOT NULL DEFAULT '0',
  `ServerNum` int(3) NOT NULL,
  `ServerName` varchar(250) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`indexnum`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `extensions`;
CREATE TABLE IF NOT EXISTS `extensions` (
  `extid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(512) NOT NULL,
  `author` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  PRIMARY KEY (`extid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `games`;
CREATE TABLE IF NOT EXISTS `games` (
  `shortname` varchar(20) NOT NULL,
  `longname` varchar(200) NOT NULL,
  `appID` int(4) NOT NULL,
  `version` varchar(16) NOT NULL,
  `expired` set('yes','no') NOT NULL DEFAULT 'no',
  `minplayers` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `metamods`;
CREATE TABLE IF NOT EXISTS `metamods` (
  `metaid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `description` varchar(512) NOT NULL,
  `url` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`metaid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `mods`;
CREATE TABLE IF NOT EXISTS `mods` (
  `modid` int(5) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL,
  `reloads` varchar(255) NOT NULL,
  `timestamp` varchar(255) NOT NULL,
  `threadid` mediumint(11) DEFAULT NULL,
  PRIMARY KEY (`modid`),
  UNIQUE KEY `filename` (`filename`,`version`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `plugindb`;
CREATE TABLE IF NOT EXISTS `plugindb` (
  `pluginid` int(11) NOT NULL,
  `threadid` int(11) NOT NULL,
  `author` varchar(255) NOT NULL,
  `filename` varchar(1000) NOT NULL,
  `altwebsite` varchar(255) NOT NULL,
  `description` varchar(1000) NOT NULL,
  `info` varchar(5000) NOT NULL,
  `version` varchar(20) NOT NULL,
  `send` set('yes','no') NOT NULL DEFAULT 'yes',
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`threadid`),
  UNIQUE KEY `filename` (`filename`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `restarts`;
CREATE TABLE IF NOT EXISTS `restarts` (
  `indexnum` int(11) NOT NULL AUTO_INCREMENT,
  `timedate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `serverid` int(11) DEFAULT NULL,
  PRIMARY KEY (`indexnum`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `servers`;
CREATE TABLE IF NOT EXISTS `servers` (
  `serverid` int(5) NOT NULL AUTO_INCREMENT,
  `servername` varchar(255) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `port` varchar(255) NOT NULL,
  `netconport` int(7) DEFAULT NULL,
  `netconpasswd` varchar(255) DEFAULT NULL,
  `rconpass` varchar(255) NOT NULL,
  `type` varchar(50) NOT NULL,
  `os` varchar(255) NOT NULL,
  `version` varchar(50) NOT NULL,
  `network` varchar(50) NOT NULL,
  `lastupdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `currentmap` varchar(50) NOT NULL,
  `currentplayers` int(2) NOT NULL,
  `currentbots` int(2) NOT NULL,
  `maxplayers` int(2) NOT NULL,
  `rulesadd` varchar(1024) NOT NULL,
  `autoupdate` set('yes','no') NOT NULL DEFAULT 'yes',
  `showmotd` set('yes','no') NOT NULL DEFAULT 'yes',
  `retries` int(5) NOT NULL DEFAULT '0',
  `restartsend` set('yes','no','update','restart','optional','emptyserver','emptyserver') NOT NULL DEFAULT 'no',
  `updatemessage` varchar(255) NOT NULL,
  `dlyrestart` set('yes','no') NOT NULL DEFAULT 'no',
  `dlytime` time DEFAULT '00:00:00',
  `dlycmd` varchar(255) NOT NULL DEFAULT '_restart',
  `dlyusers` int(2) DEFAULT '0',
  `cmdtosend` set('normal','daily','optional') NOT NULL DEFAULT 'normal',
  `goingdown` set('yes','no') NOT NULL DEFAULT 'no',
  `protected` set('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`serverid`),
  UNIQUE KEY `ip` (`ip`,`port`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `sm_cookies`;
CREATE TABLE IF NOT EXISTS `sm_cookies` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `access` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `sm_cookie_cache`;
CREATE TABLE IF NOT EXISTS `sm_cookie_cache` (
  `player` varchar(65) NOT NULL,
  `cookie_id` int(10) NOT NULL,
  `value` varchar(100) DEFAULT NULL,
  `timestamp` int(11) NOT NULL,
  PRIMARY KEY (`player`,`cookie_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `sm_logging`;
CREATE TABLE IF NOT EXISTS `sm_logging` (
  `serverid` int(5) NOT NULL,
  `steamid` varchar(100) NOT NULL,
  `logtag` varchar(100) NOT NULL,
  `message` varchar(255) NOT NULL,
  `name` varchar(32) NOT NULL,
  `time_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `steamid` (`steamid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

DROP TABLE IF EXISTS `srv_mods`;
CREATE TABLE IF NOT EXISTS `srv_mods` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `modid` int(5) NOT NULL,
  `metaid` int(5) NOT NULL,
  `extid` int(11) NOT NULL DEFAULT '0',
  `serverid` int(5) NOT NULL,
  `pluginnr` int(5) NOT NULL,
  `status` set('active','inactive') NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

