SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `filestore_volume`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `filestore_volume` ;
SHOW WARNINGS;

CREATE TABLE IF NOT EXISTS `filestore_volume` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(128) NOT NULL COMMENT 'Volume name',
  `dirname` VARCHAR(128) NULL COMMENT 'Folder name',
  `total_space` INT UNSIGNED NULL COMMENT 'Total space (not implemented)',
  `used_space` INT UNSIGNED NULL COMMENT 'Used space',
  `stored_files_cnt` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Approximate count of stored files',
  `enabled` TINYINT(1) NULL COMMENT 'Volume enabled?',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;
SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `filestore_type`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `filestore_type` ;
SHOW WARNINGS;

CREATE TABLE IF NOT EXISTS `filestore_type` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(64) NOT NULL COMMENT 'Name',
  `mime_type` VARCHAR(128) NULL COMMENT 'MIME type',
  `extension` VARCHAR(8) NULL COMMENT 'Filename extension',
  PRIMARY KEY (`id`))
ENGINE = InnoDB;
SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `filestore_file`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `filestore_file` ;
SHOW WARNINGS;

CREATE TABLE IF NOT EXISTS `filestore_file` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `filestore_type_id` INT UNSIGNED NOT NULL COMMENT 'File type',
  `filestore_volume_id` INT UNSIGNED NOT NULL COMMENT 'Volume',
  `original_filename` TEXT NULL COMMENT 'Original Name',
  `filename` VARCHAR(255) NOT NULL COMMENT 'Internal Name',
  `filesize` INT NOT NULL COMMENT 'File size',
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
SHOW WARNINGS;

-- -----------------------------------------------------
-- Table `filestore_image`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `filestore_image` ;
SHOW WARNINGS;

CREATE TABLE IF NOT EXISTS `filestore_image` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `original_file_id` INT UNSIGNED NOT NULL COMMENT 'Original File',
  `thumb_file_id` INT UNSIGNED NULL COMMENT 'Thumbnail file',
  PRIMARY KEY (`id`),
  INDEX `fk_filestore_image_filestore_file1_idx` (`original_file_id` ASC),
  INDEX `fk_filestore_image_filestore_file2_idx` (`thumb_file_id` ASC),
  CONSTRAINT `fk_filestore_image_filestore_file1`
    FOREIGN KEY (`original_file_id`)
    REFERENCES `filestore_file` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_filestore_image_filestore_file2`
    FOREIGN KEY (`thumb_file_id`)
    REFERENCES `filestore_file` (`id`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;
SHOW WARNINGS;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
