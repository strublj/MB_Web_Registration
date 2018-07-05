
-- --------------------------------------------------------

--
-- Table structure for table `mbreg_account`
--

DROP TABLE IF EXISTS `mbreg_account`;
CREATE TABLE `mbreg_account` (
  `mbreg_account_id` int(11) NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `login` varchar(35) COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `is_admin` int(11) NOT NULL DEFAULT '0',
  `uuid` char(36) COLLATE utf8_unicode_ci NOT NULL,
  `email_sent` int(11) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Triggers `mbreg_account`
--
DROP TRIGGER IF EXISTS `before_insert_mbreg_account`;
DELIMITER $$
CREATE TRIGGER `before_insert_mbreg_account` BEFORE INSERT ON `mbreg_account` FOR EACH ROW BEGIN
	SET new.uuid = uuid();
END
$$
DELIMITER ;
