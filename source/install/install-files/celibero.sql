-- MySQL dump 10.11
--
-- Host: localhost    Database: celibero
-- ------------------------------------------------------
-- Server version	5.0.41-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `abuse_forms`
--

DROP TABLE IF EXISTS `abuse_forms`;
CREATE TABLE `abuse_forms` (
  `abuse_id` int(9) NOT NULL auto_increment,
  `ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `email` varchar(120) NOT NULL default '',
  `first_name` varchar(30) NOT NULL default '',
  `last_name` varchar(30) NOT NULL default '',
  `state` varchar(30) NOT NULL default '',
  `country` varchar(30) NOT NULL default '',
  `message` text NOT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY  (`abuse_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `aol_ratio` (
  `aol_ratio_id` int(9) NOT NULL auto_increment,
  `ip` varchar(255) NOT NULL,
  `date` datetime NOT NULL,
  `ratio` varchar(40) NOT NULL,
  `message` text NOT NULL,
  `read` int(1) NOT NULL default '0',
  PRIMARY KEY  (`aol_ratio_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `bounce`
--

DROP TABLE IF EXISTS `bounce`;
CREATE TABLE `bounce` (
  `email` varchar(225) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY  (`email`),
  KEY `count` (`count`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `bouncer`
--

DROP TABLE IF EXISTS `bouncer`;
CREATE TABLE `bouncer` (
  `email` varchar(225) NOT NULL,
  PRIMARY KEY  (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE `categories` (
  `category_id` int(9) NOT NULL auto_increment,
  `title` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=21 DEFAULT CHARSET=latin1;

--
-- Table structure for table `commands`
--

DROP TABLE IF EXISTS `commands`;
CREATE TABLE `commands` (
  `command_id` smallint(9) NOT NULL auto_increment,
  `command` text NOT NULL,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `state` tinyint(1) NOT NULL default '0',
  `output` text NOT NULL,
  `return` text NOT NULL,
  `type` enum('shell','mysql') NOT NULL default 'shell',
  PRIMARY KEY  (`command_id`),
  KEY `date` (`date`),
  KEY `state` (`state`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=latin1;

--
-- Table structure for table `config`
--

DROP TABLE IF EXISTS `config`;
CREATE TABLE `config` (
  `KEY` varchar(30) NOT NULL default '',
  `value` text NOT NULL,
  PRIMARY KEY  (`KEY`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `crons`
--

DROP TABLE IF EXISTS `crons`;
CREATE TABLE `crons` (
  `name` varchar(40) NOT NULL default '',
  `last_checkin` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`name`),
  KEY `last_checkin` (`last_checkin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `clicks` (
  `email` varchar(255) NOT NULL,
  `sent` int(1) NOT NULL default '0',
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `msg_complaint` (
  `msg_id` int(9) NOT NULL,
  `ip` varchar(255) NOT NULL,
  `count` int(9) NOT NULL,
  PRIMARY KEY  (`msg_id`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `dr_hosts`
--

DROP TABLE IF EXISTS `dr_hosts`;
CREATE TABLE `dr_hosts` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL default 'pending',
  `message` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `delivery_configuration` (
  `delivery_configuration_id` int(4) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) NOT NULL,
  `header` text NOT NULL,
  `encoding_text` varchar(30) NOT NULL,
  `encoding_html` varchar(30) NOT NULL,
  `encoding_aol` varchar(30) NOT NULL,
  `boundry_prefix` varchar(30) NOT NULL,
  `boundry_postfix` varchar(30) NOT NULL,
  `charset_head` varchar(40) NOT NULL,
  `charset_text` varchar(40) NOT NULL,
  `charset_html` varchar(40) NOT NULL,
  `charset_aol` varchar(40) NOT NULL,
  PRIMARY KEY (`delivery_configuration_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;


CREATE TABLE `domain_group` (
	`domain_group_id` INT( 9 ) NOT NULL AUTO_INCREMENT ,
	`name` VARCHAR( 255 ) NOT NULL ,
	PRIMARY KEY ( `domain_group_id` )
) ENGINE = MYISAM ;
--
-- Table structure for table `email_to_category`
--

DROP TABLE IF EXISTS `email_to_category`;
CREATE TABLE `email_to_category` (
  `email` varchar(120) NOT NULL default '0',
  `category_id` int(9) NOT NULL default '0',
  `open` enum('1','0') NOT NULL default '0',
  `click` enum('1','0') NOT NULL default '0',
  PRIMARY KEY  (`email`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `email_to_sup`
--

DROP TABLE IF EXISTS `email_to_sup`;
CREATE TABLE `email_to_sup` (
  `email` varchar(120) NOT NULL default '',
  `sup_list_id` int(9) NOT NULL default '0',
  `domain` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`email`,`sup_list_id`),
  KEY `sup_list_id` (`sup_list_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `errors`
--

DROP TABLE IF EXISTS `errors`;
CREATE TABLE `errors` (
  `error_id` int(9) NOT NULL auto_increment,
  `from` varchar(50) NOT NULL default '',
  `error` text NOT NULL,
  `state` enum('n','d') NOT NULL default 'n',
  PRIMARY KEY  (`error_id`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `export`
--

DROP TABLE IF EXISTS `export`;
CREATE TABLE `export` (
  `export_id` int(9) NOT NULL auto_increment,
  `state` int(2) NOT NULL default '0',
  `name` varchar(100) NOT NULL default '',
  `progress` int(9) NOT NULL default '0',
  `type` int(2) NOT NULL default '0',
  `list-cat` varchar(100) NOT NULL default '',
  `total` int(9) NOT NULL default '0',
  `ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `openers` enum('y','n','a') NOT NULL,
  `clickers` enum('y','n','a') NOT NULL,
  `import_id` int(9) NOT NULL default '0',
  `subscribed` enum('y','n') NOT NULL default 'n',
  `unsubscribed` enum('y','n') NOT NULL default 'n',
  `bounce_s` enum('y','n') NOT NULL default 'n',
  `bounce_h` enum('y','n') NOT NULL default 'n',
  `where` varchar(225) NOT NULL,
  `slog_where` varchar(225) NOT NULL,
  PRIMARY KEY  (`export_id`),
  KEY `ts` (`ts`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `extra_content`
--

DROP TABLE IF EXISTS `extra_content`;
CREATE TABLE `extra_content` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `content_type` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;

--
-- Table structure for table `extra_content_data`
--

DROP TABLE IF EXISTS `extra_content_data`;
CREATE TABLE `extra_content_data` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `content_id` int(11) NOT NULL,
  `content_format` varchar(255) NOT NULL,
  `data` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=latin1;

--
-- Table structure for table `global_unsub`
--

DROP TABLE IF EXISTS `global_unsub`;
CREATE TABLE `global_unsub` (
  `ts` datetime default NULL,
  `address` varchar(64) NOT NULL default '',
  `how` int(2) default '0',
  `global_action` enum('0','1','2','3','4') NOT NULL default '0',
  PRIMARY KEY  (`address`),
  KEY `a` (`address`(10))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `global_unsub_domain`
--

DROP TABLE IF EXISTS `global_unsub_domain`;
CREATE TABLE `global_unsub_domain` (
  `ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `domain` varchar(50) NOT NULL default '',
  `how` int(1) NOT NULL default '0',
  `global_action` int(1) NOT NULL default '0',
  PRIMARY KEY  (`domain`),
  KEY `ts` (`ts`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `global_words`
--

DROP TABLE IF EXISTS `global_words`;
CREATE TABLE `global_words` (
  `word_id` int(9) NOT NULL auto_increment,
  `word` varchar(50) NOT NULL default '',
  `ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `how` tinyint(2) NOT NULL default '0',
  `global_action` tinyint(2) NOT NULL default '0',
  PRIMARY KEY  (`word_id`),
  UNIQUE KEY `word` (`word`),
  KEY `how` (`how`,`global_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `imports`
--

DROP TABLE IF EXISTS `imports`;
CREATE TABLE `imports` (
  `import_id` int(9) NOT NULL auto_increment,
  `title` varchar(100) NOT NULL default '',
  `description` text NOT NULL,
  `format` text NOT NULL,
  `ts` datetime NOT NULL default '0000-00-00 00:00:00',
  `start` datetime NOT NULL default '0000-00-00 00:00:00',
  `end` datetime NOT NULL default '0000-00-00 00:00:00',
  `file` varchar(100) NOT NULL default '',
  `state` varchar(30) NOT NULL default '',
  `list` varchar(50) NOT NULL default '',
  `delim` varchar(4) NOT NULL default ',',
  `added` int(11) NOT NULL default '0',
  `invalid` int(11) NOT NULL default '0',
  `dups` int(11) NOT NULL default '0',
  `unsub` int(11) NOT NULL default '0',
  `unsub_g` int(11) NOT NULL default '0',
  `unsub_d` int(11) NOT NULL default '0',
  `filtered` int(9) NOT NULL default '0',
  `total` int(11) NOT NULL default '0',
  `type` varchar(30) NOT NULL default '',
  `type_id` int(2) NOT NULL default '0',
  `overwrite` tinyint(2) NOT NULL default '0',
  `dedupe` text NOT NULL,
  PRIMARY KEY  (`import_id`),
  KEY `ts` (`ts`),
  KEY `state` (`state`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
CREATE TABLE `links` (
  `link_id` int(9) NOT NULL auto_increment,
  `msg_id` int(9) NOT NULL default '0',
  `URL` varchar(255) NOT NULL default '',
  `count` int(9) NOT NULL default '0',
  `dummy` int(1) NOT NULL default '0',
  `img` enum('0','1') NOT NULL default '0',
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`link_id`),
  KEY `msg_id` (`msg_id`),
  KEY `dummy` (`dummy`),
  KEY `date` (`date`),
  KEY `img` (`img`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `log`
--

DROP TABLE IF EXISTS `log`;
CREATE TABLE `log` (
  `log_id` int(9) NOT NULL auto_increment,
  `date` datetime NOT NULL default '0000-00-00 00:00:00',
  `type` varchar(30) NOT NULL default '',
  `message` text NOT NULL,
  `code` int(5) NOT NULL default '0',
  PRIMARY KEY  (`log_id`),
  KEY `date` (`date`),
  KEY `code` (`code`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `mm_defaults`
--

DROP TABLE IF EXISTS `mm_defaults`;
CREATE TABLE `mm_defaults` (
  `address` varchar(150) NOT NULL default '',
  `first_name` varchar(30) NOT NULL default '',
  `last_name` varchar(30) NOT NULL default '',
  `ip` varchar(30) NOT NULL default '',
  `gender` varchar(30) NOT NULL default '',
  `dob` varchar(30) NOT NULL default '',
  `state` varchar(30) NOT NULL default '',
  `zip` varchar(30) NOT NULL default '',
  `city` varchar(30) NOT NULL default '',
  `postal` varchar(30) NOT NULL default '',
  `timestamp` varchar(30) NOT NULL default ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `msg`
--

DROP TABLE IF EXISTS `msg`;
CREATE TABLE `msg` (
  `id` int(11) NOT NULL auto_increment,
  `server_id` int(5) NOT NULL default '0',
  `delivery_configuration_id` int(4) NOT NULL,
  `title` varchar(100) NOT NULL default '',
  `state` tinyint(1) default '0',
  `query` varchar(255) NOT NULL default '',
  `size` int(11) default '0',
  `send_type` int(11) default '0',
  `max_recipients` int(11) default '0',
  `start_recipient` int(11) default '0',
  `content` int(11) default '0',
  `body` text,
  `html_body` text,
  `aol_body` text,
  `comments` text NOT NULL,
  `link_tracking` enum('1','0') NOT NULL default '1',
  `category_add` int(1) NOT NULL default '3',
  `seeds` text NOT NULL,
  `sup_list_id` int(2) NOT NULL default '0',
  `header` int(11) default NULL,
  `footer` int(11) default NULL,
  PRIMARY KEY  (`id`),
  KEY `sup_list_id` (`sup_list_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

ALTER TABLE `msg` ADD `yahoo_body` TEXT NOT NULL , ADD `yahoo_date` VARCHAR( 255 ) NOT NULL, ADD `yahoo_date_original` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `msg` ADD `threads` INT( 9 ) NOT NULL ,ADD `thread_wait` INT( 9 ) NOT NULL ;
--
-- Table structure for table `msg_to_category`
--

DROP TABLE IF EXISTS `msg_to_category`;
CREATE TABLE `msg_to_category` (
  `msg_id` int(9) NOT NULL default '0',
  `category_id` int(9) NOT NULL default '0',
  PRIMARY KEY  (`msg_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `msg_to_domain`
--

DROP TABLE IF EXISTS `msg_to_domain`;
CREATE TABLE `msg_to_domain` (
  `msg_id` int(9) NOT NULL default '0',
  `domain` varchar(70) NOT NULL default '',
  KEY `msg_id` (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE `celibero`.`msg_to_domain_2` (
`msg_id` INT( 9 ) NOT NULL ,
`domain` VARCHAR( 225 ) NOT NULL ,
`invert` INT( 0 ) NOT NULL
) ENGINE = MYISAM;

--
-- Table structure for table `msg_to_from`
--

CREATE TABLE IF NOT EXISTS `msg_to_from` (
  `msg_id` int(9) NOT NULL DEFAULT '0',
  `from` varchar(100) NOT NULL DEFAULT '',
  `from_local` varchar(225) NOT NULL,
  `from_domain` varchar(225) NOT NULL,
  KEY `msg_id` (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `msg_to_ip` (
  `draft_id` int(9) NOT NULL DEFAULT '0',
  `ip_id` int(9) NOT NULL,
  `domain` varchar(225) NOT NULL,
  KEY `msg_id` (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- Table structure for table `msg_to_list`
--

DROP TABLE IF EXISTS `msg_to_list`;
CREATE TABLE `msg_to_list` (
  `msg_id` int(9) NOT NULL default '0',
  `list_id` int(9) NOT NULL default '0',
  PRIMARY KEY  (`msg_id`,`list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
ALTER TABLE `msg_to_list` ADD `skip` INT( 9 ) NOT NULL DEFAULT '0';
--
-- Table structure for table `msg_to_subject`
--

DROP TABLE IF EXISTS `msg_to_subject`;
CREATE TABLE `msg_to_subject` (
  `msg_id` int(9) NOT NULL default '0',
  `subject` varchar(100) NOT NULL default '',
  KEY `msg_id` (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



CREATE TABLE IF NOT EXISTS `msg_to_suppression` (
  `msg_id` int(9) NOT NULL,
  `suppression_list_id` int(9) NOT NULL,
  PRIMARY KEY (`msg_id`,`suppression_list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `quick_campaign`
--

DROP TABLE IF EXISTS `quick_campaign`;
CREATE TABLE `quick_campaign` (
  `qc_id` int(9) NOT NULL auto_increment,
  `title` varchar(40) NOT NULL,
  `url` varchar(200) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `type` varchar(10) NOT NULL,
  PRIMARY KEY  (`qc_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `quick_campaign_creative`
--

DROP TABLE IF EXISTS `quick_campaign_creative`;
CREATE TABLE `quick_campaign_creative` (
  `qcc_id` int(9) NOT NULL auto_increment,
  `text` text NOT NULL,
  `html` text NOT NULL,
  `subjects` text NOT NULL,
  `froms` text NOT NULL,
  `sup_list_id` varchar(40) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY  (`qcc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `rotations`
--

DROP TABLE IF EXISTS `rotations`;
CREATE TABLE `rotations` (
  `server_id` int(9) NOT NULL,
  `per_mailing` int(3) NOT NULL,
  `per_seconds` int(8) NOT NULL,
  `last_update` datetime NOT NULL,
  `last_id` varchar(50) NOT NULL default '0',
  PRIMARY KEY  (`server_id`),
  KEY `last_update` (`last_update`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `msg_to_rotated` (
        `msg_id` varchar(9) NOT NULL,
        `rotated_id` varchar(9) NOT NULL,
        `name` varchar(128) NOT NULL,
        PRIMARY KEY (`msg_id`,`rotated_id`))
         ENGINE=MyISAM DEFAULT CHARSET=latin1;
--
-- Table structure for table `schedule`
--

DROP TABLE IF EXISTS `schedule`;
CREATE TABLE `schedule` (
  `id` int(11) NOT NULL auto_increment,
  `msg_id` int(11) default NULL,
  `server_id` int(9) NOT NULL default '0',
  `title` varchar(60) NOT NULL default '',
  `state` tinyint(1) default '0',
  `old_state` tinyint(1) NOT NULL default '0',
  `scheduled_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `start_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `total_emails` int(11) default '0',
  `success` int(11) NOT NULL default '0',
  `deferral` int(11) NOT NULL default '0',
  `failure` int(11) NOT NULL default '0',
  `opens` int(11) default '0',
  `subject_lines` int(9) NOT NULL default '0',
  `from_lines` int(9) NOT NULL default '0',
  `domains` int(9) NOT NULL default '0',
  `sql_extra` text NOT NULL,
  `process_results` int(9) NOT NULL default '0',
  `max_threads` int(5) NOT NULL default '300',
  `skip_first` int(9) NOT NULL,
  `send_to_first` int(9) NOT NULL,
  `send_to_last` int(9) NOT NULL,
  `max_of` int(9) NOT NULL,
  `retry_level` int(9) NOT NULL,
  `retries` int(9) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `msgid` (`msg_id`),
  KEY `scheduled_time` (`scheduled_time`),
  KEY `process_results` (`process_results`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;

--
-- Table structure for table `schedule_log`
--

DROP TABLE IF EXISTS `schedule_log`;
CREATE TABLE `schedule_log` (
  `schedule_id` int(9) NOT NULL default '0',
  `time` datetime NOT NULL default '0000-00-00 00:00:00',
  `message` text NOT NULL,
  KEY `schedule_id` (`schedule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `server_to_ip`
--

CREATE TABLE IF NOT EXISTS `server_to_ip` (
  `server_id` int(9) NOT NULL DEFAULT '0',
  `ip` varchar(50) NOT NULL DEFAULT '',
  `domain` varchar(120) NOT NULL DEFAULT '',
  `default` enum('1','0') NOT NULL DEFAULT '0',
  `ip_id` int(9) NOT NULL AUTO_INCREMENT,
  `aol` int(1) NOT NULL,
  PRIMARY KEY (`ip_id`),
  UNIQUE KEY `server_id` (`server_id`,`ip`),
  KEY `default` (`default`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;


ALTER TABLE `server_to_ip` ADD `aol_confirmation_code` VARCHAR( 255 ) NOT NULL;
ALTER TABLE `server_to_ip` ADD `aol_date` VARCHAR( 60 ) NOT NULL;
ALTER TABLE `server_to_ip` ADD `aol_ratio` VARCHAR( 20 ) NOT NULL;
ALTER TABLE `server_to_ip` ADD `aol_deny` VARCHAR( 20 ) NOT NULL ;

--
-- Table structure for table `servers`
--

DROP TABLE IF EXISTS `servers`;
CREATE TABLE `servers` (
  `server_id` int(9) NOT NULL auto_increment,
  `type` int(2) NOT NULL default '1',
  `name` varchar(40) NOT NULL default '',
  PRIMARY KEY  (`server_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `sgdne`
--

DROP TABLE IF EXISTS `sgdne`;
CREATE TABLE `sgdne` (
  `username` varchar(30) NOT NULL default '',
  `password` varchar(30) NOT NULL default '',
  `active` enum('0','1') NOT NULL default '0',
  `last_checkin` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_email` varchar(130) NOT NULL default '',
  `hostname` varchar(100) NOT NULL default '',
  `database` varchar(50) NOT NULL default '',
  `last_checkin_domain` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_domain` varchar(100) NOT NULL default '',
  `last_checkin_word` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_word` varchar(60) NOT NULL default '',
  PRIMARY KEY  (`username`),
  KEY `last_checkin_word` (`last_checkin_word`,`last_word`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `supression_lists`
--

DROP TABLE IF EXISTS `supression_lists`;
CREATE TABLE `supression_lists` (
  `sup_list_id` int(9) NOT NULL auto_increment,
  `title` varchar(50) NOT NULL default '',
  `state` int(9) NOT NULL default '0',
  PRIMARY KEY  (`sup_list_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;


CREATE TABLE IF NOT EXISTS `tracked_link` (
  `tracked_link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `draft_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `action` int(1) NOT NULL,
  `list_id` int(4) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`tracked_link_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Table structure for table `tracked_links`
--

DROP TABLE IF EXISTS `tracked_links`;
CREATE TABLE `tracked_links` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `msg_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `action` varchar(255) NOT NULL,
  `target` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `tracked_link_click` (
`tracked_link_id` int(9) NOT NULL,
`email` varchar(255) NOT NULL,
`datetime` datetime NOT NULL,
UNIQUE KEY `tracked_link_id_2` (`tracked_link_id`,`email`),
KEY `tracked_link_id` (`tracked_link_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL auto_increment,
  `username` varchar(255) default NULL,
  `password` varchar(255) default NULL,
  `textpassword` varchar(255) default NULL,
  `db_host` varchar(255) NOT NULL default 'localhost',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Table structure for table `users_auth`
--

DROP TABLE IF EXISTS `users_auth`;
CREATE TABLE `users_auth` (
  `user_id` int(9) NOT NULL auto_increment,
  `username` varchar(50) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `text_password` varchar(80) NOT NULL default '',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `users_groups`
--

DROP TABLE IF EXISTS `users_groups`;
CREATE TABLE `users_groups` (
  `group_id` int(9) NOT NULL auto_increment,
  `name` varchar(50) NOT NULL default '',
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Table structure for table `users_groups_2_users`
--

DROP TABLE IF EXISTS `users_groups_2_users`;
CREATE TABLE `users_groups_2_users` (
  `group_id` int(9) NOT NULL default '0',
  `user_id` int(9) NOT NULL default '0',
  PRIMARY KEY  (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `users_permissions`
--

DROP TABLE IF EXISTS `users_permissions`;
CREATE TABLE `users_permissions` (
  `id` int(9) NOT NULL default '0',
  `id_type` enum('u','g') NOT NULL default 'u',
  `perm_key` varchar(50) NOT NULL default '',
  `level` int(4) NOT NULL default '1',
  `has` enum('y','n') NOT NULL default 'y',
  PRIMARY KEY  (`id`,`id_type`,`perm_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `users_profile_big_text`
--

DROP TABLE IF EXISTS `users_profile_big_text`;
CREATE TABLE `users_profile_big_text` (
  `user_id` int(9) NOT NULL default '0',
  `profile_key` varchar(50) NOT NULL default '',
  `text` text NOT NULL,
  KEY `user_id` (`user_id`,`profile_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `users_profile_text`
--

DROP TABLE IF EXISTS `users_profile_text`;
CREATE TABLE `users_profile_text` (
  `user_id` int(9) NOT NULL default '0',
  `profile_key` varchar(50) NOT NULL default '',
  `text` varchar(250) NOT NULL default '',
  KEY `profile_key` (`user_id`,`profile_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Table structure for table `users_session`
--

DROP TABLE IF EXISTS `users_session`;
CREATE TABLE `users_session` (
  `session_id` varchar(32) NOT NULL default '',
  `user_id` int(9) NOT NULL default '0',
  `last_activity` varchar(32) NOT NULL default '',
  `duration` int(9) NOT NULL default '0',
  PRIMARY KEY  (`session_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-12-28  9:14:57
