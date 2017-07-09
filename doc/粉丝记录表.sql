-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2017 年 06 月 20 日 22:39
-- 服务器版本: 5.5.53
-- PHP 版本: 5.4.45

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `jydp`
--

-- --------------------------------------------------------

--
-- 表的结构 `jydp_fans`
--

CREATE TABLE IF NOT EXISTS `jydp_fans` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `userid` int(11) NOT NULL COMMENT '粉丝id',
  `nickname` varchar(200) NOT NULL COMMENT '粉丝昵称',
  `pid` int(11) NOT NULL COMMENT '上级id',
  `pid_nickname` varchar(200) NOT NULL COMMENT '上级昵称',
  `source` varchar(255) NOT NULL COMMENT '来源',
  `siteid` int(11) NOT NULL COMMENT '站点id',
  `sitename` varchar(200) NOT NULL COMMENT '站点名称',
  `addtime` int(11) NOT NULL,
  `addtimes` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
