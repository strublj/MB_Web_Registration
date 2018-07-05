
-- --------------------------------------------------------

--
-- Table structure for table `council`
--

DROP TABLE IF EXISTS `council`;
CREATE TABLE `council` (
  `council_id` int(11) NOT NULL,
  `council` varchar(100) NOT NULL DEFAULT '',
  `council_code` varchar(5) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Council list';
