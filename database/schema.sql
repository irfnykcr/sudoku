CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user` (`user`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `games` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user` varchar(255) NOT NULL,
    `type` ENUM('normal', 'daily') NOT NULL DEFAULT 'normal',
    `daily_date` DATE DEFAULT NULL,
    `difficulty` ENUM('Easy', 'Medium', 'Hard', 'Extreme') NOT NULL DEFAULT 'Easy',
    `puzzle` JSON NOT NULL,
    `solution` JSON NOT NULL,
    `current_state` JSON NOT NULL,
    `notes` JSON DEFAULT NULL,
    `elapsed_seconds` INT UNSIGNED DEFAULT 0,
    `is_completed` BOOLEAN DEFAULT FALSE,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (`user`) REFERENCES `users`(`user`) ON DELETE CASCADE,
    INDEX `idx_user_type` (`user`, `type`),
    INDEX `idx_user_daily` (`user`, `type`, `daily_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
