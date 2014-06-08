--
-- Table structure for table `usermap_facebokgroup`
--

CREATE TABLE `usermap_facebookgroup` (
  `id` int(12) NOT NULL AUTO_INCREMENT,
  `fb_id` varchar(100) NOT NULL,
  `name` varchar(200) NOT NULL,
  `link` varchar(350) NOT NULL,
  `location` varchar(350) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lon` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=34 ;
