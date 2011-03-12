-- MySQL dump 10.13  Distrib 5.1.45, for pc-linux-gnu (i686)
--
-- Host: localhost    Database: as_01
-- ------------------------------------------------------
-- Server version	5.1.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `country`
--

DROP TABLE IF EXISTS `country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(8) NOT NULL,
  `name` varchar(128) DEFAULT NULL,
  `eu_member` enum('Y','N') NOT NULL DEFAULT 'N',
  `vat_period_length` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=249 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `country`
--

LOCK TABLES `country` WRITE;
/*!40000 ALTER TABLE `country` DISABLE KEYS */;
INSERT INTO `country` VALUES (1,'IE','Ireland','Y',2),(2,'UK','United Kingdom','Y',1),(3,'US','United States','N',1),(4,'CH','Switzerland','N',1),(5,'LV','Latvia','Y',1),(6,'RU','Russia','N',1),(7,'','United Kingdom Not EU','N',1),(8,'AF','Afghanistan','N',1),(9,'AX','&ETH;','N',1),(10,'AL','Albania','N',1),(11,'DZ','Algeria','N',1),(12,'AS','American Samoa','N',1),(13,'AD','Andorra','N',1),(14,'AO','Angola','N',1),(15,'AI','Anguilla','N',1),(16,'AQ','Antarctica','N',1),(17,'AG','Antigua And Barbuda','N',1),(18,'AR','Argentina','N',1),(19,'AM','Armenia','N',1),(20,'AW','Aruba','N',1),(21,'AU','Australia','N',1),(22,'AT','Austria','Y',1),(23,'AZ','Azerbaijan','N',1),(24,'BS','Bahamas','N',1),(25,'BH','Bahrain','N',1),(26,'BD','Bangladesh','N',1),(27,'BB','Barbados','N',1),(28,'BY','Belarus','N',1),(29,'BE','Belgium','Y',1),(30,'BZ','Belize','N',1),(31,'BJ','Benin','N',1),(32,'BM','Bermuda','N',1),(33,'BT','Bhutan','N',1),(34,'BO','Bolivia, Plurinational State Of','N',1),(35,'BA','Bosnia And Herzegovina','N',1),(36,'BW','Botswana','N',1),(37,'BV','Bouvet Island','N',1),(38,'BR','Brazil','N',1),(39,'IO','British Indian Ocean Territory','N',1),(40,'BN','Brunei Darussalam','N',1),(41,'BG','Bulgaria','Y',1),(42,'BF','Burkina Faso','N',1),(43,'BI','Burundi','N',1),(44,'KH','Cambodia','N',1),(45,'CM','Cameroon','N',1),(46,'CA','Canada','N',1),(47,'CV','Cape Verde','N',1),(48,'KY','Cayman Islands','N',1),(49,'CF','Central African Republic','N',1),(50,'TD','Chad','N',1),(51,'CL','Chile','N',1),(52,'CN','China','N',1),(53,'CX','Christmas Island','N',1),(54,'CC','Cocos (keeling) Islands','N',1),(55,'CO','Colombia','N',1),(56,'KM','Comoros','N',1),(57,'CG','Congo','N',1),(58,'CD','Congo, The Democratic Republic Of The','N',1),(59,'CK','Cook Islands','N',1),(60,'CR','Costa Rica','N',1),(61,'CI','C&ETH;&curren;te D\'ivoire','N',1),(62,'HR','Croatia','N',1),(63,'CU','Cuba','N',1),(64,'CY','Cyprus','Y',1),(65,'CZ','Czech Republic','Y',1),(66,'DK','Denmark','Y',1),(67,'DJ','Djibouti','N',1),(68,'DM','Dominica','N',1),(69,'DO','Dominican Republic','N',1),(70,'EC','Ecuador','N',1),(71,'EG','Egypt','N',1),(72,'SV','El Salvador','N',1),(73,'GQ','Equatorial Guinea','N',1),(74,'ER','Eritrea','N',1),(75,'EE','Estonia','Y',1),(76,'ET','Ethiopia','N',1),(77,'FK','Falkland Islands (malvinas)','N',1),(78,'FO','Faroe Islands','N',1),(79,'FJ','Fiji','N',1),(80,'FI','Finland','Y',1),(81,'FR','France','Y',1),(82,'GF','French Guiana','N',1),(83,'PF','French Polynesia','N',1),(84,'TF','French Southern Territories','N',1),(85,'GA','Gabon','N',1),(86,'GM','Gambia','N',1),(87,'GE','Georgia','N',1),(88,'DE','Germany','Y',1),(89,'GH','Ghana','N',1),(90,'GI','Gibraltar','N',1),(91,'GR','Greece','Y',1),(92,'GL','Greenland','N',1),(93,'GD','Grenada','N',1),(94,'GP','Guadeloupe','N',1),(95,'GU','Guam','N',1),(96,'GT','Guatemala','N',1),(97,'GG','Guernsey','N',1),(98,'GN','Guinea','N',1),(99,'GW','Guinea-bissau','N',1),(100,'GY','Guyana','N',1),(101,'HT','Haiti','N',1),(102,'HM','Heard Island And Mcdonald Islands','N',1),(103,'VA','Holy See (vatican City State)','N',1),(104,'HN','Honduras','N',1),(105,'HK','Hong Kong','N',1),(106,'HU','Hungary','Y',1),(107,'IS','Iceland','N',1),(108,'IN','India','N',1),(109,'ID','Indonesia','N',1),(110,'IR','Iran, Islamic Republic Of','N',1),(111,'IQ','Iraq','N',1),(112,'IM','Isle Of Man','N',1),(113,'IL','Israel','N',1),(114,'IT','Italy','Y',1),(115,'JM','Jamaica','N',1),(116,'JP','Japan','N',1),(117,'JE','Jersey','N',1),(118,'JO','Jordan','N',1),(119,'KZ','Kazakhstan','N',1),(120,'KE','Kenya','N',1),(121,'KI','Kiribati','N',1),(122,'KP','Korea, Democratic People\'s Republic Of','N',1),(123,'KR','Korea, Republic Of','N',1),(124,'KW','Kuwait','N',1),(125,'KG','Kyrgyzstan','N',1),(126,'LA','Lao People\'s Democratic Republic','N',1),(127,'LB','Lebanon','N',1),(128,'LS','Lesotho','N',1),(129,'LR','Liberia','N',1),(130,'LY','Libyan Arab Jamahiriya','N',1),(131,'LI','Liechtenstein','N',1),(132,'LT','Lithuania','Y',1),(133,'LU','Luxembourg','Y',1),(134,'MO','Macao','N',1),(135,'MK','Macedonia, The Former Yugoslav Republic Of','N',1),(136,'MG','Madagascar','N',1),(137,'MW','Malawi','N',1),(138,'MY','Malaysia','N',1),(139,'MV','Maldives','N',1),(140,'ML','Mali','N',1),(141,'MT','Malta','Y',1),(142,'MH','Marshall Islands','N',1),(143,'MQ','Martinique','N',1),(144,'MR','Mauritania','N',1),(145,'MU','Mauritius','N',1),(146,'YT','Mayotte','N',1),(147,'MX','Mexico','N',1),(148,'FM','Micronesia, Federated States Of','N',1),(149,'MD','Moldova, Republic Of','N',1),(150,'MC','Monaco','N',1),(151,'MN','Mongolia','N',1),(152,'ME','Montenegro','N',1),(153,'MS','Montserrat','N',1),(154,'MA','Morocco','N',1),(155,'MZ','Mozambique','N',1),(156,'MM','Myanmar','N',1),(157,'NA','Namibia','N',1),(158,'NR','Nauru','N',1),(159,'NP','Nepal','N',1),(160,'NL','Netherlands','Y',1),(161,'AN','Netherlands Antilles','N',1),(162,'NC','New Caledonia','N',1),(163,'NZ','New Zealand','N',1),(164,'NI','Nicaragua','N',1),(165,'NE','Niger','N',1),(166,'NG','Nigeria','N',1),(167,'NU','Niue','N',1),(168,'NF','Norfolk Island','N',1),(169,'MP','Northern Mariana Islands','N',1),(170,'NO','Norway','N',1),(171,'OM','Oman','N',1),(172,'PK','Pakistan','N',1),(173,'PW','Palau','N',1),(174,'PS','Palestinian Territory, Occupied','N',1),(175,'PA','Panama','N',1),(176,'PG','Papua New Guinea','N',1),(177,'PY','Paraguay','N',1),(178,'PE','Peru','N',1),(179,'PH','Philippines','N',1),(180,'PN','Pitcairn','N',1),(181,'PL','Poland','Y',1),(182,'PT','Portugal','Y',1),(183,'PR','Puerto Rico','N',1),(184,'QA','Qatar','N',1),(185,'RE','Reunion','N',1),(186,'RO','Romania','Y',1),(187,'RW','Rwanda','N',1),(188,'BL','Saint Barth&ETH;','N',1),(189,'SH','Saint Helena','N',1),(190,'KN','Saint Kitts And Nevis','N',1),(191,'LC','Saint Lucia','N',1),(192,'MF','Saint Martin','N',1),(193,'PM','Saint Pierre And Miquelon','N',1),(194,'VC','Saint Vincent And The Grenadines','N',1),(195,'WS','Samoa','N',1),(196,'SM','San Marino','N',1),(197,'ST','Sao Tome And Principe','N',1),(198,'SA','Saudi Arabia','N',1),(199,'SN','Senegal','N',1),(200,'RS','Serbia','N',1),(201,'SC','Seychelles','N',1),(202,'SL','Sierra Leone','N',1),(203,'SG','Singapore','N',1),(204,'SK','Slovakia','Y',1),(205,'SI','Slovenia','Y',1),(206,'SB','Solomon Islands','N',1),(207,'SO','Somalia','N',1),(208,'ZA','South Africa','N',1),(209,'GS','South Georgia And The South Sandwich Islands','N',1),(210,'ES','Spain','Y',1),(211,'LK','Sri Lanka','N',1),(212,'SD','Sudan','N',1),(213,'SR','Suriname','N',1),(214,'SJ','Svalbard And Jan Mayen','N',1),(215,'SZ','Swaziland','N',1),(216,'SE','Sweden','Y',1),(217,'SY','Syrian Arab Republic','N',1),(218,'TW','Taiwan, Province Of China','N',1),(219,'TJ','Tajikistan','N',1),(220,'TZ','Tanzania, United Republic Of','N',1),(221,'TH','Thailand','N',1),(222,'TL','Timor-leste','N',1),(223,'TG','Togo','N',1),(224,'TK','Tokelau','N',1),(225,'TO','Tonga','N',1),(226,'TT','Trinidad And Tobago','N',1),(227,'TN','Tunisia','N',1),(228,'TR','Turkey','N',1),(229,'TM','Turkmenistan','N',1),(230,'TC','Turks And Caicos Islands','N',1),(231,'TV','Tuvalu','N',1),(232,'UG','Uganda','N',1),(233,'UA','Ukraine','N',1),(234,'AE','United Arab Emirates','N',1),(235,'GB','United Kingdom','Y',1),(236,'UM','United States Minor Outlying Islands','N',1),(237,'UY','Uruguay','N',1),(238,'UZ','Uzbekistan','N',1),(239,'VU','Vanuatu','N',1),(240,'VE','Venezuela','N',1),(241,'VN','Viet Nam','N',1),(242,'VG','Virgin Islands, British','N',1),(243,'VI','Virgin Islands, U.s.','N',1),(244,'WF','Wallis And Futuna','N',1),(245,'EH','Western Sahara','N',1),(246,'YE','Yemen','N',1),(247,'ZM','Zambia','N',1),(248,'ZW','Zimbabwe','N',1);
/*!40000 ALTER TABLE `country` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2010-08-25 16:08:40
