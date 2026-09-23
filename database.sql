-- Base de données utilisée par connexion.php.
CREATE DATABASE IF NOT EXISTS mini_projet
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE mini_projet;

CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifiant VARCHAR(100) NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL
);

-- Informations associées aux badges et aux noms.
CREATE TABLE IF NOT EXISTS connexionn (
    id INT AUTO_INCREMENT PRIMARY KEY,
    badge VARCHAR(100) NOT NULL UNIQUE,
    nom VARCHAR(150) NOT NULL
);

-- Insérez un mot de passe généré avec password_hash() en PHP.
