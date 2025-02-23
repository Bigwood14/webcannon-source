#!/usr/bin/php -q
<?php
DEFINE('CRON',1);
mysql_connect('localhost', 'root', 'cheese');
mysql_select_db('celibero');
//require_once(dirname(__FILE__) .'/../../no-web/core/include.php');

// get version
$sql 		= "SELECT * FROM `config` WHERE `KEY` = 'VERSION';";
$row 		= mysql_fetch_array(mysql_query($sql));
$version 	= (float)$row['value'];

$sql = array();

$sql[] = "CREATE TABLE IF NOT EXISTS `msg_to_rotated` (
	`msg_id` varchar(9) NOT NULL,
	`rotated_id` varchar(9) NOT NULL,
	`name` varchar(128) NOT NULL,
	PRIMARY KEY (`msg_id`,`rotated_id`))
	 ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "ALTER TABLE `msg` ADD `yahoo_body` TEXT NOT NULL , ADD `yahoo_date` VARCHAR( 255 ) NOT NULL, ADD `yahoo_date_original` VARCHAR( 255 ) NOT NULL ;";

$sql[] = "CREATE TABLE `celibero`.`msg_to_domain_2` (
`msg_id` INT( 9 ) NOT NULL ,
`domain` VARCHAR( 225 ) NOT NULL ,
`invert` INT( 0 ) NOT NULL
) ENGINE = MYISAM;";

$sql[] = "ALTER TABLE `msg` ADD `threads` INT( 9 ) NOT NULL ,
ADD `thread_wait` INT( 9 ) NOT NULL ;";

$sql[] = "ALTER TABLE `server_to_ip` ADD `aol` INT( 1 ) NOT NULL ;";

$sql[] = "CREATE TABLE IF NOT EXISTS `msg_complaint` (
`msg_id` int(9) NOT NULL,
`ip` varchar(255) NOT NULL,
`count` int(9) NOT NULL,
PRIMARY KEY  (`msg_id`,`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "
CREATE TABLE IF NOT EXISTS `clicks` (
`email` varchar(255) NOT NULL,
`sent` int(1) NOT NULL default '0',
UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "ALTER TABLE `msg_to_list` ADD `skip` INT( 9 ) NOT NULL DEFAULT '0';";

$sql[] = "CREATE TABLE IF NOT EXISTS `aol_ratio` (
`aol_ratio_id` int(9) NOT NULL auto_increment,
`ip` varchar(255) NOT NULL,
`date` datetime NOT NULL,
`ratio` varchar(40) NOT NULL,
`message` text NOT NULL,
`read` int(1) NOT NULL default '0',
PRIMARY KEY  (`aol_ratio_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";

$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_confirmation_code` VARCHAR( 255 ) NOT NULL;";
$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_date` VARCHAR( 60 ) NOT NULL;";
$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_ratio` VARCHAR( 20 ) NOT NULL;";
$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_deny` VARCHAR( 20 ) NOT NULL ;";
$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_fl` INT( 1 ) NOT NULL DEFAULT '0';";
$sql[] = "ALTER TABLE `users_auth` ADD `mailer` INT( 0 ) NOT NULL DEFAULT '0';";
$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_fl_date` VARCHAR( 60 ) NOT NULL;";
$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_fl_code` VARCHAR( 255 ) NOT NULL ;";
$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_link` VARCHAR( 255 ) NOT NULL ;";
$sql[] = "ALTER TABLE `server_to_ip` ADD `aol_fl_link` VARCHAR( 255 ) NOT NULL ;";
$sql[] = "ALTER TABLE `msg` ADD `from_domain` VARCHAR( 255 ) NOT NULL ;";
$sql[] = "ALTER TABLE `sgdne` ADD `last_checkin_bounce` DATETIME NOT NULL , ADD `last_email_bounce` VARCHAR( 255 ) NOT NULL ;";

$sql[] = "CREATE TABLE IF NOT EXISTS `tracked_link_click` (
`tracked_link_id` int(9) NOT NULL,
`email` varchar(255) NOT NULL,
`datetime` datetime NOT NULL,
UNIQUE KEY `tracked_link_id_2` (`tracked_link_id`,`email`),
KEY `tracked_link_id` (`tracked_link_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "ALTER TABLE `users_auth` ADD `ips` TEXT NOT NULL ;";

$sql[] = "CREATE TABLE `domain_group` (
`domain_group_id` INT( 9 ) NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `domain_group_id` )
) ENGINE = MYISAM;";

$sql[] = "ALTER TABLE `server_to_ip` ADD `domain_group_id` INT( 9 ) NOT NULL ;";
$sql[] = "ALTER TABLE `msg` ADD `user_id` INT( 9 ) NOT NULL;";
$sql[] = "ALTER TABLE `msg` ADD `aol_rotate` INT( 9 ) NOT NULL DEFAULT '0';";

$sql[] = "CREATE TABLE `pages` (
`page_id` int(9) NOT NULL auto_increment,
`url` varchar(255) NOT NULL,
`content` text NOT NULL,
`type` int(9) NOT NULL,
PRIMARY KEY  (`page_id`)
) ENGINE=MyISAM;";

$sql[] = "ALTER TABLE `user`
ADD `remote_list_id` INT( 9 ) NOT NULL,
ADD `remote_hostname` VARCHAR( 255 ) NOT NULL ,
ADD `remote_username` VARCHAR( 255 ) NOT NULL ,
ADD `remote_password` VARCHAR( 255 ) NOT NULL ;";

$sql[] = "CREATE TABLE `celibero`.`list` (
`id` int( 11 ) NOT NULL AUTO_INCREMENT ,
`username` varchar( 255 ) default NULL ,
`password` varchar( 255 ) default NULL ,
`textpassword` varchar( 255 ) default NULL ,
`db_host` varchar( 255 ) NOT NULL default 'localhost',
`remote_hostname` varchar( 255 ) NOT NULL ,
`remote_username` varchar( 255 ) NOT NULL ,
`remote_password` varchar( 255 ) NOT NULL ,
`remote_list_id` int( 9 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM DEFAULT CHARSET = latin1;";

$sql[] = "INSERT INTO `celibero`.`list`
SELECT *
FROM `celibero`.`user` ;";

$sql[] = "DROP TABLE `celibero`.`user` ;";

$sql[] = "ALTER TABLE `list` CHANGE `id` `list_id` INT( 11 ) NOT NULL AUTO_INCREMENT ;";

$sql[] = "ALTER TABLE `list` DROP `password` ";

$sql[] = "ALTER TABLE `list`
DROP `textpassword`,
DROP `db_host`;";

$sql[] = " CREATE TABLE `celibero`.`list_log` (
`list_log_id` INT( 9 ) NOT NULL AUTO_INCREMENT ,
`list_id` INT( 9 ) NOT NULL ,
`date` DATETIME NOT NULL ,
`message` TEXT NOT NULL ,
PRIMARY KEY ( `list_log_id` )
) ENGINE = MYISAM ";

$sql[] = "ALTER TABLE `list` ADD `remote_position` TEXT NOT NULL ,
ADD `remote_position_unsub` TEXT NOT NULL ;";

$sql[] = "ALTER TABLE `list` ADD `remote_position_send_unsub` TEXT NOT NULL ,
ADD `send_unsubs` INT( 1 ) NOT NULL DEFAULT '0';";


$sql[] = "ALTER TABLE `list` CHANGE `username` `name` VARCHAR( 255 ) NULL DEFAULT NULL;";

$sql[] = "ALTER TABLE `msg` ADD `aol_check_total` INT( 3 ) NOT NULL ,
ADD `aol_check_hits` INT( 3 ) NOT NULL ;";

$sql[] = "ALTER TABLE `clicks` ADD `remote_list_id` INT( 9 ) NOT NULL ,
ADD `remote_sent` INT( 1 ) NOT NULL ;";

$sql[] = "ALTER TABLE `clicks` DROP INDEX `email`";

$sql[] = "ALTER TABLE `clicks` ADD `click_id` INT( 9 ) NOT NULL FIRST ;";

$sql[] = "ALTER TABLE `clicks` ADD PRIMARY KEY ( `click_id` ) ;";

$sql[] = "ALTER TABLE `clicks` CHANGE `click_id` `click_id` INT( 9 ) NOT NULL AUTO_INCREMENT;";

$sql[] = "ALTER TABLE `clicks` ADD UNIQUE `e` (`email`, `remote_list_id`);";

$sql[] = "ALTER TABLE `clicks` ADD INDEX `rs` (`remote_list_id`, `remote_sent`);";

$sql[] = "ALTER TABLE `msg` ADD `top` INT( 2 ) NOT NULL ;";

$sql[] = "ALTER TABLE `msg` ADD INDEX ( `top` ) ;";

$sql[] = "CREATE TABLE IF NOT EXISTS `content_book` (
  `content_book_id` int(9) NOT NULL auto_increment,
  `book` int(9) NOT NULL,
  `content` text NOT NULL,
  PRIMARY KEY  (`content_book_id`),
  KEY `book` (`book`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

if ($version < 4.2)
	$sql[] = "UPDATE `list` SET `remote_position` = '';";

$sql[] = "REPLACE INTO `config` (`KEY`, `value`) VALUES ('VERSION', '4.2.0')";

$sql[] = "CREATE TABLE IF NOT EXISTS `opens` (
	`open_id` int(9) NOT NULL auto_increment,
	`schedule_id` int(9) NOT NULL,
	`email` varchar(255) NOT NULL,
	`date` datetime NOT NULL,
	PRIMARY KEY  (`open_id`),
	UNIQUE KEY `schedule_id` (`schedule_id`,`email`),
	KEY `date` (`date`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

$sql[] = "ALTER TABLE `msg` ADD `open_action` INT( 1 ) NOT NULL , ADD `open_list_id` INT( 1 ) NOT NULL ;";

$sql[] = "ALTER TABLE `msg` ADD `embed_images` TINYINT NOT NULL DEFAULT '0';";

$sql[] = "CREATE TABLE `seed_account` (
`seed_account_id` INT( 9 ) NOT NULL AUTO_INCREMENT ,
`username` VARCHAR( 255 ) NOT NULL ,
`password` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `seed_account_id` )
) TYPE = MYISAM; ";

$sql[] = "ALTER TABLE `msg` ADD `seed_rotate` INT( 9 ) NOT NULL ;";

$sql[] = "CREATE TABLE `msg_seed_uid` (
`msg_id` INT( 9 ) NOT NULL ,
`uid` VARCHAR( 255 ) NOT NULL ,
UNIQUE (`msg_id`, `uid`),
INDEX ( `msg_id` )
) TYPE = MYISAM;";

$sql[] = "ALTER TABLE `msg_to_ip` ADD `spam_seed_count` INT( 9 ) NOT NULL ;";

$sql[] = "ALTER TABLE `msg_to_ip` ADD `removed` TINYINT NOT NULL ;";

$sql[] = "CREATE TABLE `msg_complaint_log` (
`msg_complaint_log_id` INT( 9 ) NOT NULL AUTO_INCREMENT ,
`msg_id` INT( 9 ) NOT NULL ,
`ip` INT UNSIGNED NOT NULL ,
`domain` VARCHAR( 255 ) NOT NULL ,
`date` DATETIME NOT NULL ,
`date_sent` DATETIME NOT NULL ,
PRIMARY KEY ( `msg_complaint_log_id` ) ,
INDEX ( `ip` , `date` )
) TYPE = MYISAM;";

$sql[] = "CREATE TABLE `del_success_stats` (
`date` DATETIME NOT NULL ,
`type` INT NOT NULL ,
`ip` INT UNSIGNED NOT NULL ,
`count` INT NOT NULL ,
PRIMARY KEY ( `date` , `type`, `ip` )
) TYPE = MYISAM;";

$sql[] = "ALTER TABLE `tracked_link_click` ADD `ip` VARCHAR( 255 ) NOT NULL ;";
$sql[] = "ALTER TABLE `tracked_link_click` ADD `ref` VARCHAR( 255 ) NOT NULL ;";
$sql[] = "ALTER TABLE `imports` ADD `md5` INT NOT NULL ;";
$sql[] = "ALTER TABLE `supression_lists` ADD `has_md5` INT NOT NULL ;";
$sql[] = "ALTER TABLE `msg_to_list` ADD `max` INT NOT NULL ;";
$sql[] = "ALTER TABLE `msg` ADD `max_per_ip` INT NOT NULL ;";

foreach ($sql as $sq)
{
//	$db->Execute($sq);
	mysql_query($sq);
}

exec('/bin/chmod 777 /www/celibero/img');

$sql 	= "SELECT * FROM `celibero`.`list`;";
$result = mysql_query($sql);

while ($row = mysql_fetch_assoc($result))
{
	//$sql = "RENAME DATABASE `{$row['name']}` TO `celibero_list_{$row['list_id']}`;";
	//mysql_query($sql);
	//print mysql_error();
	if (is_dir("/var/lib/mysql/{$row['name']}"))
		exec("mv /var/lib/mysql/{$row['name']} /var/lib/mysql/celibero_list_{$row['list_id']}");
}

exec("/sbin/service mysqld restart");

// remote list cron
$cron 		= "20 * * * * /www/celibero/no-web/crons/hour/remote_list.php >/dev/null 2>&1";
$cron_2 	= "* * * * * /usr/bin/php /www/celibero/no-web/crons/minute/spam_check.php >/dev/null 2>&1";
$contents 	= file_get_contents('/var/spool/cron/root');

if (strpos($contents, 'remote_list.php') === false)
{
	exec("echo '$cron' >> /var/spool/cron/root");
	exec("/sbin/service crond restart");
}

if (strpos($contents, 'spam_check.php') === false)
{
	exec("echo '$cron_2' >> /var/spool/cron/root");
	exec("/sbin/service crond restart");
}

exec('/bin/chmod 777 /www/celibero/no-web/crons/hour/remote_list.php');
exec('/usr/bin/mysql -uroot -pcheese celibero < /www/celibero/install/install-files/book.sql');

print "done\n";
?>
