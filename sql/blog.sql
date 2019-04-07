-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Hôte : localhost:8889
-- Généré le :  Dim 07 avr. 2019 à 22:05
-- Version du serveur :  5.7.23
-- Version de PHP :  7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `blog`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `id` int(10) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `published_at` date NOT NULL,
  `summary` text,
  `content` longtext,
  `image` varchar(255) DEFAULT NULL,
  `is_published` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `category_id`, `title`, `published_at`, `summary`, `content`, `image`, `is_published`) VALUES
(1, 47, 'Hellfest 2018, l\'affiche quasi-complète', '2017-01-06', 'Résumé de l\'article Hellfest', '&lt;p&gt;Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi. &lt;/p&gt;', '1551648580hellfest-2018-définitive.jpg', 1),
(2, 9, 'Critique « Star Wars 8 – Les derniers Jedi » de Rian Johnson : le renouveau de la saga ?', '2017-01-07', 'Résumé de l\'article Star Wars 8', '&lt;p&gt;Duis semper. Duis arcu massa, scelerisque vitae, consequat in, pretium a, enim. Pellentesque congue.&lt;/p&gt;', '1551648722star-wars-8-1.jpg', 1),
(3, 47, 'Revue - The Ramones', '2017-01-01', 'Résumé de l\'article The Ramones', '&lt;p&gt;Pellentesque sed dui ut augue blandit sodales. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Aliquam nibh.&lt;/p&gt;', '1551648648ramones_cchalkie_davies.jpg__800x500_q85_crop_subject_location-2208,1758_subsampling-2_upscale.jpg', 1),
(4, 108, 'De “Skyrim” à “L.A. Noire” ou “Doom” : pourquoi les vieux jeux sont meilleurs sur la Switch', '2017-01-03', 'Résumé de l\'article Switch', '&lt;p&gt;Mauris ac mauris sed pede pellentesque fermentum. Maecenas adipiscing ante non diam sodales hendrerit.&lt;/p&gt;', '1551648429skyrim-ok.jpg', 1),
(5, 108, 'Comment “Assassin’s Creed” trouve un nouveau souffle en Egypte', '2017-01-04', 'Résumé de l\'article Assassin’s Creed', '&lt;p&gt;Ut velit mauris, egestas sed, gravida nec, ornare ut, mi. Aenean ut orci vel massa suscipit pulvinar.&lt;/p&gt;', '1551648365ac_media_screen-pyramids_ncsa.jpg', 1),
(6, 9, 'BO de « Les seigneurs de Dogtown » : l’époque bénie du rock.', '2017-01-05', 'Résumé de l\'article Les seigneurs de Dogtown', '&lt;p&gt;Nulla sollicitudin. Fusce varius, ligula non tempus aliquam, nunc turpis ullamcorper nibh, in tempus sapien eros vitae ligula.&lt;/p&gt;', '1551648837Seigneurs_de_Dogtown_.jpg', 1),
(7, 108, 'Pourquoi &quot;Destiny 2&quot; est un remède à l’ultra-moderne solitude', '2017-01-09', 'Résumé de l\'article Destiny 2', '&lt;p&gt;Pellentesque rhoncus nunc et augue. Integer id felis. Curabitur aliquet pellentesque diam.&lt;/p&gt;', '1551648147destiny.png', 1),
(8, 108, 'Pourquoi &quot;Mario + Lapins Crétins : Kingdom Battle&quot; est le jeu de la rentrée', '2017-01-08', 'Résumé de l\'article Mario + Lapins Crétins', '&lt;p&gt;Integer quis metus vitae elit lobortis egestas. Lorem ipsum dolor sit amet, consectetuer adipiscing elit.&lt;/p&gt;', '155164831059248d643a98b.jpg', 1),
(9, 9, '« Le Crime de l’Orient Express » : rencontre avec Kenneth Branagh', '2017-01-02', 'Résumé de l\'article Le Crime de l’Orient Express', '&lt;p&gt;Morbi vel erat non mauris convallis vehicula. Nulla et sapien. Integer tortor tellus, aliquam faucibus, convallis id, congue eu, quam. Mauris ullamcorper felis vitae erat.&lt;/p&gt;', '1551648914express.jpg', 1);

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `name`, `description`, `image`) VALUES
(5, 'Théâtre', 'Dates, représentations, avis...', NULL),
(9, 'Cinéma', 'Trailers, infos, sorties...', NULL),
(47, 'Musique', 'Concerts, sorties d\'albums, festivals...', NULL),
(108, 'Jeux vidéos', 'Videos, tests...', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `mail` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `biography` text,
  `is_admin` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `first_name`, `last_name`, `mail`, `password`, `biography`, `is_admin`) VALUES
(1, 'Yoel', 'EDELMAN', 'qwerty@gmail.com', '0cc175b9c0f1b6a831c399e269772661', 'développer du blog', 1),
(2, 'Visiteur', 'TEST', 'test@gmail.com', '0cc175b9c0f1b6a831c399e269772661', 'test', 0),
(15, 'Maxime', 'BASSET', 'max@gmail.com', '0cc175b9c0f1b6a831c399e269772661', 'prof', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT pour la table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=119;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
