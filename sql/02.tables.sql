USE `photo360_development`;
SET NAMES 'utf8';

--
-- Table structure for table `users`
--
DROP TABLE IF EXISTS `users` ;
CREATE TABLE `users` (
	`id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
	`username` VARCHAR(32) NOT NULL ,
	`email` VARCHAR(32) NOT NULL,
	`encrypted_password` CHAR(40) NOT NULL ,
	`password_salt` CHAR(32) NOT NULL ,
        `created_at` INT( 13 ) UNSIGNED NOT NULL DEFAULT 0 ,
        `updated_at` INT( 13 ) UNSIGNED NOT NULL DEFAULT 0 ,
        UNIQUE INDEX `index_users_on_username` ( `username` )
) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='用户基本信息表';

--
-- Table structure for table `uploads`
--
DROP TABLE IF EXISTS `uploads` ;
CREATE TABLE `uploads` (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
        `user_id` INT NOT NULL DEFAULT 0 COMMENT '关联的用户ID' ,
        `file_key` VARCHAR( 255 ) NOT NULL DEFAULT '' COMMENT '文件在QBox Resource Server上的Key' ,
        `file_name` VARCHAR( 255 ) NOT NULL DEFAULT '' COMMENT '文件名' ,
        `file_size` INT NOT NULL DEFAULT 0 COMMENT '文件大小' ,
        `file_type` VARCHAR( 125 ) NOT NULL DEFAULT '' COMMENT '文件的MIME类型' ,
        `created_at` INT( 13 ) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上传时间' ,
        INDEX `index_uploads_on_user_id` ( `user_id` )
) ENGINE = MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci COMMENT='上传的文件信息表';
