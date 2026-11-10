@extends('layouts.dashboard')

@section('title', 'Dashboard - NutriMate')

@section('content')

    {{-- Welcome --}}
    @include('dashboard.partials.welcome')

    {{-- Health Summary --}}
    @include('dashboard.partials.health-summary')

    {{-- Recommendation --}}
    @include('dashboard.partials.recommendation-card')

    {{-- Food List --}}
    @include('dashboard.partials.food-list')

    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    @endpush

    @push('scripts')
        <script src="{{ asset('js/dashboard.js') }}" defer></script>
    @endpush

@endsection