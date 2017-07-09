-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2017 年 06 月 23 日 22:14
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
-- 表的结构 `jydp_reward`
--

CREATE TABLE IF NOT EXISTS `jydp_reward` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL COMMENT '用户id',
  `nickname` varchar(200) NOT NULL COMMENT '用户昵称',
  `reward` decimal(10,2) NOT NULL COMMENT '奖励更变数值',
  `reward_format` varchar(30) NOT NULL COMMENT '奖励数值解析',
  `status` enum('add','reduce') NOT NULL COMMENT '奖励变化状态',
  `type` varchar(200) NOT NULL COMMENT '奖励变化类型',
  `addtime` int(11) NOT NULL,
  `addtimes` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='奖励流水表' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
