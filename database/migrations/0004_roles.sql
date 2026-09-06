CREATE TABLE roles (
    id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    slug VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(80) NOT NULL,
    scope_level ENUM('self','farm','outlet','facility','district','region','national') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
