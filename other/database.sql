-- phpMyAdmin SQL Dump
-- version 3.4.9
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Aug 05, 2012 at 07:17 PM
-- Server version: 5.5.19
-- PHP Version: 5.4.0RC4
-- 
-- SQL Dump
-- needs tweaking still

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `smcore`
--

-- --------------------------------------------------------

--
-- Table structure for table `acl_privileges`
--

CREATE TABLE IF NOT EXISTS `acl_privileges` (
  `id_privilege` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `privilege_module` varchar(255) NOT NULL DEFAULT '',
  `privilege_name` varchar(255) NOT NULL DEFAULT '',
  `privilege_group` varchar(255) DEFAULT '',
  `privilege_user` int(8) unsigned NOT NULL DEFAULT '0',
  `privilege_allow` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_privilege`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `acl_roles`
--

CREATE TABLE IF NOT EXISTS `acl_roles` (
  `id_role` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) NOT NULL,
  `role_inherits` varchar(255) NOT NULL,
  `role_module` smallint(4) unsigned NOT NULL DEFAULT '0',
  `role_title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `acl_roles`
--

INSERT INTO `acl_roles` (`id_role`, `role_name`, `role_inherits`, `role_module`, `role_title`) VALUES
(1, 'admin', '', 0, 'Administrator'),
(2, 'member', '', 0, 'Member');

-- --------------------------------------------------------

--
-- Table structure for table `api_hooks`
--

CREATE TABLE IF NOT EXISTS `api_hooks` (
  `id_api_hook` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `api_hook_namespace` varchar(255) NOT NULL DEFAULT '',
  `api_hook_name` varchar(255) NOT NULL DEFAULT '',
  `api_hook_callback` varchar(255) NOT NULL DEFAULT '',
  `api_hook_owner` varchar(255) NOT NULL DEFAULT '',
  `api_hook_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_api_hook`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `api_hooks`
--

INSERT INTO `api_hooks` (`id_api_hook`, `api_hook_namespace`, `api_hook_name`, `api_hook_callback`, `api_hook_owner`, `api_hook_enabled`) VALUES
(1, 'core', 'user-search', 'Core_API,userSearch', 'com.fustrate.core', 1);

-- --------------------------------------------------------

--
-- Table structure for table `hooks`
--

CREATE TABLE IF NOT EXISTS `hooks` (
  `id_hook` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hook_namespace` varchar(255) NOT NULL DEFAULT '',
  `hook_name` varchar(255) NOT NULL DEFAULT '',
  `hook_callback` varchar(255) NOT NULL DEFAULT '',
  `hook_owner` varchar(255) NOT NULL DEFAULT '',
  `hook_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_hook`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `languages`
--

CREATE TABLE IF NOT EXISTS `languages` (
  `id_language` int(11) NOT NULL AUTO_INCREMENT,
  `language_code` varchar(6) NOT NULL,
  `language_name` varchar(32) NOT NULL,
  PRIMARY KEY (`id_language`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `languages`
--

INSERT INTO `languages` (`id_language`, `language_code`, `language_name`) VALUES
(1, 'en_US', 'english');

-- --------------------------------------------------------

--
-- Table structure for table `lang_packages`
--

CREATE TABLE IF NOT EXISTS `lang_packages` (
  `id_package` int(11) NOT NULL AUTO_INCREMENT,
  `package_name` varchar(32) NOT NULL,
  `package_type` varchar(32) NOT NULL,
  PRIMARY KEY (`id_package`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `lang_packages`
--

INSERT INTO `lang_packages` (`id_package`, `package_name`, `package_type`) VALUES
(1, 'org.smcore.common', '');

-- --------------------------------------------------------

--
-- Table structure for table `lang_strings`
--

CREATE TABLE IF NOT EXISTS `lang_strings` (
  `id_string` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `string_language` smallint(4) unsigned NOT NULL,
  `string_package` smallint(4) unsigned NOT NULL,
  `string_key` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `string_value` text NOT NULL,
  PRIMARY KEY (`id_string`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
--
-- Dumping data for table `lang_strings`
--

INSERT INTO `lang_strings` (`string_key`, `string_value`, `string_package`, `string_lamguage`) VALUES
('exceptions.error_code_404', 'HTTP 404 - Page Not Found', 1, 1),
('exceptions.error_code_403', 'HTTP 403 - Forbidden', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `modules`
--

CREATE TABLE IF NOT EXISTS `modules` (
  `id_module` smallint(4) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `module_url` varchar(255) NOT NULL,
  `module_identifier` varchar(255) NOT NULL,
  `module_class` varchar(255) NOT NULL,
  PRIMARY KEY (`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `routes`
--

CREATE TABLE IF NOT EXISTS `routes` (
  `id_route` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `route_url` varchar(255) NOT NULL,
  `route_module` smallint(4) unsigned NOT NULL,
  `route_parent` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `route_title` varchar(255) NOT NULL,
  `route_method` varchar(255) NOT NULL DEFAULT '',
  `route_default` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `route_visible` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `route_allowed_roles` varchar(255) NOT NULL DEFAULT '',
  `route_order` smallint(2) unsigned NOT NULL DEFAULT '99',
  PRIMARY KEY (`id_route`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id_session` char(32) NOT NULL,
  `session_expires` int(10) unsigned NOT NULL,
  `session_data` text NOT NULL,
  PRIMARY KEY (`session_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `themes`
--

CREATE TABLE IF NOT EXISTS `themes` (
  `id_theme` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `theme_name` varchar(255) NOT NULL,
  `theme_dir` varchar(255) NOT NULL DEFAULT '',
  `theme_class` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_theme`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `themes`
--

INSERT INTO `themes` (`id_theme`, `theme_name`, `theme_dir`, `theme_class`) VALUES
(1, 'Default', 'default', 'DefaultTheme');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(45) NOT NULL,
  `user_pass` varchar(64) NOT NULL,
  `user_primary_role` smallint(4) unsigned NOT NULL DEFAULT '0',
  `user_display_name` varchar(50) NOT NULL,
  `user_given_name` varchar(50) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_registered` int(10) unsigned NOT NULL DEFAULT '0',
  `user_token` varchar(32) NOT NULL,
  `user_active` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `user_additional_roles` varchar(255) NOT NULL DEFAULT '',
  `user_language` varchar(255) NOT NULL,
  UNIQUE KEY `id_user` (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;
