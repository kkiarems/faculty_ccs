-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 12, 2025 at 01:33 AM
-- Server version: 11.4.5-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `faculty_css`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `admin_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`, `position`, `department`, `profile_photo`, `created_at`, `updated_at`) VALUES
(1, 'Nina Lourdes', 'lourdes@admin.ccs', '$2y$10$LPHm3XryK/850oFMdLYqeOBx6ch5i.z7ql2GsxOnArQhsYBEliZei', 'System Administrator', 'Administration', NULL, '2025-10-26 03:10:45', '2025-10-26 03:10:45');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `units` int(11) DEFAULT NULL,
  `semester` int(11) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_code`, `course_name`, `description`, `units`, `semester`, `year_level`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'CC133', 'ADVANCE DATABASE', 'HARD', 3, 1, 3, NULL, '2025-10-26 23:21:18', '2025-10-26 23:21:18'),
(2, 'os123', 'OPERATING SYSTEM', 'CUTE', 3, 1, 3, NULL, '2025-10-26 23:21:50', '2025-10-26 23:21:50');

-- --------------------------------------------------------

--
-- Table structure for table `course_assignments`
--

CREATE TABLE `course_assignments` (
  `assignment_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `assigned_date` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(150) NOT NULL,
  `department_code` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `department_code`, `created_at`) VALUES
(1, 'Computer Science', 'CS', '2025-12-11 23:55:28'),
(2, 'Information Technology', 'IT', '2025-12-11 23:55:28'),
(3, 'Associate in Computer Technology major in Application Development', 'ACT-AD', '2025-12-11 23:55:28'),
(4, 'Associate in Computer Technology major in Networking', 'ACT-NET', '2025-12-11 23:55:28');

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `version_number` int(11) DEFAULT 1,
  `parent_document_id` int(11) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `document_name` varchar(255) NOT NULL,
  `document_type` varchar(50) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `uploaded_date` timestamp NULL DEFAULT current_timestamp(),
  `approval_date` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `is_template` tinyint(1) DEFAULT 0,
  `is_latest` tinyint(1) DEFAULT 1,
  `file_size` varchar(50) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`document_id`, `version_number`, `parent_document_id`, `faculty_id`, `document_name`, `document_type`, `file_path`, `category`, `status`, `uploaded_date`, `approval_date`, `approved_by`, `is_template`, `is_latest`, `file_size`, `file_name`) VALUES
(1, 1, NULL, 1, 'reffff', 'word', NULL, 'teaching', 'rejected', '2025-10-26 23:47:48', '2025-10-27 03:32:04', 1, 0, 1, NULL, ''),
(2, 1, NULL, 1, 'beef', 'image', '../uploads/documents/1/doc_68ff474946330_1761560393.jpg', 'report', 'rejected', '2025-10-27 10:19:53', '2025-10-27 10:24:46', 1, 0, 1, '41825', '');

-- --------------------------------------------------------

--
-- Table structure for table `educational_information`
--

CREATE TABLE `educational_information` (
  `education_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `degree` varchar(255) DEFAULT NULL,
  `institution` varchar(255) DEFAULT NULL,
  `graduation_year` varchar(4) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `educational_information`
--

INSERT INTO `educational_information` (`education_id`, `faculty_id`, `degree`, `institution`, `graduation_year`, `created_at`, `updated_at`) VALUES
(1, 1, 'computer science', 'western mindanao state university', '2026', '2025-10-26 05:28:09', '2025-10-26 05:54:16');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `faculty_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `position` varchar(100) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `education` varchar(255) DEFAULT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `profile_photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`faculty_id`, `name`, `email`, `password`, `position`, `department`, `education`, `contact`, `profile_photo`, `created_at`, `updated_at`) VALUES
(1, 'nina brown', 'ninabrown@admin.ccs', '$2y$10$FUinQnMATievZCC0kRbIJODdD8KMZyVfFyPI8uYbsWqcGnEPre4UK', 'faculty', 'Computer Science', NULL, '0920192834', NULL, '2025-10-26 03:32:28', '2025-10-26 03:32:28'),
(2, 'Missy Kerv', 'missy@admin.com', '$2y$10$1mpJFvZvrJCEj0geJiaCDO/BE06dW6UpDcu8aRX2z68DCxdToPfy6', 'Assistant Professor', 'Computer Science', NULL, '0923773824', NULL, '2025-10-26 23:15:23', '2025-10-26 23:15:23');

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `leave_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','declined') DEFAULT 'pending',
  `admin_comments` text DEFAULT NULL,
  `requested_date` timestamp NULL DEFAULT current_timestamp(),
  `approval_date` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`leave_id`, `faculty_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`, `admin_comments`, `requested_date`, `approval_date`, `approved_by`) VALUES
(1, 1, 'emergency', '2025-10-29', '2025-10-30', 'ddseee', 'pending', NULL, '2025-10-26 23:47:27', NULL, NULL),
(2, 1, 'vacation', '2025-10-29', '2025-10-30', 'ewww', 'pending', NULL, '2025-10-26 23:48:04', NULL, NULL),
(3, 1, 'sick', '2025-11-12', '2025-11-14', 'fff', 'approved', '', '2025-11-10 23:18:03', '2025-11-10 23:32:48', 1);

-- --------------------------------------------------------

--
-- Table structure for table `leave_balance`
--

CREATE TABLE `leave_balance` (
  `balance_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `vacation_days` int(11) DEFAULT 15,
  `sick_days` int(11) DEFAULT 10,
  `emergency_days` int(11) DEFAULT 5,
  `year` int(11) DEFAULT year(curdate())
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `user_type` enum('admin','faculty') NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`notification_id`, `user_type`, `user_id`, `title`, `message`, `type`, `reference_id`, `is_read`, `created_at`) VALUES
(1, 'admin', 1, 'New Research Submission', 'nina brown submitted research: \"WYZ\"', 'research', 21, 0, '2025-12-12 00:00:32');

-- --------------------------------------------------------

--
-- Table structure for table `research`
--

CREATE TABLE `research` (
  `research_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `status` enum('pending','approved','declined','revision_requested') DEFAULT 'pending',
  `submission_date` timestamp NULL DEFAULT current_timestamp(),
  `approval_date` timestamp NULL DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `research`
--

INSERT INTO `research` (`research_id`, `faculty_id`, `title`, `description`, `category`, `status`, `submission_date`, `approval_date`, `approved_by`, `comments`) VALUES
(4, 1, 'dd', 'ddd', 'Journal', 'pending', '2025-10-26 06:32:42', NULL, NULL, NULL),
(9, 1, 'dd', 'dd', 'Journal', 'pending', '2025-10-26 06:49:13', NULL, NULL, NULL),
(10, 1, 'cc', 'ccc', 'Journal', 'pending', '2025-10-26 06:49:29', NULL, NULL, NULL),
(11, 1, 'dd', 'dddd', 'Journal', 'pending', '2025-10-26 06:52:18', NULL, NULL, NULL),
(12, 1, 'dd', 'dddd', 'Journal', 'pending', '2025-10-26 06:53:34', NULL, NULL, NULL),
(13, 1, 'ddd', 'dggg', 'Conference', 'approved', '2025-10-26 06:53:38', '2025-10-26 07:10:46', NULL, 'hmm'),
(14, 1, 'ddd', 'dggg', 'Conference', 'declined', '2025-10-26 06:58:18', '2025-10-26 23:22:22', NULL, 'DDD'),
(15, 1, 'ddddddd', 'fee', 'Journal', 'approved', '2025-10-26 06:58:24', '2025-10-26 23:22:13', NULL, 'K LANG'),
(16, 1, 'MISSY', '2AM', 'Conference', 'declined', '2025-10-26 07:10:02', '2025-10-26 07:10:51', NULL, 'dddd'),
(17, 1, 'grabe', 'bruh', 'Conference', 'pending', '2025-10-26 23:47:10', NULL, NULL, NULL),
(18, 1, 'something', 'oododo', 'Conference', 'pending', '2025-11-11 02:35:23', NULL, NULL, NULL),
(19, 1, 'something', 'oododo', 'Conference', 'pending', '2025-11-11 02:35:26', '2025-12-12 00:11:17', 1, ''),
(20, 1, 'XYZ', 'WYZ', 'Journal', 'pending', '2025-12-11 23:57:27', NULL, NULL, NULL),
(21, 1, 'WYZ', 'WY', 'Internet of Things', 'pending', '2025-12-12 00:00:32', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `timetables`
--

CREATE TABLE `timetables` (
  `timetable_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `day_of_week` varchar(20) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `room_number` varchar(50) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetables`
--

INSERT INTO `timetables` (`timetable_id`, `faculty_id`, `course_id`, `day_of_week`, `start_time`, `end_time`, `room_number`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'Tuesday', '09:25:00', '11:22:00', 'LR1', NULL, '2025-10-26 23:22:57', '2025-10-26 23:22:57'),
(2, 1, 2, 'Wednesday', '10:24:00', '13:00:00', 'LR1', NULL, '2025-10-26 23:23:25', '2025-10-26 23:23:25'),
(3, 1, 1, 'Thursday', '08:00:00', '11:00:00', 'LR1', NULL, '2025-10-26 23:23:59', '2025-10-26 23:23:59');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_admin_email` (`email`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`),
  ADD UNIQUE KEY `course_code` (`course_code`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `course_assignments`
--
ALTER TABLE `course_assignments`
  ADD PRIMARY KEY (`assignment_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD UNIQUE KEY `department_name` (`department_name`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_document_faculty` (`faculty_id`);

--
-- Indexes for table `educational_information`
--
ALTER TABLE `educational_information`
  ADD PRIMARY KEY (`education_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`faculty_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_faculty_email` (`email`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_leave_faculty` (`faculty_id`),
  ADD KEY `idx_leave_status` (`status`);

--
-- Indexes for table `leave_balance`
--
ALTER TABLE `leave_balance`
  ADD PRIMARY KEY (`balance_id`),
  ADD UNIQUE KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `idx_user_type_id` (`user_type`,`user_id`),
  ADD KEY `idx_is_read` (`is_read`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `research`
--
ALTER TABLE `research`
  ADD PRIMARY KEY (`research_id`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_research_faculty` (`faculty_id`),
  ADD KEY `idx_research_status` (`status`);

--
-- Indexes for table `timetables`
--
ALTER TABLE `timetables`
  ADD PRIMARY KEY (`timetable_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_timetable_faculty` (`faculty_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `course_assignments`
--
ALTER TABLE `course_assignments`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `educational_information`
--
ALTER TABLE `educational_information`
  MODIFY `education_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `faculty_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `leave_balance`
--
ALTER TABLE `leave_balance`
  MODIFY `balance_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `research`
--
ALTER TABLE `research`
  MODIFY `research_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `timetables`
--
ALTER TABLE `timetables`
  MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `courses`
--
ALTER TABLE `courses`
  ADD CONSTRAINT `courses_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `course_assignments`
--
ALTER TABLE `course_assignments`
  ADD CONSTRAINT `course_assignments_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `course_assignments_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `documents_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `educational_information`
--
ALTER TABLE `educational_information`
  ADD CONSTRAINT `educational_information_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`) ON DELETE CASCADE;

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leaves_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `leaves_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `leave_balance`
--
ALTER TABLE `leave_balance`
  ADD CONSTRAINT `leave_balance_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`);

--
-- Constraints for table `research`
--
ALTER TABLE `research`
  ADD CONSTRAINT `research_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `research_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admins` (`admin_id`);

--
-- Constraints for table `timetables`
--
ALTER TABLE `timetables`
  ADD CONSTRAINT `timetables_ibfk_1` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`faculty_id`),
  ADD CONSTRAINT `timetables_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `timetables_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `admins` (`admin_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
