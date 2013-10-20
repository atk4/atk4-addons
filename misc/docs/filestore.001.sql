# Dump of table filestore_file
# ------------------------------------------------------------

DROP TABLE IF EXISTS `filestore_file`;

CREATE TABLE `filestore_file` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filestore_type_id` int(11) NOT NULL DEFAULT '0',
  `filestore_volume_id` int(11) NOT NULL DEFAULT '0',
  `filename` varchar(255) NOT NULL DEFAULT '',
  `original_filename` varchar(255) DEFAULT NULL,
  `filesize` int(11) NOT NULL DEFAULT '0',
  `filenum` int(11) NOT NULL DEFAULT '0',
  `deleted` varchar(2) DEFAULT 'N',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table filestore_image
# ------------------------------------------------------------

DROP TABLE IF EXISTS `filestore_image`;

CREATE TABLE `filestore_image` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `original_file_id` int(11) NOT NULL DEFAULT '0',
  `thumb_file_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table filestore_type
# ------------------------------------------------------------

DROP TABLE IF EXISTS `filestore_type`;

CREATE TABLE `filestore_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `mime_type` varchar(64) NOT NULL DEFAULT '',
  `extension` varchar(5) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `filestore_type` WRITE;
/*!40000 ALTER TABLE `filestore_type` DISABLE KEYS */;

INSERT INTO `filestore_type` (`id`, `name`, `mime_type`, `extension`)
VALUES
	(1,'png','image/png','png'),
	(2,'jpeg','image/jpeg','jpeg'),
	(3,'gif','image/gif','gif');

/*!40000 ALTER TABLE `filestore_type` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table filestore_volume
# ------------------------------------------------------------

DROP TABLE IF EXISTS `filestore_volume`;

CREATE TABLE `filestore_volume` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL DEFAULT '',
  `dirname` varchar(255) NOT NULL DEFAULT '',
  `total_space` bigint(20) NOT NULL DEFAULT '0',
  `used_space` bigint(20) NOT NULL DEFAULT '0',
  `stored_files_cnt` int(11) NOT NULL DEFAULT '0',
  `enabled` enum('Y','N') DEFAULT 'Y',
  `last_filenum` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `filestore_volume` WRITE;
/*!40000 ALTER TABLE `filestore_volume` DISABLE KEYS */;

INSERT INTO `filestore_volume` (`id`, `name`, `dirname`, `total_space`, `used_space`, `stored_files_cnt`, `enabled`, `last_filenum`)
VALUES
	(1,'upload','upload',1000000000,0,3,'Y',NULL);

/*!40000 ALTER TABLE `filestore_volume` ENABLE KEYS */;
UNLOCK TABLES;
