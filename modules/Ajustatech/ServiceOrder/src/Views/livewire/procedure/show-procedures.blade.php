<x-slot name="page_title">{{ $title }}</x-slot>
<div>
    <div class="d-flex justify-content-end mb-3">
        <a class="btn btn-primary" href="{{ route('service-order-procedures-create') }}">
            {{ trans('service-order::messages.new_procedure') }}
        </a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="datatables-basic table">
                <thead>
                    <tr>
                        <th>{{ trans('service-order::messages.procedure_name') }}</th>
                        <th>{{ trans('service-order::messages.procedure_value') }}</th>
                        <th>{{ trans('service-order::messages.procedure_help_title') }}</th>
                        <th>{{ trans('service-order::messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($procedures as $procedure)
                        <tr>
                            <td>{{ $procedure->name }}</td>
                            <td>R$ {{ number_format((float) $procedure->value, 2, ',', '.') }}</td>
                            <td>
                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    wire:click="showHelp('{{ $procedure->id }}')"
                                    data-bs-toggle="modal"
                                    data-bs-target="#procedureHelpModal"
                                    title="{{ trans('service-order::messages.procedure_help_open') }}"
                                    aria-label="{{ trans('service-order::messages.procedure_help_open') }}">
                                    <i class="text-primary ti ti-help-circle"></i>
                                </button>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-icon"
                                    href="{{ route('service-order-procedures-edit', ['id' => $procedure->id]) }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ trans('service-order::messages.edit') }}"
                                    aria-label="{{ trans('service-order::messages.edit') }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    wire:click="confirmDelete('{{ $procedure->id }}')"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ trans('service-order::messages.delete') }}"
                                    aria-label="{{ trans('service-order::messages.delete') }}">
                                    <i class="text-primary ti ti-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">{{ trans('service-order::messages.no_records') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="procedureHelpModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title mb-1 text-start">{{ $selectedHelp->name ?? trans('service-order::messages.procedure_help_title') }}</h5>
                        @if (!empty($selectedHelp?->description))
                            <p class="small text-muted mb-0 text-start">{{ $selectedHelp->description }}</p>
                        @endif
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (empty($selectedHelp))
                        <p class="text-muted mb-0">{{ trans('service-order::messages.no_help_registered') }}</p>
                    @else
                        @if (!empty($selectedHelp->help_image_url))
                            <img
                                src="{{ $selectedHelp->help_image_url }}"
                                alt="{{ $selectedHelp->name }}"
                                class="img-fluid rounded border mb-2"
                                style="max-height: 220px; object-fit: cover;">
                            @if (!empty($selectedHelp->help_text))
                                <p class="small mb-3">{{ $selectedHelp->help_text }}</p>
                            @endif
                        @endif

                        @if (!empty($selectedHelp->help_video_url))
                            <div class="ratio ratio-16x9 mb-2">
                                <iframe
                                    src="{{ $this->getVideoEmbedUrl($selectedHelp->help_video_url) }}"
                                    title="{{ $selectedHelp->name }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                            @if (!empty($selectedHelp->help_text))
                                <p class="small mb-0">{{ $selectedHelp->help_text }}</p>
                            @endif
                        @endif

                        @if (empty($selectedHelp->help_image_url) && empty($selectedHelp->help_video_url) && !empty($selectedHelp->help_text))
                            <p class="small mb-0">{{ $selectedHelp->help_text }}</p>
                        @endif

                        @if (empty($selectedHelp->help_image_url) && empty($selectedHelp->help_video_url) && empty($selectedHelp->help_text))
                            <p class="text-muted mb-0">{{ trans('service-order::messages.no_help_registered') }}</p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
