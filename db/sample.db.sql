

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `simplemappr`
--

-- --------------------------------------------------------

--
-- Table structure for table `citations`
--

CREATE TABLE IF NOT EXISTS `citations` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `year` int(11) NOT NULL,
  `reference` text COLLATE utf8_unicode_ci NOT NULL,
  `doi` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `first_author_surname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `year` (`year`,`first_author_surname`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `maps`
--

CREATE TABLE IF NOT EXISTS `maps` (
  `mid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL,
  `title` varchar(255) CHARACTER SET latin1 NOT NULL,
  `map` longtext CHARACTER SET utf8 COLLATE utf8_bin,
  `created` int(11) NOT NULL,
  `updated` int(11) NOT NULL,
  PRIMARY KEY (`mid`),
  KEY `uid` (`uid`),
  KEY `title` (`title`),
  KEY `idx_created` (`created`),
  KEY `idx_updated` (`updated`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `stateprovinces`
--

CREATE TABLE IF NOT EXISTS `stateprovinces` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `country_iso` char(3) DEFAULT NULL,
  `country` varchar(128) DEFAULT NULL,
  `stateprovince` varchar(128) DEFAULT NULL,
  `stateprovince_code` char(2) NOT NULL,
  UNIQUE KEY `OBJECTID` (`id`),
  KEY `index_on_country` (`country`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(255) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `displayname` varchar(125) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `role` int(11) DEFAULT '1',
  `created` int(11) DEFAULT NULL,
  `access` int(11) DEFAULT NULL,
  PRIMARY KEY (`uid`),
  KEY `identifier` (`identifier`),
  KEY `idx_username` (`username`),
  KEY `idx_access` (`access`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
