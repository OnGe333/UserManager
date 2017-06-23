SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `onge_user_manager_example`
--

-- --------------------------------------------------------

--
-- Table structure for table `attempt`
--

CREATE TABLE IF NOT EXISTS `attempt` (
  `login` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `url` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `attempt_time` datetime NOT NULL,
  KEY `login` (`login`),
  KEY `ip` (`ip`),
  KEY `attempt_time` (`attempt_time`),
  KEY `type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lockdown`
--

CREATE TABLE IF NOT EXISTS `lockdown` (
  `login` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8_czech_ci DEFAULT NULL,
  `expires` datetime NOT NULL,
  KEY `login` (`login`),
  KEY `ip` (`ip`),
  KEY `attempt_time` (`expires`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(128) COLLATE utf8_czech_ci NOT NULL,
  `password` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `created_time` date NOT NULL,
  `active` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `activation_code` varchar(128) COLLATE utf8_czech_ci DEFAULT NULL,
  `activation_time` datetime DEFAULT NULL,
  `password_reset_code` varchar(128) COLLATE utf8_czech_ci DEFAULT NULL,
  `password_reset_time` datetime DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `last_activity` datetime DEFAULT NULL,
  `permanent_auth_code` varchar(60) COLLATE utf8_czech_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Table structure for table `warning`
--

CREATE TABLE IF NOT EXISTS `warning` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8_czech_ci NOT NULL,
  `warning_time` datetime NOT NULL,
  `resolved` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_czech_ci AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
