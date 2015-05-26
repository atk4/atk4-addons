SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `filestore_volume`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `filestore_volume` ;

CREATE TABLE IF NOT EXISTS `filestore_volume` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Volume name',
  `dirname` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Folder name',
  `total_space` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Total space (not implemented)',
  `used_space` BIGINT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Used space (not implemented)',
  `stored_files_cnt` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Approximate count of stored files',
  `enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Volume enabled?',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `filestore_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `filestore_type` ;

CREATE TABLE IF NOT EXISTS `filestore_type` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Name',
  `mime_type` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'MIME type',
  `extension` VARCHAR(5) NOT NULL DEFAULT '' COMMENT 'Filename extension',
  `allow` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `filestore_file`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `filestore_file` ;

CREATE TABLE IF NOT EXISTS `filestore_file` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filestore_type_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'File type',
  `filestore_volume_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Volume',
  `original_filename` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Original Name',
  `filename` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Internal Name',
  `filesize` INT NOT NULL DEFAULT 0 COMMENT 'File size',
  `deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Deleted file',
  PRIMARY KEY (`id`),
  INDEX `fk_filestore_file_filestore_type1_idx` (`filestore_type_id` ASC),
  INDEX `fk_filestore_file_filestore_volume1_idx` (`filestore_volume_id` ASC),
  CONSTRAINT `fk_filestore_file_filestore_type1`
    FOREIGN KEY (`filestore_type_id`)
    REFERENCES `filestore_type` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_filestore_file_filestore_volume1`
    FOREIGN KEY (`filestore_volume_id`)
    REFERENCES `filestore_volume` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `filestore_image`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `filestore_image` ;

CREATE TABLE IF NOT EXISTS `filestore_image` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_file_id` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Original File',
  `thumb_file_id` INT UNSIGNED NULL DEFAULT NULL COMMENT 'Thumbnail file',
  PRIMARY KEY (`id`),
  INDEX `fk_filestore_image_filestore_file1_idx` (`original_file_id` ASC),
  INDEX `fk_filestore_image_filestore_file2_idx` (`thumb_file_id` ASC),
  CONSTRAINT `fk_filestore_image_filestore_file1`
    FOREIGN KEY (`original_file_id`)
    REFERENCES `filestore_file` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_filestore_image_filestore_file2`
    FOREIGN KEY (`thumb_file_id`)
    REFERENCES `filestore_file` (`id`)
    ON DELETE SET NULL
    ON UPDATE CASCADE)
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;





-- -----------------------------------------------------
-- Data for table `filestore_volume`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `filestore_volume` (`id`, `name`, `dirname`, `total_space`, `used_space`, `stored_files_cnt`, `enabled`) VALUES (1, 'upload', 'upload', 1000000000, 0, 0, 1);
COMMIT;

-- -----------------------------------------------------
-- Data for table `filestore_type`
-- -----------------------------------------------------
START TRANSACTION;
INSERT INTO `filestore_type` (`id`, `name`, `mime_type`, `extension`, `allow`) VALUES (1, 'png', 'image/png', 'png', 1);
INSERT INTO `filestore_type` (`id`, `name`, `mime_type`, `extension`, `allow`) VALUES (2, 'jpeg', 'image/jpeg', 'jpg', 1);
INSERT INTO `filestore_type` (`id`, `name`, `mime_type`, `extension`, `allow`) VALUES (3, 'gif', 'image/gif', 'gif', 1);
INSERT INTO `filestore_type` (`id`, `name`, `mime_type`, `extension`, `allow`) VALUES (4, 'pdf', 'application/pdf', 'pdf', 1);
INSERT INTO `filestore_type` (`id`, `name`, `mime_type`, `extension`, `allow`) VALUES (5, 'doc', 'application/doc', 'doc', 0);
INSERT INTO `filestore_type` (`id`, `name`, `mime_type`, `extension`, `allow`) VALUES (6, 'xls', 'application/xls', 'xls', 0);
COMMIT;
