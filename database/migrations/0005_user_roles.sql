CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id TINYINT UNSIGNED NOT NULL,
    granted_by BIGINT UNSIGNED NULL,
    granted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_ur_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_ur_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_ur_grantor FOREIGN KEY (granted_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
