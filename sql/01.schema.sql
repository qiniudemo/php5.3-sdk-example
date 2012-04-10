CREATE DATABASE `photo360_development` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;

CREATE USER 'photo360_devuser'@'localhost' IDENTIFIED BY  'photo360_development_password';

GRANT USAGE ON * . * TO  'photo360_devuser'@'localhost' IDENTIFIED BY  'photo360_development_password' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;

GRANT ALL PRIVILEGES ON  `photo360_development` . * TO  'photo360_devuser'@'localhost';