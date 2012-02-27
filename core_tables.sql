#
# Encoding: Unicode (UTF-8)
#

# The core will try to work with and from SMF tables, but there are exceptions.
# The following may be needed.

SET FOREIGN_KEY_CHECKS = 0;

# ------------------------------------------------------------------
# Optional

DROP TABLE IF EXISTS `acl_privileges`;

CREATE TABLE `acl_privileges` (
  `id_privilege` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `privilege_module` varchar(255) NOT NULL DEFAULT '',
  `privilege_name` varchar(255) NOT NULL DEFAULT '',
  `privilege_group` varchar(255) DEFAULT '',
  `privilege_user` int(8) unsigned NOT NULL DEFAULT '0',
  `privilege_allow` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_privilege`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# ------------------------------------------------------------------
# Optional

DROP TABLE IF EXISTS `acl_roles`;

CREATE TABLE `acl_roles` (
  `id_role` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `role_name` varchar(255) NOT NULL,
  `role_inherits` varchar(255) NOT NULL,
  `role_module` smallint(4) unsigned NOT NULL DEFAULT '0',
  `role_title` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `role_name` (`role_name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `acl_roles`
	(`id_role`, `role_name`, `role_inherits`, `role_module`, `role_title`)
VALUES
	(1, 'admin', '', 0, 'Administrator'),
	(2, 'member', '', 0, 'Member');

# ------------------------------------------------------------------

DROP TABLE IF EXISTS `api_hooks`;

CREATE TABLE `api_hooks` (
  `id_api_hook` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `api_hook_namespace` varchar(255) NOT NULL DEFAULT '',
  `api_hook_name` varchar(255) NOT NULL DEFAULT '',
  `api_hook_callback` varchar(255) NOT NULL DEFAULT '',
  `api_hook_owner` varchar(255) NOT NULL DEFAULT '',
  `api_hook_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_api_hook`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `api_hooks`
    (`id_api_hook`, `api_hook_namespace`, `api_hook_name`, `api_hook_callback`, `api_hook_owner`, `api_hook_enabled`)
VALUES
    (1, 'core', 'user-search', 'Core_API,userSearch', 'com.fustrate.core', 1);

# ------------------------------------------------------------------

DROP TABLE IF EXISTS `hooks`;

CREATE TABLE `hooks` (
  `id_hook` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hook_namespace` varchar(255) NOT NULL DEFAULT '',
  `hook_name` varchar(255) NOT NULL DEFAULT '',
  `hook_callback` varchar(255) NOT NULL DEFAULT '',
  `hook_owner` varchar(255) NOT NULL DEFAULT '',
  `hook_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_hook`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# ------------------------------------------------------------------

DROP TABLE IF EXISTS `modules`;

CREATE TABLE `modules` (
  `id_module` smallint(4) NOT NULL AUTO_INCREMENT,
  `module_name` varchar(255) NOT NULL DEFAULT '',
  `module_url` varchar(255) NOT NULL,
  `module_identifier` varchar(255) NOT NULL,
  `module_class` varchar(255) NOT NULL,
  PRIMARY KEY (`id_module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# ------------------------------------------------------------------

DROP TABLE IF EXISTS `routes`;

CREATE TABLE `routes` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# ------------------------------------------------------------------

DROP TABLE IF EXISTS `themes`;

CREATE TABLE `themes` (
  `id_theme` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `theme_name` varchar(255) NOT NULL,
  `theme_dir` varchar(255) NOT NULL DEFAULT '',
  `theme_class` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_theme`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `themes`
	(`id_theme`, `theme_name`, `theme_dir`, `theme_class`)
VALUES
	(1, 'Default', 'default', 'DefaultTheme');

# ------------------------------------------------------------------
# Users

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# ------------------------------------------------------------------

DROP TABLE IF EXISTS `sessions`;

CREATE TABLE `sessions` (
  `session_id` char(32) NOT NULL,
  `last_update` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (session_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

# ------------------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 1;
