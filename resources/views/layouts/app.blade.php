@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
@livewireStyles

    {{-- <link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css')) }}" />
    <link rel="stylesheet"
        href="{{ asset(mix('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css')) }}" />
    <link rel="stylesheet"
        href="{{ asset(mix('assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css')) }}" />
    <link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css')) }}" />
    <link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/flatpickr/flatpickr.css')) }}" />
    <link rel="stylesheet" href="{{ asset(mix('assets/vendor/libs/sweetalert2/sweetalert2.css')) }}"> --}}
    @isset($vendor_style)
        {{ $vendor_style }}
    @endisset
@endsection

@section('page-style')
    @isset($page_style)
        {{ $page_style }}
    @endisset
@endsection

@section('title', $title)


@section('content')
    <div class="bs-toast toast toast-ex animate__animated my-2 fade animate__fade hide" role="alert" aria-live="assertive"
        aria-atomic="true" data-bs-delay="2000">
        <div class="toast-header">
            <i class="ti ti-bell ti-xs me-2 text-success"></i>
            <div class="me-auto fw-medium">Bootstrap</div>
            <small class="text-muted">11 mins ago</small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body">Hello, world! This is a toast message.</div>
    </div>


    {{ $slot }}
@endsection

@section('vendor-script')
@livewireScripts
    {{-- <script src="{{ asset(mix('assets/vendor/libs/toastr/toastr.js')) }}"></script>
    <script src="{{ asset(mix('assets/vendor/libs/sweetalert2/sweetalert2.js')) }}"></script>

    <script src="{{ asset(mix('js/app.js')) }}"></script> --}}
    @isset($vendor_script)
        {{ $vendor_script }}
    @endisset
@endsection

@section('page-script')
    @isset($page_script)
        {{ $page_script }}
    @endisset
@endsection
