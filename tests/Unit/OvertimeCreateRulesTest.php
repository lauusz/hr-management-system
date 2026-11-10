<?php

use App\Http\Controllers\OvertimeRequestController;
use App\Models\OvertimeRequest;
use App\Models\User;
use App\Services\OvertimeEvidenceStorage;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Facade;
use Illuminate\Translation\ArrayLoader;
use Illuminate\Translation\Translator;
use Illuminate\Validation\Factory;

it('validates the create request rules without connecting to a database', function () {
    $app = new Container;
    Container::setInstance($app);
    Facade::setFacadeApplication($app);

    try {
        $auth = Mockery::mock();
        $auth->shouldReceive('user')->once()->andReturn(new User);
        $app->instance('auth', $auth);
        $request = Mockery::mock(Request::class);
        $rules = $messages = [];
        $request->shouldReceive('validate')->once()->andReturnUsing(function ($r, $m) use (&$rules, &$messages) {
            $rules = $r;
            $messages = $m;
            throw new RuntimeException('rules captured');
        });
        try {
            (new OvertimeRequestController)->store($request, Mockery::mock(OvertimeEvidenceStorage::class));
        } catch (RuntimeException $e) {
            expect($e->getMessage())->toBe('rules captured');
        }
        $factory = new Factory(new Translator(new ArrayLoader, 'id'), $app);
        $input = ['overtime_date' => '2026-11-05', 'start_time' => '18:00', 'end_time' => '02:00', 'description' => 'Pekerjaan'];
        foreach ([0,30,60,90,120,150,180,210,240,270,300,null,''] as $value) {
            expect($factory->make($input + ['break_duration_minutes' => $value], $rules, $messages)->passes())->toBeTrue();
        }
        expect($factory->make($input, $rules, $messages)->passes())->toBeTrue();
        foreach ([15,45,315,-30,1.5,'teks', [30]] as $value) {
            expect($factory->make($input + ['break_duration_minutes' => $value], $rules, $messages)->errors()->has('break_duration_minutes'))->toBeTrue();
        }
        foreach ([['start_time', '08:15'], ['end_time', '17:45'], ['start_time', '24:00'], ['start_time', ''], ['end_time', '']] as [$field, $value]) {
            expect($factory->make(array_replace($input, [$field => $value]), $rules, $messages)->errors()->has($field))->toBeTrue();
        }
        expect($factory->make(array_replace($input, ['start_time' => '08:30', 'end_time' => '17:30']), $rules, $messages)->passes())->toBeTrue()
            ->and($factory->make(array_replace($input, ['end_time' => '18:00']), $rules, $messages)->errors()->has('end_time'))->toBeTrue();
    } finally {
        Mockery::close();
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
        Container::setInstance(null);
    }
});

it('subtracts the break once in the model accessor', function () {
    foreach ([0,30,60,90,120,150,180,210,240,270,300] as $minutes) {
        $overtime = new OvertimeRequest(['duration_minutes' => 480, 'break_minutes' => $minutes]);
        expect($overtime->working_duration_minutes)->toBe(480 - $minutes);
    }
});

it('keeps overnight duration and the create and edit break boundaries', function () {
    $app = new Container;
    $app->instance('validator', new Factory(new Translator(new ArrayLoader, 'id'), $app));
    Facade::setFacadeApplication($app);
    try {
    $controller = new OvertimeRequestController;
    $duration = new ReflectionMethod($controller, 'calculateDurationMinutes');
    $calculateBreak = new ReflectionMethod($controller, 'calculateBreakMinutes');
    $validate = new ReflectionMethod($controller, 'validateBreakDuration');
    expect($duration->invoke($controller, '23:30', '00:30'))->toBe(60)
        ->and($duration->invoke($controller, '18:00', '02:00'))->toBe(480)
        ->and($calculateBreak->invoke($controller, ['break_hours' => 1, 'break_minutes' => 15]))->toBe(75);
    $validate->invoke($controller, 30, 60, 'break_duration_minutes');
    $validate->invoke($controller, 90, 120, 'break_duration_minutes');
    foreach ([[60, 60, 'break_duration_minutes'], [120, 120, 'break_duration_minutes'], [150, 120, 'break_duration_minutes'], [120, 120, 'break_hours']] as [$break, $total, $field]) {
        try {
            $validate->invoke($controller, $break, $total, $field);
            throw new RuntimeException('Expected validation failure');
        } catch (\Illuminate\Validation\ValidationException $e) {
            expect($e->errors())->toHaveKey($field);
        }
    }
    } finally {
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication(null);
    }
});
