-- MVP Kebutuhan Operasional
-- Jalankan manual setelah backup dan review. Codex tidak menjalankan file ini.

-- Pemeriksaan read-only sebelum perubahan:
-- SHOW COLUMNS FROM atk_items;
-- SHOW COLUMNS FROM atk_requests;

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

-- Pemeriksaan read-only setelah perubahan:
-- SELECT module, COUNT(*) FROM atk_items GROUP BY module;
-- SELECT module, COUNT(*) FROM atk_requests GROUP BY module;

-- Contoh pemberian Admin OPS awal. Ganti 123 dengan ID user yang dipilih,
-- lalu jalankan manual hanya bila diperlukan:
-- INSERT INTO user_access_roles (user_id, role, created_at, updated_at)
-- VALUES (123, 'ADMIN OPS', NOW(), NOW());
