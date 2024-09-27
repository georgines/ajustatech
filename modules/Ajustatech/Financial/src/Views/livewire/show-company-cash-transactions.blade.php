<x-slot name="page_title">
    {{ $title }}
</x-slot>
<div>
    <div class="row">
        <div class="col-sm-12 col-lg-12 mb-4">
            <div class="card card-border-shadow-primary">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="fa fa-sack-dollar fa-lg"></i>
                            </span>
                        </div>
                        <h4 class="ms-1 mb-0">R${{ $balance }}</h4>
                    </div>
                    <p class="mb-1">{{ trans('financial::messages.balance') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title m-0 me-2">
                    <h5 class="m-0 me-2">{{ trans('financial::messages.transactions') }}</h5>
                    {{-- <small
                        class="text-muted">{{ trans('financial::messages.total_transactions', ['count' => count($transactions)]) }}
                    </small> --}}
                </div>
                <div class="dropdown">
                    <button class="btn p-0" type="button" id="transactionID" data-bs-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="ti ti-dots-vertical ti-sm text-muted"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="transactionID">
                        <a class="dropdown-item" href="javascript:void(0);" wire:click="loadLast7Days">
                            {{ trans('financial::messages.last_7_days') }}
                        </a>
                        <a class="dropdown-item" href="javascript:void(0);" wire:click="loadLastMonth">
                            {{ trans('financial::messages.last_month') }}
                        </a>
                        <a class="dropdown-item" href="javascript:void(0);" wire:click="loadLastYear">
                            {{ trans('financial::messages.last_year') }}
                        </a>
                        {{-- <a class="dropdown-item" href="javascript:void(0);"
                            wire:click="loadCustomInterval('{{ $customStartDate }}', '{{ $customEndDate }}')">
                            {{ trans('financial::messages.custom_interval') }}
                        </a> --}}
                    </div>
                </div>
            </div>

            <div class="card-body">
                <ul class="p-0 m-0">
                    @foreach ($transactions as $transaction)
                        <li class="d-flex mb-3 pb-1 align-items-center">
                            <div class="badge bg-label-primary me-3 rounded p-2">
                                <i class="ti ti-wallet ti-sm"></i>
                            </div>
                            <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                <div class="me-2">
                                    <h6 class="mb-0">{{ $transaction['description'] }}</h6>
                                    <small class="text-muted d-block">{{ $transaction['id'] }}</small>
                                </div>
                                <div class="user-progress d-flex align-items-center gap-1">
                                    @if ($transaction['is_inflow'])
                                        <h6 class="mb-0 text-success">
                                            {{ trans('financial::messages.money_inflow', ['amount' => $transaction['amount']]) }}
                                        </h6>
                                    @else
                                        <h6 class="mb-0 text-danger">
                                            {{ trans('financial::messages.money_outflow', ['amount' => $transaction['amount']]) }}
                                        </h6>
                                    @endif
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if (count($transactions) >= $offset)
                    <button type="button" class="btn btn-primary" wire:click="loadMore">
                        {{ trans('financial::messages.view_more') }}
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
