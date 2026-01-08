-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 08, 2026 at 03:01 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `finexe_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `abonnements`
--

DROP TABLE IF EXISTS `abonnements`;
CREATE TABLE IF NOT EXISTS `abonnements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) NOT NULL,
  `description` text,
  `prix` decimal(10,2) NOT NULL,
  `duree` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `configuration`
--

DROP TABLE IF EXISTS `configuration`;
CREATE TABLE IF NOT EXISTS `configuration` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `cle` varchar(50) NOT NULL,
  `valeur` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_config` (`utilisateur_id`,`cle`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `configuration`
--

INSERT INTO `configuration` (`id`, `utilisateur_id`, `cle`, `valeur`) VALUES
(1, 1, 'cotisations_requises', '10'),
(2, 6, 'cotisations_requises', '10'),
(3, 9, 'cotisations_requises', '50');

-- --------------------------------------------------------

--
-- Table structure for table `cotisations`
--

DROP TABLE IF EXISTS `cotisations`;
CREATE TABLE IF NOT EXISTS `cotisations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `numero_cotisation` int DEFAULT '1',
  `membre_id` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_cotisation` date NOT NULL,
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `membre_id` (`membre_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `cotisations`
--

INSERT INTO `cotisations` (`id`, `numero_cotisation`, `membre_id`, `montant`, `date_cotisation`, `utilisateur_id`) VALUES
(30, 1, 16, 2500.00, '2025-12-29', 10),
(29, 1, 3, 500.00, '2025-12-18', 1);

-- --------------------------------------------------------

--
-- Table structure for table `depenses_membres`
--

DROP TABLE IF EXISTS `depenses_membres`;
CREATE TABLE IF NOT EXISTS `depenses_membres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `membre_id` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `description` varchar(255) NOT NULL,
  `date_depense` date NOT NULL,
  `utilisateur_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `membre_id` (`membre_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `depenses_membres`
--

INSERT INTO `depenses_membres` (`id`, `membre_id`, `montant`, `description`, `date_depense`, `utilisateur_id`, `created_at`) VALUES
(1, 2, 25000.00, 'main sol', '2025-11-25', 1, '2025-11-25 03:04:50');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
CREATE TABLE IF NOT EXISTS `logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int NOT NULL,
  `action` text NOT NULL,
  `date_action` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=111 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `utilisateur_id`, `action`, `date_action`) VALUES
(110, 10, 'Remboursement prêt #47 : 3125.00 GDES', '2025-12-29 09:02:50'),
(109, 10, 'Nouvelle cotisation #1: 2500.00 G', '2025-12-29 09:02:26'),
(108, 10, 'Prêt accordé : 2500 GDES', '2025-12-29 09:01:56'),
(107, 1, 'Remboursement prêt #46 : 1650.00 GDES', '2025-12-29 08:49:45'),
(106, 1, 'Prêt accordé : 1500 GDES', '2025-12-29 08:49:20'),
(105, 1, 'Remboursement prêt #45 : 4750.00 GDES', '2025-12-18 09:24:07'),
(104, 1, 'Prêt accordé : 2500 GDES', '2025-12-18 09:23:30'),
(103, 1, 'Nouvelle cotisation #1: 500.00 G', '2025-12-18 09:22:41');

-- --------------------------------------------------------

--
-- Table structure for table `membres`
--

DROP TABLE IF EXISTS `membres`;
CREATE TABLE IF NOT EXISTS `membres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code_membre` varchar(20) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `adresse` text,
  `sexe` enum('M','F') DEFAULT NULL,
  `date_entree` date NOT NULL,
  `plan` decimal(10,2) NOT NULL,
  `utilisateur_id` int NOT NULL,
  `nombre_cases` int DEFAULT '1',
  `cases_cochees` int DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_membre` (`code_membre`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `membres`
--

INSERT INTO `membres` (`id`, `code_membre`, `nom`, `prenom`, `telephone`, `email`, `adresse`, `sexe`, `date_entree`, `plan`, `utilisateur_id`, `nombre_cases`, `cases_cochees`) VALUES
(1, 'AI030055', 'Isberlanda', 'Aldajuste', '33174080', 'isberlandaaldajuste@yahoo.fr', 'drrrr', 'F', '2025-11-10', 50000.00, 1, 1, 0),
(2, 'SP031301', 'Pierre', 'Sheednalie', '35704000', 'pierre@gmail.com', 'Trois Ponts', 'F', '2025-11-09', 50000.00, 1, 1, 0),
(3, 'WA032100', 'Alexandre', 'Woodjina', '48756090', 'alexandrewood@yahoo.fr', 'Decahos', 'F', '2025-11-10', 35000.00, 1, 20, 1),
(8, 'RM024238', 'Marcelin', 'Ricardo', '35709809', 'ricardo@yahoo.fr', 'Gonaives tarasse', 'M', '2025-11-18', 25000.00, 1, 1, 0),
(5, 'MS020107', 'Sainville', 'Mitech Kerlandjina', '43573412', 'micthe@yahoo.fr', 'Bigot, Sou dren an', 'M', '2025-11-12', 10000.00, 1, 1, 0),
(6, 'SA020943', 'ALectine', 'Samuel', '33174080', 'alectinesamueljohnkelly@gmail.com', 'Tarasse route national #1', 'M', '2025-11-11', 1000.00, 2, 1, 0),
(7, 'EP014358', 'Paganot', 'Eudena', '35905798', 'edepaganot@yahoo.fr', 'Tarasse route national #1', 'F', '2025-11-13', 2500.00, 1, 6, 6),
(9, 'MD215333', 'ddddd', 'mmmmm', 'mmmmm', '', '', 'M', '2025-11-19', 25000.00, 6, 1, 0),
(10, 'EJ035455', 'Jean', 'Ephesien', '55374055', 'jeanephesien@gmail.com', 'New york city', 'M', '2025-11-26', 8000.00, 1, 1, 0),
(12, 'RS203019', 'sax', 'Roby', '42000334', 'admin@example.com', '25, ruelle succès, Gonaives, Haiti', 'M', '2025-12-04', 1000.00, 9, 1, 0),
(13, 'OJ140356', 'Joseph', 'Odjina', '38754750', 'odjinajoseph@yahoo.com', 'Gonaives tarasse', 'F', '2025-12-16', 50000.00, 1, 1, 0),
(14, 'KJ170018', 'Joseph', 'Kelly', '24345677', 'joseph@gmail.com', 'pont gaudin', 'M', '2025-12-16', 50000.00, 8, 1, 0),
(15, 'WA133400', 'Alexandre', 'Woodjina', '36780980', 'alexandrewood@gmail.com', 'Deschaos', 'F', '2025-12-17', 15000.00, 5, 1, 0),
(16, 'AJ134305', 'John', 'Allia Christina', '35759035', 'johncrist@gmail.com', 'Pont gaudin', 'F', '2025-12-17', 10000.00, 10, 1, 0),
(17, 'MV200224', 'Valdonus', 'Marckenlove', '35756789', 'val@yahoo.fr', 'rrrrrrrrrrr', 'M', '2025-12-17', 50000.00, 3, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `membre_id` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_paiement` date NOT NULL,
  `semaine` int DEFAULT NULL,
  `utilisateur_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_paiements_membre` (`membre_id`),
  KEY `idx_paiements_date` (`date_paiement`),
  KEY `idx_paiements_utilisateur` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `paiements`
--

INSERT INTO `paiements` (`id`, `membre_id`, `montant`, `date_paiement`, `semaine`, `utilisateur_id`, `created_at`) VALUES
(1, 8, 15000.00, '2025-11-25', 1, 1, '2025-11-25 03:03:09'),
(2, 8, 1700.00, '2025-11-25', 2, 1, '2025-11-25 03:06:47'),
(3, 8, 3000.00, '2025-11-25', 3, 1, '2025-11-25 03:07:01'),
(4, 8, 5000.00, '2025-11-25', 4, 1, '2025-11-25 03:07:12'),
(5, 8, 7000.00, '2025-11-25', 1, 1, '2025-11-25 03:07:43'),
(6, 3, 25000.00, '2025-11-25', NULL, 1, '2025-11-25 03:11:31');

-- --------------------------------------------------------

--
-- Table structure for table `prets`
--

DROP TABLE IF EXISTS `prets`;
CREATE TABLE IF NOT EXISTS `prets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `membre_id` int NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `taux_interet` decimal(5,2) NOT NULL,
  `duree_mois` int NOT NULL,
  `date_pret` date NOT NULL,
  `date_echeance` date NOT NULL,
  `montant_a_rembourser` decimal(10,2) NOT NULL,
  `statut` enum('en_cours','rembourse','en_retard') DEFAULT 'en_cours',
  `utilisateur_id` int NOT NULL,
  `date_remboursement` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `membre_id` (`membre_id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prets`
--

INSERT INTO `prets` (`id`, `membre_id`, `montant`, `taux_interet`, `duree_mois`, `date_pret`, `date_echeance`, `montant_a_rembourser`, `statut`, `utilisateur_id`, `date_remboursement`) VALUES
(1, 1, 15000.00, 5.00, 12, '2025-11-11', '2026-11-11', 15750.00, 'rembourse', 1, NULL),
(2, 1, 15000.00, 5.00, 12, '2025-11-11', '2026-11-11', 15750.00, 'rembourse', 1, NULL),
(27, 14, 12500.00, 5.00, 6, '2025-12-16', '2026-06-16', 13125.00, 'rembourse', 8, NULL),
(26, 14, 5000.00, 30.00, 12, '2025-12-16', '2026-12-16', 6500.00, 'rembourse', 8, NULL),
(7, 5, 50000.00, 25.00, 12, '2025-11-12', '2026-11-12', 62500.00, 'rembourse', 1, NULL),
(32, 9, 1500.00, 12.00, 12, '2025-12-17', '2026-12-17', 1680.00, 'rembourse', 6, '2025-12-17'),
(30, 1, 25000.00, 15.00, 12, '2025-12-17', '2026-12-17', 28750.00, 'rembourse', 1, '2025-12-16'),
(31, 14, 2500.00, 10.00, 13, '2025-12-17', '2027-01-17', 2750.00, 'en_cours', 8, NULL),
(29, 14, 120.00, 5.00, 12, '2025-12-16', '2026-12-16', 126.00, 'rembourse', 8, NULL),
(16, 1, 20000.00, 12.00, 3, '2025-11-12', '2026-02-12', 22400.00, 'rembourse', 1, NULL),
(17, 2, 5000.00, 9.00, 3, '2025-11-12', '2026-02-12', 5450.00, 'rembourse', 1, NULL),
(28, 14, 100.00, 1.00, 12, '2025-12-16', '2026-12-16', 101.00, 'rembourse', 8, NULL),
(19, 9, 50000.00, 25.00, 12, '2025-12-03', '2026-12-03', 62500.00, 'rembourse', 6, NULL),
(20, 12, 500.00, 0.00, 1, '2025-12-04', '2026-01-04', 500.00, 'rembourse', 9, NULL),
(21, 13, 25000.00, 20.00, 12, '2025-12-16', '2026-12-16', 30000.00, 'rembourse', 1, NULL),
(22, 13, 25000.00, 20.00, 12, '2025-12-16', '2026-12-16', 30000.00, 'rembourse', 1, NULL),
(23, 13, 25000.00, 20.00, 12, '2025-12-16', '2026-12-16', 30000.00, 'rembourse', 1, NULL),
(24, 13, 25000.00, 20.00, 12, '2025-12-16', '2026-12-16', 30000.00, 'rembourse', 1, NULL),
(25, 13, 25000.00, 20.00, 12, '2025-12-16', '2026-12-16', 30000.00, 'rembourse', 1, NULL),
(33, 9, 10000.00, 15.00, 12, '2025-12-17', '2026-12-17', 11500.00, 'rembourse', 6, '2025-12-16'),
(34, 9, 10000.00, 15.00, 12, '2025-12-17', '2026-12-17', 11500.00, 'rembourse', 6, '2025-12-16'),
(35, 15, 1500.00, 12.00, 12, '2025-12-17', '2026-12-17', 1680.00, 'rembourse', 5, '2025-12-17'),
(36, 15, 1500.00, 12.00, 12, '2025-12-17', '2026-12-17', 1680.00, 'rembourse', 5, '2025-12-17'),
(37, 16, 1500.00, 12.00, 12, '2025-12-17', '2026-12-17', 1680.00, 'rembourse', 10, '2025-12-17'),
(38, 16, 1500.00, 12.00, 12, '2025-12-17', '2026-12-17', 1680.00, 'rembourse', 10, '2025-12-17'),
(39, 16, 1500.00, 12.00, 10, '2025-12-17', '2026-10-17', 1680.00, 'rembourse', 10, '2025-12-17'),
(40, 16, 1000.00, 1.00, 12, '2025-12-17', '2026-12-17', 1010.00, 'rembourse', 10, '2025-12-17'),
(41, 3, 15000.00, 75.00, 24, '2025-12-17', '2027-12-17', 26250.00, 'rembourse', 1, '2025-12-17'),
(42, 9, 35000.00, 25.00, 12, '2025-12-17', '2026-12-17', 43750.00, 'rembourse', 6, '2025-12-17'),
(43, 17, 3500.00, 90.00, 12, '2025-12-17', '2026-12-17', 6650.00, 'en_cours', 3, NULL),
(44, 16, 15000.00, 15.00, 12, '2025-12-18', '2026-12-18', 17250.00, 'rembourse', 10, '2025-12-17'),
(45, 1, 2500.00, 90.00, 12, '2025-12-18', '2026-12-18', 4750.00, 'rembourse', 1, '2025-12-18'),
(46, 2, 1500.00, 10.00, 12, '2025-12-29', '2026-12-29', 1650.00, 'rembourse', 1, '2025-12-29'),
(47, 16, 2500.00, 25.00, 12, '2025-12-29', '2026-12-29', 3125.00, 'rembourse', 10, '2025-12-29');

-- --------------------------------------------------------

--
-- Table structure for table `salaires`
--

DROP TABLE IF EXISTS `salaires`;
CREATE TABLE IF NOT EXISTS `salaires` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_employe` varchar(100) NOT NULL,
  `mois` varchar(50) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `statut` enum('paye','non_paye') DEFAULT 'non_paye',
  `utilisateur_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `salaires`
--

INSERT INTO `salaires` (`id`, `nom_employe`, `mois`, `montant`, `statut`, `utilisateur_id`) VALUES
(1, 'Robed Sanon', 'Decembre 2025', 15000.00, 'paye', 1);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE IF NOT EXISTS `subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `plan_type` varchar(50) NOT NULL,
  `transaction_number` varchar(50) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transaction_number` (`transaction_number`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `user_id`, `plan_type`, `transaction_number`, `amount`, `created_at`) VALUES
(1, 2, 'premium_ans', '002567909123', NULL, '2025-12-17 13:29:29');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` enum('entree','depense') NOT NULL,
  `description` varchar(255) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `date_transaction` date NOT NULL,
  `utilisateur_id` int NOT NULL,
  `membre_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `membre_id` (`membre_id`)
) ENGINE=MyISAM AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `type`, `description`, `montant`, `date_transaction`, `utilisateur_id`, `membre_id`) VALUES
(80, 'entree', 'Remboursement de prêt #47', 3125.00, '2025-12-29', 10, NULL),
(79, 'entree', 'Cotisation #1 - Allia Christina John', 2500.00, '2025-12-29', 10, 16),
(78, 'depense', 'Prêt accordé au membre #16', 2500.00, '2025-12-29', 10, NULL),
(77, 'entree', 'Remboursement de prêt #46', 1650.00, '2025-12-29', 1, NULL),
(76, 'depense', 'Prêt accordé au membre #2', 1500.00, '2025-12-29', 1, NULL),
(75, 'entree', 'Remboursement de prêt #45', 4750.00, '2025-12-18', 1, NULL),
(74, 'depense', 'Prêt accordé au membre #1', 2500.00, '2025-12-18', 1, NULL),
(73, 'entree', 'Cotisation #1 - Woodjina Alexandre', 500.00, '2025-12-18', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text,
  `sexe` enum('M','F') DEFAULT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_creation` datetime DEFAULT CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '0',
  `derniere_connexion` timestamp NULL DEFAULT NULL,
  `nom_sol` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `telephone`, `adresse`, `sexe`, `mot_de_passe`, `date_creation`, `is_admin`, `is_active`, `derniere_connexion`, `nom_sol`) VALUES
(1, 'Isberlanda', 'Aldajuste', 'isberlandaaldajuste@yahoo.fr', '32000405', 'Decahos routre nationale #1', 'F', '$2y$10$OxYtxyk1tIP8ehQFQcnrpeIuEsNTER2M0rSEvZsa.Us6pduyRMsKO', '2025-11-09 21:44:57', 0, 1, '2025-12-29 13:33:23', 'Dada'),
(2, 'Sanon', 'Robed', 'robedsanon@gmail.com', '35704000', 'Asifa ruelle Succes', 'M', '$2y$10$7KeTeLoG56l1VqNwzR.bTuBJBaF15yFTA5tCKLHg9/NEPtF6vtd7q', '2025-11-09 22:22:03', 0, 0, '2025-11-25 02:09:23', NULL),
(3, 'Jean', 'Ephesien', 'ephesienjean@gmail.com', '47568909', 'New york city', 'M', '$2y$10$i/7EPYYFiaoHBpeT3xFIv.9IsFdREe0DMTy/E59TMKKyQ2GU7Cde.', '2025-11-17 21:52:16', 0, 1, '2025-12-17 20:01:29', 'Ephesien Jean Tipa'),
(4, 'Alectine', 'Samuel Johnkelly', 'samuelalectine@yahoo.com', '33174080', 'Tarasse', 'M', '$2y$10$1V3ryd505xUPL/4y5MeGy.IEL5pJVivRnqQL.ZEYa0u19lu12Fh2O', '2025-11-17 21:53:13', 1, 1, '2025-12-29 13:38:07', NULL),
(5, 'Alexandre', 'Woodjina', 'alexandrewood@yahoo.fr', '35709579', 'Gonaives, Ruelle Saint-louis #3', 'F', '$2y$10$d0kXi/6vYD8/Fp9AwQTslenW0LLSe0/86CqVh5a2AtdOSpRhBvO5O', '2025-11-18 13:19:07', 0, 1, '2025-12-17 13:33:07', 'Wood'),
(6, 'Sainvil', 'billy', 'billytest@gmail.com', '35905780', 'Gonaives Pont Gaudin', 'M', '$2y$10$NS5ipXaA3M6SG3CfGnnV5Ouz6R6YIOJCHWu..zyTasDBkZkZRExHq', '2025-11-19 16:34:42', 0, 0, '2025-12-16 14:41:23', 'Billy Test Tipa'),
(8, 'Joseph', 'Kelly', 'kelly@gmail.com', '37509078', 'Tarasse route nationale', 'M', '$2y$10$5Sf.aMTiclUF7pL1uCuCn.6/ONoLPHI13/8mG7mF.u/.iqSypZUGu', '2025-11-26 22:20:07', 0, 1, '2025-12-16 16:58:52', 'Kelly Tipa sol'),
(9, 'Sanon', 'Robed', 'robedsanon7@gmail.com', '42000334', '25, ruelle succès, Gonaives, Haiti', 'M', '$2y$10$Z.I7Qczfke6kP9W0Dah/IumTP0WgHAO9TI3jfdyS/iNZErPdjA6Qm', '2025-12-04 15:28:36', 1, 1, '2025-12-04 20:32:54', 'Robed Sanon'),
(10, 'Allia', 'Christina John', 'christallia@hotmail.com', '34907560', 'Trois Pont', 'F', '$2y$10$yOB05xfmUj0p2RQrcwRs2uYeJHkIafMnuniHjEeHuNwhX668KHmH6', '2025-12-17 08:41:47', 0, 1, '2025-12-29 14:01:37', 'Christ John'),
(11, 'Marcelin', 'Ricardo', 'ricardo@yahoo.fr', '35758909', 'Gonaives tarasse', 'M', '$2y$10$cUphzCETVCJB46Gm4.2mCOiqH9rUAezL0ohUIkufMb6sadZLnMROC', '2025-12-28 21:07:15', 0, 0, NULL, 'Rica');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
