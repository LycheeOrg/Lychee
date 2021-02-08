-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost
-- Généré le : lun. 08 fév. 2021 à 20:04
-- Version du serveur :  10.5.8-MariaDB-3
-- Version de PHP : 7.4.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `homestead`
--

-- --------------------------------------------------------

--
-- Structure de la table `lychee_albums`
--

CREATE TABLE `lychee_albums` (
  `id` bigint(14) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(1000) DEFAULT '',
  `sysstamp` int(11) NOT NULL,
  `public` tinyint(1) NOT NULL DEFAULT 0,
  `visible` tinyint(1) NOT NULL DEFAULT 1,
  `downloadable` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(100) DEFAULT NULL,
  `min_takestamp` int(11) NOT NULL,
  `max_takestamp` int(11) NOT NULL,
  `license` varchar(20) DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `lychee_albums`
--

INSERT INTO `lychee_albums` (`id`, `title`, `description`, `sysstamp`, `public`, `visible`, `downloadable`, `password`, `min_takestamp`, `max_takestamp`, `license`) VALUES
(16128109002453, 'test', '', 1612810900, 0, 1, 0, NULL, 0, 0, 'none');

-- --------------------------------------------------------

--
-- Structure de la table `lychee_photos`
--

CREATE TABLE `lychee_photos` (
  `id` bigint(14) UNSIGNED NOT NULL,
  `title` varchar(100) NOT NULL DEFAULT '',
  `description` varchar(1000) DEFAULT '',
  `url` varchar(100) NOT NULL,
  `tags` varchar(1000) NOT NULL DEFAULT '',
  `public` tinyint(1) NOT NULL,
  `type` varchar(15) DEFAULT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `size` varchar(20) NOT NULL,
  `iso` varchar(15) NOT NULL,
  `aperture` varchar(20) NOT NULL,
  `make` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `lens` varchar(100) NOT NULL DEFAULT '',
  `shutter` varchar(30) NOT NULL,
  `focal` varchar(20) NOT NULL,
  `takestamp` int(11) DEFAULT NULL,
  `star` tinyint(1) NOT NULL,
  `thumbUrl` char(37) NOT NULL,
  `album` bigint(14) UNSIGNED NOT NULL,
  `checksum` char(40) DEFAULT NULL,
  `medium` tinyint(1) NOT NULL DEFAULT 0,
  `small` tinyint(1) NOT NULL DEFAULT 0,
  `license` varchar(20) NOT NULL DEFAULT 'none'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `lychee_photos`
--

INSERT INTO `lychee_photos` (`id`, `title`, `description`, `url`, `tags`, `public`, `type`, `width`, `height`, `size`, `iso`, `aperture`, `make`, `model`, `lens`, `shutter`, `focal`, `takestamp`, `star`, `thumbUrl`, `album`, `checksum`, `medium`, `small`, `license`) VALUES
(16128109116437, 'sky_color_2x', '', 'de2484a90bafa7e4ae318cf8a4451297.png', '', 0, 'image/png', 560, 861, '58 KB', '', '', '', '', '', '', '', 0, 0, 'de2484a90bafa7e4ae318cf8a4451297.jpeg', 16128109002453, '6fba378a431c273862b1f3d3106f2ac1d8c55983', 0, 1, 'none');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `lychee_albums`
--
ALTER TABLE `lychee_albums`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `lychee_photos`
--
ALTER TABLE `lychee_photos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Index_album` (`album`),
  ADD KEY `Index_star` (`star`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
