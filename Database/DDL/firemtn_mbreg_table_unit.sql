
-- --------------------------------------------------------

--
-- Table structure for table `unit`
--

DROP TABLE IF EXISTS `unit`;
CREATE TABLE `unit` (
  `unit_id` int(11) NOT NULL,
  `council_id` int(11) NOT NULL,
  `unit_type` varchar(25) COLLATE utf8_unicode_ci NOT NULL,
  `unit_number` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `week_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
