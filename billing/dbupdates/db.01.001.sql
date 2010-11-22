
CREATE TABLE `billing_paymentmethod` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `merchant_type` varchar(20) DEFAULT NULL,
  `card_ref` varchar(40) DEFAULT NULL,
  `cc_type` varchar(10) DEFAULT NULL,
  `cc_number` varchar(25) DEFAULT NULL,
  `cc_name` varchar(255) DEFAULT NULL,
  `cc_expiry` varchar(5) DEFAULT NULL,
  `cc_cvn` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;



CREATE TABLE `billing_transaction` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sent` text,
  `response` text,
  `result` int(11) DEFAULT NULL,
  `ts` datetime DEFAULT NULL,
  `time_taken` int(11) DEFAULT NULL,
  `calc_hash` varchar(255) DEFAULT NULL,
  `md5_hash` varchar(255) DEFAULT NULL,
  `sha1_hash` varchar(255) DEFAULT NULL,
  `billing_paymentmethod_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `billing_paymentmethod_id` (`billing_paymentmethod_id`),
  CONSTRAINT `billing_transaction_ibfk_1` FOREIGN KEY (`billing_paymentmethod_id`) REFERENCES `billing_paymentmethod` (`id`)
) ENGINE=InnoDB ;

