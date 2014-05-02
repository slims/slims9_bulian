-- SENAYAN 3.0 stable 7
-- Senayan SQL Database upgrade script

ALTER TABLE `item` CHANGE `item_code` `item_code` VARCHAR(20) NOT NULL;
ALTER TABLE `item` CHANGE `biblio_id` `biblio_id` INT( 11 ) NOT NULL;
ALTER TABLE `biblio` CHANGE `title` `title` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `mst_publisher` DROP `publisher_place`;
ALTER TABLE `setting` CHANGE `setting_value` `setting_value` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL;
ALTER TABLE `item` ADD `inventory_code` VARCHAR(200) NULL DEFAULT NULL AFTER `item_code`;
ALTER TABLE `item` ADD UNIQUE (`inventory_code`);
ALTER TABLE `member` ADD `is_pending` SMALLINT(1) NOT NULL DEFAULT '0' AFTER `member_notes`;
