@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-700">Users</h2>
        <p class="mt-4 text-4xl font-bold text-blue-600">{{ $userCount }}</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-700">Categories</h2>
        <p class="mt-4 text-4xl font-bold text-green-600">{{ $categoryCount }}</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-xl font-semibold text-gray-700">Books</h2>
        <p class="mt-4 text-4xl font-bold text-purple-600">{{ $bookCount }}</p>
    </div>

</div>

@endsection