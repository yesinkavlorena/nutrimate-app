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

@endsection