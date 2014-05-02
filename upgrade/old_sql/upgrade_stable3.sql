-- SENAYAN 3.0 stable 3
-- Senayan SQL Database upgrade script

ALTER TABLE `biblio_author` ADD `level` INT(1) NOT NULL DEFAULT '1';
ALTER TABLE `biblio_topic` ADD `level` INT(1) NOT NULL DEFAULT '1';
ALTER TABLE `mst_gmd` CHANGE `gmd_code` `gmd_code` VARCHAR(3) COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `mst_language` CHANGE `language_id` `language_id` CHAR(5) COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `biblio` CHANGE `language_id` `language_id` CHAR(5) COLLATE utf8_unicode_ci NULL DEFAULT 'en';
ALTER TABLE `mst_item_status` CHANGE `item_status_id` `item_status_id` CHAR(3) COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `mst_gmd` DROP INDEX `gmd_code` ADD INDEX `gmd_code` (`gmd_code`);
ALTER TABLE `item` CHANGE `item_status_id` `item_status_id` CHAR(3) COLLATE utf8_unicode_ci NULL DEFAULT NULL;
ALTER TABLE `mst_author` CHANGE `authority_type` `authority_type` ENUM('p','o','c') NULL DEFAULT 'p';
