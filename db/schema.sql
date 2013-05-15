-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 15, 2013 at 05:24 AM
-- Server version: 5.6.10
-- PHP Version: 5.4.12

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `shib_lti`
--

-- --------------------------------------------------------

--
-- Table structure for table `lti_context`
--

CREATE TABLE IF NOT EXISTS `lti_context` (
  `id` bigint(19) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `label` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `lti_context_resource`
--

CREATE TABLE IF NOT EXISTS `lti_context_resource` (
  `lti_context_id` bigint(19) NOT NULL,
  `lti_resource_id` bigint(19) NOT NULL,
  PRIMARY KEY (`lti_context_id`,`lti_resource_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lti_resource`
--

CREATE TABLE IF NOT EXISTS `lti_resource` (
  `id` bigint(19) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `lti_user`
--

CREATE TABLE IF NOT EXISTS `lti_user` (
  `id` bigint(19) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name_given` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_family` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `name_full` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `lti_user_context`
--

CREATE TABLE IF NOT EXISTS `lti_user_context` (
  `lti_user_id` bigint(19) NOT NULL,
  `lti_context_id` bigint(19) NOT NULL,
  `is_instructor` tinyint(1) NOT NULL DEFAULT '0',
  `roles` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`lti_user_id`,`lti_context_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shibboleth_lti_user`
--

CREATE TABLE IF NOT EXISTS `shibboleth_lti_user` (
  `shibboleth_user_id` bigint(19) NOT NULL,
  `lti_user_id` bigint(19) NOT NULL,
  PRIMARY KEY (`shibboleth_user_id`,`lti_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shibboleth_user`
--

CREATE TABLE IF NOT EXISTS `shibboleth_user` (
  `id` bigint(19) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
