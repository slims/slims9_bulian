-- SENAYAN 3.0 stable 5
-- Senayan SQL Database upgrade script

ALTER TABLE `mst_member_type` ADD `grace_periode` INT(2) NULL DEFAULT '0' AFTER `fine_each_day`;
ALTER TABLE `mst_loan_rules` ADD `grace_periode` INT(2) NULL DEFAULT '0' AFTER `fine_each_day`;
ALTER TABLE `stock_take_item` ADD `gmd_name` VARCHAR(30) NULL AFTER `title`;
ALTER TABLE `stock_take_item` ADD `coll_type_name` VARCHAR(30) NULL AFTER `classification`;
ALTER TABLE `stock_take_item` ADD `call_number` VARCHAR(50) NULL AFTER `coll_type_name`;
ALTER TABLE `stock_take_item` ADD `location` VARCHAR(100) NULL AFTER `call_number`;
ALTER TABLE `stock_take_item` ADD INDEX `item_properties_idx` (`gmd_name`, `classification`, `coll_type_name`, `location`);
