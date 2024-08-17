@php
    $configData = Helper::appClasses();
@endphp

@extends('layouts/layoutMaster')

@section('vendor-style')
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.scss'])
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
    @vite(['resources/assets/vendor/libs/sweetalert2/sweetalert2.js'])
    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('alert', event => {
                Swal.fire({
                    icon: event.alert.type,
                    title: event.alert.message,
                    customClass: {
                        confirmButton: 'btn btn-primary'
                    },
                    buttonsStyling: false
                });
            });

            Livewire.on('confirmation', event => {
                Swal.fire({
                    icon: event.confirmation.type,
                    title: event.confirmation.message,
                    showCancelButton: true,
                    confirmButtonText: event.confirmation.confirmText,
                    cancelButtonText: event.confirmation.cancelText,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) {
                        if (event.confirmation.dispatchTo != null) {
                            let parameters = event.confirmation.parameters;
                            if(typeof parameters === "object"){
                                Livewire.dispatch(event.confirmation.dispatchTo, parameters);
                            }else{
                                Livewire.dispatch(event.confirmation.dispatchTo)
                            }
                        }
                    }
                });
            });
        });
    </script>
    @isset($vendor_script)
        {{ $vendor_script }}
    @endisset
@endsection

@section('page-script')
    @isset($page_script)
        {{ $page_script }}
    @endisset
@endsection
