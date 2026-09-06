CREATE TABLE districts (
    id SMALLINT UNSIGNED PRIMARY KEY,
    region_id TINYINT UNSIGNED NOT NULL,
    code VARCHAR(4) NOT NULL,
    name VARCHAR(120) NOT NULL,
    capital VARCHAR(120) NULL,
    UNIQUE KEY uq_district_code (region_id, code),
    UNIQUE KEY uq_district_region (id, region_id),
    CONSTRAINT fk_district_region FOREIGN KEY (region_id) REFERENCES regions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
