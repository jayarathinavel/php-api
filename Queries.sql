CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `app_id` varchar(100) NOT NULL DEFAULT '',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `role` varchar(50) NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_app` (`email`,`app_id`)
);

CREATE TABLE `crud_resources` (
  `id` int NOT NULL AUTO_INCREMENT,
  `app_id` varchar(100) NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `primary_key` varchar(100) NOT NULL DEFAULT 'id',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `app_feature` (`app_id`,`feature_name`)
);

-- Create tasks table for Work Tracker's task manager
CREATE TABLE IF NOT EXISTS `workTracker_tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext,
  `status` enum('pending', 'in-progress', 'completed', 'cancelled') DEFAULT 'pending',
  `reference` varchar(255),
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

-- Create worklogs table for Work Tracker's work log tracking
CREATE TABLE IF NOT EXISTS `workTracker_worklogs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `date` date NOT NULL,
  `done` longtext,
  `todo` longtext,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);

ALTER TABLE worktracker_worklogs ADD CONSTRAINT worktracker_worklogs_pk PRIMARY KEY (id);
ALTER TABLE worktracker_tasks ADD CONSTRAINT worktracker_tasks_pk PRIMARY KEY (id);
ALTER TABLE users ADD CONSTRAINT users_pk PRIMARY KEY (id);

CREATE TABLE IF NOT EXISTS `workTracker_task_comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `comment_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_task_id` (`task_id`),
  KEY `idx_user_id` (`user_id`),
  CONSTRAINT `fk_comment_task` FOREIGN KEY (`task_id`) REFERENCES `workTracker_tasks` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_comment_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
)

