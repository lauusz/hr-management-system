-- Update database untuk modul Kebutuhan Operasional.
-- Jalankan satu kali setelah backup database.

ALTER TABLE atk_items
    ADD COLUMN module VARCHAR(10) NOT NULL DEFAULT 'ATK' AFTER id,
    ADD COLUMN deleted_at TIMESTAMP NULL AFTER created_by,
    ADD COLUMN deleted_by BIGINT UNSIGNED NULL AFTER deleted_at,
    ADD COLUMN deletion_note TEXT NULL AFTER deleted_by,
    ADD INDEX atk_items_module_active_deleted_index (module, is_active, deleted_at),
    ADD CONSTRAINT atk_items_deleted_by_foreign
        FOREIGN KEY (deleted_by) REFERENCES users(id) ON DELETE SET NULL;

ALTER TABLE atk_requests
    ADD COLUMN module VARCHAR(10) NOT NULL DEFAULT 'ATK' AFTER id,
    ADD INDEX atk_requests_module_status_created_index (module, status, created_at);

CREATE TABLE ops_access_divisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    division_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY ops_access_divisions_division_unique (division_id),
    KEY ops_access_divisions_created_by_foreign (created_by),
    CONSTRAINT ops_access_divisions_division_foreign
        FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE CASCADE,
    CONSTRAINT ops_access_divisions_created_by_foreign
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
