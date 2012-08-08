-- phpMyAdmin SQL Dump
-- version 3.5.2
-- http://www.phpmyadmin.net
--
-- 主机: 127.0.0.1
-- 生成日期: 2012 年 08 月 08 日 20:52
-- 服务器版本: 5.5.25a
-- PHP 版本: 5.3.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `photo360_development`
--

-- --------------------------------------------------------

--
-- 表的结构 `wmstyles`
--

CREATE TABLE IF NOT EXISTS `wmstyles` (
  `id` int(10) NOT NULL,
  `user_id` int(10) NOT NULL,
  `style` varchar(128) NOT NULL,
  `value` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `wmstyles`
--

INSERT INTO `wmstyles` (`id`, `user_id`, `style`, `value`) VALUES
(0, 2, 'biggest', 'imageView/0/w/512/h/512'),
(0, 2, 'small', 'imageView/0/w/64/h/64'),
(0, 2, 'mid', 'imageView/0/w/128/h/128');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
