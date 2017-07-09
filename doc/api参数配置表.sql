-- phpMyAdmin SQL Dump
-- version phpStudy 2014
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2017 年 07 月 05 日 23:42
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
-- 表的结构 `jydp_api_site`
--

CREATE TABLE IF NOT EXISTS `jydp_api_site` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `siteid` int(11) NOT NULL,
  `type` int(11) NOT NULL COMMENT '栏目类别：1精彩2精选3潮派',
  `model` int(11) NOT NULL COMMENT '模型：1栏目2广告',
  `api_site_id` int(11) NOT NULL COMMENT '配置id',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='api配置表' AUTO_INCREMENT=7 ;

--
-- 转存表中的数据 `jydp_api_site`
--

INSERT INTO `jydp_api_site` (`id`, `siteid`, `type`, `model`, `api_site_id`) VALUES
(1, 1, 1, 2, 11),
(2, 1, 3, 2, 12),
(3, 1, 2, 2, 13),
(4, 1, 1, 1, 9),
(5, 1, 2, 1, 10),
(6, 1, 3, 1, 11);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
