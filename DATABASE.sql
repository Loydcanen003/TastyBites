
ALTER TABLE `users`
ADD `firstname` VARCHAR(50) NOT NULL AFTER `id`,
ADD `middlename` VARCHAR(50) NULL DEFAULT NULL AFTER `firstname`,
ADD `lastname` VARCHAR(50) NOT NULL AFTER `middlename`;


CREATE TABLE IF NOT EXISTS `recipes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recipe_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(255) NOT NULL COMMENT 'File path or URL to the image',
  `author` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `recipes`
ADD `ingredients` TEXT NOT NULL AFTER `description`,
ADD `instructions` TEXT NOT NULL AFTER `ingredients`,
ADD `uploaded_by` VARCHAR(50) NOT NULL AFTER `instructions`;


CREATE TABLE favorites (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,                           
  recipe_id INT NOT NULL,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (recipe_id) REFERENCES recipes(id) ON DELETE CASCADE
);

ALTER TABLE `recipes` ADD `recipe_type` VARCHAR(50) NOT NULL AFTER `description`;

ALTER TABLE `recipes` DROP COLUMN `is_favorite`;

CREATE TABLE `user_favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `recipe_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_recipe_unique` (`user_id`,`recipe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;