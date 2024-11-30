-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2024 at 03:30 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `autoschedula`
--

-- --------------------------------------------------------

--
-- Table structure for table `batch_tbl`
--

CREATE TABLE `batch_tbl` (
  `batch_code` varchar(50) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `course_director` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `course_tbl`
--

CREATE TABLE `course_tbl` (
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `school` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lecturers_tbl`
--

CREATE TABLE `lecturers_tbl` (
  `emp_no` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `modules_tbl`
--

CREATE TABLE `modules_tbl` (
  `module_code` varchar(50) NOT NULL,
  `module_hours` int(11) NOT NULL,
  `module_name` varchar(100) NOT NULL,
  `course_code` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `postaff`
--

CREATE TABLE `postaff` (
  `emp_no` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `stu_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `batch_tbl`
--
ALTER TABLE `batch_tbl`
  ADD PRIMARY KEY (`batch_code`),
  ADD KEY `course_code` (`course_code`),
  ADD KEY `course_director` (`course_director`);

--
-- Indexes for table `course_tbl`
--
ALTER TABLE `course_tbl`
  ADD PRIMARY KEY (`course_code`);

--
-- Indexes for table `lecturers_tbl`
--
ALTER TABLE `lecturers_tbl`
  ADD PRIMARY KEY (`emp_no`);

--
-- Indexes for table `modules_tbl`
--
ALTER TABLE `modules_tbl`
  ADD PRIMARY KEY (`module_code`),
  ADD KEY `course_code` (`course_code`);

--
-- Indexes for table `postaff`
--
ALTER TABLE `postaff`
  ADD PRIMARY KEY (`emp_no`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`stu_id`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `batch_tbl`
--
ALTER TABLE `batch_tbl`
  ADD CONSTRAINT `batch_tbl_ibfk_1` FOREIGN KEY (`course_code`) REFERENCES `course_tbl` (`course_code`),
  ADD CONSTRAINT `batch_tbl_ibfk_2` FOREIGN KEY (`course_director`) REFERENCES `lecturers_tbl` (`emp_no`);

--
-- Constraints for table `modules_tbl`
--
ALTER TABLE `modules_tbl`
  ADD CONSTRAINT `modules_tbl_ibfk_1` FOREIGN KEY (`course_code`) REFERENCES `course_tbl` (`course_code`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
