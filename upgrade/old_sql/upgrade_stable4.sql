-- SENAYAN 3.0 stable 4
-- Senayan SQL Database upgrade script

ALTER TABLE `mst_member_type` DROP `is_pending`;
ALTER TABLE `mst_supplier` CHANGE `supplier_name` `supplier_name` VARCHAR(100);
ALTER TABLE `mst_supplier` CHANGE `address` `address` VARCHAR(100);
ALTER TABLE `mst_gmd` DROP INDEX `gmd_name`;
ALTER TABLE `mst_gmd` ADD UNIQUE (`gmd_name`); 
