-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 11, 2025 at 06:38 PM
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
-- Table structure for table `assign_module_tbl`
--

CREATE TABLE `assign_module_tbl` (
  `id` int(11) NOT NULL,
  `batch_id` varchar(50) NOT NULL,
  `lecturer_id` varchar(50) NOT NULL,
  `module_id` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `status` varchar(20) NOT NULL,
  `session_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assign_module_tbl`
--

INSERT INTO `assign_module_tbl` (`id`, `batch_id`, `lecturer_id`, `module_id`, `date`, `status`, `session_type`) VALUES
(21, 'HDSE232F', '1004', 'ML', '2025-02-02', '0', 'Full day'),
(22, 'HDSE232F', '1004', 'ML', '2025-01-29', '0', 'Full day'),
(23, 'HDSE232F', '1004', 'ML', '2025-02-08', '0', 'Full day'),
(24, 'HDSE232F', '1004', 'ML', '2025-01-21', '0', 'Full day'),
(25, 'HDSE232F', '1004', 'ML', '2025-01-12', '0', 'Full day'),
(26, 'HDSE232F', '1004', 'ML', '2025-01-24', '0', 'Full day'),
(27, 'HDSE232F', '1004', 'ML', '2025-01-19', '0', 'Full day'),
(28, 'HDSE232F', '1004', 'ML', '2025-01-25', '0', 'Full day'),
(29, 'HDSE232F', '1004', 'ML', '2025-02-01', '0', 'Full day'),
(30, 'HDSE232F', '1004', 'ML', '2025-01-10', '0', 'Full day');

-- --------------------------------------------------------

--
-- Table structure for table `batch_tbl`
--

CREATE TABLE `batch_tbl` (
  `batch_code` varchar(50) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `course_director` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `batch_tbl`
--

INSERT INTO `batch_tbl` (`batch_code`, `course_code`, `course_director`) VALUES
('HDSE232F', 'HDSE', '1004'),
('HDSE233F', 'HDSE', '1004');

-- --------------------------------------------------------

--
-- Table structure for table `course_tbl`
--

CREATE TABLE `course_tbl` (
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(100) NOT NULL,
  `school` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_tbl`
--

INSERT INTO `course_tbl` (`course_code`, `course_name`, `school`) VALUES
('HDSE', 'Higher National Diploma in Software Engineering', 'Computing.');

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

--
-- Dumping data for table `lecturers_tbl`
--

INSERT INTO `lecturers_tbl` (`emp_no`, `full_name`, `email`, `password`) VALUES
('1004', 'Sandaruwani Pathirage', 'sandaruwani@nibm.lk', '$2y$10$WwhibBXC4Dp5FAknGFoI5.nRxQHc8dyDWakmNi.m.3vIeGlWzrgCe');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_timetable`
--

CREATE TABLE `lecturer_timetable` (
  `id` int(11) NOT NULL,
  `lecturer_id` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturer_timetable`
--

INSERT INTO `lecturer_timetable` (`id`, `lecturer_id`, `description`, `date`) VALUES
(21, '1004', 'Lectures', '2025-02-02'),
(22, '1004', 'Lectures', '2025-01-29'),
(23, '1004', 'Lectures', '2025-02-08'),
(24, '1004', 'Lectures', '2025-01-21'),
(25, '1004', 'Lectures', '2025-01-12'),
(26, '1004', 'Lectures', '2025-01-24'),
(27, '1004', 'Lectures', '2025-01-19'),
(28, '1004', 'Lectures', '2025-01-25'),
(29, '1004', 'Lectures', '2025-02-01'),
(30, '1004', 'Lectures', '2025-01-10');

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

--
-- Dumping data for table `modules_tbl`
--

INSERT INTO `modules_tbl` (`module_code`, `module_hours`, `module_name`, `course_code`) VALUES
('ML', 60, 'Machine Learning', 'HDSE');

-- --------------------------------------------------------

--
-- Table structure for table `postaff_tbl`
--

CREATE TABLE `postaff_tbl` (
  `emp_no` varchar(255) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `postaff_tbl`
--

INSERT INTO `postaff_tbl` (`emp_no`, `full_name`, `email`, `password`) VALUES
('1002', 'Pasindu Aluthwalahewa', 'pasindue@99x.io', '$2y$10$5hYoeek54M7W.SpMvsxmQ.WQ3L4GURkUG4zQRvQl5Lu94kBHjAkt6');

-- --------------------------------------------------------

--
-- Table structure for table `student_tbl`
--

CREATE TABLE `student_tbl` (
  `stu_id` varchar(50) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assign_module_tbl`
--
ALTER TABLE `assign_module_tbl`
  ADD PRIMARY KEY (`id`),
  ADD KEY `batch_id` (`batch_id`),
  ADD KEY `lecturer_id` (`lecturer_id`),
  ADD KEY `module_id` (`module_id`);

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
-- Indexes for table `lecturer_timetable`
--
ALTER TABLE `lecturer_timetable`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lecturer_id` (`lecturer_id`);

--
-- Indexes for table `modules_tbl`
--
ALTER TABLE `modules_tbl`
  ADD PRIMARY KEY (`module_code`),
  ADD KEY `course_code` (`course_code`);

--
-- Indexes for table `postaff_tbl`
--
ALTER TABLE `postaff_tbl`
  ADD PRIMARY KEY (`emp_no`);

--
-- Indexes for table `student_tbl`
--
ALTER TABLE `student_tbl`
  ADD PRIMARY KEY (`stu_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assign_module_tbl`
--
ALTER TABLE `assign_module_tbl`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `lecturer_timetable`
--
ALTER TABLE `lecturer_timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assign_module_tbl`
--
ALTER TABLE `assign_module_tbl`
  ADD CONSTRAINT `assign_module_tbl_ibfk_1` FOREIGN KEY (`batch_id`) REFERENCES `batch_tbl` (`batch_code`),
  ADD CONSTRAINT `assign_module_tbl_ibfk_2` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers_tbl` (`emp_no`),
  ADD CONSTRAINT `assign_module_tbl_ibfk_3` FOREIGN KEY (`module_id`) REFERENCES `modules_tbl` (`module_code`);

--
-- Constraints for table `batch_tbl`
--
ALTER TABLE `batch_tbl`
  ADD CONSTRAINT `batch_tbl_ibfk_1` FOREIGN KEY (`course_code`) REFERENCES `course_tbl` (`course_code`),
  ADD CONSTRAINT `batch_tbl_ibfk_2` FOREIGN KEY (`course_director`) REFERENCES `lecturers_tbl` (`emp_no`);

--
-- Constraints for table `lecturer_timetable`
--
ALTER TABLE `lecturer_timetable`
  ADD CONSTRAINT `lecturer_timetable_ibfk_1` FOREIGN KEY (`lecturer_id`) REFERENCES `lecturers_tbl` (`emp_no`);

--
-- Constraints for table `modules_tbl`
--
ALTER TABLE `modules_tbl`
  ADD CONSTRAINT `modules_tbl_ibfk_1` FOREIGN KEY (`course_code`) REFERENCES `course_tbl` (`course_code`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
