<x-slot name="page_title">{{ $title }}</x-slot>
<div
    x-data="{
        selectedHelp: {
            name: '',
            description: '',
            help_text: '',
            help_image_url: '',
            help_video_url: '',
        },
        openHelp(payload) {
            this.selectedHelp = payload ?? {
                name: '',
                description: '',
                help_text: '',
                help_image_url: '',
                help_video_url: '',
            };
        },
        confirmDelete(id) {
            const runDelete = () => $wire.deleteProcedure(id);

            if (window.Swal) {
                Swal.fire({
                    icon: 'warning',
                    title: @js(trans('service-order::messages.procedure_confirm_delete')),
                    showCancelButton: true,
                    confirmButtonText: @js(trans('service-order::messages.confirm_yes')),
                    cancelButtonText: @js(trans('service-order::messages.confirm_no')),
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-danger'
                    },
                    buttonsStyling: false
                }).then(result => {
                    if (result.isConfirmed) runDelete();
                });
                return;
            }

            if (confirm(@js(trans('service-order::messages.procedure_confirm_delete')))) {
                runDelete();
            }
        },
        getVideoEmbedUrl(url) {
            if (!url) return null;
            try {
                const parsed = new URL(url);
                const host = parsed.hostname.toLowerCase();

                if (host.includes('youtu.be')) {
                    const id = parsed.pathname.replaceAll('/', '');
                    return id ? `https://www.youtube.com/embed/${id}` : null;
                }

                if (host.includes('youtube.com')) {
                    const id = parsed.searchParams.get('v');
                    return id ? `https://www.youtube.com/embed/${id}` : null;
                }

                return url;
            } catch (e) {
                return url;
            }
        }
    }">
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
                        <th>{{ trans('service-order::messages.procedure_help_short') }}</th>
                        <th>{{ trans('service-order::messages.actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($procedures as $procedure)
                        <tr>
                            <td>{{ $procedure['name'] }}</td>
                            <td>R$ {{ number_format((float) $procedure['value'], 2, ',', '.') }}</td>
                            <td>
                                @if ($procedure['has_help'])
                                    <button type="button"
                                        class="btn btn-sm btn-icon"
                                        x-on:click="openHelp({
                                            name: @js($procedure['name']),
                                            description: @js($procedure['description']),
                                            help_text: @js($procedure['help_text']),
                                            help_image_url: @js($procedure['help_image_url']),
                                            help_video_url: @js($procedure['help_video_url'])
                                        })"
                                        data-bs-toggle="modal"
                                        data-bs-target="#procedureHelpModal"
                                        title="{{ trans('service-order::messages.procedure_help_open') }}"
                                        aria-label="{{ trans('service-order::messages.procedure_help_open') }}">
                                        <i class="text-primary ti ti-help-circle"></i>
                                    </button>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-sm btn-icon"
                                    href="{{ route('service-order-procedures-edit', ['id' => $procedure['id']]) }}"
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="top"
                                    title="{{ trans('service-order::messages.edit') }}"
                                    aria-label="{{ trans('service-order::messages.edit') }}">
                                    <i class="text-primary ti ti-pencil"></i>
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-icon"
                                    x-on:click="confirmDelete('{{ $procedure['id'] }}')"
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
                        <h5 class="modal-title mb-1 text-start" x-text="selectedHelp.name || @js(trans('service-order::messages.procedure_help_title'))"></h5>
                        <template x-if="selectedHelp.description">
                            <p class="small text-muted mb-0 text-start" x-text="selectedHelp.description"></p>
                        </template>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <template x-if="selectedHelp.help_image_url">
                        <div>
                            <img
                                :src="selectedHelp.help_image_url"
                                :alt="selectedHelp.name"
                                class="img-fluid rounded border mb-2"
                                style="max-height: 220px; object-fit: cover;">
                            <template x-if="selectedHelp.help_text">
                                <p class="small mb-3" x-text="selectedHelp.help_text"></p>
                            </template>
                        </div>
                    </template>

                    <template x-if="selectedHelp.help_video_url">
                        <div>
                            <div class="ratio ratio-16x9 mb-2">
                                <iframe
                                    :src="getVideoEmbedUrl(selectedHelp.help_video_url)"
                                    :title="selectedHelp.name"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                            <template x-if="selectedHelp.help_text">
                                <p class="small mb-0" x-text="selectedHelp.help_text"></p>
                            </template>
                        </div>
                    </template>

                    <template x-if="!selectedHelp.help_image_url && !selectedHelp.help_video_url && selectedHelp.help_text">
                        <p class="small mb-0" x-text="selectedHelp.help_text"></p>
                    </template>

                    <template x-if="!selectedHelp.help_image_url && !selectedHelp.help_video_url && !selectedHelp.help_text">
                        <p class="text-muted mb-0">{{ trans('service-order::messages.no_help_registered') }}</p>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>
