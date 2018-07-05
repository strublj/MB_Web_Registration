
-- --------------------------------------------------------

--
-- Table structure for table `merit_badge`
--

DROP TABLE IF EXISTS `merit_badge`;
CREATE TABLE `merit_badge` (
  `mb_id` int(11) NOT NULL,
  `mb_name` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `eagle_req` int(1) NOT NULL DEFAULT '0',
  `stem` int(1) NOT NULL DEFAULT '0',
  `pre_req` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `area` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `location` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `cost` int(11) DEFAULT NULL,
  `difficulty` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `min_age` int(11) DEFAULT NULL,
  `min_rank_id` int(11) DEFAULT NULL,
  `mb_notes` varchar(200) COLLATE utf8_unicode_ci DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
