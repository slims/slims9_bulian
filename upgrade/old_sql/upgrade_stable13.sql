-- SENAYAN 3.0 stable 13
-- Senayan SQL Database upgrade script
INSERT INTO `setting` (`setting_id`, `setting_name`, `setting_value`) VALUES (NULL, 'circulation_receipt', 'b:1;'),
(NULL, 'barcode_encoding', 's:4:"128B";');
