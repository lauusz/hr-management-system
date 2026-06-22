<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Leave request filters: employee history, HR inbox, duplicate/overlap checks, and master export filters.
        $this->addIndex('leave_requests', 'leave_requests_user_type_dates_status_index', ['user_id', 'type', 'start_date', 'end_date', 'status']);
        $this->addIndex('leave_requests', 'leave_requests_status_created_at_index', ['status', 'created_at']);
        $this->addIndex('leave_requests', 'leave_requests_user_created_at_index', ['user_id', 'created_at']);
        $this->addIndex('leave_requests', 'leave_requests_type_created_at_index', ['type', 'created_at']);
        $this->addIndex('leave_requests', 'leave_requests_created_at_index', ['created_at']);

        // Leave balance transaction filters: refund/deduct lookups and per-employee balance history.
        $this->addIndex('leave_balance_transactions', 'leave_balance_transactions_leave_type_index', ['leave_request_id', 'transaction_type']);
        $this->addIndex('leave_balance_transactions', 'leave_balance_transactions_user_created_at_index', ['user_id', 'created_at']);

        // Payroll filters: period lookup for payroll index/import/email flows.
        $this->addIndex('payslips', 'payslips_period_user_index', ['period_year', 'period_month', 'user_id']);
        $this->addIndex('payslips', 'payslips_user_period_index', ['user_id', 'period_year', 'period_month']);

        // Attendance filters: HR attendance recap, pending remote approval, and employee remote history.
        $this->addIndex('attendances', 'attendances_date_clock_in_index', ['date', 'clock_in_at']);
        $this->addIndex('attendances', 'attendances_completion_date_index', ['completion_status', 'date']);
        $this->addIndex('attendances', 'attendances_approval_created_at_index', ['approval_status', 'created_at']);
        $this->addIndex('attendances', 'attendances_user_type_created_at_index', ['user_id', 'type', 'created_at']);

        // Loan filters: HR loan list and repayment history.
        $this->addIndex('loan_requests', 'loan_requests_status_created_at_index', ['status', 'created_at']);
        $this->addIndex('loan_requests', 'loan_requests_submitted_at_created_at_index', ['submitted_at', 'created_at']);
        $this->addIndex('loan_requests', 'loan_requests_user_created_at_index', ['user_id', 'created_at']);
        $this->addIndex('loan_repayments', 'loan_repayments_loan_paid_at_index', ['loan_request_id', 'paid_at']);
        $this->addIndex('loan_repayments', 'loan_repayments_user_paid_at_index', ['user_id', 'paid_at']);

        // Overtime filters: HR/supervisor master filters by date/status and employee history.
        $this->addIndex('overtime_requests', 'overtime_requests_status_created_at_index', ['status', 'created_at']);
        $this->addIndex('overtime_requests', 'overtime_requests_user_created_at_index', ['user_id', 'created_at']);

        // Employee filters: PT/category/probation/join-date filters used by HR employee and payroll screens.
        $this->addIndex('employee_profiles', 'employee_profiles_pt_category_index', ['pt_id', 'kategori']);
        $this->addIndex('employee_profiles', 'employee_profiles_probation_end_index', ['tgl_akhir_percobaan']);
        $this->addIndex('employee_profiles', 'employee_profiles_joined_at_index', ['tgl_bergabung']);
        $this->addIndex('users', 'users_status_name_index', ['status', 'name']);
        $this->addIndex('users', 'users_position_status_name_index', ['position_id', 'status', 'name']);
    }

    public function down(): void
    {
        $this->dropIndex('users', 'users_position_status_name_index');
        $this->dropIndex('users', 'users_status_name_index');
        $this->dropIndex('employee_profiles', 'employee_profiles_joined_at_index');
        $this->dropIndex('employee_profiles', 'employee_profiles_probation_end_index');
        $this->dropIndex('employee_profiles', 'employee_profiles_pt_category_index');
        $this->dropIndex('overtime_requests', 'overtime_requests_user_created_at_index');
        $this->dropIndex('overtime_requests', 'overtime_requests_status_created_at_index');
        $this->dropIndex('loan_repayments', 'loan_repayments_user_paid_at_index');
        $this->dropIndex('loan_repayments', 'loan_repayments_loan_paid_at_index');
        $this->dropIndex('loan_requests', 'loan_requests_user_created_at_index');
        $this->dropIndex('loan_requests', 'loan_requests_submitted_at_created_at_index');
        $this->dropIndex('loan_requests', 'loan_requests_status_created_at_index');
        $this->dropIndex('attendances', 'attendances_user_type_created_at_index');
        $this->dropIndex('attendances', 'attendances_approval_created_at_index');
        $this->dropIndex('attendances', 'attendances_completion_date_index');
        $this->dropIndex('attendances', 'attendances_date_clock_in_index');
        $this->dropIndex('payslips', 'payslips_user_period_index');
        $this->dropIndex('payslips', 'payslips_period_user_index');
        $this->dropIndex('leave_balance_transactions', 'leave_balance_transactions_user_created_at_index');
        $this->dropIndex('leave_balance_transactions', 'leave_balance_transactions_leave_type_index');
        $this->dropIndex('leave_requests', 'leave_requests_created_at_index');
        $this->dropIndex('leave_requests', 'leave_requests_type_created_at_index');
        $this->dropIndex('leave_requests', 'leave_requests_user_created_at_index');
        $this->dropIndex('leave_requests', 'leave_requests_status_created_at_index');
        $this->dropIndex('leave_requests', 'leave_requests_user_type_dates_status_index');
    }

    private function addIndex(string $table, string $indexName, array $columns): void
    {
        if (! $this->tableExists($table) || ! $this->columnsExist($table, $columns) || $this->indexExists($table, $indexName)) {
            return;
        }

        $columnsSql = implode(', ', array_map(fn (string $column): string => "`{$column}`", $columns));

        DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` ({$columnsSql})");
    }

    private function dropIndex(string $table, string $indexName): void
    {
        if (! $this->tableExists($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        DB::statement("DROP INDEX `{$indexName}` ON `{$table}`");
    }

    private function tableExists(string $table): bool
    {
        return Schema::hasTable($table);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return Schema::hasIndex($table, $indexName);
    }

    private function columnsExist(string $table, array $columns): bool
    {
        foreach ($columns as $column) {
            if (! Schema::hasColumn($table, $column)) {
                return false;
            }
        }

        return true;
    }
};
