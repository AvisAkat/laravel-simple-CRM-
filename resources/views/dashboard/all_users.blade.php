@extends('layout.pages-layout')
@section('pageTitle', isset($pageTitle) ? $pageTitle : 'Page Title Here')
@section('content')

    <!-- Customers Section -->
    @livewire('all-users')


@endsection