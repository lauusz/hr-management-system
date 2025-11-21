@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Admin Dashboard</h1>
    <p>Welcome to the admin panel. Here you can manage your application.</p>
    <a href="{{ route('admin.users') }}" class="btn btn-primary">Manage Users</a>
    <a href="{{ route('admin.settings') }}" class="btn btn-secondary">Settings</a>
</div>
@endsection