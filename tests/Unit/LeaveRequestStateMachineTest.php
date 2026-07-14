<?php

uses(Tests\TestCase::class);
uses(Illuminate\Foundation\Testing\DatabaseTransactions::class);

use App\Models\LeaveRequest;
use App\Models\User;
use App\Services\LeaveRequestStateMachine;

describe('LeaveRequestStateMachine', function () {
    it('reports correct transition matrix', function (string $status, string $action, ?string $expectedTarget) {
        $machine = new LeaveRequestStateMachine;

        expect($machine->canPerform($status, $action))->toBe($expectedTarget !== null)
            ->and($machine->getTargetStatus($status, $action))->toBe($expectedTarget);
    })->with(function () {
        $statuses = [
            LeaveRequest::PENDING_SUPERVISOR,
            LeaveRequest::PENDING_HR,
            LeaveRequest::STATUS_APPROVED,
            LeaveRequest::STATUS_REJECTED,
            'BATAL',
            'CANCEL_REQ',
        ];

        $valid = [
            [LeaveRequest::PENDING_SUPERVISOR, LeaveRequestStateMachine::FORWARD_TO_HR, LeaveRequest::PENDING_HR],
            [LeaveRequest::PENDING_SUPERVISOR, LeaveRequestStateMachine::APPROVE, LeaveRequest::STATUS_APPROVED],
            [LeaveRequest::PENDING_HR, LeaveRequestStateMachine::APPROVE, LeaveRequest::STATUS_APPROVED],
            [LeaveRequest::PENDING_SUPERVISOR, LeaveRequestStateMachine::REJECT, LeaveRequest::STATUS_REJECTED],
            [LeaveRequest::PENDING_HR, LeaveRequestStateMachine::REJECT, LeaveRequest::STATUS_REJECTED],
            [LeaveRequest::PENDING_SUPERVISOR, LeaveRequestStateMachine::CANCEL, 'BATAL'],
            [LeaveRequest::PENDING_HR, LeaveRequestStateMachine::CANCEL, 'BATAL'],
            [LeaveRequest::STATUS_APPROVED, LeaveRequestStateMachine::CANCEL, 'BATAL'],
            [LeaveRequest::PENDING_SUPERVISOR, LeaveRequestStateMachine::REVISE_FOR_HR, LeaveRequest::PENDING_HR],
            [LeaveRequest::PENDING_HR, LeaveRequestStateMachine::REVISE_FOR_HR, LeaveRequest::PENDING_HR],
            [LeaveRequest::PENDING_SUPERVISOR, LeaveRequestStateMachine::EDIT_PENDING, LeaveRequest::PENDING_SUPERVISOR],
            [LeaveRequest::PENDING_HR, LeaveRequestStateMachine::EDIT_PENDING, LeaveRequest::PENDING_HR],
            [LeaveRequest::STATUS_APPROVED, 'EDIT_APPROVED_DATE', LeaveRequest::STATUS_APPROVED],
        ];

        $present = [];
        foreach ($valid as $row) {
            $present[$row[0]][$row[1]] = true;
            yield $row;
        }

        $machine = new LeaveRequestStateMachine;
        foreach ($statuses as $status) {
            foreach ($machine->getActions() as $action) {
                if (! isset($present[$status][$action])) {
                    yield [$status, $action, null];
                }
            }
        }
    });

    it('exposes allowed actions per status', function () {
        $machine = new LeaveRequestStateMachine;

        expect($machine->getAllowedActions(LeaveRequest::PENDING_SUPERVISOR))->toContain(
            LeaveRequestStateMachine::FORWARD_TO_HR,
            LeaveRequestStateMachine::APPROVE,
            LeaveRequestStateMachine::REJECT,
            LeaveRequestStateMachine::CANCEL,
            LeaveRequestStateMachine::REVISE_FOR_HR,
            LeaveRequestStateMachine::EDIT_PENDING,
        );

        expect($machine->getAllowedActions(LeaveRequest::STATUS_APPROVED))->toContain(
            LeaveRequestStateMachine::CANCEL,
            'EDIT_APPROVED_DATE',
        );

        expect($machine->getAllowedActions(LeaveRequest::STATUS_REJECTED))->toBeEmpty();
        expect($machine->getAllowedActions('BATAL'))->toBeEmpty();
        expect($machine->getAllowedActions('CANCEL_REQ'))->toBeEmpty();
    });

    it('keeps REVISE_FOR_HR on PENDING_HR as same-state revision', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => LeaveRequest::PENDING_HR,
            'reason' => 'original',
        ]);

        $machine = new LeaveRequestStateMachine;
        $result = $machine->perform(
            $leave,
            LeaveRequestStateMachine::REVISE_FOR_HR,
            fn () => ['reason' => 'revised']
        );

        expect($result)->toBeTrue()
            ->and($leave->fresh()->status)->toBe(LeaveRequest::PENDING_HR)
            ->and($leave->fresh()->reason)->toBe('revised');
    });

    it('preserves pending status with EDIT_PENDING', function (string $status) {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => $status,
            'reason' => 'original',
        ]);

        $machine = new LeaveRequestStateMachine;
        $result = $machine->perform(
            $leave,
            LeaveRequestStateMachine::EDIT_PENDING,
            fn () => ['reason' => 'edited']
        );

        expect($result)->toBeTrue()
            ->and($leave->fresh()->status)->toBe($status)
            ->and($leave->fresh()->reason)->toBe('edited');
    })->with([
        LeaveRequest::PENDING_SUPERVISOR,
        LeaveRequest::PENDING_HR,
    ]);

    it('throws when performing on unsaved leave request', function () {
        $machine = new LeaveRequestStateMachine;
        $leave = new LeaveRequest(['status' => LeaveRequest::PENDING_SUPERVISOR]);

        expect(fn () => $machine->perform($leave, LeaveRequestStateMachine::APPROVE, fn () => []))
            ->toThrow(RuntimeException::class);
    });

    it('rolls back and rethrows when callback throws', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => LeaveRequest::PENDING_HR,
            'reason' => 'original',
        ]);

        $machine = new LeaveRequestStateMachine;
        $thrown = null;

        try {
            $machine->perform($leave, LeaveRequestStateMachine::APPROVE, function () {
                throw new RuntimeException('callback failure');
            });
        } catch (RuntimeException $e) {
            $thrown = $e;
        }

        expect($thrown)->not->toBeNull()
            ->and($thrown->getMessage())->toBe('callback failure')
            ->and($leave->fresh()->status)->toBe(LeaveRequest::PENDING_HR)
            ->and($leave->fresh()->reason)->toBe('original');
    });

    it('returns false and skips callback on stale/invalid status', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => LeaveRequest::PENDING_HR,
        ]);

        LeaveRequest::where('id', $leave->id)->update(['status' => LeaveRequest::STATUS_APPROVED]);

        $called = 0;
        $machine = new LeaveRequestStateMachine;
        $result = $machine->perform($leave, LeaveRequestStateMachine::APPROVE, function () use (&$called) {
            $called++;

            return [];
        });

        expect($result)->toBeFalse()
            ->and($called)->toBe(0)
            ->and($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED);
    });

    it('returns false and skips callback when expected source status does not match locked row', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => LeaveRequest::PENDING_HR,
        ]);

        $called = 0;
        $machine = new LeaveRequestStateMachine;
        $result = $machine->perform(
            $leave,
            LeaveRequestStateMachine::APPROVE,
            function () use (&$called) {
                $called++;

                return [];
            },
            [],
            LeaveRequest::PENDING_SUPERVISOR
        );

        expect($result)->toBeFalse()
            ->and($called)->toBe(0)
            ->and($leave->fresh()->status)->toBe(LeaveRequest::PENDING_HR);
    });

    it('allows multiple allowed source statuses when locked row matches one of them', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => LeaveRequest::PENDING_HR,
        ]);

        $called = 0;
        $machine = new LeaveRequestStateMachine;
        $result = $machine->perform(
            $leave,
            LeaveRequestStateMachine::CANCEL,
            function () use (&$called) {
                $called++;

                return [];
            },
            [],
            [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR]
        );

        expect($result)->toBeTrue()
            ->and($called)->toBe(1)
            ->and($leave->fresh()->status)->toBe('BATAL');
    });

    it('returns false and skips callback when locked row status is not in multiple allowed statuses', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);

        $called = 0;
        $machine = new LeaveRequestStateMachine;
        $result = $machine->perform(
            $leave,
            LeaveRequestStateMachine::CANCEL,
            function () use (&$called) {
                $called++;

                return [];
            },
            [],
            [LeaveRequest::PENDING_SUPERVISOR, LeaveRequest::PENDING_HR]
        );

        expect($result)->toBeFalse()
            ->and($called)->toBe(0)
            ->and($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED);
    });

    it('throws and rolls back when callback returns non-array value', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => LeaveRequest::PENDING_HR,
            'reason' => 'original',
        ]);

        $machine = new LeaveRequestStateMachine;
        $thrown = null;

        try {
            $machine->perform($leave, LeaveRequestStateMachine::APPROVE, function () {
                return 'invalid';
            });
        } catch (RuntimeException $e) {
            $thrown = $e;
        }

        expect($thrown)->not->toBeNull()
            ->and($thrown->getMessage())->toContain('Callback state machine')
            ->and($leave->fresh()->status)->toBe(LeaveRequest::PENDING_HR)
            ->and($leave->fresh()->reason)->toBe('original');
    });

    it('runs afterUpdate hook with model already updated', function () {
        $user = User::factory()->create();
        $leave = LeaveRequest::factory()->forUser($user)->create([
            'status' => LeaveRequest::PENDING_HR,
        ]);

        $hookStatuses = [];
        $machine = new LeaveRequestStateMachine;
        $result = $machine->perform(
            $leave,
            LeaveRequestStateMachine::APPROVE,
            fn () => [],
            [],
            null,
            function (LeaveRequest $lockedLeave) use (&$hookStatuses) {
                $hookStatuses[] = $lockedLeave->status;
            }
        );

        expect($result)->toBeTrue()
            ->and($hookStatuses)->toBe([LeaveRequest::STATUS_APPROVED])
            ->and($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED);
    });
});
