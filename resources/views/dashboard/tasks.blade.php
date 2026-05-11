@extends('layout.pages-layout')
@section('pageTitle', isset($pageTitle) ? $pageTitle : 'Page Title Here')
@section('content')

    <!-- Tasks Section -->
    @livewire('tasks')


@endsection