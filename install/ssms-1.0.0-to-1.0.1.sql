ALTER TABLE `servers` ADD `multic` SET( 'yes', 'no' ) NOT NULL DEFAULT 'yes'
UPDATE `ssms`.`config` SET `config` = '1.0.1' WHERE `config`.`setting` = 'version' AND `config`.`config` = '1.0.0';
