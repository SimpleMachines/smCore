# ------------------------------------------------------------
#
# Replace {db_prefix} with your database prefix.
#
# ------------------------------------------------------------

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET NAMES utf8;

# ------------------------------------------------------------
# Dump of table event_listeners
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}event_listeners`;

CREATE TABLE `{db_prefix}event_listeners` (
  `id_listener` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `listener_name` tinytext CHARACTER SET latin1 NOT NULL,
  `listener_callback` tinytext CHARACTER SET latin1 NOT NULL,
  `listener_enabled` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_listener`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# ------------------------------------------------------------
# Dump of table lang_packages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}lang_packages`;

CREATE TABLE `{db_prefix}lang_packages` (
  `id_package` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `package_name` varchar(255) NOT NULL DEFAULT '',
  `package_type` varchar(255) NOT NULL,
  PRIMARY KEY (`id_package`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{db_prefix}lang_packages` (`id_package`, `package_name`, `package_type`)
VALUES
	(1,'org.smcore.common','common'),
	(2,'org.smcore.auth','common'),
	(3,'org.smcore.auth.menu','menu');
# ------------------------------------------------------------
# Dump of table lang_strings
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}lang_strings`;

CREATE TABLE `{db_prefix}lang_strings` (
  `string_language` smallint(4) unsigned NOT NULL,
  `string_package` smallint(4) unsigned NOT NULL,
  `string_key` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
  `string_value` text NOT NULL,
  UNIQUE KEY `language_key` (`string_language`,`string_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{db_prefix}lang_strings` (`string_language`, `string_package`, `string_key`, `string_value`)
VALUES
	(1,1,'admin','Admin'),
	(1,4,'admin.authenticate','Authenticate'),
	(1,4,'admin.maintenance.empty_cache','Clear the Cache'),
	(1,4,'admin.maintenance.empty_cache.button','Clear'),
	(1,4,'admin.maintenance.empty_cache.help','This function will empty out the cache should you need it to be cleared.'),
	(1,4,'admin.maintenance.empty_logs','Empty Unimportant Logs'),
	(1,4,'admin.maintenance.empty_logs.button','Empty'),
	(1,4,'admin.maintenance.empty_logs.help','This function will empty out all unimportant logs. This should be avoided unless something\'s wrong, but it doesn\'t hurt anything.'),
	(1,4,'admin.maintenance.titles.main','Maintenance Tasks'),
	(1,5,'admin.menu.admin','Administration'),
	(1,5,'admin.menu.config','Configuration'),
	(1,5,'admin.menu.main','Admin Center'),
	(1,5,'admin.menu.maintenance','Maintenance'),
	(1,5,'admin.menu.maintenance.database','Database'),
	(1,5,'admin.menu.maintenance.main','General'),
	(1,5,'admin.menu.modules','Modules'),
	(1,4,'admin.modules.developer','Developer'),
	(1,4,'admin.modules.identifier','Identifier'),
	(1,4,'admin.modules.name','Name'),
	(1,4,'admin.modules.version','Version'),
	(1,4,'admin.settings.cache_default_ttl','Default Cache Time'),
	(1,4,'admin.settings.cache_default_ttl.help','Time to keep data in the cache before considering it stale (out of date).'),
	(1,4,'admin.settings.cache_dir','Cache Directory'),
	(1,4,'admin.settings.cache_dir.help','Directory to save cache files (i.e. compiled Twig templates, file-based caching) in'),
	(1,4,'admin.settings.cache_driver','Cache Driver'),
	(1,4,'admin.settings.cache_driver.help','@todo'),
	(1,4,'admin.settings.cache_driver_apc','APC - Alternative PHP Cache'),
	(1,4,'admin.settings.cache_driver_blackhole','No Caching'),
	(1,4,'admin.settings.cache_driver_file','Filesystem (Default)'),
	(1,4,'admin.settings.cache_driver_memcached','Memcached'),
	(1,4,'admin.settings.caching','Caching'),
	(1,4,'admin.settings.cookies_sessions','Cookies & Sessions'),
	(1,4,'admin.settings.cookie_domain','Cookie Domain'),
	(1,4,'admin.settings.cookie_name','Cookie Name'),
	(1,4,'admin.settings.cookie_path','Cookie Directory'),
	(1,4,'admin.settings.module_dir','Modules Directory'),
	(1,4,'admin.settings.module_dir.help','Directory where smCore will search for installed modules'),
	(1,4,'admin.settings.session_db_driven','Database-Driven Sessions'),
	(1,4,'admin.settings.session_db_driven.help','This option makes use of the database for session storage - it is best for load balanced servers, but helps with all timeout issues and can make the site faster.'),
	(1,4,'admin.settings.theme_dir','Themes Directory'),
	(1,4,'admin.settings.theme_dir.help','Directory where themes are installed'),
	(1,4,'admin.settings.url','Site URL'),
	(1,4,'admin.settings.url.help','The full URL to this site'),
	(1,4,'admin.settings.urls_directories','URLs & Directories'),
	(1,4,'admin.titles.authenticate','Admin Authentication'),
	(1,4,'admin.titles.cache','Cache Info'),
	(1,4,'admin.titles.installed_modules','Installed Modules'),
	(1,4,'admin.titles.main','Admin Center'),
	(1,4,'admin.titles.settings','General Settings'),
	(1,4,'admin.welcome_message','Welcome, %1$s! This is your \"Administration Center\". From here, you can edit settings, maintain your website, view logs, install packages, manage themes, and many other things.\n\nIf you have any trouble, please look at the \"<a href=\"%2$s\">Support & Credits</a>\" page. If the information there doesn\'t help you, feel free to <a href=\"http://smcore.org/forum/\">look to us for help</a> with the problem.'),
	(1,2,'auth.admin.titles.main','User Administration'),
	(1,2,'auth.exception.register.username_reserved','The username you tried to register with is reserved.'),
	(1,2,'auth.exceptions.login.incorrect_login','Error! Incorrect username or password.'),
	(1,2,'auth.exceptions.register.email_already_used','The email address you entered is already in use. Did you forget your password?'),
	(1,2,'auth.exceptions.register.invalid_email','The email address you entered does not seem to be valid.'),
	(1,2,'auth.exceptions.register.no_email','Please enter an email address.'),
	(1,2,'auth.exceptions.register.no_password','Please enter a password.'),
	(1,2,'auth.exceptions.register.password_mismatch','Please make sure you entered the same password in both boxes.'),
	(1,2,'auth.exceptions.register.username_taken','The username you entered is already taken.'),
	(1,2,'auth.exceptions.register.username_too_long','The username you entered is too long.'),
	(1,2,'auth.exceptions.register.username_too_short','The username you entered is too short.'),
	(1,2,'auth.login.log_in','Log In'),
	(1,2,'auth.login.log_in_for','Log in for'),
	(1,2,'auth.login.stay_logged_in','Stay logged in'),
	(1,3,'auth.menu.admin.maintenance.users','Users'),
	(1,3,'auth.menu.admin.users','Users'),
	(1,3,'auth.menu.login','Log In'),
	(1,3,'auth.menu.logout','Log Out'),
	(1,3,'auth.menu.register','Register'),
	(1,2,'auth.register','Register'),
	(1,2,'auth.register.agree','I accept the terms of the agreement and I am at least 13 years old.'),
	(1,2,'auth.register.agreement','Registration Agreement'),
	(1,2,'auth.register.agreement_text','This is the registration agreement text.\n\nYou should agree to it.'),
	(1,2,'auth.register.agree_young','I accept the terms of the agreement and I am younger than 13 years old.'),
	(1,2,'auth.register.captcha','Captcha'),
	(1,2,'auth.register.finished','Finished Registering'),
	(1,2,'auth.register.form','Registration Form'),
	(1,2,'auth.register.form_title','Registration Form'),
	(1,2,'auth.register.required_info','Required Information'),
	(1,2,'auth.register.verification','Verification'),
	(1,2,'auth.register.verify_password','Verify Password'),
	(1,1,'credits','Credits'),
	(1,1,'email','Email'),
	(1,1,'exceptions.error_code_403','You are not allowed to access this page.'),
	(1,1,'exceptions.error_code_404','Page not found.'),
	(1,1,'exceptions.error_code_unknown','Unknown error.'),
	(1,1,'exceptions.modules.invalid_controller','Invalid controller \"%s\", file not found.'),
	(1,1,'exceptions.modules.invalid_csrf','Hacking attempt...?'),
	(1,1,'exceptions.modules.invalid_model','Could not create a module model named \"%s\".'),
	(1,1,'exceptions.modules.invalid_storage','Could not instantiate a module storage named \"%s\".'),
	(1,1,'exceptions.modules.method_not_callable','The \"%1$s\" controller does not have a method named \"%2$s\".'),
	(1,1,'exceptions.no_guest_access','This page requires you to be logged in.'),
	(1,1,'exceptions.no_permission','You do not have the permissions required to access this page.'),
	(1,1,'minutes','minutes'),
	(1,1,'password','Password'),
	(1,1,'privacy_policy','Privacy Policy'),
	(1,1,'save','Save'),
	(1,1,'submit','Submit'),
	(1,1,'terms_and_privacy','Terms of Use & Privacy Policy'),
	(1,1,'terms_of_use','Terms of Use'),
	(1,1,'username','Username\n');

# ------------------------------------------------------------
# Dump of table languages
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}languages`;

CREATE TABLE `{db_prefix}languages` (
  `id_language` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `language_code` varchar(255) NOT NULL DEFAULT '',
  `language_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_language`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{db_prefix}languages` (`id_language`, `language_code`, `language_name`)
VALUES
	(1,'en_US','English (American)');

# ------------------------------------------------------------
# Dump of table menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}menu`;

CREATE TABLE `{db_prefix}menu` (
  `id_menu` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `menu_title` varchar(255) NOT NULL DEFAULT '',
  `menu_visible` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `menu_module` varchar(255) NOT NULL DEFAULT '',
  `menu_parent` int(8) unsigned NOT NULL,
  `menu_order` tinyint(3) unsigned NOT NULL DEFAULT '50',
  `menu_url` varchar(255) NOT NULL,
  `menu_permission` varchar(255) NOT NULL DEFAULT '',
  `menu_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_menu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{db_prefix}menu` (`id_menu`, `menu_title`, `menu_visible`, `menu_module`, `menu_parent`, `menu_order`, `menu_url`, `menu_permission`, `menu_name`)
VALUES
	(1,'auth.menu.login',1,'org.smcore.auth',0,98,'/login/','org.smcore.auth.is_guest','login'),
	(2,'auth.menu.register',1,'org.smcore.auth',0,99,'/register/','org.smcore.auth.is_guest','register'),
	(3,'auth.menu.logout',1,'org.smcore.auth',0,99,'/logout/','org.smcore.auth.is_member','logout');

# ------------------------------------------------------------
# Dump of table permissions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}permissions`;

CREATE TABLE `{db_prefix}permissions` (
  `id_permission` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `permission_role` mediumint(8) unsigned NOT NULL,
  `permission_namespace` varchar(255) NOT NULL DEFAULT '',
  `permission_state` tinyint(1) NOT NULL DEFAULT '0',
  `permission_name` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_permission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# ------------------------------------------------------------
# Dump of table roles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}roles`;

CREATE TABLE `{db_prefix}roles` (
  `id_role` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `role_title` varchar(255) NOT NULL DEFAULT '',
  `role_inherits` mediumint(8) unsigned NOT NULL,
  `role_permission` varchar(255) NOT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `role_name` (`role_title`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{db_prefix}roles` (`id_role`, `role_title`, `role_inherits`, `role_permission`)
VALUES
	(0,'Guest',0,'is_guest'),
	(1,'Administrator',2,'is_admin'),
	(2,'Member',0,'is_member');

# ------------------------------------------------------------
# Dump of table sessions
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}sessions`;

CREATE TABLE `{db_prefix}sessions` (
  `id_session` char(32) NOT NULL DEFAULT '',
  `session_expires` int(10) NOT NULL,
  `session_data` text NOT NULL,
  PRIMARY KEY (`id_session`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# ------------------------------------------------------------
# Dump of table themes
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}themes`;

CREATE TABLE `{db_prefix}themes` (
  `id_theme` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `theme_name` varchar(255) NOT NULL,
  `theme_dir` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`id_theme`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `{db_prefix}themes` (`id_theme`, `theme_name`, `theme_dir`)
VALUES
	(1,'Default Theme','default');

# ------------------------------------------------------------
# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `{db_prefix}users`;

CREATE TABLE `{db_prefix}users` (
  `id_user` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_login` varchar(45) NOT NULL,
  `user_email` varchar(255) NOT NULL,
  `user_registered` int(10) unsigned NOT NULL,
  `user_token` varchar(32) NOT NULL,
  `user_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_pass` varchar(64) NOT NULL DEFAULT '',
  `user_primary_role` smallint(4) unsigned NOT NULL DEFAULT '2',
  `user_display_name` varchar(50) NOT NULL DEFAULT '',
  `user_additional_roles` varchar(255) NOT NULL DEFAULT '',
  `user_language` smallint(4) unsigned NOT NULL DEFAULT '1',
  `user_theme` smallint(4) unsigned NOT NULL DEFAULT '1',
  `user_activation` varchar(32) NOT NULL DEFAULT '',
  `user_reset_key` varchar(32) NOT NULL DEFAULT '',
  UNIQUE KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;