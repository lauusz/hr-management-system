<?php

use App\Http\Controllers\HRAttendanceController;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

it('refuses an attendance recap when a non HR user has no viewing access', function () {
    try {
        $viewer = Mockery::mock(User::class)->makePartial();
        $viewer->shouldReceive('isHR')->once()->andReturn(false);
        $viewer->shouldReceive('canViewTeamAttendance')->once()->andReturn(false);
        $request = Request::create('/supervisor/attendances');
        $request->setUserResolver(fn () => $viewer);

        try {
            (new HRAttendanceController)->team($request);
            throw new RuntimeException('Akses seharusnya ditolak.');
        } catch (HttpException $exception) {
            expect($exception->getStatusCode())->toBe(403);
        }
    } finally {
        Mockery::close();
    }
});

it('refuses the HR attendance page to non HR users even when granted team access', function () {
    try {
        $viewer = Mockery::mock(User::class)->makePartial();
        $viewer->shouldReceive('isHR')->once()->andReturn(false);
        $request = Request::create('/hr/attendances');
        $request->setUserResolver(fn () => $viewer);

        try {
            (new HRAttendanceController)->index($request);
            throw new RuntimeException('Akses seharusnya ditolak.');
        } catch (HttpException $exception) {
            expect($exception->getStatusCode())->toBe(403);
        }
    } finally {
        Mockery::close();
    }
});
