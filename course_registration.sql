-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Oct 31, 2024 at 12:08 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `course_registration`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `adminID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `manageCourses` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`adminID`, `userID`, `manageCourses`) VALUES
(3, 5, 1);

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `courseID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `enrolled` int(11) DEFAULT 0,
  `enrolledStudent` text DEFAULT NULL,
  `waitlistCount` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`courseID`, `name`, `capacity`, `enrolled`, `enrolledStudent`, `waitlistCount`) VALUES
(1, 'PHP Programming - Summer', 3, 3, '', 0),
(2, 'Java Programming - Fall', 3, 1, '', 0),
(3, 'Python Programming - Spring', 3, 1, '', 0),
(4, 'JavaScript Programming - Spring', 3, 0, '', 0),
(5, 'C# Programming - Fall', 3, 1, '', 0),
(6, 'Ruby Programming - Summer', 4, 1, '', 0),
(7, 'C++ Programming - Fall', 4, 3, '', 0),
(8, 'Swift Programming - Summer', 3, 3, '', 0),
(9, 'HTML & CSS - Summer', 3, 1, '', 0),
(10, 'SQL & Databases - Spring', 4, 1, '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `registrations`
--

CREATE TABLE `registrations` (
  `registrationID` int(11) NOT NULL,
  `studentID` int(11) DEFAULT NULL,
  `courseID` int(11) DEFAULT NULL,
  `status` enum('Enrolled','Waitlisted') DEFAULT 'Enrolled',
  `enrollmentStatus` enum('Active','Cancelled') DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `registrations`
--

INSERT INTO `registrations` (`registrationID`, `studentID`, `courseID`, `status`, `enrollmentStatus`) VALUES
(6, 1, 8, 'Enrolled', 'Active'),
(7, 1, 1, 'Enrolled', 'Active'),
(9, 5, 5, 'Enrolled', 'Active'),
(10, 5, 8, 'Enrolled', 'Active'),
(14, 4, 7, 'Enrolled', 'Active'),
(15, 7, 7, 'Enrolled', 'Active'),
(16, 7, 9, 'Enrolled', 'Active'),
(17, 7, 3, 'Enrolled', 'Active'),
(18, 4, 8, 'Enrolled', 'Active'),
(19, 4, 1, 'Enrolled', 'Active'),
(20, 5, 1, 'Enrolled', 'Active'),
(21, 5, 7, 'Enrolled', 'Active'),
(22, 2, 1, 'Enrolled', 'Active'),
(23, 2, 10, 'Enrolled', 'Active'),
(24, 3, 2, 'Enrolled', 'Active'),
(25, 3, 6, 'Enrolled', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `studentID` int(11) NOT NULL,
  `userID` int(11) DEFAULT NULL,
  `viewCourses` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`studentID`, `userID`, `viewCourses`) VALUES
(1, 1, 1),
(2, 4, 1),
(3, 6, 1),
(4, 7, 1),
(5, 8, 1),
(6, 9, 1),
(7, 10, 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `userID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`userID`, `name`, `phone`, `email`, `password`) VALUES
(1, 'John Doe', '321-123-4567', 'johndoe@mail.com', '$2y$10$XXCCLB2eOiwSHmKWoeoLOuCIGMq1d8wQcXPwEY4vTzQxQoan8nzl2'),
(4, 'Mary Poppins', '567-123-0101', 'mary@mail.com', '$2y$10$Vb69Xkr4minMbnFpK./8Oew8XZ4nvVh5bG3TLGo5UrU/SPQxLzsE.'),
(5, 'David Bins', '345-123-1234', 'david@mail.com', '$2y$10$K5r5oN4ubASCebgmCUmSbOpSYIdRihCh5e6Ph1QbBzP.T/MwQrruW'),
(6, 'Jane Johnson', '456-876-8765', 'jane@mail.com', '$2y$10$ExJqgNgTLoAtEpwSWt3NyOud1fOaxFujChPBP23iDhPyKns9CS3DW'),
(7, 'Tom Jerry', '567=123-4567', 'tom@mail.com', '$2y$10$M.D2IKIl6lgTZI5al0ezp.paKZKDiU2YC.fsSTqus72ciS3CfSFI2'),
(8, 'Kevin Fowler', '453-234-0987', 'kevin@mail.com', '$2y$10$z04qnXUy0ssLngP/PBwNxOwnxafLD2yNqVkGt6QzN0ve0vhgxu462'),
(9, 'Jennifer Dunn', '234-123-4567', 'jennifer@mail.com', '$2y$10$aDPUK6BpHkz1HcN9buJKq.rpEBjR2q7DI4ouKbKPtOeU3QBe1ajLO'),
(10, 'Denver Doran', '321=123=4567', 'denver@mail.com', '$2y$10$SK9LahAHZatu1pcZRCiMXuaOaHdzJVYg7YoWc3tJZfzcvK1291QGq');

-- --------------------------------------------------------

--
-- Table structure for table `waitlist`
--

CREATE TABLE `waitlist` (
  `waitlistID` int(11) NOT NULL,
  `courseID` int(11) DEFAULT NULL,
  `studentID` int(11) DEFAULT NULL,
  `dateAdded` timestamp NOT NULL DEFAULT current_timestamp(),
  `position` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `waitlist`
--

INSERT INTO `waitlist` (`waitlistID`, `courseID`, `studentID`, `dateAdded`, `position`) VALUES
(2, 8, 5, '2024-10-28 01:38:09', 1),
(3, 8, 2, '2024-10-28 01:40:24', 2),
(4, 1, 3, '2024-10-28 01:42:50', 1),
(5, 8, 3, '2024-10-28 01:43:04', 3),
(6, 8, 3, '2024-10-28 01:43:29', 4),
(7, 1, 7, '2024-10-28 02:38:56', 2),
(8, 1, 5, '2024-10-29 01:19:52', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`adminID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`courseID`);

--
-- Indexes for table `registrations`
--
ALTER TABLE `registrations`
  ADD PRIMARY KEY (`registrationID`),
  ADD KEY `studentID` (`studentID`),
  ADD KEY `courseID` (`courseID`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`studentID`),
  ADD KEY `userID` (`userID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`userID`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD PRIMARY KEY (`waitlistID`),
  ADD KEY `courseID` (`courseID`),
  ADD KEY `studentID` (`studentID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `adminID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `courseID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `registrations`
--
ALTER TABLE `registrations`
  MODIFY `registrationID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `studentID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `userID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `waitlist`
--
ALTER TABLE `waitlist`
  MODIFY `waitlistID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `registrations`
--
ALTER TABLE `registrations`
  ADD CONSTRAINT `registrations_ibfk_1` FOREIGN KEY (`studentID`) REFERENCES `students` (`studentID`),
  ADD CONSTRAINT `registrations_ibfk_2` FOREIGN KEY (`courseID`) REFERENCES `courses` (`courseID`);

--
-- Constraints for table `students`
--
ALTER TABLE `students`
  ADD CONSTRAINT `students_ibfk_1` FOREIGN KEY (`userID`) REFERENCES `users` (`userID`);

--
-- Constraints for table `waitlist`
--
ALTER TABLE `waitlist`
  ADD CONSTRAINT `waitlist_ibfk_1` FOREIGN KEY (`courseID`) REFERENCES `courses` (`courseID`),
  ADD CONSTRAINT `waitlist_ibfk_2` FOREIGN KEY (`studentID`) REFERENCES `students` (`studentID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
