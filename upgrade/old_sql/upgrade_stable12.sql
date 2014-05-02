-- SENAYAN 3.0 stable 12
-- Senayan SQL Database upgrade script
-- NOTE: Changes in table 'content' for stable12 are not reflected in this upgrade script

-- biblio_attachment table change
ALTER TABLE `biblio_attachment` ADD `access_limit` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL AFTER `access_type`;

-- member table change
ALTER TABLE `member` ADD `last_login_ip` VARCHAR(20) NULL AFTER `is_pending`;
ALTER TABLE `member` ADD `last_login` DATETIME NULL AFTER `is_pending`;
ALTER TABLE `member` ADD `mpasswd` CHAR(32) NULL AFTER `is_pending`;

