-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2017 年 06 月 16 日 23:13
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
-- 表的结构 `jydp_chaopai_registre`
--

CREATE TABLE IF NOT EXISTS `jydp_chaopai_registre` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siteid` int(11) NOT NULL COMMENT '站点id',
  `sitename` varchar(200) NOT NULL COMMENT '站点名称',
  `catid` int(11) NOT NULL COMMENT '模型id',
  `catname` varchar(200) NOT NULL COMMENT '分类名称',
  `chaopai_id` int(11) NOT NULL COMMENT '潮派活动id',
  `chaopai_name` varchar(200) NOT NULL COMMENT '活动名称',
  `userid` int(11) NOT NULL COMMENT '用户id',
  `nickname` varchar(200) NOT NULL COMMENT '用户昵称',
  `name` varchar(200) NOT NULL COMMENT '姓名',
  `phone` varchar(30) NOT NULL COMMENT '电话',
  `num` int(11) NOT NULL COMMENT '人数',
  `addtime` int(11) NOT NULL,
  `addtimes` varchar(30) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='活动报名记录表' AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
