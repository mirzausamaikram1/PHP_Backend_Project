-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 28, 2025 at 07:56 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projectdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `cid` int(11) NOT NULL,
  `cname` varchar(255) NOT NULL,
  `cpassword` varchar(255) NOT NULL,
  `ctel` int(11) DEFAULT NULL,
  `caddr` text DEFAULT NULL,
  `company` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer`
--

INSERT INTO `customer` (`cid`, `cname`, `cpassword`, `ctel`, `caddr`, `company`) VALUES
(1, 'Alex Wong', 'password', 21232123, 'G/F, ABC Building, King Yip Street, KwunTong, Kowloon, Hong Kong', 'Fat Cat Company Limited'),
(2, 'Tina Chan', 'password', 31233123, '303, Mei Hing Center, Yuen Long, NT, Hong Kong', 'XDD LOL Company'),
(3, 'Bowie', 'password', 61236123, '401, Sing Kei Building, Kowloon, Hong Kong', 'GPA4 Company');

-- --------------------------------------------------------

--
-- Table structure for table `material`
--

CREATE TABLE `material` (
  `mid` int(11) NOT NULL,
  `mname` varchar(255) NOT NULL,
  `mqty` int(11) NOT NULL,
  `mrqty` int(11) NOT NULL,
  `munit` varchar(20) NOT NULL,
  `mreorderqty` int(11) NOT NULL,
  `mimage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material`
--

INSERT INTO `material` (`mid`, `mname`, `mqty`, `mrqty`, `munit`, `mreorderqty`, `mimage`) VALUES
(1, 'Rubber 3233', 1000, 0, 'KG', 200, NULL),
(2, 'Cotten CDC24', 2000, 247, 'KG', 400, NULL),
(3, 'Wood RAW77', 5000, 0, 'KG', 1000, NULL),
(4, 'ABS LL Chem 5026', 2000, 196, 'KG', 400, NULL),
(5, '4 x 1 Flat Head Stainless Steel Screws', 50000, 2650, 'PC', 20000, NULL),
(6, 'Rubber', 10, 9, 'Pcs', 1, '6.jpg'),
(7, 'Rubber', 10, 9, 'Pcs', 1, '7.jpg'),
(8, 'Rubber', 10, 9, 'Pcs', 1, '8.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `oid` int(11) NOT NULL,
  `odate` datetime NOT NULL,
  `pid` int(11) NOT NULL,
  `oqty` int(11) NOT NULL,
  `ocost` decimal(20,2) NOT NULL,
  `cid` int(11) NOT NULL,
  `odeliverdate` datetime DEFAULT NULL,
  `ostatus` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`oid`, `odate`, `pid`, `oqty`, `ocost`, `cid`, `odeliverdate`, `ostatus`) VALUES
(1, '2025-04-12 17:50:00', 1, 200, 3980.00, 1, NULL, 1),
(2, '2025-04-13 12:01:00', 5, 200, 99800.00, 2, '2025-06-22 12:30:00', 3),
(3, '2025-04-14 10:00:00', 2, 150, 1485.00, 1, NULL, 1),
(4, '2025-04-15 14:30:00', 3, 100, 24990.00, 2, '2025-04-20 10:00:00', 2),
(5, '2025-04-16 09:15:00', 1, 250, 4975.00, 3, NULL, 1),
(6, '2025-04-17 11:45:00', 4, 180, 5400.00, 1, '2025-04-22 15:30:00', 3),
(8, '2025-06-28 04:24:40', 5, 2, 998.00, 2, '2025-07-01 04:24:40', 1),
(9, '2025-06-28 04:28:27', 1, 1, 19.90, 2, '2025-07-01 04:28:27', 1),
(10, '2025-06-28 04:28:37', 3, 1, 249.90, 2, '2025-07-01 04:28:37', 1),
(11, '2025-06-28 04:57:17', 1, 1, 19.90, 2, '2025-07-01 04:57:17', 1),
(12, '2025-06-28 04:58:00', 4, 1, 30.00, 2, '2025-07-01 04:58:00', 1),
(13, '2025-06-28 11:04:33', 5, 1, 499.00, 2, '2025-07-01 11:04:33', 1);

-- --------------------------------------------------------

--
-- Table structure for table `prodmat`
--

CREATE TABLE `prodmat` (
  `pid` int(11) NOT NULL,
  `mid` int(11) NOT NULL,
  `pmqty` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `prodmat`
--

INSERT INTO `prodmat` (`pid`, `mid`, `pmqty`) VALUES
(1, 4, 1),
(1, 5, 6),
(2, 3, 1),
(2, 5, 4),
(3, 4, 1),
(3, 5, 12),
(4, 4, 1),
(4, 5, 8),
(5, 2, 1),
(5, 5, 6),
(6, 2, 10);

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `pid` int(11) NOT NULL,
  `pname` varchar(255) NOT NULL,
  `pdesc` text DEFAULT NULL,
  `pcost` decimal(12,2) NOT NULL,
  `pimage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`pid`, `pname`, `pdesc`, `pcost`, `pimage`) VALUES
(1, 'Cyberpunk Truck C204', 'Explore the world of imaginative play with our vibrant and durable toy truck. Perfect for little hands, this truck will inspire endless storytelling adventures both indoors and outdoors. Made from high-quality materials, it is built to withstand hours of creative playtime.', 19.90, NULL),
(2, 'XDD Wooden Plane', 'Take to the skies with our charming wooden plane toy. Crafted from eco-friendly and child-safe materials, this beautifully designed plane sparks the imagination and encourages interactive play. With smooth edges and a sturdy construction, it\'s a delightful addition to any young aviator\'s toy collection.', 9.90, NULL),
(3, 'iRobot 3233GG', 'Introduce your child to the wonders of technology and robotics with our smart robot companion. Packed with interactive features and educational benefits, this futuristic toy engages curious minds and promotes STEM learning in a fun and engaging way. Watch as your child explores coding, problem-solving, and innovation with this cutting-edge robot friend.', 249.90, NULL),
(4, 'Apex Ball Ball Helicopter M1297', 'Experience the thrill of flight with our ball helicopter toy. Easy to launch and navigate, this exciting toy provides hours of entertainment for children of all ages. With colorful LED lights and a durable design, it\'s a fantastic outdoor toy that brings joy and excitement to playtime.', 30.00, NULL),
(5, 'RoboKat AI Cat Robot', 'Meet our AI Cat Robot – the purr-fect blend of technology and cuddly companionship. This interactive robotic feline offers lifelike movements, sounds, and responses, providing a realistic pet experience without the hassle. With customizable features and playful interactions, this charming cat robot is a delightful addition to your child\'s playroom.', 499.00, NULL),
(6, 'Airplane', 'hello', 10.00, '6.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `sid` int(11) NOT NULL,
  `spassword` varchar(255) NOT NULL,
  `sname` varchar(255) NOT NULL,
  `srole` varchar(45) DEFAULT NULL,
  `stel` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`sid`, `spassword`, `sname`, `srole`, `stel`) VALUES
(1, 'password', 'Peter Wong', 'Sales Manager', 25669197);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`cid`);

--
-- Indexes for table `material`
--
ALTER TABLE `material`
  ADD PRIMARY KEY (`mid`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`oid`),
  ADD KEY `fk_orders_cid` (`cid`),
  ADD KEY `fk_orders_pid` (`pid`);

--
-- Indexes for table `prodmat`
--
ALTER TABLE `prodmat`
  ADD PRIMARY KEY (`pid`,`mid`),
  ADD KEY `fk_prodmat_mid` (`mid`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`pid`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`sid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `cid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `material`
--
ALTER TABLE `material`
  MODIFY `mid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `oid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `pid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `sid` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_cid` FOREIGN KEY (`cid`) REFERENCES `customer` (`cid`),
  ADD CONSTRAINT `fk_orders_pid` FOREIGN KEY (`pid`) REFERENCES `product` (`pid`);

--
-- Constraints for table `prodmat`
--
ALTER TABLE `prodmat`
  ADD CONSTRAINT `fk_prodmat_mid` FOREIGN KEY (`mid`) REFERENCES `material` (`mid`),
  ADD CONSTRAINT `fk_prodmat_pid` FOREIGN KEY (`pid`) REFERENCES `product` (`pid`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
