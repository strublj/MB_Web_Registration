
--
-- Truncate table before insert `merit_badge`
--

TRUNCATE TABLE `merit_badge`;
--
-- Dumping data for table `merit_badge`
--

INSERT INTO `merit_badge` (`mb_id`, `mb_name`, `eagle_req`, `stem`, `pre_req`, `area`, `location`, `cost`, `difficulty`, `min_age`, `min_rank_id`, `mb_notes`) VALUES
(1, 'Basketry', 0, 0, NULL, 'Handicrafts', '', 15, NULL, NULL, NULL, 'estimated cost of kit/materials'),
(2, 'Leatherwork', 0, 0, NULL, 'Handicrafts', '', 10, NULL, NULL, NULL, 'estimated cost of kit/materials'),
(3, 'Photography', 0, 1, 'Pre-req: 7c', 'Handicrafts', '', NULL, NULL, NULL, NULL, 'bring a digital camera'),
(4, 'Woodcarving', 0, 0, NULL, 'Handicrafts', '', 6, NULL, NULL, NULL, 'estimated cost of kit/materials; bring a whittling knife'),
(5, 'Archeology', 0, 1, 'Pre-req: 7', 'Nature', '', NULL, NULL, NULL, NULL, NULL),
(6, 'Astronomy', 0, 1, 'Pre-req: 5b and 6b', 'Nature', '', NULL, NULL, NULL, NULL, NULL),
(7, 'Environmental Science', 1, 1, NULL, 'Nature', '', NULL, NULL, 13, NULL, NULL),
(8, 'Forestry', 0, 1, NULL, 'Nature', '', NULL, NULL, NULL, NULL, NULL),
(9, 'Geology', 0, 1, NULL, 'Nature', '', NULL, NULL, NULL, NULL, NULL),
(10, 'Mammal Study/Nature', 0, 1, NULL, 'Nature', '', NULL, NULL, NULL, NULL, '2 merit badges offered together'),
(11, 'Robotics', 0, 1, NULL, 'Nature', '', NULL, NULL, NULL, NULL, NULL),
(12, 'Soil & Water Conservation', 0, 1, NULL, 'Nature', '', NULL, NULL, NULL, NULL, NULL),
(13, 'Space Exploration', 0, 1, 'Pre-req: 2 and 5', 'Nature', '', 12, NULL, NULL, NULL, 'estimated cost of kit/materials'),
(14, 'Canoeing', 0, 1, 'must be a swimmer', 'Aquatics', '', NULL, NULL, NULL, NULL, NULL),
(15, 'Kayaking', 0, 0, 'must be a swimmer', 'Aquatics', '', NULL, NULL, NULL, NULL, NULL),
(16, 'Lifesaving', 1, 0, 'must be a swimmer; pre-req: 1a', 'Aquatics', '', NULL, NULL, 13, NULL, NULL),
(17, 'Small Boat Sailing', 0, 0, 'must be a swimmer', 'Aquatics', '', NULL, NULL, 13, NULL, NULL),
(18, 'Swimming', 1, 0, 'must be a swimmer', 'Aquatics', '', NULL, NULL, NULL, NULL, NULL),
(19, 'Archery', 0, 0, NULL, 'Shooting Sports', '', 5, NULL, NULL, NULL, 'estimated cost of kit/materials'),
(20, 'Rifle Shooting', 0, 0, NULL, 'Shooting Sports', '', 9, NULL, NULL, NULL, 'estimated cost of materials'),
(21, 'Shotgun Shooting', 0, 0, NULL, 'Shooting Sports', '', 30, NULL, 13, NULL, 'estimated cost of materials'),
(22, 'Camping', 1, 0, 'Pre-req: 4b, 5e, 7b, 8d, 9a, 9b', 'Outdoor Skills', '', NULL, NULL, NULL, NULL, NULL),
(23, 'Cooking', 1, 0, 'Pre-req: 4, 5a-f, 6a-e', 'Outdoor Skills', '', NULL, NULL, NULL, NULL, NULL),
(24, 'Cycling', 1, 0, NULL, 'Outdoor Skills', '', NULL, NULL, NULL, NULL, 'Partial'),
(25, 'Emergency Preparedness', 1, 0, 'Pre-req: 1, 2c, 7b', 'Outdoor Skills', '', NULL, NULL, 13, NULL, NULL),
(26, 'First Aid', 1, 0, 'Must be at least Second Class', 'Outdoor Skills', '', NULL, NULL, 13, NULL, NULL),
(27, 'Fishing', 0, 0, NULL, 'Outdoor Skills', '', NULL, NULL, NULL, NULL, NULL),
(28, 'Orienteering', 0, 0, NULL, 'Outdoor Skills', '', NULL, NULL, NULL, NULL, NULL),
(29, 'Pioneering', 0, 1, NULL, 'Outdoor Skills', '', NULL, NULL, NULL, NULL, NULL),
(30, 'Search and Rescue', 0, 0, 'Pre-req: 6a', 'Outdoor Skills', '', NULL, NULL, NULL, NULL, NULL),
(31, 'Wilderness Survival', 0, 0, NULL, 'Outdoor Skills', '', NULL, NULL, NULL, NULL, NULL),
(32, 'Climbing', 0, 0, NULL, 'Special Merit Badges/Programs', '', NULL, NULL, 13, NULL, NULL),
(33, 'COPE', 0, 0, NULL, 'Special Merit Badges/Programs', '', NULL, NULL, 13, NULL, 'not a merit badge'),
(34, 'BSA Lifeguard', 0, 0, NULL, 'Special Merit Badges/Programs', '', NULL, NULL, 15, NULL, 'not a merit badge; uses all sessions all week'),
(35, 'Pathfinders: Tenderfoot', 0, 0, NULL, 'Special Merit Badges/Programs', '', NULL, NULL, NULL, NULL, NULL),
(36, 'Pathfinders: Second Class', 0, 0, NULL, 'Special Merit Badges/Programs', '', NULL, NULL, NULL, NULL, NULL),
(37, 'Pathfinders: First Class', 0, 0, NULL, 'Special Merit Badges/Programs', '', NULL, NULL, NULL, NULL, NULL);
