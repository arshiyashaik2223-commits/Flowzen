-- ==============================================================================
-- FLOWZEN - Premium Intelligent Task Management Web Application
-- RTIH IT Internship Technical Assessment
-- Database Architecture Specification
-- Database Name: flowzen_db
-- ==============================================================================

-- Create database with modern utf8mb4 encoding
CREATE DATABASE IF NOT EXISTS `flowzen_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `flowzen_db`;

-- ------------------------------------------------------------------------------
-- 1. USERS TABLE
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 2. TASKS TABLE
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tasks` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `status` ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    `priority` ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    `due_date` DATE NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `archived_at` TIMESTAMP NULL DEFAULT NULL,
    CONSTRAINT `fk_tasks_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX `idx_tasks_user_status` (`user_id`, `status`),
    INDEX `idx_tasks_user_due` (`user_id`, `due_date`),
    INDEX `idx_tasks_user_priority` (`user_id`, `priority`),
    INDEX `idx_tasks_user_archived` (`user_id`, `archived_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 3. ACTIVITY LOGS TABLE (AUDIT TIMELINE)
-- ------------------------------------------------------------------------------
-- Foreign key design note:
-- task_id has ON DELETE SET NULL so deleting a task preserves audit history
-- without triggering foreign key violation errors.
CREATE TABLE IF NOT EXISTS `activity_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `task_id` INT UNSIGNED NULL DEFAULT NULL,
    `action` VARCHAR(50) NOT NULL,
    `description` TEXT NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_activity_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_activity_task` FOREIGN KEY (`task_id`)
        REFERENCES `tasks` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    INDEX `idx_activity_user_created` (`user_id`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 4. NOTIFICATIONS TABLE
-- ------------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `task_id` INT UNSIGNED NULL DEFAULT NULL,
    `title` VARCHAR(150) NOT NULL,
    `message` TEXT NOT NULL,
    `type` ENUM('info', 'warning', 'danger', 'success') NOT NULL DEFAULT 'info',
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`)
        REFERENCES `users` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT `fk_notifications_task` FOREIGN KEY (`task_id`)
        REFERENCES `tasks` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    INDEX `idx_notifications_user_read` (`user_id`, `is_read`, `created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------------------------
-- 5. SEED DATA FOR DEMO & ASSESSMENT EVALUATION
-- ------------------------------------------------------------------------------
-- Demo user: demo@flowzen.com / Password123!
INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Demo Evaluator', 'demo@flowzen.com', '$2y$10$jYEGMnw1iBKRV1JgjpGly.nWUh31Y4KWmpv/8M8Mf7896oC17GC8C', NOW())
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Sample tasks across all 3 statuses and priorities with realistic deadlines
INSERT INTO `tasks` (`id`, `user_id`, `title`, `description`, `status`, `priority`, `due_date`, `created_at`) VALUES
(1, 1, 'Review RTIH Technical Assessment Guidelines', 'Examine all mandatory and bonus assessment criteria for the IT internship evaluation.', 'completed', 'high', DATE_SUB(CURRENT_DATE, INTERVAL 2 DAY), NOW()),
(2, 1, 'Design Nocturne Citron Architecture', 'Establish the core tokens, modular MVC PHP structure, and database schema.', 'completed', 'high', DATE_SUB(CURRENT_DATE, INTERVAL 1 DAY), NOW()),
(3, 1, 'Implement Task CRUD & Search Filters', 'Build create, read, update, delete functionality with server-side validation and search.', 'in_progress', 'high', CURRENT_DATE, NOW()),
(4, 1, 'Configure Kanban Interactive Board', 'Implement drag-and-drop workflow updates with asynchronous backend persistence.', 'in_progress', 'medium', DATE_ADD(CURRENT_DATE, INTERVAL 2 DAY), NOW()),
(5, 1, 'Integrate Chart.js Analytics Dashboard', 'Render real-time doughnut distribution and weekly productivity velocity charts.', 'pending', 'medium', DATE_ADD(CURRENT_DATE, INTERVAL 4 DAY), NOW()),
(6, 1, 'Conduct Comprehensive Security Audit', 'Verify CSRF tokens, PDO prepared statements, XSS escaping, and user isolation boundaries.', 'pending', 'high', DATE_ADD(CURRENT_DATE, INTERVAL 5 DAY), NOW()),
(7, 1, 'Prepare Final RTIH Submission Package', 'Generate documentation, SQL export, testing report, and package directory structure.', 'pending', 'low', DATE_ADD(CURRENT_DATE, INTERVAL 7 DAY), NOW())
ON DUPLICATE KEY UPDATE `id` = `id`;

-- Sample audit trail entries
INSERT INTO `activity_logs` (`user_id`, `task_id`, `action`, `description`, `created_at`) VALUES
(1, 1, 'created', 'Created task: Review RTIH Technical Assessment Guidelines', DATE_SUB(NOW(), INTERVAL 48 HOUR)),
(1, 1, 'status_changed', 'Changed status to Completed for: Review RTIH Technical Assessment Guidelines', DATE_SUB(NOW(), INTERVAL 24 HOUR)),
(1, 2, 'created', 'Created task: Design Nocturne Citron Architecture', DATE_SUB(NOW(), INTERVAL 36 HOUR)),
(1, 2, 'status_changed', 'Changed status to Completed for: Design Nocturne Citron Architecture', DATE_SUB(NOW(), INTERVAL 12 HOUR)),
(1, 3, 'created', 'Created task: Implement Task CRUD & Search Filters', DATE_SUB(NOW(), INTERVAL 6 HOUR))
ON DUPLICATE KEY UPDATE `id` = `id`;
