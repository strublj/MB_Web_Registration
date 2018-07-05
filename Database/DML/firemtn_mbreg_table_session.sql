
--
-- Truncate table before insert `session`
--

TRUNCATE TABLE `session`;
--
-- Dumping data for table `session`
--

INSERT INTO `session` (`session_id`, `session_number`, `start_time`, `end_time`) VALUES
(1, 1, '9:15 am', '10:15 am'),
(2, 2, '10:30 am', '11:30 am'),
(3, 3, '1:30 pm', '2:30 pm'),
(4, 4, '2:45 pm', '3:45 pm');
