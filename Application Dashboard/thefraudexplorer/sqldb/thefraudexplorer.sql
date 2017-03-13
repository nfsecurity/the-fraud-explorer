SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

USE `thefraudexplorer`;

CREATE TABLE IF NOT EXISTS `t_captcha` (
  `captcha` varchar(5) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `t_crypt` (
  `key` varchar(256) DEFAULT NULL,
  `iv` varchar(256) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `t_crypt` (`key`, `iv`, `password`) VALUES
('1uBu8ycVugDIJz61', '1uBu8ycVugDIJz61', 'WCCE207');

CREATE TABLE IF NOT EXISTS `t_agents` (
  `agent` varchar(200) DEFAULT NULL,
  `heartbeat` datetime DEFAULT NULL,
  `system` varchar(20) DEFAULT NULL,
  `version` varchar(15) DEFAULT NULL,
  `status` varchar(15) DEFAULT NULL,
  `domain` varchar(256) DEFAULT NULL,
  `ipaddress` varchar(128) DEFAULT NULL,
  `name` varchar(200) DEFAULT NULL,
  `ruleset` varchar(200) DEFAULT NULL,
  `gender` varchar(200) DEFAULT NULL,
  `totalwords` int DEFAULT NULL,
  `pressure` int DEFAULT NULL,
  `opportunity` int DEFAULT NULL,
  `rationalization` int DEFAULT NULL, 
  PRIMARY KEY (`agent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `t_users` (
  `user` varchar(50) NOT NULL DEFAULT '',
  `password` varchar(40) DEFAULT NULL,
  `domain` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE t_login_attempts (
 ip varchar(20),
 attempts int default 0,
 lastlogin datetime default NULL	
);

INSERT INTO `t_users` (`user`, `password`) VALUES
('admin', 'e731a7b612ab389fcb7f973c452f33df3eb69c99');
