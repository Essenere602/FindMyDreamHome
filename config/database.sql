-- Création de la base
CREATE DATABASE IF NOT EXISTS find_my_dream_home;
USE find_my_dream_home;

-- Table propertyType
CREATE TABLE propertyType (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table transactionType
CREATE TABLE transactionType (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table user
CREATE TABLE user (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table listing
CREATE TABLE listing (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    price INT NOT NULL,
    city VARCHAR(150) NOT NULL,
    image_url VARCHAR(255),
    property_type_id INT NOT NULL,
    transaction_type_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_type_id) REFERENCES propertyType(id),
    FOREIGN KEY (transaction_type_id) REFERENCES transactionType(id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);

-- Données de test
INSERT INTO propertyType (name) VALUES 
('House'), ('Apartment');

INSERT INTO transactionType (name) VALUES 
('Sale'), ('Rent');

INSERT INTO user (email, password) VALUES 
('test@example.com', 'test123'),
('admin@example.com', 'admin123');

INSERT INTO listing (title, description, price, city, image_url, property_type_id, transaction_type_id, user_id) VALUES
('Belle maison de campagne', 'Magnifique maison avec jardin et piscine', 250000, 'Lyon', 'https://images.unsplash.com/photo-1580587771525-78b9dba3b914', 1, 1, 1),
('Appartement lumineux', 'Appartement 3 pièces avec grande terrasse', 180000, 'Marseille', 'https://images.unsplash.com/photo-1493809842364-78817add7ffb', 2, 1, 2),
('Maison moderne', 'Maison neuve avec tout le confort moderne', 320000, 'Paris', 'https://images.unsplash.com/photo-1512917774080-9991f1c4c750', 1, 1, 1),
('Studio à louer', 'Studio meublé proche centre ville', 650, 'Lille', 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688', 2, 2, 2);

-- MAISONS
INSERT INTO listing (title, description, price, city, image_url, property_type_id, transaction_type_id, user_id)
VALUES
('Maison familiale avec jardin', 'Belle maison familiale dans quartier résidentiel calme', 680000, 'Neuilly-sur-Seine', 'https://images.unsplash.com/photo-1568605114967-8130f3a36994?w=400&h=300&fit=crop', 1, 1, 1),
('Villa moderne avec piscine', 'Villa contemporaine avec vue mer et piscine privée', 950000, 'Cannes', 'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=300&fit=crop', 1, 1, 1),
('Maison de ville rénovée', 'Charmante maison de ville entièrement rénovée', 520000, 'Bordeaux Centre', 'https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=400&h=300&fit=crop', 1, 1, 1);

-- APPARTEMENTS
INSERT INTO listing (title, description, price, city, image_url, property_type_id, transaction_type_id, user_id)
VALUES
('Appartement moderne 3 pièces', 'Appartement lumineux avec balcon et vue dégagée', 450000, 'Paris 15e', 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=400&h=300&fit=crop', 2, 1, 1),
('Penthouse avec terrasse', 'Magnifique penthouse avec grande terrasse panoramique', 780000, 'Lyon 6e', 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=400&h=300&fit=crop', 2, 1, 1),
('Studio lumineux centre-ville', 'Studio optimisé en plein cœur de la ville', 280000, 'Marseille 1er', 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?w=400&h=300&fit=crop', 2, 1, 1);
 