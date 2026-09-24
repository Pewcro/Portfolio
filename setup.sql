CREATE DATABASE IF NOT EXISTS portfolio_db;
USE portfolio_db;

CREATE TABLE IF NOT EXISTS profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255),
    short_name VARCHAR(100),
    roles TEXT,
    hero_desc TEXT,
    about_text TEXT,
    email VARCHAR(255),
    profile_image VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS social_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    platform VARCHAR(50),
    url VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    skill_name VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255),
    description TEXT,
    image VARCHAR(255),
    tags VARCHAR(255),
    github_link VARCHAR(255),
    live_link VARCHAR(255)
);

TRUNCATE TABLE profile;
TRUNCATE TABLE social_links;
TRUNCATE TABLE skills;
TRUNCATE TABLE projects;

INSERT INTO profile (full_name, short_name, roles, hero_desc, about_text, email, profile_image) 
VALUES (
    'Jonathan Parulian Tobing', 
    'Jonathan', 
    'Software Engineer.,Informatics Student.,Tech Enthusiast.,Creative Developer.', 
    'I''m a Software Engineer and Informatics Engineering student (Class of 2023). I enjoy building interactive applications, games, and beautiful digital experiences.', 
    'Hello! My name is Jonathan and I enjoy creating engaging digital experiences. My journey into tech formally started in 2023 when I enrolled as a college student majoring in Informatics Engineering.|Since then, I''ve been diving deep into software engineering, learning how to build everything from 2D games to video streaming applications. I love bridging the gap between clean code and visual design.', 
    'your.email@example.com', 
    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&auto=format&fit=crop&w=774&q=80'
);

INSERT INTO social_links (platform, url) VALUES 
('github', 'https://github.com'),
('linkedin', 'https://linkedin.com'),
('instagram', 'https://instagram.com/jonathannnpt');

INSERT INTO skills (skill_name) VALUES 
('Python'), ('HTML'), ('CSS'), ('Canva');

INSERT INTO projects (title, description, image, tags, github_link, live_link) VALUES 
('Tank Game 2D', 'An interactive 2D tank battle game built from scratch. Features engaging gameplay mechanics, custom graphics, and smooth controls.', 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'Python,Game Development', '#', ''),
('RJB Flix', 'A video streaming application tailored for watching anime and other shows. Features a user-friendly interface and custom UI elements.', 'https://images.unsplash.com/photo-1574375927938-d5a98e8ffe85?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80', 'HTML,CSS,Canva', '#', '#');
