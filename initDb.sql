DROP DATABASE IF EXISTS accounts_images_db;

CREATE DATABASE accounts_images_db;

USE accounts_images_db;


CREATE TABLE users (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `username` VARCHAR(30) UNIQUE NOT NULL, 
    `password` VARCHAR(255) NOT NULL,
    `email` VARCHAR(50) UNIQUE NOT NULL,
    `path_profile_picture` VARCHAR(50) NULL
);

CREATE TABLE roles (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(30) NOT NULL,
    `description` TEXT NULL
);

CREATE TABLE images (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `name` VARCHAR(30) NULL,
    `path` VARCHAR(100) NOT NULL,
    `description` TEXT NULL
);

CREATE TABLE users_has_roles (
    users_id  INT,
    roles_id INT,
    PRIMARY KEY (users_id, roles_id)
);

ALTER TABLE users_has_roles
ADD CONSTRAINT FK_UHR_USERS
FOREIGN KEY (users_id)
REFERENCES users(id);

ALTER TABLE users_has_roles
ADD CONSTRAINT FK_UHR_ROLES
FOREIGN KEY (roles_id)
REFERENCES roles(id);

-------------------------------------

INSERT INTO users (`username`, `password`, `email`)
VALUES            ("youseur", "$2y$12$ivzM28EAPNgzSpDRXh1mPO8YQHv.X5/ngq1meqtljIyGnWp0jsLNK", "zoromihawk59@gmail.com"); -- Hash d'un mot de passe correspondant à : souper

INSERT INTO roles (`name`, `description`)
VALUES            ("user", "utilisateur normal"),
                  ("admin", "administrateur qui peut modérer les comptes et les listes");

INSERT INTO users_has_roles(`users_id`, `roles_id`)
VALUES                     (1, 1);