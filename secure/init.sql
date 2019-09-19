-- TODO: Put ALL SQL in between `BEGIN TRANSACTION` and `COMMIT`
BEGIN TRANSACTION;

-- User Table
CREATE TABLE `users` (
	`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`username` TEXT NOT NULL UNIQUE,
    `password` TEXT NOT NULL
);

--User table seed data
INSERT INTO `users` (id, username, password) VALUES (1, 'ferrariguy101','$2y$10$/gRq5M8ix8cCSPSCwGRCEuHiNXI9Ur7gkcmKBocqx3hthYh6GNv.q'); -- password: party1
INSERT INTO `users` (id, username, password) VALUES (2, 'mechanic27', '$2y$10$Rl/3NjB9Spu2ZiKAsMCdcej5x3gUfbZXHPxIlkzlMx37GvvyYi8Em'); -- password: noparty2



-- Images Table
CREATE TABLE `images` (
	`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`file_ext` TEXT NOT NULL,
    `file_name` TEXT NOT NULL,
    `user_id` INTEGER NOT NULL
);

-- All images original, created by Kimberly Baum

-- Images table seed data
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (1, 'png', 'autoshop.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (2, 'png', 'autoshop-thursday.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (3, 'png', 'wednesday-autoshop.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (4, 'png', 'car-interior.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (5, 'png', 'engine.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (6, 'png', 'workday.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (7, 'png', 'highschool-autoshop.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (8, 'png', 'pipes.png', 2);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (9, 'png', 'inspection.png', 2);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (10, 'png', 'full-car-shot.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (11, 'png', 'propeller.png', 1);
INSERT INTO `images` (id, file_ext, file_name, user_id) VALUES (12, 'png', 'ek-selfie.png', 1);

-- All images are original created by Kimberly Baum

-- Image Tags Table
CREATE TABLE `image_tags` (
	`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`image_id` INTEGER NOT NULL,
    `tag_id` INTEGER NOT NULL
);

INSERT INTO `image_tags` (image_id, tag_id) VALUES (12, 1);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (12, 2);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (9, 3);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (9, 11);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (9, 6);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (5, 3);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (11, 3);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (1, 7);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (2, 4);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (3, 7);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (3, 6);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (5, 3);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (5, 4);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (5, 5);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (7, 8);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (7, 6);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (7, 5);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (7, 4);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (6, 6);
INSERT INTO `image_tags` (image_id, tag_id) VALUES (6, 8);


-- Tags Table
CREATE TABLE `tags` (
	`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
    `tag` TEXT NOT NULL UNIQUE
);

INSERT INTO `tags` (tag) VALUES ('Kim');
INSERT INTO `tags` (tag) VALUES ('Elodie');
INSERT INTO `tags` (tag) VALUES ('Jake');
INSERT INTO `tags` (tag) VALUES ('Christian');
INSERT INTO `tags` (tag) VALUES ('Ellis');
INSERT INTO `tags` (tag) VALUES ('Bjorn');
INSERT INTO `tags` (tag) VALUES ('Tristan');
INSERT INTO `tags` (tag) VALUES ('Domingo');

-- All images original, created by Kimberly Baum

-- Sessions Table
CREATE TABLE `sessions` (
	`id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,
	`user_id` INTEGER NOT NULL,
    `session` TEXT NOT NULL UNIQUE
);


COMMIT;
