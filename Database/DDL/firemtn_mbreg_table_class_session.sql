
-- --------------------------------------------------------

--
-- Table structure for table `class_session`
--

DROP TABLE IF EXISTS `class_session`;
CREATE TABLE `class_session` (
  `class_session_id` int(11) NOT NULL,
  `merit_badge_id` int(11) NOT NULL,
  `start_session_id` int(11) NOT NULL,
  `number_of_sessions` int(11) NOT NULL,
  `size_limit` int(11) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
