@extends('layout.pages-layout')
@section('pageTitle', isset($pageTitle) ? $pageTitle : 'Page Title Here')
@section('content')
    <!-- Dashboard Section -->
    <div id="dashboard">
        <h1 class="section-title">Dashboard</h1>

        @livewire('index')
    </div>

@endsection