<?php

it('keeps nullable approver foreign keys without incompatible MariaDB checks', function () {
    $sql = file_get_contents('database/deployment/2026_10_17_prd_020_approver_lembur.sql');
    [$assignment, $actions] = explode('CREATE TABLE IF NOT EXISTS `overtime_approval_actions`', $sql, 2);
    $assignment = preg_replace('/^\s*--.*$/m', '', $assignment);

    expect($assignment)->toMatch('/FOREIGN KEY\s*\(`supervisor_id`\)\s*REFERENCES\s*`users`\s*\(`id`\)\s*ON DELETE SET NULL/i')
        ->toMatch('/FOREIGN KEY\s*\(`manager_id`\)\s*REFERENCES\s*`users`\s*\(`id`\)\s*ON DELETE SET NULL/i')
        ->not->toMatch('/\bCHECK\s*\([^;]*(?:`supervisor_id`|`manager_id`)/is')
        ->and($actions)->toContain('`ot_actions_round_slot_unique`', '`ot_actions_round_positive_chk`', '`ot_actions_slot_action_chk`');
});
