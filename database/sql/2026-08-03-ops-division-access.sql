-- Akses pengguna OPS berdasarkan divisi.
-- Jalankan manual setelah backup dan review. Codex tidak menjalankan file ini.

CREATE TABLE ops_access_divisions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    division_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY ops_access_divisions_division_unique (division_id),
    CONSTRAINT ops_access_divisions_division_foreign
        FOREIGN KEY (division_id) REFERENCES divisions(id) ON DELETE CASCADE,
    CONSTRAINT ops_access_divisions_created_by_foreign
        FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Pemeriksaan read-only setelah perubahan:
-- SHOW CREATE TABLE ops_access_divisions;
-- SELECT d.id, d.name FROM ops_access_divisions oad JOIN divisions d ON d.id = oad.division_id;
