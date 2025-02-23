-- phpMyAdmin SQL Dump
-- version 2.11.3
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 06, 2008 at 12:32 PM
-- Server version: 5.1.22
-- PHP Version: 5.2.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `celibero`
--

-- --------------------------------------------------------

--
-- Table structure for table `abuse_forms`
--

CREATE TABLE IF NOT EXISTS `abuse_forms` (
  `abuse_id` int(9) NOT NULL AUTO_INCREMENT,
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `email` varchar(120) NOT NULL DEFAULT '',
  `first_name` varchar(30) NOT NULL DEFAULT '',
  `last_name` varchar(30) NOT NULL DEFAULT '',
  `state` varchar(30) NOT NULL DEFAULT '',
  `country` varchar(30) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `comments` text NOT NULL,
  PRIMARY KEY (`abuse_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `abuse_forms`
--


-- --------------------------------------------------------

--
-- Table structure for table `bounce`
--

CREATE TABLE IF NOT EXISTS `bounce` (
  `email` varchar(225) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`email`),
  KEY `count` (`count`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bounce`
--


-- --------------------------------------------------------

--
-- Table structure for table `bouncer`
--

CREATE TABLE IF NOT EXISTS `bouncer` (
  `email` varchar(225) NOT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `bouncer`
--


-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE IF NOT EXISTS `categories` (
  `category_id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`category_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `title`) VALUES
(1, 'Calling Cards'),
(2, 'Credit Improvement'),
(3, 'Gizmos / Toys'),
(4, 'Dating'),
(5, 'Electronics'),
(6, 'Food and Wine'),
(7, 'Giveaways'),
(8, 'Health and Wellness'),
(9, 'Home Improvement'),
(10, 'InkJet'),
(11, 'Jewelry'),
(12, 'Loans and Finances'),
(13, 'MLM'),
(14, 'PC Improvements'),
(15, 'Pets and Pet Supplies'),
(16, 'Prescriptions'),
(17, 'Small Business'),
(18, 'Software B2C'),
(19, 'Software B2B'),
(20, 'Weight Loss');

-- --------------------------------------------------------

--
-- Table structure for table `commands`
--

CREATE TABLE IF NOT EXISTS `commands` (
  `command_id` smallint(9) NOT NULL AUTO_INCREMENT,
  `command` text NOT NULL,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `state` tinyint(1) NOT NULL DEFAULT '0',
  `output` text NOT NULL,
  `return` text NOT NULL,
  `type` enum('shell','mysql') NOT NULL DEFAULT 'shell',
  PRIMARY KEY (`command_id`),
  KEY `date` (`date`),
  KEY `state` (`state`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=25 ;

--
-- Dumping data for table `commands`
--

INSERT INTO `commands` (`command_id`, `command`, `date`, `state`, `output`, `return`, `type`) VALUES
(22, 'chown upload:upload -R /home/upload/import', '2008-03-05 19:50:59', 2, 'a:0:{}', '1', 'shell'),
(23, 'echo ''cyberdummy.co.uk'' >> /var/qmail/control/locals; echo ''cyberdummy.co.uk'' >> /var/qmail/control/rcpthosts;svc -h /service/qmail-send;', '2008-03-05 21:10:33', 2, 'a:0:{}', '127', 'shell'),
(24, 'chown upload:upload -R /home/upload/import', '2008-03-15 10:07:16', 2, 'a:0:{}', '1', 'shell');

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE IF NOT EXISTS `config` (
  `KEY` varchar(30) NOT NULL DEFAULT '',
  `value` text NOT NULL,
  PRIMARY KEY (`KEY`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`KEY`, `value`) VALUES
('BOUNCE_PRUNE', '0'),
('GOODBYE_EMAIL', 'Goodbye.'),
('ADDRESS', 'ADDRESS'),
('CAN_SPAM', '<h1>Can Spam Act of 2003 Compliancy:</h1>\n\n<p>This document summarizes {domain}''s strict interpretation of the\n  \n"Can Spam Act of 2003" and the role it plays on our company\n  and our clients.\n</p>\n\n<p>{domain} follows all Mandates outlined in the Can Spam Act\n   of 2003. In order to provide our clients a better service we are happy\n   to follow all requirements and guidelines declared by the government.\n   "Can Spam Act of 2003 is a broad explanation of rules and requirement;\n   {domain} insists on taking the strictness interpretation.\n</p>\n\n<h1>False Representation </h1>\n\n<p>{domain} will not transfer any commercial email that has header\n  information that was obtained by means of false representation. This\n  includes an originating electronic mail address, domain name, or Internet\n  Protocol address.\n</p>\n\n<h1>"From Line" </h1>\n\n<p>{domain} will use a "from line" that declares\n  {domain} the advertiser, or any other party responsible for originating\n  or transmitting a commercial electronic mail message.\n</p>\n\n<h1>Misleading Header Information </h1>\n\n<p>{domain} will not use, nor work with a third party that uses\n  another to relay or transmit the message for the purpose of disguising\n  its origin. {domain} shall not transmit any commercial email\n  with misleading header information. {domain}, Inc. will require\n  accurate information identifying the computer used to initiate the message.\n</p>\n\n<h1>Misleading Subject Line </h1>\n       \n<p>{domain}, Inc. will not transmit commercial email if it believes\n  that a subject heading of the email would be likely to mislead a recipient\n  regarding the contents or subject matter of the message.\n</p>\n\n<h1>Opt-out Mechanism </h1>\n\n<p>{domain} will provide the recipient of a commercial email\n   message an obvious and distinct "opt-out" option. A recipient\n   may choose this option to submit an Internet based request not to receive\n   future commercial emails from any party advertised or promoted by the\n   message.\n</p>\n\n<p>In the case that more than one party advertises in a single commercial\n  email, {domain} will require that the message have an obvious\n  "opt-out" device for each party.\n</p>\n<br />\n\n<p>{domain} requires all clients to share any requests not to\n  receive messages from any recipients.\n</p>\n       \n       \n<h1>Opt-out Services </h1>\n       \n<p>{domain} will provide services to any of its clients to support\n  an Internet-based "opt-out" device, if the client agrees to\n  the following:\n<ul>\n       <li>{domain} requires exclusive authority to distribute such\n           party''s commercial email messages. {domain} will manage all\n           Internet-based "opt-out" mechanisms for the advertiser.\n           Advertisers will agree to have its offers exclusively hosted in {domain}''s\n           system.\n       </li>\n\n       <li>Advertisers must provide an accurate physical address so that {domain} may include it in each commercial email. {domain} will\n           also provide a physical address in each commercial email.\n       </li>\n</ul>\n</p>\n\n<h1>Detailed Declaration </h1>\n\n<p>{domain}, Inc. will provide an obvious and detailed declaration\n  in each commercial email explaining that the message is an advertisement\n  or solicitation. All commercial electronic mail messages that {domain} transmits to recipients are done so only with the consent of each\n  recipient.\n</p>\n<br />\n       \n<h1>Physical Address </h1>\n       \n<p>{domain}, Inc. will always include a valid physical address for\n  {domain}, Inc. and the postal address of any client who is advertised\n  or promoted in a commercial email.\n</p>\n\n<br />\n       \n<h1>Electronic Mail Address Collection </h1>\n       \n<p>{domain} will not initiate the sending of an electronic mail\n  message to a recipient whose address was obtained via automated means.\n  {domain} will not give, sell, or transfer any address if statement\n  was provided assuring a unique relationship with {domain}, Inc.\n  {domain} does not generate automated electronic mail addresses\n   by randomly combining names, letters, or numbers for the purpose of\n   illicitly collecting recipients'' electronic mail addresses.\n</p>\n<br />\n       \n<h1>Sexually Oriented Material </h1>\n       \n<p>{domain}, Inc. is not currently nor has it ever been in the business\n  of transmitting electronic mail containing sexually oriented material.\n</p>\n\n<p>{domain} has always had a perfect record and has never endorsed\n  fraudulent practices. Furthermore {domain} expressly forbids\n  unauthorized transmission of any messages on behalf of our customers\n  including but not limited to the following:\n\n       <ul>\n   <li>Use any of falsified electronic mail transmission information. </li>\n       <li>Automated means to register online user accounts. </li>\n       <li>Transmission of any commercial electronic mail from or through a computer\n           that has been accessed without authorization. </li>\n       <li>Transmission of commercial email with intent to disguise the actual\n           origin of the email or to falsify the identity of the actually registrant.</li>\n       <li>We at {domain} are excited about the new law because it is\n           a ground breaking achievement in the fight against spam, and will help\n           us do business more effectively. </li>\n   </ul>\n</p>'),
('PRIVACY_POLICY', 'This is the web site of {domain}<br />\n               <br />\n               Our postal address is {address}<br />\n               <strong><br />\n               We can be reached via e-mail at abuse "at" {domain}\n               or check out our Abuse / Unsolicited Commercial <a href="/abuse.php">Email\n               Reporting Page </a></strong><br />\n\n               <br />\n               For each visitor to our Web page, our Web server automatically\n               recognizes only the consumer''s domain name, but not the e-mail\n               address (where possible). <br />\n               <br />\n               We collect only the domain name, but not the e-mail address of\n               visitors to our Web page, the e-mail addresses of those who post\n               messages to our bulletin board, the e-mail addresses of those\n               who communicate with us via e-mail, the e-mail addresses of those\n               who make postings to our chat areas, aggregate information on\n               what pages consumers access or visit, user-specific information\n               on what pages consumers access or visit, information volunteered\n               by the consumer, such as survey information and/or site registrations.\n               <br />\n               <br />\n               The information we collect is used to improve the content of our\n               Web page, used to customize the content and/or layout of our page\n               for each individual visitor, used to notify consumers about updates\n               to our Web site, shared with other reputable organizations to\n               help them contact consumers for marketing purposes. <br />\n               <br />\n\n               With respect to cookies: We use cookies to store visitors preferences,\n               record session information, such as items that consumers add to\n               their shopping cart, record user-specific information on what\n               pages users access or visit, alert visitors to new areas that\n               we think might be of interest to them when they return to our\n               site, record past activity at a site in order to provide better\n               service when visitors return to our site , ensure that visitors\n               are not repeatedly sent the same banner ads, customize Web page\n               content based on visitors'' browser type or other information that\n               the visitor sends. <br />\n               <br />\n               If you do not want to receive e-mail from us in the future, please\n               let us know by sending us e-mail at the above address, writing\n               to us at the above address. <br />\n               <br />\n               From time to time, we make the e-mail addresses of those who access\n               our site available to other reputable organizations whose products\n               or services we think you might find interesting. If you do not\n               want us to share your e-mail address with other companies or organizations,\n               please let us know by calling us at the number provided above,\n               writing to us at the above address. <br />\n               <br />\n               From time to time, we make our customer e-mail list available\n               to other reputable organizations whose products or services we\n               think you might find interesting. If you do not want us to share\n               your e-mail address with other companies or organizations, please\n               let us know by calling us at the number provided above, writing\n               to us at the above address. <br />\n\n               <br />\n               If you supply us with your postal address on-line you may receive\n               periodic mailings from us with information on new products and\n               services or upcoming events. If you do not wish to receive such\n               mailings, please let us know by calling us at the number provided\n               above, writing to us at the above address. <br />\n               <br />\n               you may receive mailings from other reputable companies. You can,\n               however, have your name put on our do-not-share list by calling\n               us at the number provided above, writing to us at the above address.\n               <br />\n               <br />\n               Please provide us with your exact name and address. We will be\n               sure your name is removed from the list we share with other organizations\n               <br />\n               <br />\n\n               Persons who supply us with their telephone numbers on-line may\n               receive telephone contact from us with information regarding new\n               products and services or upcoming events. If you do not wish to\n               receive such telephone calls, please let us know by sending us\n               e-mail at the above address, writing to us at the above address.\n               <br />\n               <br />\n               Persons who supply us with their telephone numbers on-line may\n               receive telephone contact from other reputable companies. You\n               can, however, have your name put on our do-not-share list by ,\n               sending us e-mail at the above address, writing to us at the above\n               address. <br />\n               <br />\n               Please provide us with your name and phone number. We will be\n               sure your name is removed from the list we share with other organizations\n               With respect to Ad Servers: To try and bring you offers that are\n               of interest to you, we have relationships with other companies\n               that we allow to place ads on our Web pages. As a result of your\n               visit to our site, ad server companies may collect information\n               such as your domain type, your IP address and clickstream information.\n               For further information, consult the privacy policies of: <br />\n               <br />\n               From time to time, we may use customer information for new, unanticipated\n               uses not previously disclosed in our privacy notice. If our information\n               practices change at some time in the future we will post the policy\n               changes to our Web site to notify you of these changes and provide\n               you with the ability to opt out of these new uses. If you are\n               concerned about how your information is used, you should check\n               back at our Web site periodically. <br />\n\n               <br />\n               Customers may prevent their information from being used for purposes\n               other than those for which it was originally collected by calling\n               us at the number provided above. <br />\n               <br />\n               Upon request we provide site visitors with access to no information\n               that we have collected and that we maintain about them. <br />\n               <br />\n               Upon request we offer visitors no ability to have factual inaccuracies\n               corrected in information that we maintain about them <br />\n               <br />\n\n               With respect to security: We have appropriate security measures\n               in place in our physical facilities to protect against the loss,\n               misuse or alteration of information that we have collected from\n               you at our site. <br />\n               <br />\n               <br />'),
('TEXT_HEADER', ''),
('HTML_HEADER', ''),
('HTML_FOOTER', '<br /><br />\n<table>\n    <tr>\n      <td rowspan="2"><a href="http://{{dn}}/privacy-policy.php"><img src="http://{{dn}}/images/mail/left.gif" width="172" height="96" border="0" /></a></td>\n      <td align="left"><b>{{dn}}</b><br /><font size="1">ADDRESS</font></td>\n    </tr>\n    <tr>\n      <td valign="bottom"><a href="http://{{dn}}/index.php"><img src="http://{{dn}}/images/mail/right.gif" width="304" height="34" border="0" /></a></td>\n    </tr>\n</table>\n</body>\n</html>'),
('TEXT_FOOTER', '\n\n==============================================================\n{{dn}} never sends unsolicited email. {{dn}} has been\ngiven the right to market to you through our Web site partners and their\nprivacy policies. Specifically, you are receiving this correspondence\nbecause you have provided permission (via your registration and\nacceptance of the privacy policies for a newsletter, contest, web-based\nservice or other activity on the web) to receive recurring promotions or\noffers from various third parties.\n==============================================================\n* To be removed from this mailing list, click or follow this link:\nhttp://{{dn}}/index.php?l={{03}}&e={{01}}\nThis message was sent to: {{01}}\nX-{{dn}}-Recipient: {{01}}\nX-{{dn}}-Userid: {{03}}\n{{dn}} \nADDRESS // x{{02}}x{{03}}x\n{{dn}} is not responsible for third party offers, services or\nproducts and makes no representations or warranties regarding them. \n'),
('VERSION', '3.0'),
('DEFAULT_TESTS', ''),
('HEADERS', 'X-Userid: {{01}}\r\nX-ID: {{02}}\r\nX-Recipient: {{01}}\r\nTo: {{01}}\r\nDate: {{header_date}}\r\nMessage-ID: <{{header_mi}}@{{dn}}>\r\nX-{{dn}}-MsgID: {{02}}-{{03}}\r\nSubject: {{sl}}'),
('UNSUB_REDIR', ''),
('BOUNCE_IGNORE', 'Excessive unknown recipients\nResources temporarily unavailable\nsocket socket'),
('ENGINE_CT', '30'),
('ENGINE_RT', '10'),
('ENGINE_DNS', '127.0.0.1'),
('ENGINE_MSC', '1000'),
('COMPLAINT_EMAIL', '');

-- --------------------------------------------------------

--
-- Table structure for table `crons`
--

CREATE TABLE IF NOT EXISTS `crons` (
  `name` varchar(40) NOT NULL DEFAULT '',
  `last_checkin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`name`),
  KEY `last_checkin` (`last_checkin`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `crons`
--


-- --------------------------------------------------------

--
-- Table structure for table `delivery_configuration`
--

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `delivery_configuration`
--

INSERT INTO `delivery_configuration` (`delivery_configuration_id`, `name`, `header`, `encoding_text`, `encoding_html`, `encoding_aol`, `boundry_prefix`, `boundry_postfix`, `charset_head`, `charset_text`, `charset_html`, `charset_aol`) VALUES
(1, 'Name', 'Received: from {{dn}} [{{ip}}] by {{dn}} [{{ip}}]\r\nMessage-ID: {{y0206}}\r\nMIME-Version: 1.0\r\nX-Originating-IP: [{{ip}}]\r\nX-Originating-Email: [supportdept@{{dn}}]\r\nX-Sender: HelpCenter@{{dn}}\r\nFrom: {{fl}}\r\nReply-To: {{fl}}\r\nTo: {{01}}\r\nCc: <{{y0410}}@{{dn}}>\r\nSubject: {{sl}}\r\nDate: \r\nErrors-To: {{fl}}\r\nThread-Index: {{y3030}}==\r\nRouting-path: {{y0710}}.{{y1619}}=', '7bit', '7bit', '7bit', '-=', 'ghu', 'ISO-8859-1', 'ISO-8859-1', 'ISO-8859-1', 'ISO-8859-1'),
(2, 'Test', 'Headers', 'enc text', 'enc html', 'end aol', 'b pre', '', 'char head', 'char text', 'char html', '');

-- --------------------------------------------------------

--
-- Table structure for table `dr_hosts`
--

CREATE TABLE IF NOT EXISTS `dr_hosts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `address` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'pending',
  `message` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `dr_hosts`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_to_category`
--

CREATE TABLE IF NOT EXISTS `email_to_category` (
  `email` varchar(120) NOT NULL DEFAULT '0',
  `category_id` int(9) NOT NULL DEFAULT '0',
  `open` enum('1','0') NOT NULL DEFAULT '0',
  `click` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`email`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `email_to_category`
--


-- --------------------------------------------------------

--
-- Table structure for table `email_to_sup`
--

CREATE TABLE IF NOT EXISTS `email_to_sup` (
  `email` varchar(120) NOT NULL DEFAULT '',
  `sup_list_id` int(9) NOT NULL DEFAULT '0',
  `domain` enum('0','1') NOT NULL DEFAULT '0',
  PRIMARY KEY (`email`,`sup_list_id`),
  KEY `sup_list_id` (`sup_list_id`),
  KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `email_to_sup`
--


-- --------------------------------------------------------

--
-- Table structure for table `errors`
--

CREATE TABLE IF NOT EXISTS `errors` (
  `error_id` int(9) NOT NULL AUTO_INCREMENT,
  `from` varchar(50) NOT NULL DEFAULT '',
  `error` text NOT NULL,
  `state` enum('n','d') NOT NULL DEFAULT 'n',
  PRIMARY KEY (`error_id`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `errors`
--


-- --------------------------------------------------------

--
-- Table structure for table `export`
--

CREATE TABLE IF NOT EXISTS `export` (
  `export_id` int(9) NOT NULL AUTO_INCREMENT,
  `state` int(2) NOT NULL DEFAULT '0',
  `name` varchar(100) NOT NULL DEFAULT '',
  `progress` int(9) NOT NULL DEFAULT '0',
  `type` int(2) NOT NULL DEFAULT '0',
  `list-cat` varchar(100) NOT NULL DEFAULT '',
  `total` int(9) NOT NULL DEFAULT '0',
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `openers` enum('y','n','a') NOT NULL,
  `clickers` enum('y','n','a') NOT NULL,
  `import_id` int(9) NOT NULL DEFAULT '0',
  `subscribed` enum('y','n') NOT NULL DEFAULT 'n',
  `unsubscribed` enum('y','n') NOT NULL DEFAULT 'n',
  `bounce_s` enum('y','n') NOT NULL DEFAULT 'n',
  `bounce_h` enum('y','n') NOT NULL DEFAULT 'n',
  `where` varchar(225) NOT NULL,
  `slog_where` varchar(225) NOT NULL,
  PRIMARY KEY (`export_id`),
  KEY `ts` (`ts`),
  KEY `state` (`state`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `export`
--


-- --------------------------------------------------------

--
-- Table structure for table `extra_content`
--

CREATE TABLE IF NOT EXISTS `extra_content` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `content_type` varchar(255) NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=13 ;

--
-- Dumping data for table `extra_content`
--

INSERT INTO `extra_content` (`id`, `name`, `content_type`, `is_default`) VALUES
(12, 'Footer', 'footer', 0);

-- --------------------------------------------------------

--
-- Table structure for table `extra_content_data`
--

CREATE TABLE IF NOT EXISTS `extra_content_data` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `content_id` int(11) NOT NULL,
  `content_format` varchar(255) NOT NULL,
  `data` text,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `extra_content_data`
--

INSERT INTO `extra_content_data` (`id`, `content_id`, `content_format`, `data`) VALUES
(19, 12, 'text', 'Footer Text'),
(20, 12, 'html', 'HTML Footer');

-- --------------------------------------------------------

--
-- Table structure for table `global_unsub`
--

CREATE TABLE IF NOT EXISTS `global_unsub` (
  `ts` datetime DEFAULT NULL,
  `address` varchar(64) NOT NULL DEFAULT '',
  `how` int(2) DEFAULT '0',
  `global_action` enum('0','1','2','3','4') NOT NULL DEFAULT '0',
  PRIMARY KEY (`address`),
  KEY `a` (`address`(10))
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `global_unsub`
--


-- --------------------------------------------------------

--
-- Table structure for table `global_unsub_domain`
--

CREATE TABLE IF NOT EXISTS `global_unsub_domain` (
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `domain` varchar(50) NOT NULL DEFAULT '',
  `how` int(1) NOT NULL DEFAULT '0',
  `global_action` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`domain`),
  KEY `ts` (`ts`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `global_unsub_domain`
--


-- --------------------------------------------------------

--
-- Table structure for table `global_words`
--

CREATE TABLE IF NOT EXISTS `global_words` (
  `word_id` int(9) NOT NULL AUTO_INCREMENT,
  `word` varchar(50) NOT NULL DEFAULT '',
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `how` tinyint(2) NOT NULL DEFAULT '0',
  `global_action` tinyint(2) NOT NULL DEFAULT '0',
  PRIMARY KEY (`word_id`),
  UNIQUE KEY `word` (`word`),
  KEY `how` (`how`,`global_action`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `global_words`
--


-- --------------------------------------------------------

--
-- Table structure for table `imports`
--

CREATE TABLE IF NOT EXISTS `imports` (
  `import_id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `format` text NOT NULL,
  `ts` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `file` varchar(100) NOT NULL DEFAULT '',
  `state` varchar(30) NOT NULL DEFAULT '',
  `list` varchar(50) NOT NULL DEFAULT '',
  `delim` varchar(4) NOT NULL DEFAULT ',',
  `added` int(11) NOT NULL DEFAULT '0',
  `invalid` int(11) NOT NULL DEFAULT '0',
  `dups` int(11) NOT NULL DEFAULT '0',
  `unsub` int(11) NOT NULL DEFAULT '0',
  `unsub_g` int(11) NOT NULL DEFAULT '0',
  `unsub_d` int(11) NOT NULL DEFAULT '0',
  `filtered` int(9) NOT NULL DEFAULT '0',
  `total` int(11) NOT NULL DEFAULT '0',
  `type` varchar(30) NOT NULL DEFAULT '',
  `type_id` int(2) NOT NULL DEFAULT '0',
  `overwrite` tinyint(2) NOT NULL DEFAULT '0',
  `dedupe` text NOT NULL,
  PRIMARY KEY (`import_id`),
  KEY `ts` (`ts`),
  KEY `state` (`state`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=9 ;

--
-- Dumping data for table `imports`
--


-- --------------------------------------------------------

--
-- Table structure for table `links`
--

CREATE TABLE IF NOT EXISTS `links` (
  `link_id` int(9) NOT NULL AUTO_INCREMENT,
  `msg_id` int(9) NOT NULL DEFAULT '0',
  `URL` varchar(255) NOT NULL DEFAULT '',
  `count` int(9) NOT NULL DEFAULT '0',
  `dummy` int(1) NOT NULL DEFAULT '0',
  `img` enum('0','1') NOT NULL DEFAULT '0',
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`link_id`),
  KEY `msg_id` (`msg_id`),
  KEY `dummy` (`dummy`),
  KEY `date` (`date`),
  KEY `img` (`img`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=63 ;

--
-- Dumping data for table `links`
--

INSERT INTO `links` (`link_id`, `msg_id`, `URL`, `count`, `dummy`, `img`, `date`) VALUES
(1, 7, 'http://www.gopher.com', 0, 1, '0', '2008-03-05 19:18:35'),
(2, 8, 'http://www.gopher.com', 0, 1, '0', '2008-03-05 21:05:02'),
(3, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-05 21:48:28'),
(4, 12, 'http://www.gopher.com', 1, 0, '0', '2008-03-05 21:48:32'),
(5, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:09:27'),
(6, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:13:04'),
(7, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:13:16'),
(8, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:13:54'),
(9, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:25:15'),
(10, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:25:15'),
(11, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:26:13'),
(12, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:27:07'),
(13, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:27:35'),
(14, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:27:53'),
(15, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:28:40'),
(16, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:30:06'),
(17, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:30:44'),
(18, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 14:31:58'),
(19, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 15:08:31'),
(20, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 15:08:31'),
(21, 1, 'http://www.gopher.com', 0, 0, '0', '2008-03-09 15:08:44'),
(22, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-09 15:09:10'),
(23, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-15 09:56:36'),
(24, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-15 09:56:48'),
(25, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-15 09:56:48'),
(26, 12, 'http://www.gopher.com', 0, 1, '0', '2008-03-15 09:57:48'),
(27, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-29 20:51:16'),
(28, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-29 20:51:16'),
(29, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-29 20:52:30'),
(30, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-29 20:52:30'),
(31, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-29 20:53:30'),
(32, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-29 20:53:30'),
(33, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 13:53:28'),
(34, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 13:53:28'),
(35, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 13:55:43'),
(36, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 13:55:43'),
(37, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:00:37'),
(38, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:00:37'),
(39, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:00:48'),
(40, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:00:48'),
(41, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:00:51'),
(42, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:00:51'),
(43, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:01:13'),
(44, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:01:13'),
(45, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:02:01'),
(46, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:02:01'),
(47, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:26:17'),
(48, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:26:17'),
(49, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:29:26'),
(50, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:29:26'),
(51, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:32:01'),
(52, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:32:01'),
(53, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:32:27'),
(54, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:32:27'),
(55, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:41:10'),
(56, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:41:10'),
(57, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:44:03'),
(58, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:44:03'),
(59, 46, 'http://www.greatoffer.com/a/23/s', 0, 1, '0', '2008-03-30 14:44:30'),
(60, 46, 'http://www.google.com/logo.gif', 0, 1, '1', '2008-03-30 14:44:30'),
(61, 46, 'http://www.greatoffer.com/a/23/s', 0, 0, '0', '2008-03-30 19:17:31'),
(62, 46, 'http://www.google.com/logo.gif', 0, 0, '1', '2008-03-30 19:17:31');

-- --------------------------------------------------------

--
-- Table structure for table `log`
--

CREATE TABLE IF NOT EXISTS `log` (
  `log_id` int(9) NOT NULL AUTO_INCREMENT,
  `date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `type` varchar(30) NOT NULL DEFAULT '',
  `message` text NOT NULL,
  `code` int(5) NOT NULL DEFAULT '0',
  PRIMARY KEY (`log_id`),
  KEY `date` (`date`),
  KEY `code` (`code`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `log`
--

INSERT INTO `log` (`log_id`, `date`, `type`, `message`, `code`) VALUES
(4, '2008-03-05 19:50:59', 'schedule', 'Could not update table to trasnfering Id: 4 (You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''1'''' at line 1).', 0),
(5, '2008-03-05 20:27:58', 'schedule', 'Could not update table to trasnfering Id: 4 (You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''1'''' at line 1).', 0),
(6, '2008-03-05 20:38:53', 'schedule', 'Could not update table to trasnfering Id: 4 (You have an error in your SQL syntax; check the manual that corresponds to your MySQL server version for the right syntax to use near ''1'''' at line 1) (UPDATE schedule SET state = ''3'', total_emails = ''1'' WHERE id = ''4 AND `state` = ''1'').', 0);

-- --------------------------------------------------------

--
-- Table structure for table `mm_defaults`
--

CREATE TABLE IF NOT EXISTS `mm_defaults` (
  `address` varchar(150) NOT NULL DEFAULT '',
  `first_name` varchar(30) NOT NULL DEFAULT '',
  `last_name` varchar(30) NOT NULL DEFAULT '',
  `ip` varchar(30) NOT NULL DEFAULT '',
  `gender` varchar(30) NOT NULL DEFAULT '',
  `dob` varchar(30) NOT NULL DEFAULT '',
  `state` varchar(30) NOT NULL DEFAULT '',
  `zip` varchar(30) NOT NULL DEFAULT '',
  `city` varchar(30) NOT NULL DEFAULT '',
  `postal` varchar(30) NOT NULL DEFAULT '',
  `timestamp` varchar(30) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `mm_defaults`
--


-- --------------------------------------------------------

--
-- Table structure for table `msg`
--

CREATE TABLE IF NOT EXISTS `msg` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `server_id` int(5) NOT NULL DEFAULT '0',
  `delivery_configuration_id` int(4) NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `state` tinyint(1) DEFAULT '0',
  `query` varchar(255) NOT NULL DEFAULT '',
  `size` int(11) DEFAULT '0',
  `send_type` int(11) DEFAULT '0',
  `max_recipients` int(11) DEFAULT '0',
  `start_recipient` int(11) DEFAULT '0',
  `content` int(11) DEFAULT '0',
  `body` text,
  `html_body` text,
  `aol_body` text,
  `comments` text NOT NULL,
  `link_tracking` enum('1','0') NOT NULL DEFAULT '1',
  `category_add` int(1) NOT NULL DEFAULT '3',
  `seeds` text NOT NULL,
  `sup_list_id` int(2) NOT NULL DEFAULT '0',
  `header` int(11) DEFAULT NULL,
  `footer` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sup_list_id` (`sup_list_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=51 ;

--
-- Dumping data for table `msg`
--

INSERT INTO `msg` (`id`, `server_id`, `delivery_configuration_id`, `title`, `state`, `query`, `size`, `send_type`, `max_recipients`, `start_recipient`, `content`, `body`, `html_body`, `aol_body`, `comments`, `link_tracking`, `category_add`, `seeds`, `sup_list_id`, `header`, `footer`) VALUES
(7, 2, 0, 'Test', 1, '', 30, 0, 0, 0, 2, 'TEXT\nhttp://www.gopher.com', 'HTML', '', '', '', 3, '', 0, NULL, 12),
(8, 2, 0, 'Test', 1, '', 30, 0, 0, 0, 2, 'TEXT\nhttp://www.gopher.com', 'HTML', '', '', '1', 3, '', 0, NULL, 12),
(9, 2, 0, 'Test', 1, '', 30, 0, 0, 0, 2, 'TEXT\nhttp://www.gopher.com', 'HTML', '', '', '', 3, '', 0, NULL, 12),
(10, 2, 0, 'Test', 1, '', 30, 0, 0, 0, 2, 'TEXT\nhttp://www.gopher.com', 'HTML', '', '', '', 3, '', 0, NULL, 12),
(11, 2, 0, 'Test', 1, '', 30, 0, 0, 0, 2, 'TEXT\nhttp://www.gopher.com', 'HTML', '', '', '', 3, '', 0, NULL, 12),
(12, 2, 0, 'Test', 1, '', 30, 0, 0, 0, 2, 'TEXT\nhttp://www.gopher.com', 'HTML', '', '', '1', 3, '', 0, NULL, 12),
(13, 2, 0, 'Test 2', 1, '', 8, 0, 0, 0, 2, 'TEXT', 'HTML', '', '', '', 3, '', 0, NULL, 12),
(14, 2, 0, 'Test 2', 1, '', 8, 0, 0, 0, 2, 'TEXT', 'HTML', '', '', '', 3, '', 0, NULL, 12),
(15, 2, 2, 'sfdsdfdf', 0, '', 15, 0, 0, 0, 2, 'sdfsdf', 'sfdsdfsdf', '', '', '', 3, '', 0, NULL, NULL),
(16, 2, 1, 'test random', 1, '', 21, 0, 0, 0, 2, 'fdgfdgfdgfg\n{{y1099}}', '', '', '', '', 3, '', 0, NULL, NULL),
(17, 2, 1, 'test random 2', 1, '', 21, 0, 0, 0, 2, 'start\n\n{{y1099}}\n\nend', '', '', '', '', 3, '', 0, NULL, NULL),
(18, 2, 1, 'test random 2', 1, '', 34, 0, 0, 0, 2, 'start\n\n{{y1099}}\n{sft{%a, %Y}}\nend', '', '', '', '', 3, '', 0, NULL, NULL),
(19, 2, 1, 'test random 2', 1, '', 36, 0, 0, 0, 2, 'start\n\n{{y1099}}\n[{sft{%a, %Y}}]\nend', '', '', '', '', 3, '', 0, NULL, NULL),
(20, 2, 1, 'Title', 0, '', 100, 0, 0, 0, 0, 'fadas \nhttp://google.com', 'fadas \nhttp://google.com\n<img src="http://www.clickboothlnk.com/i/a/.gid" />', NULL, '', '1', 3, '', 0, NULL, 12),
(21, 2, 1, 'Title', 0, '', 100, 0, 0, 0, 0, 'fadas \nhttp://google.com', 'fadas \nhttp://google.com\n<img src="http://www.clickboothlnk.com/i/a/.gid" />', NULL, '', '1', 3, '', 0, NULL, 12),
(22, 2, 1, 'Title', 0, '', 100, 0, 0, 0, 0, 'fadas \nhttp://google.com', 'fadas \nhttp://google.com\n<img src="http://www.clickboothlnk.com/i/a/.gid" />', NULL, '', '1', 3, '', 0, NULL, 12),
(23, 2, 1, 'Title', 0, '', 100, 0, 0, 0, 0, 'fadas \nhttp://google.com', 'fadas \nhttp://google.com\n<img src="http://www.clickboothlnk.com/i/a/.gid" />', NULL, '', '1', 3, '', 0, NULL, 12),
(24, 2, 1, 'Title', 0, '', 100, 0, 0, 0, 0, 'fadas \nhttp://google.com', 'fadas \nhttp://google.com\n<img src="http://www.clickboothlnk.com/i/a/.gid" />', NULL, '', '1', 3, '', 0, NULL, 12),
(25, 2, 1, 'sdasdsd', 0, '', 6, 0, 0, 0, 0, 'asdsad', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(26, 2, 1, 'sdasdsd', 0, '', 6, 0, 0, 0, 0, 'asdsad', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(27, 2, 1, 'sdasdsd', 0, '', 6, 0, 0, 0, 0, 'asdsad', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(28, 2, 1, 'sdasdsd', 0, '', 6, 0, 0, 0, 0, 'asdsad', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(29, 2, 1, 'sdasdsd', 0, '', 6, 0, 0, 0, 0, 'asdsad', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(30, 2, 1, 'sdasdsd', 0, '', 6, 0, 0, 0, 0, 'asdsad', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(31, 2, 1, 'sdasdsd', 0, '', 6, 0, 0, 0, 0, 'asdsad', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(32, 2, 1, 'dfgfdg', 0, '', 4, 0, 0, 0, 0, 'cvxg', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(33, 2, 1, 'dfgfdg', 0, '', 4, 0, 0, 0, 0, 'cvxg', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(34, 2, 1, 'dfsdf', 0, '', 9, 0, 0, 0, 0, 'sfdsdfsdf', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(35, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(36, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(37, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(38, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(39, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(40, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(41, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(42, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(43, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(44, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(45, 2, 1, 'erwerewr', 0, '', 6, 0, 0, 0, 0, 'werere', NULL, NULL, '', '1', 3, '', 0, NULL, NULL),
(46, 2, 1, 'Title', 1, '', 129, 0, 0, 0, 0, 'Text\nhttp://www.greatoffer.com/a/23/s', 'Text\n<a href="http://www.greatoffer.com/a/23/s"><img src="http://www.google.com/logo.gif" />', NULL, '', '1', 3, '', 0, NULL, 12),
(47, 2, 1, 'TItle', 1, '', 155, 0, 0, 0, 0, 'Test\nhttp://www.clickbooth.com/index.html', 'Test\n<a href="http://www.clickbooth.com/index.html"><img src="http://www.clickbooth.com/images/home_01.gif" /></a>', NULL, '', '1', 3, '', 0, NULL, 12),
(48, 2, 1, 'TItle', 1, '', 155, 0, 0, 0, 0, 'Test\nhttp://www.clickbooth.com/index.html', 'Test\n<a href="http://www.clickbooth.com/index.html"><img src="http://www.clickbooth.com/images/home_01.gif" /></a>', NULL, '', '1', 3, '', 0, NULL, 12),
(49, 2, 1, 'TItle', 1, '', 155, 0, 0, 0, 0, 'Test\nhttp://www.clickbooth.com/index.html', 'Test\n<a href="http://www.clickbooth.com/index.html"><img src="http://www.clickbooth.com/images/home_01.gif" /></a>', NULL, '', '1', 3, '', 0, NULL, 12),
(50, 2, 1, 'TItle', 1, '', 155, 0, 0, 0, 0, 'Test\nhttp://www.clickbooth.com/index.html', 'Test\n<a href="http://www.clickbooth.com/index.html"><img src="http://www.clickbooth.com/images/home_01.gif" /></a>', NULL, '', '1', 3, '', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `msg_to_category`
--

CREATE TABLE IF NOT EXISTS `msg_to_category` (
  `msg_id` int(9) NOT NULL DEFAULT '0',
  `category_id` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`msg_id`,`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `msg_to_category`
--


-- --------------------------------------------------------

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

--
-- Dumping data for table `msg_to_from`
--

INSERT INTO `msg_to_from` (`msg_id`, `from`, `from_local`, `from_domain`) VALUES
(1, 'blah blah', 'local', 'ayhoo.info'),
(23, 'Name', 'name', '{{dn}}'),
(23, 'Name23', 'name23', '{{dn}}'),
(24, 'Name', 'name', '{{dn}}'),
(25, 'Name', 'name', '{{dn}}'),
(26, 'Name', 'name', '{{dn}}'),
(27, 'Name', 'name', '{{dn}}'),
(28, 'Name', 'name', '{{dn}}'),
(29, 'Name', 'name', '{{dn}}'),
(30, 'Name', 'name', '{{dn}}'),
(31, 'Name', 'name', '{{dn}}'),
(32, 'Name', 'name', '{{dn}}'),
(33, 'Name', 'name', '{{dn}}'),
(34, 'Name', 'name', '{{dn}}'),
(35, 'Name', 'name', '{{dn}}'),
(36, 'Name', 'name', '{{dn}}'),
(37, 'Name', 'name', '{{dn}}'),
(38, 'Name', 'name', '{{dn}}'),
(39, 'Name', 'name', '{{dn}}'),
(40, 'Name', 'name', '{{dn}}'),
(41, 'Name', 'name', '{{dn}}'),
(42, 'Name', 'name', '{{dn}}'),
(43, 'Name', 'name', '{{dn}}'),
(44, 'Name', 'name', '{{dn}}'),
(45, 'Name', 'name', '{{dn}}'),
(46, 'Name', 'name', '{{dn}}'),
(47, 'Name', 'name', '{{dn}}'),
(48, 'Name', 'name', '{{dn}}'),
(49, 'Name', 'name', '{{dn}}'),
(50, 'Name', 'name', '{{dn}}');

-- --------------------------------------------------------

--
-- Table structure for table `msg_to_ip`
--

CREATE TABLE IF NOT EXISTS `msg_to_ip` (
  `draft_id` int(9) NOT NULL DEFAULT '0',
  `ip_id` int(9) NOT NULL,
  `domain` varchar(225) NOT NULL,
  KEY `msg_id` (`draft_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `msg_to_ip`
--

INSERT INTO `msg_to_ip` (`draft_id`, `ip_id`, `domain`) VALUES
(42, 1, ''),
(42, 2, ''),
(43, 2, ''),
(43, 1, ''),
(44, 2, ''),
(44, 1, ''),
(45, 2, ''),
(45, 1, ''),
(46, 2, 'celibero.thestone'),
(46, 1, 'cyberdummy.co.uk'),
(47, 2, 'celibero.thestone'),
(47, 1, 'cyberdummy.co.uk'),
(48, 2, 'celibero.thestone'),
(48, 1, 'cyberdummy.co.uk'),
(49, 1, 'cyberdummy.co.uk'),
(50, 1, 'cyberdummy.co.uk');

-- --------------------------------------------------------

--
-- Table structure for table `msg_to_list`
--

CREATE TABLE IF NOT EXISTS `msg_to_list` (
  `msg_id` int(9) NOT NULL DEFAULT '0',
  `list_id` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`msg_id`,`list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `msg_to_list`
--

INSERT INTO `msg_to_list` (`msg_id`, `list_id`) VALUES
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2),
(47, 2),
(48, 2),
(49, 2),
(50, 2);

-- --------------------------------------------------------

--
-- Table structure for table `msg_to_subject`
--

CREATE TABLE IF NOT EXISTS `msg_to_subject` (
  `msg_id` int(9) NOT NULL DEFAULT '0',
  `subject` varchar(100) NOT NULL DEFAULT '',
  KEY `msg_id` (`msg_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `msg_to_subject`
--

INSERT INTO `msg_to_subject` (`msg_id`, `subject`) VALUES
(7, 'Subject'),
(8, 'Subject'),
(9, 'Subject'),
(10, 'Subject'),
(11, 'Subject'),
(12, 'Subject'),
(13, 'Subject'),
(14, 'Subject'),
(15, 'sdfsdf'),
(16, 'fdgfg'),
(17, 'fdgfg'),
(18, 'fdgfg'),
(19, 'fdgfg'),
(23, 'Subject'),
(23, ''),
(23, 'Subject 2'),
(24, 'Subject'),
(24, 'uiuiyiui'),
(24, ''),
(25, 'sadsad'),
(26, 'sadsad'),
(27, 'sadsad'),
(28, 'sadsad'),
(29, 'sadsad'),
(30, 'sadsad'),
(31, 'sadsad'),
(32, 'fgfdgfdg'),
(33, 'fgfdgfdg'),
(34, 'sdfsdfdf'),
(35, 'wererer'),
(36, 'wererer'),
(37, 'wererer'),
(38, 'wererer'),
(39, 'wererer'),
(40, 'wererer'),
(41, 'wererer'),
(42, 'wererer'),
(43, 'wererer'),
(44, 'wererer'),
(45, 'wererer'),
(46, 'Subject 2'),
(46, 'Subject 1'),
(47, 'Subject'),
(48, 'Subject'),
(49, 'Subject'),
(50, 'Subject');

-- --------------------------------------------------------

--
-- Table structure for table `quick_campaign`
--

CREATE TABLE IF NOT EXISTS `quick_campaign` (
  `qc_id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(40) NOT NULL,
  `url` varchar(200) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `type` varchar(10) NOT NULL,
  PRIMARY KEY (`qc_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `quick_campaign`
--


-- --------------------------------------------------------

--
-- Table structure for table `quick_campaign_creative`
--

CREATE TABLE IF NOT EXISTS `quick_campaign_creative` (
  `qcc_id` int(9) NOT NULL AUTO_INCREMENT,
  `text` text NOT NULL,
  `html` text NOT NULL,
  `subjects` text NOT NULL,
  `froms` text NOT NULL,
  `sup_list_id` varchar(40) NOT NULL,
  `comment` text NOT NULL,
  PRIMARY KEY (`qcc_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `quick_campaign_creative`
--


-- --------------------------------------------------------

--
-- Table structure for table `rotations`
--

CREATE TABLE IF NOT EXISTS `rotations` (
  `server_id` int(9) NOT NULL,
  `per_mailing` int(3) NOT NULL,
  `per_seconds` int(8) NOT NULL,
  `last_update` datetime NOT NULL,
  `last_id` varchar(50) NOT NULL DEFAULT '0',
  PRIMARY KEY (`server_id`),
  KEY `last_update` (`last_update`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `rotations`
--


-- --------------------------------------------------------

--
-- Table structure for table `schedule`
--

CREATE TABLE IF NOT EXISTS `schedule` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `msg_id` int(11) DEFAULT NULL,
  `server_id` int(9) NOT NULL DEFAULT '0',
  `title` varchar(60) NOT NULL DEFAULT '',
  `state` tinyint(1) DEFAULT '0',
  `old_state` tinyint(1) NOT NULL DEFAULT '0',
  `scheduled_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `start_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `end_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `total_emails` int(11) DEFAULT '0',
  `success` int(11) NOT NULL DEFAULT '0',
  `deferral` int(11) NOT NULL DEFAULT '0',
  `failure` int(11) NOT NULL DEFAULT '0',
  `opens` int(11) DEFAULT '0',
  `subject_lines` int(9) NOT NULL DEFAULT '0',
  `from_lines` int(9) NOT NULL DEFAULT '0',
  `domains` int(9) NOT NULL DEFAULT '0',
  `sql_extra` text NOT NULL,
  `process_results` int(9) NOT NULL DEFAULT '0',
  `max_threads` int(5) NOT NULL DEFAULT '300',
  `skip_first` int(9) NOT NULL,
  `send_to_first` int(9) NOT NULL,
  `send_to_last` int(9) NOT NULL,
  `max_of` int(9) NOT NULL,
  `retry_level` int(9) NOT NULL,
  `retries` int(9) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `msgid` (`msg_id`),
  KEY `scheduled_time` (`scheduled_time`),
  KEY `process_results` (`process_results`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

--
-- Dumping data for table `schedule`
--

INSERT INTO `schedule` (`id`, `msg_id`, `server_id`, `title`, `state`, `old_state`, `scheduled_time`, `start_time`, `end_time`, `total_emails`, `success`, `deferral`, `failure`, `opens`, `subject_lines`, `from_lines`, `domains`, `sql_extra`, `process_results`, `max_threads`, `skip_first`, `send_to_first`, `send_to_last`, `max_of`, `retry_level`, `retries`) VALUES
(4, 7, 2, '', 9, 0, '2008-03-05 19:49:29', '0000-00-00 00:00:00', '2008-03-05 21:08:00', 1, 0, 0, 0, 0, 1, 1, 1, '', 2, 1000, 0, 0, 0, 0, 0, 0),
(5, 8, 2, '', 9, 0, '2008-03-05 21:05:07', '0000-00-00 00:00:00', '2008-03-05 21:08:06', 1, 0, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(6, 9, 2, '', 7, 0, '2008-03-05 21:07:41', '2008-03-05 21:09:11', '2008-03-05 21:09:16', 1, 0, 0, 1, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(7, 10, 2, '', 7, 0, '2008-03-05 21:11:05', '2008-03-05 21:11:11', '2008-03-05 21:11:11', 1, 1, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(8, 11, 2, '', 7, 0, '2008-03-05 21:24:43', '2008-03-05 21:25:11', '2008-03-05 21:25:11', 1, 1, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(9, 12, 2, '', 7, 0, '2008-03-05 21:48:32', '2008-03-05 21:50:11', '2008-03-05 21:50:11', 1, 1, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(10, 13, 2, '', 9, 0, '2008-03-15 10:02:47', '0000-00-00 00:00:00', '2008-03-15 10:06:07', 1, 0, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(11, 14, 2, '', 7, 0, '2008-03-15 10:06:34', '2008-03-15 10:07:58', '2008-03-15 10:08:04', 1, 1, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(12, 16, 2, '', 7, 0, '2008-03-23 20:03:15', '2008-03-23 21:24:28', '2008-03-23 21:24:34', 1, 1, 0, 0, 0, 1, 1, 2, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(13, 17, 2, '', 9, 0, '2008-03-23 21:52:36', '2008-03-23 21:53:50', '2008-03-23 23:25:30', 1, 1, 0, 0, 0, 1, 1, 2, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(14, 18, 2, '', 7, 0, '2008-03-23 23:14:40', '2008-03-23 23:31:37', '2008-03-23 23:31:42', 1, 1, 0, 0, 0, 1, 1, 2, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(15, 19, 2, '', 7, 0, '2008-03-23 23:35:25', '2008-03-23 23:36:06', '2008-03-23 23:36:12', 1, 1, 0, 0, 0, 1, 1, 2, '', 0, 1000, 0, 0, 0, 0, 0, 0),
(16, 46, 2, '', 9, 0, '2008-03-30 20:03:00', '2008-03-30 20:11:27', '2008-03-30 20:12:38', 1, 0, 0, 0, 0, 2, 1, 1, '', 0, 1000, 0, 0, 0, 0, 1, 1),
(17, 47, 2, '', 7, 0, '2008-03-30 20:17:11', '2008-03-30 20:56:14', '2008-03-30 20:56:21', 1, 1, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 1, 1),
(18, 48, 2, '', 7, 0, '2008-03-30 21:14:42', '2008-03-30 21:16:42', '2008-03-30 21:16:48', 1, 1, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 1, 1),
(19, 49, 2, '', 7, 0, '2008-03-30 21:24:13', '2008-04-05 18:07:58', '2008-04-05 18:08:04', 1, 1, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 1, 1),
(20, 50, 2, '', 7, 0, '2008-04-05 17:52:05', '2008-04-05 17:52:37', '2008-04-05 17:53:14', 1, 1, 0, 0, 0, 1, 1, 1, '', 0, 1000, 0, 0, 0, 0, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `schedule_log`
--

CREATE TABLE IF NOT EXISTS `schedule_log` (
  `schedule_id` int(9) NOT NULL DEFAULT '0',
  `time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message` text NOT NULL,
  KEY `schedule_id` (`schedule_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `schedule_log`
--

INSERT INTO `schedule_log` (`schedule_id`, `time`, `message`) VALUES
(4, '2008-03-05 19:49:29', 'Draft Inputted for schedule'),
(4, '2008-03-05 19:50:59', 'Started Prepare'),
(4, '2008-03-05 20:27:58', 'Started Prepare'),
(4, '2008-03-05 20:38:53', 'Started Prepare'),
(4, '2008-03-05 20:41:24', 'Started Prepare'),
(4, '2008-03-05 20:41:24', 'Ended Prepare (Suppressed 0 emails)'),
(5, '2008-03-05 21:05:07', 'Draft Inputted for schedule'),
(6, '2008-03-05 21:07:41', 'Draft Inputted for schedule'),
(6, '2008-03-05 21:08:22', 'Started Prepare'),
(6, '2008-03-05 21:08:22', 'Ended Prepare (Suppressed 0 emails)'),
(7, '2008-03-05 21:11:05', 'Draft Inputted for schedule'),
(7, '2008-03-05 21:11:10', 'Started Prepare'),
(7, '2008-03-05 21:11:10', 'Ended Prepare (Suppressed 0 emails)'),
(8, '2008-03-05 21:24:43', 'Draft Inputted for schedule'),
(8, '2008-03-05 21:25:03', 'Started Prepare'),
(8, '2008-03-05 21:25:03', 'Ended Prepare (Suppressed 0 emails)'),
(9, '2008-03-05 21:48:32', 'Draft Inputted for schedule'),
(9, '2008-03-05 21:49:18', 'Started Prepare'),
(9, '2008-03-05 21:49:18', 'Ended Prepare (Suppressed 0 emails)'),
(10, '2008-03-15 10:02:47', 'Draft Inputted for schedule'),
(11, '2008-03-15 10:06:34', 'Draft Inputted for schedule'),
(11, '2008-03-15 10:07:16', 'Started Prepare'),
(11, '2008-03-15 10:07:16', 'Ended Prepare (Suppressed 0 emails)'),
(12, '2008-03-23 20:03:15', 'Draft Inputted for schedule'),
(12, '2008-03-23 20:03:35', 'Started Prepare'),
(12, '2008-03-23 20:03:35', 'Ended Prepare (Suppressed 0 emails)'),
(12, '2008-03-23 20:12:02', 'Started Prepare'),
(12, '2008-03-23 20:12:02', 'Ended Prepare (Suppressed 0 emails)'),
(12, '2008-03-23 20:16:19', 'Started Prepare'),
(12, '2008-03-23 20:16:19', 'Ended Prepare (Suppressed 0 emails)'),
(12, '2008-03-23 20:52:07', 'Started Prepare'),
(12, '2008-03-23 20:52:07', 'Ended Prepare (Suppressed 0 emails)'),
(12, '2008-03-23 21:00:30', 'Started Prepare'),
(12, '2008-03-23 21:00:30', 'Ended Prepare (Suppressed 0 emails)'),
(12, '2008-03-23 21:23:39', 'Started Prepare'),
(12, '2008-03-23 21:23:39', 'Ended Prepare (Suppressed 0 emails)'),
(13, '2008-03-23 21:52:36', 'Draft Inputted for schedule'),
(13, '2008-03-23 21:52:43', 'Started Prepare'),
(13, '2008-03-23 21:52:43', 'Ended Prepare (Suppressed 0 emails)'),
(14, '2008-03-23 23:14:40', 'Draft Inputted for schedule'),
(14, '2008-03-23 23:14:45', 'Started Prepare'),
(14, '2008-03-23 23:14:45', 'Ended Prepare (Suppressed 0 emails)'),
(14, '2008-03-23 23:25:45', 'Started Prepare'),
(14, '2008-03-23 23:25:45', 'Ended Prepare (Suppressed 0 emails)'),
(14, '2008-03-23 23:27:16', 'Started Prepare'),
(14, '2008-03-23 23:27:16', 'Ended Prepare (Suppressed 0 emails)'),
(14, '2008-03-23 23:30:43', 'Started Prepare'),
(14, '2008-03-23 23:30:43', 'Ended Prepare (Suppressed 0 emails)'),
(15, '2008-03-23 23:35:25', 'Draft Inputted for schedule'),
(15, '2008-03-23 23:35:31', 'Started Prepare'),
(15, '2008-03-23 23:35:31', 'Ended Prepare (Suppressed 0 emails)'),
(16, '2008-03-30 20:03:00', 'Draft Inputted for schedule'),
(16, '2008-03-30 20:07:28', 'Started Prepare'),
(16, '2008-03-30 20:07:28', 'Ended Prepare (Suppressed 0 emails)'),
(16, '2008-03-30 20:09:33', 'Started Prepare'),
(16, '2008-03-30 20:09:33', 'Ended Prepare (Suppressed 0 emails)'),
(17, '2008-03-30 20:17:11', 'Draft Inputted for schedule'),
(17, '2008-03-30 20:17:44', 'Started Prepare'),
(17, '2008-03-30 20:17:44', 'Ended Prepare (Suppressed 0 emails)'),
(17, '2008-03-30 20:20:56', 'Started Prepare'),
(17, '2008-03-30 20:20:56', 'Ended Prepare (Suppressed 0 emails)'),
(17, '2008-03-30 20:25:43', 'Started Prepare'),
(17, '2008-03-30 20:25:43', 'Ended Prepare (Suppressed 0 emails)'),
(17, '2008-03-30 20:37:03', 'Started Prepare'),
(17, '2008-03-30 20:37:03', 'Ended Prepare (Suppressed 0 emails)'),
(17, '2008-03-30 20:39:02', 'Started Prepare'),
(17, '2008-03-30 20:39:02', 'Ended Prepare (Suppressed 0 emails)'),
(17, '2008-03-30 20:45:44', 'Started Prepare'),
(17, '2008-03-30 20:45:44', 'Ended Prepare (Suppressed 0 emails)'),
(17, '2008-03-30 20:55:22', 'Started Prepare'),
(17, '2008-03-30 20:55:22', 'Ended Prepare (Suppressed 0 emails)'),
(18, '2008-03-30 21:14:42', 'Draft Inputted for schedule'),
(18, '2008-03-30 21:15:32', 'Started Prepare'),
(18, '2008-03-30 21:15:32', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-03-30 21:24:13', 'Draft Inputted for schedule'),
(19, '2008-03-30 21:24:34', 'Started Prepare'),
(19, '2008-03-30 21:24:34', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-03-30 21:43:09', 'Started Prepare'),
(19, '2008-03-30 21:43:09', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-03-30 21:49:29', 'Started Prepare'),
(19, '2008-03-30 21:49:29', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-04-05 16:45:35', 'Started Prepare'),
(19, '2008-04-05 16:45:35', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-04-05 16:58:26', 'Started Prepare'),
(19, '2008-04-05 16:58:26', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-04-05 17:19:28', 'Started Prepare'),
(19, '2008-04-05 17:19:29', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-04-05 17:38:08', 'Started Prepare'),
(19, '2008-04-05 17:38:08', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-04-05 17:42:55', 'Started Prepare'),
(19, '2008-04-05 17:42:55', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-04-05 17:45:49', 'Started Prepare'),
(19, '2008-04-05 17:45:49', 'Ended Prepare (Suppressed 0 emails)'),
(20, '2008-04-05 17:52:05', 'Draft Inputted for schedule'),
(20, '2008-04-05 17:52:14', 'Started Prepare'),
(20, '2008-04-05 17:52:14', 'Ended Prepare (Suppressed 0 emails)'),
(19, '2008-04-05 18:07:55', 'Started Prepare'),
(19, '2008-04-05 18:07:55', 'Ended Prepare (Suppressed 0 emails)');

-- --------------------------------------------------------

--
-- Table structure for table `servers`
--

CREATE TABLE IF NOT EXISTS `servers` (
  `server_id` int(9) NOT NULL AUTO_INCREMENT,
  `type` int(2) NOT NULL DEFAULT '1',
  `name` varchar(40) NOT NULL DEFAULT '',
  PRIMARY KEY (`server_id`),
  KEY `type` (`type`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `servers`
--

INSERT INTO `servers` (`server_id`, `type`, `name`) VALUES
(2, 5, 'Mailer + DB');

-- --------------------------------------------------------

--
-- Table structure for table `server_to_ip`
--

CREATE TABLE IF NOT EXISTS `server_to_ip` (
  `server_id` int(9) NOT NULL DEFAULT '0',
  `ip` varchar(50) NOT NULL DEFAULT '',
  `domain` varchar(120) NOT NULL DEFAULT '',
  `default` enum('1','0') NOT NULL DEFAULT '0',
  PRIMARY KEY (`server_id`,`ip`),
  KEY `default` (`default`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `server_to_ip`
--

INSERT INTO `server_to_ip` (`server_id`, `ip`, `domain`, `default`) VALUES
(2, '192.168.1.65', 'celibero.thestone', '1'),
(1, '192.168.1.65', 'cyberdummy.co.uk', '0');

-- --------------------------------------------------------

--
-- Table structure for table `sgdne`
--

CREATE TABLE IF NOT EXISTS `sgdne` (
  `username` varchar(30) NOT NULL DEFAULT '',
  `password` varchar(30) NOT NULL DEFAULT '',
  `active` enum('0','1') NOT NULL DEFAULT '0',
  `last_checkin` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_email` varchar(130) NOT NULL DEFAULT '',
  `hostname` varchar(100) NOT NULL DEFAULT '',
  `database` varchar(50) NOT NULL DEFAULT '',
  `last_checkin_domain` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_domain` varchar(100) NOT NULL DEFAULT '',
  `last_checkin_word` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `last_word` varchar(60) NOT NULL DEFAULT '',
  PRIMARY KEY (`username`),
  KEY `last_checkin_word` (`last_checkin_word`,`last_word`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sgdne`
--

INSERT INTO `sgdne` (`username`, `password`, `active`, `last_checkin`, `last_email`, `hostname`, `database`, `last_checkin_domain`, `last_domain`, `last_checkin_word`, `last_word`) VALUES
('sgdne_user', 'screamer', '1', '0000-00-00 00:00:00', '', 'dne.celibero.com', 'sgdne', '0000-00-00 00:00:00', '', '0000-00-00 00:00:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `supression_lists`
--

CREATE TABLE IF NOT EXISTS `supression_lists` (
  `sup_list_id` int(9) NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '',
  `state` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`sup_list_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=12 ;

--
-- Dumping data for table `supression_lists`
--


-- --------------------------------------------------------

--
-- Table structure for table `tracked_link`
--

CREATE TABLE IF NOT EXISTS `tracked_link` (
  `tracked_link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `draft_id` int(10) unsigned NOT NULL,
  `url` varchar(255) NOT NULL,
  `action` int(1) NOT NULL,
  `list_id` int(4) NOT NULL,
  `count` int(11) NOT NULL,
  PRIMARY KEY (`tracked_link_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=32 ;

--
-- Dumping data for table `tracked_link`
--

INSERT INTO `tracked_link` (`tracked_link_id`, `draft_id`, `url`, `action`, `list_id`, `count`) VALUES
(3, 21, 'http://www.clickboothlnk.com/i/a/.gid', 2, 0, 0),
(4, 21, 'http://google.com', 3, 0, 0),
(5, 21, '', 1, 0, 0),
(6, 22, 'http://www.clickboothlnk.com/i/a/.gid', 1, 0, 0),
(7, 22, 'http://google.com', 1, 0, 0),
(8, 23, 'http://www.clickboothlnk.com/i/a/.gid', 1, 0, 0),
(9, 23, 'http://google.com', 1, 0, 0),
(10, 24, 'http://www.clickboothlnk.com/i/a/.gid', 1, 0, 0),
(11, 24, 'http://google.com', 1, 0, 0),
(23, 46, 'http://www.greatoffer.com/a/23/s', 1, 0, 0),
(22, 46, 'http://www.google.com/logo.gif', 1, 0, 0),
(24, 47, 'http://www.clickbooth.com/images/home_01.gif', 1, 0, 1),
(25, 47, 'http://www.clickbooth.com/index.html', 1, 0, 0),
(26, 48, 'http://www.clickbooth.com/images/home_01.gif', 1, 0, 0),
(27, 48, 'http://www.clickbooth.com/index.html', 1, 0, 0),
(28, 49, 'http://www.clickbooth.com/images/home_01.gif', 1, 0, 0),
(29, 49, 'http://www.clickbooth.com/index.html', 1, 0, 0),
(30, 50, 'http://www.clickbooth.com/images/home_01.gif', 1, 0, 0),
(31, 50, 'http://www.clickbooth.com/index.html', 1, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `textpassword` varchar(255) DEFAULT NULL,
  `db_host` varchar(255) NOT NULL DEFAULT 'localhost',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `username`, `password`, `textpassword`, `db_host`) VALUES
(2, 'test', NULL, '3b3c376a84', 'localhost');

-- --------------------------------------------------------

--
-- Table structure for table `users_auth`
--

CREATE TABLE IF NOT EXISTS `users_auth` (
  `user_id` int(9) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(32) NOT NULL DEFAULT '',
  `text_password` varchar(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users_auth`
--

INSERT INTO `users_auth` (`user_id`, `username`, `password`, `text_password`) VALUES
(2, 'admin', '5f4dcc3b5aa765d61d8327deb882cf99', 'password');

-- --------------------------------------------------------

--
-- Table structure for table `users_groups`
--

CREATE TABLE IF NOT EXISTS `users_groups` (
  `group_id` int(9) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`group_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `users_groups`
--

INSERT INTO `users_groups` (`group_id`, `name`) VALUES
(1, 'Admins'),
(2, 'Normal');

-- --------------------------------------------------------

--
-- Table structure for table `users_groups_2_users`
--

CREATE TABLE IF NOT EXISTS `users_groups_2_users` (
  `group_id` int(9) NOT NULL DEFAULT '0',
  `user_id` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`group_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_groups_2_users`
--

INSERT INTO `users_groups_2_users` (`group_id`, `user_id`) VALUES
(1, 2),
(2, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users_permissions`
--

CREATE TABLE IF NOT EXISTS `users_permissions` (
  `id` int(9) NOT NULL DEFAULT '0',
  `id_type` enum('u','g') NOT NULL DEFAULT 'u',
  `perm_key` varchar(50) NOT NULL DEFAULT '',
  `level` int(4) NOT NULL DEFAULT '1',
  `has` enum('y','n') NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`,`id_type`,`perm_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_permissions`
--

INSERT INTO `users_permissions` (`id`, `id_type`, `perm_key`, `level`, `has`) VALUES
(2, 'g', 'CP', 1, 'y'),
(1, 'g', 'PAGE_CONFIG', 1, 'y');

-- --------------------------------------------------------

--
-- Table structure for table `users_profile_big_text`
--

CREATE TABLE IF NOT EXISTS `users_profile_big_text` (
  `user_id` int(9) NOT NULL DEFAULT '0',
  `profile_key` varchar(50) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  KEY `user_id` (`user_id`,`profile_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_profile_big_text`
--


-- --------------------------------------------------------

--
-- Table structure for table `users_profile_text`
--

CREATE TABLE IF NOT EXISTS `users_profile_text` (
  `user_id` int(9) NOT NULL DEFAULT '0',
  `profile_key` varchar(50) NOT NULL DEFAULT '',
  `text` varchar(250) NOT NULL DEFAULT '',
  KEY `profile_key` (`user_id`,`profile_key`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_profile_text`
--


-- --------------------------------------------------------

--
-- Table structure for table `users_session`
--

CREATE TABLE IF NOT EXISTS `users_session` (
  `session_id` varchar(32) NOT NULL DEFAULT '',
  `user_id` int(9) NOT NULL DEFAULT '0',
  `last_activity` varchar(32) NOT NULL DEFAULT '',
  `duration` int(9) NOT NULL DEFAULT '0',
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users_session`
--

INSERT INTO `users_session` (`session_id`, `user_id`, `last_activity`, `duration`) VALUES
('5c2cfa3a8d7a36691cb4eb0306006e24', 2, '1207414449', 259200);
