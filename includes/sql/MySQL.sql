-- phpMyAdmin SQL Dump
-- version 2.10.0-rc1
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Jan 10, 2010 at 04:07 PM
-- Server version: 5.0.27
-- PHP Version: 5.2.3

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- 
-- Database: `n27-source`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `booking_admin_functions`
-- 

CREATE TABLE `booking_admin_functions` (
  `function_id` mediumint(9) NOT NULL auto_increment,
  `name` text NOT NULL,
  `function_value` text NOT NULL,
  `last_updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`function_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- 
-- Dumping data for table `booking_admin_functions`
-- 

INSERT INTO `booking_admin_functions` (`function_id`, `name`, `function_value`, `last_updated`) VALUES 
(1, 'public_register', '1', '2009-04-12 15:34:22'),
(2, 'booking_hours_limit', '1440', '2009-05-13 19:45:27'),
(3, 'cancellation_hours_limit', '12', '2010-01-10 15:56:28'),
(4, 'minimum_booking_hours_limit', '12', '2010-01-10 15:56:28'),
(5, 'public_details_viewing', '1', '2007-08-30 23:15:08'),
(6, 'user_details_viewing', '1', '2006-08-03 21:26:28'),
(7, 'user_minimum_booking_options', '0', '2010-01-10 15:56:45'),
(8, 'admin_minimum_booking_options', '0', '2008-03-18 17:00:40'),
(9, 'send_booking_conf_email', '0', '2010-01-10 16:01:59'),
(10, 'send_booking_conf_email_from', 'email@yourdomain.com', '2010-01-10 15:57:48'),
(11, 'send_booking_conf_email_from_name', 'Online Booking System', '2010-01-10 15:57:48'),
(12, 'send_booking_conf_email_subject', '%sitename% Booking Confirmation', '2005-11-11 12:51:25'),
(13, 'send_booking_conf_email_body', 'Dear %firstname%,\r\n\r\nThank you for booking online at %sitename%.\r\n\r\nYour booking details are confirmed below:\r\n\r\n%bookingtimesvertical%\r\n\r\nLocation: %location%\r\nDescription: %fulldesc%\r\nOptions: %options%\r\nBooking slots are for %period% each.\r\n\r\n\r\nKind regards,\r\n\r\nThe %sitename% team.', '2010-01-10 16:01:50'),
(14, 'send_booking_conf_email_cc', '', '2010-01-10 15:57:58'),
(15, 'send_buddy_list_email', '1', '2007-07-08 11:30:06'),
(16, 'send_buddy_list_email_from', 'noreply@n27.co.uk', '2006-02-25 13:02:59'),
(17, 'send_buddy_list_email_subject', 'Buddy List Notification', '2006-02-25 13:02:59'),
(18, 'send_buddy_list_email_body', 'This is an automated email from the %sitename% booking system.\r\n\r\nUser %firstname% %lastname% has made the following booking:\r\n\r\n%bookingtimesvertical%\r\n\r\nLocation: %location%\r\nDescription: %fulldesc%\r\nOptions: %options%\r\nBooking slots are for %period% each.\r\n\r\n\r\nKind regards,\r\n\r\nThe %sitename% team.\r\n\r\nNote: If you do not wish to receive further buddy list notifications, please login to the %sitename% booking system and remove %firstname% %lastname% from your buddy list.', '2006-02-25 13:02:59'),
(19, 'send_buddy_list_email_from_name', 'noreply', '2006-02-25 13:02:59'),
(20, 'payment_gateway', '0', '2010-01-10 16:03:46'),
(21, 'paypal_business_email', 'paypal@mydomain.com', '2010-01-10 16:03:01'),
(25, 'send_user_register_email_to', 'benhagan@gmail.com', '2009-04-12 15:11:51'),
(23, 'paypal_notification_email', 'email@mydomain.com', '2010-01-10 16:03:01');

-- --------------------------------------------------------

-- 
-- Table structure for table `booking_attachments`
-- 

CREATE TABLE `booking_attachments` (
  `attachment_id` smallint(6) unsigned NOT NULL auto_increment,
  `mailshot_id` smallint(6) unsigned NOT NULL default '0',
  `filename` text NOT NULL,
  PRIMARY KEY  (`attachment_id`),
  KEY `user_id` (`mailshot_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_attachments`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_attachments_temp`
-- 

CREATE TABLE `booking_attachments_temp` (
  `attachment_id` smallint(6) unsigned NOT NULL auto_increment,
  `user_id` smallint(6) unsigned NOT NULL default '0',
  `filename` text NOT NULL,
  PRIMARY KEY  (`attachment_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- 
-- Dumping data for table `booking_attachments_temp`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_contact_sent_emails`
-- 

CREATE TABLE `booking_contact_sent_emails` (
  `email_id` bigint(20) unsigned NOT NULL auto_increment,
  `sent_by_user_id` mediumint(9) NOT NULL default '0',
  `from_name` varchar(100) NOT NULL default '',
  `from_email` varchar(100) NOT NULL default '',
  `cc_me` set('0','1') NOT NULL default '1',
  `subject` varchar(255) NOT NULL default '',
  `body` text NOT NULL,
  `sent` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`email_id`),
  KEY `sent_by_user_id` (`sent_by_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_contact_sent_emails`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_contact_sent_email_groups`
-- 

CREATE TABLE `booking_contact_sent_email_groups` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `email_id` bigint(20) NOT NULL default '0',
  `group_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `email_id` (`email_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_contact_sent_email_groups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_contact_sent_email_users`
-- 

CREATE TABLE `booking_contact_sent_email_users` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `email_id` bigint(20) NOT NULL default '0',
  `user_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `email_id` (`email_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_contact_sent_email_users`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_credit_types`
-- 

CREATE TABLE `booking_credit_types` (
  `credit_type_id` mediumint(9) NOT NULL auto_increment,
  `credit_type_name` varchar(50) NOT NULL,
  `credit_type_booking_days` smallint(6) NOT NULL,
  PRIMARY KEY  (`credit_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

-- 
-- Dumping data for table `booking_credit_types`
-- 

INSERT INTO `booking_credit_types` (`credit_type_id`, `credit_type_name`, `credit_type_booking_days`) VALUES 
(1, 'Use Site Default', 0),
(2, 'Standard', 7),
(3, 'Bronze', 30),
(4, 'Silver', 60),
(5, 'Gold', 90),
(6, 'Platinum', 120);

-- --------------------------------------------------------

-- 
-- Table structure for table `booking_event`
-- 

CREATE TABLE `booking_event` (
  `event_id` mediumint(9) unsigned NOT NULL auto_increment,
  `user_id` smallint(6) unsigned NOT NULL default '0',
  `subject` varchar(150) NOT NULL default '',
  `location` varchar(50) NOT NULL default '',
  `starting_date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `ending_date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `recur_interval` varchar(15) NOT NULL default 'none',
  `recur_freq` tinyint(4) NOT NULL default '0',
  `recur_until_date` date NOT NULL default '0000-00-00',
  `description` text NOT NULL,
  `date_time_added` datetime NOT NULL default '0000-00-00 00:00:00',
  `last_mod_by_id` smallint(6) NOT NULL default '0',
  `last_mod_date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`event_id`),
  UNIQUE KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_event`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_event_options`
-- 

CREATE TABLE `booking_event_options` (
  `id` bigint(20) NOT NULL auto_increment,
  `event_id` bigint(20) NOT NULL default '0',
  `option_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `event_id` (`event_id`,`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_event_options`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_groups`
-- 

CREATE TABLE `booking_groups` (
  `group_id` mediumint(8) unsigned NOT NULL auto_increment,
  `group_name` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_groups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_options`
-- 

CREATE TABLE `booking_options` (
  `option_id` bigint(20) NOT NULL auto_increment,
  `description` varchar(100) NOT NULL default '',
  PRIMARY KEY  (`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_options`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_paypal_transactions`
-- 

CREATE TABLE `booking_paypal_transactions` (
  `id` int(11) NOT NULL auto_increment,
  `payer_id` varchar(60) default NULL,
  `n27_user_id` int(11) NOT NULL,
  `payment_date` varchar(50) default NULL,
  `txn_id` varchar(50) default NULL,
  `first_name` varchar(50) default NULL,
  `last_name` varchar(50) default NULL,
  `payer_email` varchar(75) default NULL,
  `payer_status` varchar(50) default NULL,
  `payment_type` varchar(50) default NULL,
  `memo` tinytext,
  `item_name` varchar(127) default NULL,
  `item_number` varchar(127) default NULL,
  `quantity` int(11) NOT NULL default '0',
  `mc_gross` decimal(9,2) default NULL,
  `mc_currency` char(3) default NULL,
  `address_name` varchar(255) NOT NULL default '',
  `address_street` varchar(255) NOT NULL default '',
  `address_city` varchar(255) NOT NULL default '',
  `address_state` varchar(255) NOT NULL default '',
  `address_zip` varchar(255) NOT NULL default '',
  `address_country` varchar(255) NOT NULL default '',
  `address_status` varchar(255) NOT NULL default '',
  `payer_business_name` varchar(255) NOT NULL default '',
  `payment_status` varchar(255) NOT NULL default '',
  `pending_reason` varchar(255) NOT NULL default '',
  `reason_code` varchar(255) NOT NULL default '',
  `txn_type` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `txn_id` (`txn_id`),
  KEY `txn_id_2` (`txn_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_paypal_transactions`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_product_groups`
-- 

CREATE TABLE `booking_product_groups` (
  `id` bigint(20) unsigned NOT NULL auto_increment,
  `product_id` mediumint(9) NOT NULL default '0',
  `group_id` mediumint(9) NOT NULL default '0',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  KEY `product_id` (`product_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_product_groups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_product_item`
-- 

CREATE TABLE `booking_product_item` (
  `id` int(11) NOT NULL auto_increment,
  `product_name` varchar(128) NOT NULL,
  `quantity` mediumint(9) NOT NULL default '1',
  `mc_gross` decimal(9,2) NOT NULL default '0.00',
  `mc_currency` enum('USD','CAD','EUR','GBP','JPY','CAD') NOT NULL default 'USD',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=51 ;

-- 
-- Dumping data for table `booking_product_item`
-- 

INSERT INTO `booking_product_item` (`id`, `product_name`, `quantity`, `mc_gross`, `mc_currency`) VALUES 
(1, 'Default Product', 1, '0.01', 'GBP');

-- --------------------------------------------------------

-- 
-- Table structure for table `booking_schedule`
-- 

CREATE TABLE `booking_schedule` (
  `date_time_id` mediumint(9) unsigned NOT NULL auto_increment,
  `schedule_date_time` datetime NOT NULL default '0000-00-00 00:00:00',
  `day_of_the_week_id` tinyint(4) unsigned NOT NULL default '0',
  `event_id_location_1` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_2` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_3` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_4` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_5` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_6` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_7` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_8` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_9` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_10` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_11` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_12` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_13` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_14` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_15` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_16` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_17` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_18` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_19` mediumint(9) unsigned NOT NULL default '0',
  `event_id_location_20` mediumint(9) unsigned NOT NULL default '0',
  PRIMARY KEY  (`date_time_id`),
  UNIQUE KEY `starting_date_time` (`schedule_date_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1613 ;

-- --------------------------------------------------------

-- 
-- Table structure for table `booking_user`
-- 

CREATE TABLE `booking_user` (
  `user_id` mediumint(9) unsigned NOT NULL auto_increment,
  `credit_type_id` smallint(6) NOT NULL default '1',
  `username` varchar(16) NOT NULL default '',
  `passwd` varchar(36) NOT NULL default '',
  `firstname` varchar(100) NOT NULL default '',
  `lastname` varchar(100) NOT NULL default '',
  `address_l1` varchar(100) NOT NULL default '',
  `address_l2` varchar(100) NOT NULL default '',
  `address_town` varchar(100) NOT NULL default '',
  `address_county` varchar(100) NOT NULL default '',
  `address_postcode` varchar(20) NOT NULL default '',
  `groups` varchar(100) NOT NULL default '',
  `email` varchar(100) NOT NULL default '',
  `max_bookings` int(11) NOT NULL default '2',
  `block_book` set('0','1') NOT NULL default '0',
  `is_admin` set('0','1') NOT NULL default '0',
  `booking_credits` varchar(16) NOT NULL default 'Not used',
  `login_enabled` set('0','1') NOT NULL default '1',
  `phone_home` varchar(20) NOT NULL default '',
  `phone_work` varchar(20) NOT NULL default '',
  `phone_mobile` varchar(20) NOT NULL default '',
  `dob` date NOT NULL default '0000-00-00',
  `gender` set('male','female') NOT NULL default '',
  `is_member` set('0','1') NOT NULL default '0',
  `mail_opt_out` set('0','1') NOT NULL default '0',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `user_id` (`user_id`),
  KEY `credit_type_id` (`credit_type_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=139 ;

-- 
-- Dumping data for table `booking_user`
-- 

INSERT INTO `booking_user` (`user_id`, `credit_type_id`, `username`, `passwd`, `firstname`, `lastname`, `address_l1`, `address_l2`, `address_town`, `address_county`, `address_postcode`, `groups`, `email`, `max_bookings`, `block_book`, `is_admin`, `booking_credits`, `login_enabled`, `phone_home`, `phone_work`, `phone_mobile`, `dob`, `gender`, `is_member`, `mail_opt_out`) VALUES 
(1, 1, 'administrator', '6feb301ddb3c657d1e4eca90c27444e7:ff', 'Admin', 'User', '', '', '', '', '', '', 'me@mydomain.com', 8, '0', '1', 'Not used', '1', '', '', '', '0000-00-00', '', '0', '0');

-- --------------------------------------------------------

-- 
-- Table structure for table `booking_user_buddies`
-- 

CREATE TABLE `booking_user_buddies` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` bigint(20) NOT NULL default '0',
  `buddy_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`buddy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_user_buddies`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_user_buddies_pending`
-- 

CREATE TABLE `booking_user_buddies_pending` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` bigint(20) NOT NULL default '0',
  `buddy_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`buddy_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_user_buddies_pending`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_user_groups`
-- 

CREATE TABLE `booking_user_groups` (
  `user_group_id` bigint(20) unsigned NOT NULL auto_increment,
  `user_id` mediumint(9) NOT NULL default '0',
  `group_id` mediumint(9) NOT NULL default '0',
  `created` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`user_group_id`),
  KEY `user_id` (`user_id`,`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_user_groups`
-- 


-- --------------------------------------------------------

-- 
-- Table structure for table `booking_user_options`
-- 

CREATE TABLE `booking_user_options` (
  `id` bigint(20) NOT NULL auto_increment,
  `user_id` bigint(20) NOT NULL default '0',
  `option_id` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `user_id` (`user_id`,`option_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- 
-- Dumping data for table `booking_user_options`
-- 

