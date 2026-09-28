-- Adminer 6.1.0 MariaDB 10.11.18-MariaDB-0+deb12u1 dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `Acces`;
CREATE TABLE `Acces` (
  `id_acces` int(11) NOT NULL AUTO_INCREMENT,
  `date_heure` datetime NOT NULL DEFAULT current_timestamp(),
  `statut` enum('autorise','refuse') NOT NULL,
  `id_casier` int(11) NOT NULL,
  `id_badge` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_acces`),
  KEY `fk_acces_casier` (`id_casier`),
  KEY `fk_acces_badge` (`id_badge`),
  CONSTRAINT `fk_acces_badge` FOREIGN KEY (`id_badge`) REFERENCES `Badge` (`id_badge`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_acces_casier` FOREIGN KEY (`id_casier`) REFERENCES `Casier` (`id_casier`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `Badge`;
CREATE TABLE `Badge` (
  `id_badge` int(11) NOT NULL AUTO_INCREMENT,
  `uid_badge` varchar(50) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT 1,
  `date_attribution` datetime DEFAULT current_timestamp(),
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_badge`),
  UNIQUE KEY `uid_badge` (`uid_badge`),
  KEY `fk_badge_user` (`id_user`),
  CONSTRAINT `fk_badge_user` FOREIGN KEY (`id_user`) REFERENCES `Utilisateur` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `Badge` (`id_badge`, `uid_badge`, `actif`, `date_attribution`, `id_user`) VALUES
(1,	'2A:8E:BF:24',	1,	'2026-09-24 09:29:47',	1),
(2,	'99:9F:62:C2',	1,	'2026-09-24 09:29:47',	2),
(3,	'F9:13:93:C2',	1,	'2026-09-24 09:29:47',	3);

DROP TABLE IF EXISTS `Casier`;
CREATE TABLE `Casier` (
  `id_casier` int(11) NOT NULL AUTO_INCREMENT,
  `numero` varchar(20) NOT NULL,
  `etat_porte` enum('ouverte','fermee') NOT NULL DEFAULT 'fermee',
  `etat_occupation` enum('disponible','occupe') NOT NULL DEFAULT 'disponible',
  `id_user_attribue` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_casier`),
  UNIQUE KEY `numero` (`numero`),
  KEY `fk_casier_user` (`id_user_attribue`),
  CONSTRAINT `fk_casier_user` FOREIGN KEY (`id_user_attribue`) REFERENCES `Utilisateur` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `Casier` (`id_casier`, `numero`, `etat_porte`, `etat_occupation`, `id_user_attribue`) VALUES
(1,	'Casier-01',	'fermee',	'occupe',	1),
(2,	'Casier-02',	'fermee',	'disponible',	NULL),
(3,	'Casier-03',	'fermee',	'disponible',	NULL);

DROP TABLE IF EXISTS `Evenement`;
CREATE TABLE `Evenement` (
  `id_evenement` int(11) NOT NULL AUTO_INCREMENT,
  `date_heure` datetime NOT NULL DEFAULT current_timestamp(),
  `type_evenement` enum('porte_ouverte_longtemps','tentatives_refusees_multiples','autre') NOT NULL,
  `description` text DEFAULT NULL,
  `id_casier` int(11) NOT NULL,
  PRIMARY KEY (`id_evenement`),
  KEY `fk_evenement_casier` (`id_casier`),
  CONSTRAINT `fk_evenement_casier` FOREIGN KEY (`id_casier`) REFERENCES `Casier` (`id_casier`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


DROP TABLE IF EXISTS `Utilisateur`;
CREATE TABLE `Utilisateur` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mot_de_passe` varchar(255) DEFAULT NULL,
  `role` enum('admin','eleve') NOT NULL DEFAULT 'eleve',
  PRIMARY KEY (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `Utilisateur` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`) VALUES
(1,	'Anastasya',	NULL,	NULL,	NULL,	'admin'),
(2,	'Barbatos',	NULL,	NULL,	NULL,	'eleve'),
(3,	'Dainsleif',	NULL,	NULL,	NULL,	'eleve');

-- 2026-09-25 08:56:57 UTC
