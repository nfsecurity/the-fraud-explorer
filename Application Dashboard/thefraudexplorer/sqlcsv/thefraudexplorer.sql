/*
 * The Fraud Explorer 
 * https://www.thefraudexplorer.com/
 *
 * Copyright (c) 2014-2021 The Fraud Explorer
 * email: customer@thefraudexplorer.com
 * Licensed under GNU GPL v3
 * https://www.thefraudexplorer.com/License
 *
 * Author: jrios@nofraud.la
 * Version code-name: nemesis
 *
 * Description: SQL database
 */

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

USE `thefraudexplorer`;

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
    `totalwords` int DEFAULT 0,
    `pressure` int DEFAULT 0,
    `opportunity` int DEFAULT 0,
    `rationalization` int DEFAULT 0,
    `flags` int DEFAULT 0,
    PRIMARY KEY (`agent`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `t_agents` (`agent`, `heartbeat`, `system`, `version`, `status`, `domain`, `ipaddress`, `name`, `ruleset`, `gender`, `totalwords`, `pressure`, `opportunity`, `rationalization`, `flags`) VALUES ('johndoe_90214c1_agt', '2017-04-15 07:46:12', '6.2', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.7', 'John Doe', 'BASELINE', 'male', '12723', '8', '10', '7', '0'), ('nigel_abc14c1_agt', '2017-04-15 08:21:10', '6.1', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.8', 'Nigel Eagle', 'BASELINE', 'female', '7321', '25', '0', '0', '0'), ('desmond_402vcc4_agt', '2017-04-15 09:34:18', '6.2', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.9', 'Desmond Wiedenbauer', 'BASELINE', 'male', '1983', '0', '25', '0', '0'), ('spruce_s0214ck_agt', '2017-04-06 05:36:20', '6.1', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.10', 'Spruce Bellevedere', 'BASELINE', 'male', '3000', '0', '0', '25', '0'), ('fletch_80j14g1_agt', '2017-04-15 17:01:12', '6.1', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.11', 'Fletch Nigel', 'BASELINE', 'male', '1560', '10', '10', '5', '0'), ('ingredia_tq2v4c1_agt', '2017-04-06 03:11:02', '6.2', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.12', 'Ingredia Douchebag', 'BASELINE', 'female', '3489', '5', '5', '15', '0'), ('archibald_b0314cm_agt', '2017-04-06 09:14:37', '6.1', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.13', 'Archibald Gibson', 'BASELINE', 'male', '921', '20', '2', '3', '1'), ('niles_1011jcl_agt', '2017-04-15 02:37:13', '6.2', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.14', 'Niles Ameter', 'BASELINE', 'male', '7528', '9', '13', '3', '0'), ('lurch_t021ycp_agt', '2017-04-15 19:33:49', '6.1', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.15', 'Lurch Barrow', 'BASELINE', 'male', '9800', '9', '5', '11', '0'), ('eleanor_1114c3_agt', '2017-04-15 03:36:11', '6.2', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.16', 'Eleanor Rails', 'BASELINE', 'female', '2899', '17', '3', '5', '3'), ('gordon_bbb94cc_agt', '2017-04-15 04:16:09', '6.1', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.17', 'Gordon Mondover', 'BASELINE', 'male', '1488', '7', '18', '0', '0'), ('gustav_cht14f2_agt', '2017-04-15 06:46:22', '6.2', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.18', 'Gustav Deck', 'BASELINE', 'male', '23900', '4', '9', '12', '0'), ('jason_j8g12cg_agt', '2017-04-15 09:56:37', '6.1', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.19', 'Jason Posture', 'BASELINE', 'male', '249', '0', '16', '9', '0'), ('burgundy_18hg4cj_agt', '2017-04-15 17:12:43', '6.2', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.20', 'Burgundy Skinner', 'BASELINE', 'male', '76', '9', '9', '7', '0'), ('benjamin_0001kc9_agt', '2017-04-15 21:00:51', '6.1', 'v1.3.2', 'inactive', 'thefraudexplorer.com', '172.16.10.21', 'Benjamin Evalent', 'BASELINE', 'male', '7599', '7', '7', '11', '0');

CREATE TABLE IF NOT EXISTS `t_users` (
    `user` varchar(50) NOT NULL DEFAULT '',
    `password` varchar(40) DEFAULT NULL,
    `domain` varchar(256) DEFAULT NULL,
    PRIMARY KEY (`user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `t_users` (`user`, `password`, `domain`) VALUES
('admin', 'a5281d9d55b0d0bd22d2b69f295cee8fd966cf98', 'all');

CREATE TABLE t_login_attempts (
    ip varchar(20),
    attempts int default 0,
    lastlogin datetime default NULL	
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `t_config` (
    `score_ts_low_from` int DEFAULT NULL,
    `score_ts_low_to` int DEFAULT NULL,
    `score_ts_medium_from` int DEFAULT NULL,
    `score_ts_medium_to` int DEFAULT NULL,
    `score_ts_high_from` int DEFAULT NULL,
    `score_ts_high_to` int DEFAULT NULL,
    `score_ts_critic_from` int DEFAULT NULL,
    `score_ts_critic_to` int DEFAULT NULL,
    `sample_data_calculation` varchar(10) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `t_config` (
    `score_ts_low_from`, 
    `score_ts_low_to`, 
    `score_ts_medium_from`,
    `score_ts_medium_to`, 
    `score_ts_high_from`, 
    `score_ts_high_to`, 
    `score_ts_critic_from`, 
    `score_ts_critic_to`,
    `sample_data_calculation`
) VALUES ('0', '1', '2', '3', '4', '5', '6', '100', 'enabled');

CREATE TABLE IF NOT EXISTS `t_inferences` (
    `endpoint` varchar(128) DEFAULT NULL,
    `domain` varchar(128) DEFAULT NULL,
    `ruleset` varchar(128) DEFAULT NULL,
    `application` varchar(128) DEFAULT NULL,
    `date` datetime DEFAULT NULL,
    `reason` varchar(12) DEFAULT NULL,
    `alertid` varchar(128) DEFAULT NULL,
    `deduction` int DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `t_workflows` (
    `name` varchar(128) DEFAULT NULL,
    `workflow` varchar(2048) DEFAULT NULL,
    `interval` int DEFAULT 0,
    `custodian` varchar(128) DEFAULT NULL,
    `tone` int DEFAULT 0,
    `flag` int DEFAULT 0,
    `triggers` int DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

INSERT INTO `t_workflows` (`name`, `workflow`, `interval`, `custodian`, `tone`, `flag`, `triggers`) VALUES ('Executives external businesses', '[D]=CLEVEL, [V]=ALLV, [D]=ALLD, [E]=ALLE, [A]=ALLA, [P]=billing statement, [O]=END', '0', 'events@thefraudexplorer.com', '0', '0', '0'), ('Credit alternatives', '[D]=CREDIT, [V]=ALLV, [D]=thefraudexplorer.com, [E]=ALLE, [A]=WhatsApp, [P]=billing statement, [O]=END', '0', 'events@thefraudexplorer.com', '1', '0', '0'), ('Purchases pressures and opportunities', '[D]=PURCHASES, [V]=PRESSURE, [D]=ALLD, [E]=ALLE, [A]=ALLA, [P]=ALLP, [O]=AND, [D]=PURCHASES, [V]=OPPORTUNITY, [D]=ALLD, [E]=ALLE, [A]=ALLA, [P]=ALLP, [O]=END', '8', 'events@thefraudexplorer.com', '1', '0', '0');

CREATE TABLE IF NOT EXISTS `t_wtriggers` (
    `date` datetime DEFAULT NULL,
    `name` varchar(128) DEFAULT NULL,
    `ids` varchar(65535) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `t_metrics` (
    `endpoint` varchar(128) DEFAULT NULL,
    `domain` varchar(128) DEFAULT NULL,
    `ruleset` varchar(128) DEFAULT NULL,
    `11P` int DEFAULT 0, `11O` int DEFAULT 0, `11R` int DEFAULT 0, `10P` int DEFAULT 0, `10O` int DEFAULT 0, `10R` int DEFAULT 0,
    `9P` int DEFAULT 0, `9O` int DEFAULT 0, `9R` int DEFAULT 0, `8P` int DEFAULT 0, `8O` int DEFAULT 0, `8R` int DEFAULT 0,
    `7P` int DEFAULT 0, `7O` int DEFAULT 0, `7R` int DEFAULT 0, `6P` int DEFAULT 0, `6O` int DEFAULT 0, `6R` int DEFAULT 0,
    `5P` int DEFAULT 0, `5O` int DEFAULT 0, `5R` int DEFAULT 0, `4P` int DEFAULT 0, `4O` int DEFAULT 0, `4R` int DEFAULT 0,
    `3P` int DEFAULT 0, `3O` int DEFAULT 0, `3R` int DEFAULT 0, `2P` int DEFAULT 0, `2O` int DEFAULT 0, `2R` int DEFAULT 0,
    `1P` int DEFAULT 0, `1O` int DEFAULT 0, `1R` int DEFAULT 0, `0P` int DEFAULT 0, `0O` int DEFAULT 0, `0R` int DEFAULT 0
) ENGINE=MyISAM DEFAULT CHARSET=latin1;