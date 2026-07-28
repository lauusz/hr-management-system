<?php

use App\Enums\LeaveType;
use App\Enums\UserRole;
use App\Models\LeaveRequest;
use App\Models\OffSpvChange;
use App\Models\OffSpvPeriod;
use App\Models\User;
use App\Services\OffSpvQuotaService;
use Carbon\Carbon;

use function Pest\Laravel\actingAs;

afterEach(function () {
    Carbon::setTestNow();
});

describe('HR OFF SPV management', function () {
    beforeEach(function () {
        Carbon::setTestNow('2026-07-30 09:00:00');
        $this->hr = User::factory()->create(['role' => UserRole::HRD]);
        $this->supervisor = User::factory()->create([
            'role' => UserRole::SUPERVISOR,
            'status' => User::STATUS_ACTIVE,
        ]);
        actingAs($this->hr, 'web');
    });

    it('shows the selected supervisor annual analytics', function () {
        $period = app(OffSpvQuotaService::class)
            ->getOrCreatePeriod($this->supervisor, Carbon::parse('2026-08-01'));
        LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => $period->id,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-01',
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);

        $this->get(route('hr.supervisors.show', $this->supervisor))
            ->assertOk()
            ->assertSee($this->supervisor->name)
            ->assertSee('Analytics OFF SPV')
            ->assertSee('Agustus 2026');
    });

    it('renders the supervisor detail with the HR header and back navigation', function () {
        $response = $this->get(route('hr.supervisors.show', $this->supervisor))->assertOk();

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " topbar ")]//*[contains(concat(" ", normalize-space(@class), " "), " section-title ") and normalize-space()="Detail Supervisor & Manager"]')->length)->toBe(1)
            ->and($xpath->query('//main[contains(concat(" ", normalize-space(@class), " "), " main-content ")]//a[contains(concat(" ", normalize-space(@class), " "), " back-btn ") and contains(@href, "/hr/supervisors")]')->length)->toBe(1);
    });

    it('renders the supervisor create form with the HR visual shell', function () {
        $response = $this->get(route('hr.supervisors.create'))->assertOk();

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " topbar ")]//*[contains(concat(" ", normalize-space(@class), " "), " section-title ") and normalize-space()="Tambah Supervisor & Manager"]')->length)->toBe(1)
            ->and($xpath->query('//main//a[contains(concat(" ", normalize-space(@class), " "), " back-btn ") and contains(@href, "/hr/supervisors")]')->length)->toBe(1)
            ->and($xpath->query('//form[@method="POST"]//select[@name="user_id"]')->length)->toBe(1)
            ->and($xpath->query('//form[@method="POST"]//select[@name="role"]')->length)->toBe(1);
    });

    it('renders the supervisor edit form with the HR visual shell', function () {
        $response = $this->get(route('hr.supervisors.edit', $this->supervisor))->assertOk();

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " topbar ")]//*[contains(concat(" ", normalize-space(@class), " "), " section-title ") and normalize-space()="Edit Supervisor & Manager"]')->length)->toBe(1)
            ->and($xpath->query('//main//a[contains(concat(" ", normalize-space(@class), " "), " back-btn ") and contains(@href, "/hr/supervisors")]')->length)->toBe(1)
            ->and($xpath->query('//form//input[@name="_method" and @value="PUT"]')->length)->toBe(1)
            ->and($xpath->query('//form//select[@name="role"]')->length)->toBe(1);
    });

    it('renders responsive supervisor cards with the same actions', function () {
        $this->get(route('hr.supervisors.index'))
            ->assertOk()
            ->assertSee('spv-desktop-view', false)
            ->assertSee('spv-mobile-list', false)
            ->assertSee('spv-mobile-card', false)
            ->assertSee('Detail OFF SPV')
            ->assertSee('Edit Level')
            ->assertSee('Demote');
    });

    it('renders the supervisor index with the leave master visual shell', function () {
        $response = $this->get(route('hr.supervisors.index'))->assertOk();

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " topbar ")]//*[contains(concat(" ", normalize-space(@class), " "), " section-title ") and normalize-space()="Supervisor & Manager"]')->length)->toBe(1)
            ->and($xpath->query('//main[contains(concat(" ", normalize-space(@class), " "), " main-content ")]//a[contains(@href, "/hr/supervisors/create")]')->length)->toBe(1)
            ->and($xpath->query('//main[contains(concat(" ", normalize-space(@class), " "), " main-content ")]//*[contains(concat(" ", normalize-space(@class), " "), " spv-container ")]')->length)->toBe(0);
    });

    it('only lists active supervisors and managers', function () {
        User::factory()->create([
            'name' => 'ACTIVE SUPERVISOR SAMPLE',
            'role' => UserRole::SUPERVISOR,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::factory()->create([
            'name' => 'ACTIVE MANAGER SAMPLE',
            'role' => UserRole::MANAGER,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::factory()->create([
            'name' => 'INACTIVE SUPERVISOR SAMPLE',
            'role' => UserRole::SUPERVISOR,
            'status' => 'INACTIVE',
        ]);
        User::factory()->create([
            'name' => 'INACTIVE MANAGER SAMPLE',
            'role' => UserRole::MANAGER,
            'status' => 'INACTIVE',
        ]);

        $this->get(route('hr.supervisors.index', [
            'roles' => [
                UserRole::SUPERVISOR->value,
                UserRole::MANAGER->value,
            ],
        ]))
            ->assertOk()
            ->assertSee('ACTIVE SUPERVISOR SAMPLE')
            ->assertSee('ACTIVE MANAGER SAMPLE')
            ->assertDontSee('INACTIVE SUPERVISOR SAMPLE')
            ->assertDontSee('INACTIVE MANAGER SAMPLE');
    });

    it('defaults to active supervisors without listing managers', function () {
        User::factory()->create([
            'name' => 'DEFAULT SUPERVISOR SAMPLE',
            'role' => UserRole::SUPERVISOR,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::factory()->create([
            'name' => 'DEFAULT MANAGER SAMPLE',
            'role' => UserRole::MANAGER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $response = $this->get(route('hr.supervisors.index'))
            ->assertOk()
            ->assertSee('DEFAULT SUPERVISOR SAMPLE')
            ->assertDontSee('DEFAULT MANAGER SAMPLE');

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//input[@name="roles[]" and @value="SUPERVISOR" and @checked]')->length)->toBe(1)
            ->and($xpath->query('//input[@name="roles[]" and @value="MANAGER" and @checked]')->length)->toBe(0)
            ->and($xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " spv-btn-reset ")]')->length)->toBe(0);
    });

    it('gives the header icon intrinsic dimensions before page styles load', function () {
        $response = $this->get(route('hr.supervisors.index'))->assertOk();

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//div[contains(concat(" ", normalize-space(@class), " "), " section-icon ")]/svg[@width="16" and @height="16"]')->length)->toBe(1);
    });

    it('loads the global svg size fallback before body content', function () {
        $html = $this->get(route('hr.supervisors.index'))
            ->assertOk()
            ->getContent();

        $bodyPosition = strpos($html, '<body');
        $widthFallbackPosition = strpos($html, 'svg:not([width])');
        $heightFallbackPosition = strpos($html, 'svg:not([height])');

        expect($widthFallbackPosition)->not->toBeFalse()
            ->and($heightFallbackPosition)->not->toBeFalse()
            ->and($widthFallbackPosition)->toBeLessThan($bodyPosition)
            ->and($heightFallbackPosition)->toBeLessThan($bodyPosition);
    });

    it('filters supervisors by name and selected role', function () {
        User::factory()->create([
            'name' => 'Needle Supervisor',
            'role' => UserRole::SUPERVISOR,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::factory()->create([
            'name' => 'Needle Manager',
            'role' => UserRole::MANAGER,
            'status' => User::STATUS_ACTIVE,
        ]);
        User::factory()->create([
            'name' => 'Other Manager',
            'role' => UserRole::MANAGER,
            'status' => User::STATUS_ACTIVE,
        ]);

        $this->get(route('hr.supervisors.index', [
            'q' => 'Needle',
            'roles' => [UserRole::MANAGER->value],
        ]))
            ->assertOk()
            ->assertSee('Needle Manager')
            ->assertDontSee('Needle Supervisor')
            ->assertDontSee('Other Manager');
    });

    it('preserves supervisor filters in the form and pagination', function () {
        User::factory()
            ->count(21)
            ->sequence(fn ($sequence) => [
                'name' => sprintf('Needle Supervisor %02d', $sequence->index + 1),
                'role' => UserRole::SUPERVISOR,
                'status' => User::STATUS_ACTIVE,
            ])
            ->create();

        $response = $this->get(route('hr.supervisors.index', [
            'q' => 'Needle',
            'roles' => [UserRole::SUPERVISOR->value],
        ]))->assertOk();

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);
        $pageTwoLink = $xpath->query('//a[contains(@href, "page=2")]')->item(0);

        expect($xpath->query('//input[@name="q" and @value="Needle"]')->length)->toBe(1)
            ->and($xpath->query('//input[@name="roles[]" and @value="SUPERVISOR" and @checked]')->length)->toBe(1)
            ->and($xpath->query('//input[@name="roles[]" and @value="MANAGER" and @checked]')->length)->toBe(0)
            ->and($xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " spv-btn-reset ")]')->length)->toBe(1)
            ->and($pageTwoLink)->not->toBeNull();

        parse_str(parse_url(html_entity_decode($pageTwoLink->getAttribute('href')), PHP_URL_QUERY), $query);

        expect($query['q'])->toBe('Needle')
            ->and($query['roles'])->toBe([UserRole::SUPERVISOR->value]);
    });

    it('allows HR to adjust quota and clamps a negative value to zero', function () {
        $period = app(OffSpvQuotaService::class)
            ->getOrCreatePeriod($this->supervisor, Carbon::parse('2026-08-01'));

        $this->patch(route('hr.supervisors.off-spv-periods.update', [$this->supervisor, $period]), [
            'effective_quota' => -5,
            'reason' => 'Penyesuaian operasional',
        ])->assertRedirect();

        expect($period->fresh()->effective_quota)->toBe(0);

        $change = OffSpvChange::where('change_type', OffSpvChange::TYPE_MANUAL_ADJUSTMENT)->sole();
        expect($change->quota_before)->toBe(2)
            ->and($change->quota_after)->toBe(0)
            ->and($change->changed_by)->toBe($this->hr->id)
            ->and($change->reason)->toBe('Penyesuaian operasional');
    });

    it('initializes the full active year when a new supervisor is promoted', function () {
        $employee = User::factory()->create(['role' => UserRole::EMPLOYEE]);

        $this->post(route('hr.supervisors.store'), [
            'user_id' => $employee->id,
            'role' => UserRole::SUPERVISOR->value,
        ])->assertRedirect(route('hr.supervisors.index'));

        expect(OffSpvPeriod::where('user_id', $employee->id)
            ->where('period_year', 2026)
            ->count())->toBe(12);
    });

    it('denies the supervisor detail page to non HR users', function () {
        actingAs(User::factory()->create(['role' => UserRole::EMPLOYEE]), 'web');

        $this->get(route('hr.supervisors.show', $this->supervisor))->assertForbidden();
    });

    it('separates active remaining quota from expired quota', function () {
        Carbon::setTestNow('2026-09-10 09:00:00');
        $august = app(OffSpvQuotaService::class)
            ->getOrCreatePeriod($this->supervisor, '2026-08-01');
        LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => $august->id,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-01',
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);

        $response = $this->get(route('hr.supervisors.show', $this->supervisor));

        expect($response->viewData('totals'))->toMatchArray([
            'remaining' => 2,
            'expired' => 1,
        ]);
    });

    it('shows approved usage above quota as excess instead of expired', function () {
        Carbon::setTestNow('2026-09-10 09:00:00');
        $august = app(OffSpvQuotaService::class)
            ->getOrCreatePeriod($this->supervisor, '2026-08-01');
        $august->update(['effective_quota' => 1]);

        foreach (['2026-08-01', '2026-08-08'] as $date) {
            LeaveRequest::factory()->create([
                'user_id' => $this->supervisor->id,
                'off_spv_period_id' => $august->id,
                'type' => LeaveType::OFF_SPV,
                'start_date' => $date,
                'end_date' => $date,
                'status' => LeaveRequest::STATUS_APPROVED,
            ]);
        }

        $response = $this->get(route('hr.supervisors.show', $this->supervisor))
            ->assertOk()
            ->assertSee('Sisa/Hangus/Kelebihan')
            ->assertSee('1 kelebihan');

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//tr[td[contains(normalize-space(), "Agustus 2026")]]//span[contains(@class, "spvd-pill--red") and normalize-space()="1 kelebihan"]')->length)
            ->toBe(1);
    });

    it('imports historical OFF SPV dates and links existing records without duplicates', function () {
        $existing = LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => null,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-01-03',
            'end_date' => '2026-01-03',
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);
        $url = "/hr/supervisors/{$this->supervisor->id}/off-spv-history";
        $payload = [
            'period_year' => 2026,
            'period_month' => 1,
            'effective_quota' => 4,
            'dates' => ['2026-01-10'],
            'reason' => 'Backfill Januari',
        ];

        $this->post($url, $payload)->assertSessionHas('success');
        $this->post($url, $payload)->assertSessionHas('success');

        $period = OffSpvPeriod::where('user_id', $this->supervisor->id)
            ->where('period_year', 2026)
            ->where('period_month', 1)
            ->sole();

        expect($existing->fresh()->off_spv_period_id)->toBe($period->id)
            ->and($period->effective_quota)->toBe(4)
            ->and(LeaveRequest::where('user_id', $this->supervisor->id)
                ->where('type', LeaveType::OFF_SPV->value)
                ->where('off_spv_period_id', $period->id)
                ->count())->toBe(2)
            ->and(LeaveRequest::whereDate('start_date', '2026-01-10')->sole()->approved_by)->toBe($this->hr->id);
    });

    it('synchronizes historical OFF SPV dates without deleting records', function () {
        $unchecked = LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => null,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-06-06',
            'end_date' => '2026-06-06',
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);
        $selectedPending = LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => null,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-06-13',
            'end_date' => '2026-06-13',
            'status' => LeaveRequest::PENDING_SUPERVISOR,
        ]);
        $url = "/hr/supervisors/{$this->supervisor->id}/off-spv-history";
        $payload = [
            'period_year' => 2026,
            'period_month' => 6,
            'effective_quota' => 2,
            'dates' => ['2026-06-13'],
            'reason' => 'Koreksi data Juni',
        ];

        $this->post($url, $payload)->assertSessionHas('success');

        expect($unchecked->fresh()->status)->toBe(LeaveRequest::STATUS_CANCELLED)
            ->and(LeaveRequest::whereKey($unchecked->id)->exists())->toBeTrue()
            ->and($selectedPending->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED)
            ->and($selectedPending->fresh()->approved_by)->toBe($this->hr->id)
            ->and(LeaveRequest::where('user_id', $this->supervisor->id)
                ->where('type', LeaveType::OFF_SPV->value)
                ->count())->toBe(2);

        $payload['dates'] = ['2026-06-06', '2026-06-13'];
        $this->post($url, $payload)->assertSessionHas('success');

        expect($unchecked->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED)
            ->and($unchecked->fresh()->approved_by)->toBe($this->hr->id)
            ->and(LeaveRequest::where('user_id', $this->supervisor->id)
                ->where('type', LeaveType::OFF_SPV->value)
                ->count())->toBe(2);
    });

    it('rejects an invalid historical OFF SPV date', function () {
        $this->post("/hr/supervisors/{$this->supervisor->id}/off-spv-history", [
            'period_year' => 2026,
            'period_month' => 1,
            'effective_quota' => 3,
            'dates' => ['2026-01-09'],
        ])->assertSessionHasErrors('dates.0');

        expect(OffSpvPeriod::where('user_id', $this->supervisor->id)
            ->where('period_month', 1)
            ->exists())->toBeFalse();
    });

    it('shows the historical entry form with existing dates marked', function () {
        LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => null,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-01-03',
            'end_date' => '2026-01-03',
            'status' => LeaveRequest::STATUS_APPROVED,
        ]);

        $response = $this->get(route('hr.supervisors.show', ['user' => $this->supervisor, 'year' => 2026]))
            ->assertOk()
            ->assertSee('Input Data Lampau')
            ->assertSee('27 Des 2025')
            ->assertSee('03 Jan 2026')
            ->assertSee('Sudah tercatat');

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//input[@name="dates[]" and @value="2026-01-03" and @checked and not(@disabled)]')->length)
            ->toBe(1);
    });

    it('keeps the historical input action visible after the period is saved', function () {
        app(OffSpvQuotaService::class)
            ->getOrCreatePeriod($this->supervisor, '2026-01-03');

        $response = $this->get(route('hr.supervisors.show', ['user' => $this->supervisor, 'year' => 2026]))
            ->assertOk();

        $dom = new DOMDocument;
        @$dom->loadHTML($response->getContent());
        $xpath = new DOMXPath($dom);

        expect($xpath->query('//button[@data-modal-open="history-1" and normalize-space()="Input Data Lampau"]')->length)
            ->toBe(1);
    });
});

describe('HR manual OFF SPV entry', function () {
    beforeEach(function () {
        Carbon::setTestNow('2026-07-30 09:00:00');
        $this->hr = User::factory()->create(['role' => UserRole::HRD]);
        $this->supervisor = User::factory()->create(['role' => UserRole::SUPERVISOR]);
        actingAs($this->hr, 'web');
    });

    it('still requires Saturday for a manual HR entry', function () {
        $this->from(route('hr.leave.manual.create'))
            ->post(route('hr.leave.manual.store'), [
                'user_id' => $this->supervisor->id,
                'type' => LeaveType::OFF_SPV->value,
                'start_date' => '2026-08-07',
                'end_date' => '2026-08-07',
                'status' => LeaveRequest::STATUS_APPROVED,
            ])
            ->assertRedirect(route('hr.leave.manual.create'))
            ->assertSessionHas('error', 'OFF SPV hanya dapat dicatat untuk hari Sabtu.');

        expect(LeaveRequest::where('user_id', $this->supervisor->id)->count())->toBe(0);
    });

    it('accepts a valid HR entry even when effective quota is zero', function () {
        $period = app(OffSpvQuotaService::class)
            ->getOrCreatePeriod($this->supervisor, Carbon::parse('2026-08-01'));
        $period->update(['effective_quota' => 0]);

        $this->post(route('hr.leave.manual.store'), [
            'user_id' => $this->supervisor->id,
            'type' => LeaveType::OFF_SPV->value,
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-01',
            'status' => LeaveRequest::STATUS_APPROVED,
        ])->assertRedirect(route('hr.leave.master'));

        $leave = LeaveRequest::where('user_id', $this->supervisor->id)->sole();
        expect($leave->off_spv_period_id)->toBe($period->id)
            ->and($leave->start_date->isSameDay($leave->end_date))->toBeTrue();
    });

    it('allows HR to edit an existing legacy July OFF request', function () {
        Carbon::setTestNow('2026-08-01 09:00:00');
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => null,
            'type' => LeaveType::OFF_SPV,
            'start_date' => '2026-07-18',
            'end_date' => '2026-07-18',
            'status' => LeaveRequest::PENDING_HR,
        ]);

        $this->from(route('hr.leave.show', $leave))
            ->put(route('hr.leave.update', $leave), [
                'type' => LeaveType::OFF_SPV->value,
                'start_date' => '2026-07-18',
                'end_date' => '2026-07-18',
                'reason' => 'Koreksi data legacy',
            ])
            ->assertRedirect(route('hr.leave.show', $leave))
            ->assertSessionHas('success');

        expect($leave->fresh()->off_spv_period_id)->not->toBeNull()
            ->and($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED);
    });

    it('allows HR to convert a legacy non OFF request into OFF', function () {
        Carbon::setTestNow('2026-08-01 09:00:00');
        $leave = LeaveRequest::factory()->create([
            'user_id' => $this->supervisor->id,
            'off_spv_period_id' => null,
            'type' => LeaveType::IZIN,
            'start_date' => '2026-07-18',
            'end_date' => '2026-07-18',
            'status' => LeaveRequest::PENDING_HR,
        ]);

        $this->from(route('hr.leave.show', $leave))
            ->put(route('hr.leave.update', $leave), [
                'type' => LeaveType::OFF_SPV->value,
                'start_date' => '2026-07-18',
                'end_date' => '2026-07-18',
                'reason' => 'Ubah tipe',
            ])
            ->assertRedirect(route('hr.leave.show', $leave))
            ->assertSessionHas('success');

        expect($leave->fresh()->type)->toBe(LeaveType::OFF_SPV)
            ->and($leave->fresh()->off_spv_period_id)->not->toBeNull()
            ->and($leave->fresh()->status)->toBe(LeaveRequest::STATUS_APPROVED);
    });
});
