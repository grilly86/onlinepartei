-- phpMyAdmin SQL Dump
-- version 3.4.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 13. Jul 2012 um 17:14
-- Server Version: 5.5.16
-- PHP-Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `op`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `senderID` int(11) NOT NULL DEFAULT '0',
  `receiverID` int(11) NOT NULL DEFAULT '0',
  `message` text NOT NULL,
  `messageHtml` text NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `read` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `poll`
--

CREATE TABLE IF NOT EXISTS `poll` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` text NOT NULL,
  `text` text NOT NULL,
  `messageHtml` text NOT NULL,
  `answers` text NOT NULL,
  `userID` int(11) NOT NULL DEFAULT '0',
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `poll_vote`
--

CREATE TABLE IF NOT EXISTS `poll_vote` (
  `pollID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  `vote` int(11) NOT NULL DEFAULT '0',
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`pollID`,`userID`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `post`
--

CREATE TABLE IF NOT EXISTS `post` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `postID` int(11) NOT NULL DEFAULT '0',
  `type` enum('','poll') NOT NULL DEFAULT '',
  `userID` int(11) NOT NULL DEFAULT '0',
  `caption` text NOT NULL,
  `message` text NOT NULL,
  `messageHtml` text NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `deleted` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `post_tag`
--

CREATE TABLE IF NOT EXISTS `post_tag` (
  `tagID` int(11) NOT NULL,
  `parentID` int(11) NOT NULL,
  `type` enum('','poll') NOT NULL,
  PRIMARY KEY (`tagID`,`parentID`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rating`
--

CREATE TABLE IF NOT EXISTS `rating` (
  `type` enum('','poll') NOT NULL DEFAULT '',
  `postID` int(11) NOT NULL DEFAULT '0',
  `userID` int(11) NOT NULL DEFAULT '0',
  `rating` enum('like','dislike') NOT NULL DEFAULT 'like',
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`type`,`postID`,`userID`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `stat`
--

CREATE TABLE IF NOT EXISTS `stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '0',
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `ip` text NOT NULL,
  `ua` text NOT NULL,
  `referrer` text NOT NULL,
  `request` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` text NOT NULL,
  `name` text NOT NULL,
  `password` text NOT NULL,
  `online` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `color` text NOT NULL,
  `hasImage` int(11) NOT NULL DEFAULT '0',
  `language` enum('de','en') NOT NULL DEFAULT 'de',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;