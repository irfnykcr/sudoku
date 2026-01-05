CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `best_score` int UNSIGNED DEFAULT 0,
  `total_score` bigint UNSIGNED DEFAULT 0,
  `stats` JSON DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `games` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user` varchar(255) NOT NULL,
    `type` ENUM('normal', 'daily') NOT NULL DEFAULT 'normal',
    `daily_date` DATE DEFAULT NULL,
    `difficulty` ENUM('Easy', 'Medium', 'Hard', 'Extreme', 'Test') NOT NULL DEFAULT 'Easy',
    `puzzle` JSON NOT NULL,
    `solution` JSON NOT NULL,
    `current_state` JSON NOT NULL,
    `notes` JSON DEFAULT NULL,
    `elapsed_seconds` INT UNSIGNED DEFAULT 0,
    `score` INT UNSIGNED DEFAULT 0,
    `is_completed` BOOLEAN DEFAULT FALSE,
    `is_replay` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user`) REFERENCES `users`(`user`) ON DELETE CASCADE,
    INDEX `idx_user_type` (`user`, `type`),
    INDEX `idx_user_daily` (`user`, `type`, `daily_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `achievements` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(50) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `icon` VARCHAR(50) DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_achievements` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `achievement_id` INT UNSIGNED NOT NULL,
    `unlocked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`achievement_id`) REFERENCES `achievements`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `idx_user_achievement` (`user_id`, `achievement_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `achievements` (`slug`, `name`, `description`, `icon`) VALUES
('first_win', 'First Victory', 'Complete your first Sudoku puzzle.', 'trophy'),
('speed_demon', 'Speed Demon', 'Complete a puzzle in under 5 minutes.', 'bolt'),
('extreme_solver', 'Extreme Solver', 'Complete a puzzle on Extreme difficulty.', 'brain');
