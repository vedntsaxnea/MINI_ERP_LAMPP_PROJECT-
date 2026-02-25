-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 23, 2026 at 04:54 PM
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
-- Database: `mini_erp`
--

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(55) NOT NULL,
  `last_name` varchar(55) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `user_id`, `first_name`, `last_name`, `phone`, `position`, `status`) VALUES
(2, 3, 'BOBA', 'MAN', '+918758836890', 'Developer', 'active'),
(3, 4, 'UI', 'Saxena', '+918758787878', 'Developer', 'inactive'),
(4, 5, 'ginger', 'grape', '+918795368425', 'manager', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `status` enum('pending','active','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `name`, `description`, `start_date`, `status`, `created_at`) VALUES
(1, 'awdawda', 'wa d dafgaw  awfasfwa', '2026-02-20', 'pending', '2026-02-16 20:54:14'),
(2, 'Vedant Saxena', 'a dsa dwad ad awafeasd', '2026-02-21', 'pending', '2026-02-16 20:54:35');

-- --------------------------------------------------------

--
-- Table structure for table `role_access`
--

CREATE TABLE `role_access` (
  `id` int(11) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `permission_code` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_access`
--

INSERT INTO `role_access` (`id`, `role`, `permission_code`, `description`, `created_at`) VALUES
(1, 'admin', 'employee_create', 'Create new employees', '2026-02-23 14:37:07'),
(2, 'admin', 'employee_delete', 'Delete an employee', '2026-02-23 14:37:07'),
(3, 'admin', 'employee_edit', 'Edit employee details', '2026-02-23 14:37:07'),
(4, 'admin', 'employee_view', 'View list of all employees', '2026-02-23 14:37:07'),
(5, 'admin', 'project_create', 'Create new projects', '2026-02-23 14:37:07'),
(6, 'admin', 'project_delete', 'Delete a project', '2026-02-23 14:37:07'),
(7, 'admin', 'project_edit', 'Edit project details', '2026-02-23 14:37:07'),
(8, 'admin', 'project_view', 'View all projects', '2026-02-23 14:37:07'),
(9, 'admin', 'task_create', 'Create and assign tasks', '2026-02-23 14:37:07'),
(10, 'admin', 'task_view_all', 'View all tasks in the system', '2026-02-23 14:37:07'),
(11, 'employee', 'task_update_status', 'Update status of assigned tasks', '2026-02-23 14:37:07'),
(12, 'employee', 'task_view_assigned', 'View only tasks assigned to self', '2026-02-23 14:37:07');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext DEFAULT NULL,
  `priority` enum('low','medium','high') NOT NULL DEFAULT 'medium',
  `due_date` date DEFAULT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `assigned_to`, `name`, `description`, `priority`, `due_date`, `status`, `created_at`) VALUES
(1, 1, 2, 'bob task', 'yoyoyoyoyoyoyoyoyo', 'medium', '2026-02-28', 'completed', '2026-02-16 21:12:41');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `Role` enum('admin','employee') NOT NULL DEFAULT 'employee',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = active, 0 = inactive/blocked',
  `Created_at` timestamp NOT NULL DEFAULT curtime()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `Role`, `is_active`, `Created_at`) VALUES
(1, 'admin@test.com', '$2y$10$iu9/d5ixLrN09IFsAJACv.Wmzs2erblifYVZ1VkZLlt7XrxChWGky', 'admin', 1, '2026-02-11 22:54:28'),
(3, 'bobaman@gmail.com', '$2y$10$gN..Zj32L3.9NXSIovdh8OJjz0besWPQKceFgL1BqqYAbQUmiJr72', 'employee', 1, '2026-02-15 20:16:34'),
(4, 'boba23man@gmail.com', '$2y$10$T0BB8NJtE0w2DdypUx37ye9QDEdFZ0S5trbN1ssQsFU4kG8PMQH4K', 'employee', 0, '2026-02-16 18:06:56'),
(5, 'ginger@grape.com', '$2y$10$egoWkB0bNAXQiaMWj9XShOZIkDoCODwY8MG1vT3I/a4nlqn6gYTkS', 'employee', 1, '2026-02-23 14:51:38');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_access`
--
ALTER TABLE `role_access`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_perm` (`role`,`permission_code`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `role_access`
--
ALTER TABLE `role_access`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
