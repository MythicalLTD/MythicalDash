CREATE TABLE IF NOT EXISTS `mythicaldash_timed_tasks` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `task_name` varchar(191) NOT NULL,
    `last_run_at` timestamp NULL DEFAULT NULL,
    `last_run_success` tinyint(1) NOT NULL DEFAULT 0,
    `last_run_message` text DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique_task_name` (`task_name`),
    KEY `timed_tasks_last_run_at_index` (`last_run_at`),
    KEY `timed_tasks_last_run_success_index` (`last_run_success`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_general_ci;