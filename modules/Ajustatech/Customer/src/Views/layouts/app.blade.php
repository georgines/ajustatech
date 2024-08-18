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
            Livewire.on('confirmation', event => {
                const data = event[0];

                const swalOptions = {
                    icon: data.type,
                    title: data.message,
                    showCancelButton: data.cancelText !== 'default',
                    confirmButtonText: data.confirmText,
                    cancelButtonText: data.cancelText,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                };

                if (!data.message) {
                    delete swalOptions.title;
                }

                if (data.cancelText === "default") {
                    delete swalOptions.customClass.cancelButton;
                }

                Swal.fire(swalOptions).then(result => {
                    if (result.isConfirmed) {
                        if (data.dispatchTo && data.dispatchTo !== 'default') {
                            let parameters = data.parameters;
                            if (parameters.length !== 0) {
                                Livewire.dispatch(data.dispatchTo, parameters);
                            } else if (parameters.length === 0) {
                                Livewire.dispatch(data.dispatchTo);
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
