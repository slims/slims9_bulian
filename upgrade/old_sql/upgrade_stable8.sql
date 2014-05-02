-- SENAYAN 3.0 stable 8
-- Senayan SQL Database upgrade script

ALTER TABLE `item` ADD `call_number` VARCHAR(50) NULL DEFAULT NULL AFTER `biblio_id`;
ALTER TABLE `biblio` ADD `opac_hide` INT(1) NOT NULL DEFAULT 0 AFTER `file_att`;
ALTER TABLE `biblio` DROP INDEX `title_ft_idx`, ADD FULLTEXT `title_ft_idx` (`title`, `series_title`);
