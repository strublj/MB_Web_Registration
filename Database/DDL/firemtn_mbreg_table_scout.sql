
-- --------------------------------------------------------

--
-- Table structure for table `scout`
--

DROP TABLE IF EXISTS `scout`;
CREATE TABLE `scout` (
  `scout_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `first_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `rank_id` int(11) DEFAULT NULL,
  `age` int(11) NOT NULL,
  `pref1_mb_id` int(11) DEFAULT NULL,
  `pref2_mb_id` int(11) DEFAULT NULL,
  `pref3_mb_id` int(11) DEFAULT NULL,
  `pref4_mb_id` int(11) DEFAULT NULL,
  `pref5_mb_id` int(11) DEFAULT NULL,
  `pref6_mb_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
