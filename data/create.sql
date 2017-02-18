/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `division` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `cond` text COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `fee` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `since` datetime DEFAULT NULL,
  `amount` int(11) DEFAULT NULL,
  `type` enum('player_per_season','player_per_tournament','team_per_season','team_per_tournament') COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `fee_needed_for_league` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `league_id` int(11) DEFAULT NULL,
  `fee_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_id` (`league_id`),
  KEY `fee_id` (`fee_id`),
  CONSTRAINT `fee_needed_for_league_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`),
  CONSTRAINT `fee_needed_for_league_ibfk_2` FOREIGN KEY (`fee_id`) REFERENCES `fee` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_bin COMMENT='Ve které lize musí člověk/tým hrát, aby pro něj poplatek platil. Pokud poplatek patří do více lig, stačí aby hrál jednu z nich. Stejně tak, pokud hraje hráč více lig se stejným poplatkem, platí pouze jednou';
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `fee_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) DEFAULT NULL,
  `season_id` int(11) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `paid_amount` int(11) DEFAULT NULL,
  `variable_symbol` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `season_id` (`season_id`),
  CONSTRAINT `fee_payments_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`),
  CONSTRAINT `fee_payments_ibfk_2` FOREIGN KEY (`season_id`) REFERENCES `season` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `highschool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `league` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `cond` text COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `player` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `birth_date` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `phone` varchar(32) COLLATE utf8_bin DEFAULT NULL,
  `sex` enum('male','female') COLLATE utf8_bin DEFAULT NULL,
  `state` enum('active','inactive','deleted') COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1548 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `player_at_highschool` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_id` int(11) DEFAULT NULL,
  `highschool_id` int(11) DEFAULT NULL,
  `since` datetime DEFAULT NULL,
  `until` datetime DEFAULT NULL,
  `valid` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `player_id` (`player_id`),
  KEY `highschool_id` (`highschool_id`),
  CONSTRAINT `player_at_highschool_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `player` (`id`),
  CONSTRAINT `player_at_highschool_ibfk_2` FOREIGN KEY (`highschool_id`) REFERENCES `highschool` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `player_at_roster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `roster_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `roster_id` (`roster_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `player_at_roster_ibfk_1` FOREIGN KEY (`roster_id`) REFERENCES `roster` (`id`),
  CONSTRAINT `player_at_roster_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `player` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=34861 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `player_at_team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `since` datetime DEFAULT NULL,
  `until` datetime DEFAULT NULL,
  `valid` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `player_at_team_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`),
  CONSTRAINT `player_at_team_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `player` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1757 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `roster` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  `seeding` int(11) DEFAULT NULL,
  `final_result` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `tournament_id` (`tournament_id`),
  CONSTRAINT `roster_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `team` (`id`),
  CONSTRAINT `roster_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournament` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2259 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `season` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_czech_ci NOT NULL,
  `start` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `team` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `founded_at` datetime DEFAULT NULL,
  `city` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `www` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `team_representative` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `player_at_team_id` int(11) NOT NULL,
  `function` enum('captain','contact') COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `player_at_team_id` (`player_at_team_id`),
  CONSTRAINT `team_representative_ibfk_1` FOREIGN KEY (`player_at_team_id`) REFERENCES `player_at_team` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `token` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) COLLATE utf8_bin DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `type` enum('email_verification','login') COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `token_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `tournament` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `location` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `duration` int(11) DEFAULT NULL,
  `season_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  CONSTRAINT `tournament_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `season` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `tournament_belongs_to_league_and_division` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `league_id` int(11) DEFAULT NULL,
  `division_id` int(11) DEFAULT NULL,
  `tournament_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `league_id` (`league_id`),
  KEY `tournament_id` (`tournament_id`),
  KEY `division_id` (`division_id`),
  CONSTRAINT `tournament_belongs_to_league_and_division_ibfk_1` FOREIGN KEY (`league_id`) REFERENCES `league` (`id`),
  CONSTRAINT `tournament_belongs_to_league_and_division_ibfk_2` FOREIGN KEY (`tournament_id`) REFERENCES `tournament` (`id`),
  CONSTRAINT `tournament_belongs_to_league_and_division_ibfk_3` FOREIGN KEY (`division_id`) REFERENCES `division` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `login` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `salt` char(32) COLLATE utf8_bin NOT NULL,
  `password` char(64) COLLATE utf8_bin NOT NULL,
  `created_at` datetime NOT NULL,
  `state` enum('waiting_for_confirmation','confirmed','blocked','password_reset') COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE IF NOT EXISTS `user_has_privilege` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `privilege` enum('admin','edit','view') COLLATE utf8_bin DEFAULT NULL,
  `entity` enum('team','highschool') COLLATE utf8_bin DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_has_privilege_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
