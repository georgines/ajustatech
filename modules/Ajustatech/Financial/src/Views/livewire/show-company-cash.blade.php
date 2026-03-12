<x-slot name="page_title">
    {{ $title }}
</x-slot>

<div>
    <div class="row mb-3">
        <div class="col-md">
            <h5 class="pb-1">{{ trans('financial::messages.company_cash_list_title') }}</h5>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-md text-end">
            <a type="button" class="btn btn-primary d-none d-md-inline-block" href="{{ route('companycash-create') }}">
                <span class="tf-icon ti ti-plus ti-xs me-1"></span>{{ trans('financial::messages.company_cash_register') }}
            </a>

            <a type="button" class="btn btn-xl rounded-pill btn-icon btn-primary waves-effect waves-light d-md-none"
                href="{{ route('companycash-create') }}">
                <span class="ti ti-plus ti-md"></span>
            </a>
        </div>
    </div>

    <div class="row mb-5">
        @foreach ($cashs as $cash)
            <div class="col-md-6">
                <div class="card mb-3">
                    <div class="row g-0">
                        <div class="card-body">
                            <div class="card-title header-elements d-flex align-items-center">
                                <h5 class="card-title m-0 me-2">{{ $cash['cash_name'] }}</h5>
                                <i class="ti ti-wallet me-1"></i>
                            </div>
                            <p class="text-muted">{{ $cash['description'] }}</p>
                            <div class="row">
                                <div class="col-12">
                                    <p class="m-0"><strong>{{ trans('financial::messages.agency') }}:</strong> {{ $cash['agency'] }}</p>
                                    <p class="m-0"><strong>{{ trans('financial::messages.account') }}:</strong> {{ $cash['account'] }}</p>
                                    <p class="mt-3 mb-0 fs-5"><strong>{{ trans('financial::messages.balance_label') }}:</strong> {{ $cash['balance'] }}</p>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-3">
                                <a type="button" class="btn btn-primary"
                                    href="{{ route('company-cash-transactions-show', ['id' => $cash['id']]) }}">
                                    {{ trans('financial::messages.company_cash_details') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
