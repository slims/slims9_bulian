ALTER TABLE  `loan` ADD  `input_date` DATETIME NULL DEFAULT NULL ,
ADD  `last_update` DATETIME NULL DEFAULT NULL ,
ADD  `uid` INT( 11 ) NULL DEFAULT NULL ,
ADD INDEX (  `input_date` ,  `last_update` ,  `uid` ) ;