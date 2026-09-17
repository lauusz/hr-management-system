-- PRD-001 Approver Cuti/Izin + detail perlakuan per tanggal.
-- Disusun berdasarkan inspeksi backup:
-- backups/hrd_system_20260914_1121.sql
--
-- Tujuan:
-- 1. Menambahkan users.approver_id untuk penunjukan Approver cuti/izin.
-- 2. Menambahkan users.can_approve_leave untuk akses eksplisit Approver cuti/izin.
-- 3. Menambahkan leave_request_approval_actions untuk histori tindakan approval.
-- 4. Menambahkan leave_request_days untuk detail perlakuan/potongan per tanggal.
-- 5. Backfill leave_request_days dari leave_requests existing secara idempoten.
--
-- Catatan:
-- - File ini adalah artefak SQL manual. Penerapan database dilakukan lewat proses
--   internal pemilik repositori, bukan lewat command Artisan.
-- - Backfill tidak mengubah users.leave_balance dan tidak menulis
--   leave_balance_transactions.
-- - Aman dijalankan ulang: ADD COLUMN/FK memakai pemeriksaan metadata, CREATE TABLE
--   memakai IF NOT EXISTS, dan backfill memakai INSERT IGNORE.

SELECT
  'preflight' AS stage,
  DATABASE() AS current_database,
  VERSION() AS server_version;

SELECT
  'before_schema' AS stage,
  SUM(TABLE_NAME = 'users') AS has_users,
  SUM(TABLE_NAME = 'leave_requests') AS has_leave_requests,
  SUM(TABLE_NAME = 'leave_balance_transactions') AS has_leave_balance_transactions,
  SUM(TABLE_NAME = 'office_holidays') AS has_office_holidays,
  SUM(TABLE_NAME = 'leave_request_days') AS has_leave_request_days,
  SUM(TABLE_NAME = 'leave_request_approval_actions') AS has_leave_request_approval_actions,
  (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND COLUMN_NAME = 'can_approve_leave'
  ) AS has_users_can_approve_leave
FROM INFORMATION_SCHEMA.TABLES
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN (
    'users',
    'leave_requests',
    'leave_balance_transactions',
    'office_holidays',
    'leave_request_days',
    'leave_request_approval_actions'
  );

SET @column_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'approver_id'
);

SET @sql := IF(
  @column_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `approver_id` BIGINT UNSIGNED NULL AFTER `manager_id`',
  'SELECT ''users.approver_id already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @fk_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS
  WHERE CONSTRAINT_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND CONSTRAINT_NAME = 'users_approver_id_foreign'
);

SET @sql := IF(
  @fk_exists = 0,
  'ALTER TABLE `users` ADD CONSTRAINT `users_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
  'SELECT ''users_approver_id_foreign already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @column_exists := (
  SELECT COUNT(*)
  FROM INFORMATION_SCHEMA.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'users'
    AND COLUMN_NAME = 'can_approve_leave'
);

SET @sql := IF(
  @column_exists = 0,
  'ALTER TABLE `users` ADD COLUMN `can_approve_leave` TINYINT(1) NOT NULL DEFAULT 0 AFTER `approver_id`',
  'SELECT ''users.can_approve_leave already exists'' AS info'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `leave_request_approval_actions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `leave_request_id` BIGINT UNSIGNED NOT NULL,
  `actor_id` BIGINT UNSIGNED NULL,
  `actor_name` VARCHAR(255) NOT NULL,
  `actor_role` VARCHAR(50) NULL,
  `capacity` VARCHAR(30) NOT NULL,
  `action` VARCHAR(30) NOT NULL,
  `from_status` VARCHAR(50) NULL,
  `to_status` VARCHAR(50) NULL,
  `notes` TEXT NULL,
  `acted_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  KEY `lr_approval_actions_request_time_index` (`leave_request_id`, `acted_at`),
  KEY `lr_approval_actions_actor_time_index` (`actor_id`, `acted_at`),
  KEY `lr_approval_actions_capacity_action_index` (`capacity`, `action`),
  CONSTRAINT `leave_request_approval_actions_leave_request_id_foreign`
    FOREIGN KEY (`leave_request_id`) REFERENCES `leave_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_request_approval_actions_actor_id_foreign`
    FOREIGN KEY (`actor_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `leave_request_days` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `leave_request_id` BIGINT UNSIGNED NOT NULL,
  `leave_date` DATE NOT NULL,
  `treatment` VARCHAR(30) NOT NULL DEFAULT 'NONE',
  `deduction_amount` DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  `decided_by` BIGINT UNSIGNED DEFAULT NULL,
  `decided_at` TIMESTAMP NULL DEFAULT NULL,
  `created_at` TIMESTAMP NULL DEFAULT NULL,
  `updated_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_request_days_request_date_unique` (`leave_request_id`, `leave_date`),
  KEY `leave_request_days_date_treatment_index` (`leave_date`, `treatment`),
  KEY `leave_request_days_decided_by_foreign` (`decided_by`),
  CONSTRAINT `leave_request_days_leave_request_id_foreign`
    FOREIGN KEY (`leave_request_id`) REFERENCES `leave_requests` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_request_days_decided_by_foreign`
    FOREIGN KEY (`decided_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `leave_request_days_treatment_check`
    CHECK (`treatment` IN ('NONE', 'MEAL_ALLOWANCE', 'LEAVE_BALANCE')),
  CONSTRAINT `leave_request_days_amount_check`
    CHECK (`deduction_amount` IN (0.00, 0.50, 1.00)),
  CONSTRAINT `leave_request_days_treatment_amount_check`
    CHECK (
      (`treatment` = 'LEAVE_BALANCE' AND `deduction_amount` IN (0.50, 1.00))
      OR (`treatment` IN ('NONE', 'MEAL_ALLOWANCE') AND `deduction_amount` = 0.00)
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `leave_request_days` (
  `leave_request_id`,
  `leave_date`,
  `treatment`,
  `deduction_amount`,
  `decided_by`,
  `decided_at`,
  `created_at`,
  `updated_at`
)
SELECT
  allocation.leave_request_id,
  allocation.leave_date,
  CASE
    WHEN allocation.day_weight = 0 THEN 'NONE'
    WHEN allocation.ledger_net > allocation.allocated_before
      AND LEAST(allocation.day_weight, allocation.ledger_net - allocation.allocated_before) >= 0.50
      THEN 'LEAVE_BALANCE'
    WHEN allocation.deduct_um = 1 THEN 'MEAL_ALLOWANCE'
    WHEN allocation.ledger_net = 0
      AND allocation.leave_type = 'CUTI'
      AND allocation.leave_status IN ('PENDING_SUPERVISOR', 'PENDING_HR', 'APPROVED')
      THEN 'LEAVE_BALANCE'
    ELSE 'NONE'
  END AS treatment,
  CASE
    WHEN allocation.day_weight = 0 THEN 0.00
    WHEN allocation.ledger_net > allocation.allocated_before
      AND LEAST(allocation.day_weight, allocation.ledger_net - allocation.allocated_before) >= 1.00
      THEN 1.00
    WHEN allocation.ledger_net > allocation.allocated_before
      AND LEAST(allocation.day_weight, allocation.ledger_net - allocation.allocated_before) >= 0.50
      THEN 0.50
    WHEN allocation.deduct_um = 1 THEN 0.00
    WHEN allocation.ledger_net = 0
      AND allocation.leave_type = 'CUTI'
      AND allocation.leave_status IN ('PENDING_SUPERVISOR', 'PENDING_HR', 'APPROVED')
      THEN allocation.day_weight
    ELSE 0.00
  END AS deduction_amount,
  NULL AS decided_by,
  NULL AS decided_at,
  allocation.parent_created_at,
  allocation.parent_updated_at
FROM (
  SELECT
    weighted.*,
    COALESCE(
      SUM(weighted.day_weight) OVER (
        PARTITION BY weighted.leave_request_id
        ORDER BY weighted.leave_date
        ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING
      ),
      0
    ) AS allocated_before
  FROM (
    SELECT
      expanded.leave_request_id,
      expanded.leave_date,
      expanded.leave_type,
      expanded.leave_status,
      expanded.deduct_um,
      expanded.ledger_net,
      expanded.parent_created_at,
      expanded.parent_updated_at,
      CASE
        WHEN DAYOFWEEK(expanded.leave_date) = 1 THEN 0.00
        WHEN DAYOFWEEK(expanded.leave_date) = 7
          AND expanded.user_role IN ('HRD', 'MANAGER') THEN 0.00
        WHEN expanded.holiday_id IS NOT NULL AND expanded.holiday_deducts_leave = 0 THEN 0.00
        WHEN DAYOFWEEK(expanded.leave_date) = 7 THEN 0.50
        ELSE 1.00
      END AS day_weight
    FROM (
      SELECT
        lr.id AS leave_request_id,
        DATE_ADD(lr.start_date, INTERVAL sequence_numbers.n DAY) AS leave_date,
        UPPER(COALESCE(lr.type, '')) AS leave_type,
        UPPER(COALESCE(lr.status, '')) AS leave_status,
        lr.deduct_um,
        UPPER(COALESCE(u.role, '')) AS user_role,
        GREATEST(COALESCE(ledger.ledger_net, 0), 0) AS ledger_net,
        oh.id AS holiday_id,
        COALESCE(oh.deducts_leave, 0) AS holiday_deducts_leave,
        lr.created_at AS parent_created_at,
        lr.updated_at AS parent_updated_at
      FROM leave_requests lr
      INNER JOIN users u ON u.id = lr.user_id
      INNER JOIN (
        SELECT
          ones.n + (tens.n * 10) + (hundreds.n * 100) + (thousands.n * 1000) AS n
        FROM
          (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
           UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) ones
        CROSS JOIN
          (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
           UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) tens
        CROSS JOIN
          (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
           UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) hundreds
        CROSS JOIN
          (SELECT 0 n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
           UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) thousands
      ) sequence_numbers
        ON sequence_numbers.n <= DATEDIFF(COALESCE(lr.end_date, lr.start_date), lr.start_date)
      LEFT JOIN (
        SELECT
          leave_request_id,
          ROUND(SUM(
            CASE
              WHEN transaction_type IN ('DEDUCT', 'ADJUSTMENT') THEN amount
              WHEN transaction_type = 'REFUND' THEN -amount
              ELSE 0
            END
          ), 2) AS ledger_net
        FROM leave_balance_transactions
        WHERE leave_request_id IS NOT NULL
        GROUP BY leave_request_id
      ) ledger ON ledger.leave_request_id = lr.id
      LEFT JOIN office_holidays oh
        ON oh.holiday_date = DATE_ADD(lr.start_date, INTERVAL sequence_numbers.n DAY)
       AND oh.is_active = 1
    ) expanded
  ) weighted
) allocation;

SELECT
  'after_schema' AS stage,
  COUNT(*) AS total_detail_tanggal,
  COUNT(DISTINCT leave_request_id) AS total_pengajuan_tercakup,
  SUM(treatment = 'LEAVE_BALANCE') AS total_tanggal_potong_cuti,
  SUM(treatment = 'MEAL_ALLOWANCE') AS total_tanggal_potong_um,
  SUM(treatment = 'NONE') AS total_tanggal_tanpa_potongan
FROM leave_request_days;

SELECT
  'postflight_columns' AS stage,
  SUM(TABLE_NAME = 'users' AND COLUMN_NAME = 'approver_id') AS has_users_approver_id,
  SUM(TABLE_NAME = 'users' AND COLUMN_NAME = 'can_approve_leave') AS has_users_can_approve_leave,
  SUM(TABLE_NAME = 'leave_request_days' AND COLUMN_NAME = 'leave_date') AS has_leave_request_days_leave_date,
  SUM(TABLE_NAME = 'leave_request_approval_actions' AND COLUMN_NAME = 'acted_at') AS has_approval_actions_acted_at
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_SCHEMA = DATABASE()
  AND TABLE_NAME IN ('users', 'leave_request_days', 'leave_request_approval_actions');
