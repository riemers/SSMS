SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `config` (
  `setting` varchar(30) NOT NULL,
  `config` varchar(512) NOT NULL,
  `shortname` varchar(512) NOT NULL,
  `description` varchar(512) NOT NULL,
  UNIQUE KEY `setting` (`setting`,`config`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `config` (`setting`, `config`, `shortname`, `description`) VALUES
('usereplay', 'no', 'Use Replays', 'Keep a record of match id''s per map for servers.'),
('replaydays', '30', 'Replay Days', 'How long to keep the matchid''s for matching with Youtube?'),
('showrestarts', 'no', 'Show Restarts', 'Weither or not to show the extra tab for restarts, if you dont use the restarts plugin you cannot see any restarts from servers. If you dont use the plugin this is pretty much useless. This option will remove that tab, same as the rules option.'),
('defaultmotd', 'yes', 'Default motd', 'If you add a new server, should it by default have the motd flag on? This means that if you use the motd page that comes with SSMS it will show in there, handy if you dont want your warservers or test servers in the motd.'),
('defaultautoupdate', 'yes', 'Auto update', 'Default set to yes, will mean that if a update comes out for this type of game, it will initiate a quit to the server in order for it to automaticly update (assuming you have -autoupdate or some other form that tries to update when you restart your server)'),
('defaultgametypeinplugins', 'all', 'Default Gametype (NOT IN USE YET)', 'For the plugin tab, if you have 30 counter strike servers and only 1 tf2 server i can imagine that you want to have counter strike as default. Set this to the gametype value of that game. (if uncertain, leave it to all)'),
('announceupdate', 'yes', 'Announce Updates', 'announce it to the messagers that are turned on'),
('usegrowl', 'no', 'Use Growl', 'If you want to use growl or not'),
('usetwitter', 'no', 'Use Twitter', 'If you want to use twitter or not'),
('useemail', 'no', 'Use E-Mail', 'If you want to use mail or not'),
('consumerkey', '', 'Consumer Key', 'Your consumer key found on the dev twitter site when you create a new app for it.'),
('emailalert', 'contact@somewhere.com', 'Email', 'The primary email address it will send a notification too if it needs to send out a email.'),
('retrycount', '5', 'Retry Count', 'Number of retry''s the server can have before it sends out a notification that the server is down. Dont change this lower then 3 since a server could be changing maps and wont respond (and that will increase the retry count) thats also why in the server tab it will show as amber. '),
('announceserverdown', 'yes', 'Announce down Server', 'If a server goes down, should we announce this to the steamgroup, email or both? (Valid values are, ''both'',''email'',''steam'',''none'')'),
('defaultannounce', 'sm_msay Server is going down for update;sm_hsay Server is going down for restart', 'Client info update', 'if a server is allowed to do a update, this is the default string a new server will get. It will send out this string to the server if a update is starting. After the defined number of seconds it will issue the quit. This is the be more userfriendly and inform the users of what is happening.'),
('server_prefix', '[EU] Lethal-Zone.eu', 'Prefix', 'Server prefix (to cut down the name in plugin overview)'),
('growlip', '', 'Growl IP', 'IP address/hostname where to send the growl message towards (port  9887 udp needs to be open)'),
('growlpass', 'lethal', 'Growl Password', 'Password used in growl to authenticate, can be a new one ofcourse in growl itself.'),
('consumersecret', '', 'Consumer Secret', 'Your consumer secret found on the dev twitter site when you create a new app for it.'),
('OAuthToken', '', 'OAuthToken', 'OAuthToken found within your account by access tokens.'),
('OAuthTokenSecret', '', 'OAuthTokenSecret', 'OAuthTokenSecret found within your account by access tokens.'),
('netconrestart', 'shutdown', 'Netcon Restart', 'The command that is send towards servers that use netcon (forks for l4d/l4d2 have this) preffered is ''shutdown'' since it will wait for the last players to go. Ofcourse ''quit'' can also be used.'),
('adminactivity', 'yes', 'Admin Logs', 'Show the tab for Admin activity (only works in combination with the dbadmin.smx loaded)'),
('usestats', 'yes', 'Use Stats', 'Use statistics pages like hlstats (only hlstats for now)'),
('statsprogram', 'hlxce', '', ''),
('statsurl', 'http://stats.lethal-zone.eu', 'Stats URL', 'Link to the webpage for the stats'),
('useboxcar', 'yes', 'Boxcar Alerts', 'Get alerts on Android/iPhone via Boxcar notifications.'),
('boxemail', '', 'Boxcar emails', 'Boxcar list of comma seperated email(s)'),
('version', '1.0.0', 'Version of SSMS', 'Version number for SSMS to make installers happy.');

CREATE TABLE IF NOT EXISTS `extensions` (
  `extid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(512) NOT NULL,
  `author` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `url` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  PRIMARY KEY (`extid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=230 ;

CREATE TABLE IF NOT EXISTS `games` (
  `shortname` varchar(20) NOT NULL,
  `longname` varchar(200) NOT NULL,
  `appID` int(4) NOT NULL,
  `version` varchar(16) NOT NULL,
  `expired` set('yes','no') NOT NULL DEFAULT 'no',
  `minplayers` int(2) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `games` (`shortname`, `longname`, `appID`, `version`, `expired`, `minplayers`) VALUES
('tf', 'Team Fortress 2', 440, '1.1.9.8', 'no', 4),
('left4dead', 'Left 4 Dead', 500, '1.0.2.6', 'no', NULL),
('left4dead2', 'Left for Dead 2', 550, '2.0.9.9', 'no', NULL),
('cstrike', 'Counter-Strike: Source', 240, '1.0.0.70', 'no', 4),
('all', 'All Games', 0, '', '', NULL),
('dod', 'Day of Defeat: Source', 300, '1.0.0.30', 'no', NULL),
('nucleardawn', 'Nuclear Dawn', 17710, '12.02.02', 'no', NULL);

CREATE TABLE IF NOT EXISTS `matchids` (
  `serverid` int(5) NOT NULL,
  `mapname` varchar(50) NOT NULL,
  `sessionid` int(50) NOT NULL,
  `matchdate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `sessionid` (`sessionid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `metamods` (
  `metaid` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `version` varchar(255) NOT NULL,
  `description` varchar(512) NOT NULL,
  `url` varchar(255) NOT NULL,
  `details` varchar(255) NOT NULL,
  `file` varchar(255) NOT NULL,
  PRIMARY KEY (`metaid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=191 ;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=966 ;

CREATE TABLE IF NOT EXISTS `restarts` (
  `indexnum` int(11) NOT NULL AUTO_INCREMENT,
  `timedate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `serverid` int(11) DEFAULT NULL,
  PRIMARY KEY (`indexnum`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=47 ;

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
  `restartsend` set('yes','no','update','restart','optional','emptyserver') NOT NULL DEFAULT 'no',
  `updatemessage` varchar(255) NOT NULL,
  `dlyrestart` set('yes','no') NOT NULL DEFAULT 'no',
  `dlytime` time DEFAULT '00:00:00',
  `dlycmd` varchar(255) NOT NULL DEFAULT '_restart',
  `dlyusers` int(2) DEFAULT '0',
  `cmdtosend` set('normal','daily','optional') NOT NULL DEFAULT 'normal',
  `goingdown` set('yes','no') NOT NULL DEFAULT 'no',
  `protected` set('1','0') NOT NULL DEFAULT '0',
  `servertags` varchar(512) NOT NULL,
  `replaymatch` set('yes','no') NOT NULL,
  PRIMARY KEY (`serverid`),
  UNIQUE KEY `ip` (`ip`,`port`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=75 ;

CREATE TABLE IF NOT EXISTS `sm_logging` (
  `serverid` int(5) NOT NULL,
  `steamid` varchar(100) NOT NULL,
  `logtag` varchar(100) NOT NULL,
  `message` varchar(255) NOT NULL,
  `name` varchar(32) NOT NULL,
  `time_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `steamid` (`steamid`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9182 ;

CREATE TABLE IF NOT EXISTS `videos` (
  `id` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `youtubeid` varchar(50) NOT NULL,
  `youtubeuser` varchar(256) NOT NULL,
  `map` varchar(50) DEFAULT NULL,
  `sessionid` int(50) NOT NULL,
  `matchdate` timestamp NULL DEFAULT NULL,
  `matchduration` int(5) NOT NULL,
  `role` varchar(64) NOT NULL,
  `serverid` int(5) DEFAULT NULL,
  `duration` int(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(2000) NOT NULL,
  `crawldate` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `youtubeid` (`youtubeid`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=24 ;
