CREATE DATABASE IF NOT EXISTS api_rest_laravel;
USE api_rest_laravel;

CREATE TABLE users (
    id          INT auto_increment NOT NULL,
    name        VARCHAR(50) NOT NULL,
    surname     VARCHAR(50) NOT NULL,
    email       VARCHAR(100) NOT NULL,
    rol         VARCHAR(50),
    password    VARCHAR(100) NOT NULL,
    image       VARCHAR(150),
    description TEXT,
    created_at  datetime DEFAULT NULL,
    updated_at  datetime DEFAULT NULL,
    remember_token VARCHAR(150),
    CONSTRAINT pk_users PRIMARY KEY(id)
)ENGINE=InnoDb;

CREATE TABLE categories(
    id          INT auto_increment NOT NULL,
    name        VARCHAR(50) NOT NULL,
    created_at  datetime DEFAULT NULL,
    updated_at  datetime DEFAULT NULL,
    CONSTRAINT pk_categories PRIMARY KEY(id) 
)ENGINE=InnoDb;

CREATE TABLE posts(
    id          INT auto_increment NOT NULL,
    user_id     INT(255) NOT NULL,
    category_id INT(255) NOT NULL,
    title       VARCHAR(100) NOT NULL,
    content     TEXT NOT NULL,
    image       VARCHAR(255),
    created_at  datetime DEFAULT NULL,
    updated_at  datetime DEFAULT NULL,
    CONSTRAINT pk_posts PRIMARY KEY(id),
    CONSTRAINT fk_posts_users FOREIGN KEY(user_id) REFERENCES users(id),
    CONSTRAINT fk_posts_categories FOREIGN KEY(category_id) REFERENCES categories(id)
)ENGINE=InnoDb;

