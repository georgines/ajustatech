<x-slot name="page_title">{{ $title }}</x-slot>
<div
    x-data="{
        selectedHelp: {
            name: '',
            description: '',
            help_text: '',
            images: [],
            videos: [],
            pdfs: [],
        },
        previewMedia: {
            type: '',
            url: '',
            name: '',
        },
        openHelp(payload) {
            this.selectedHelp = payload ?? {
                name: '',
                description: '',
                help_text: '',
                images: [],
                videos: [],
                pdfs: [],
            };
        },
        openFullscreen(type, url, name = '') {
            this.previewMedia = { type, url, name };

            if (!window.bootstrap) {
                return;
            }

            const modalEl = document.getElementById('procedureMediaFullscreenModal');
            if (!modalEl) {
                return;
            }

            bootstrap.Modal.getOrCreateInstance(modalEl).show();
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
                                        images: @js($procedure['images']),
                                        videos: @js($procedure['videos']),
                                        pdfs: @js($procedure['pdfs'])
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
                    <template x-for="(image, imageIndex) in selectedHelp.images" :key="'img-'+imageIndex">
                        <div class="mb-3">
                            <button type="button"
                                class="btn p-0 border-0 bg-transparent d-block"
                                x-on:click="openFullscreen('image', image.url, selectedHelp.name)">
                                <img
                                    :src="image.url"
                                    :alt="selectedHelp.name"
                                    class="img-fluid rounded border mb-2"
                                    style="max-height: 220px; object-fit: cover;">
                            </button>
                            <a :href="image.url"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="btn btn-sm btn-outline-primary mb-2">
                                {{ trans('service-order::messages.open_in_new_tab') }}
                            </a>
                            <template x-if="image.description">
                                <p class="small mb-3" x-text="image.description"></p>
                            </template>
                        </div>
                    </template>

                    <template x-for="(video, videoIndex) in selectedHelp.videos" :key="'video-'+videoIndex">
                        <div>
                            <div class="ratio ratio-16x9 mb-2">
                                <iframe
                                    :src="getVideoEmbedUrl(video.url)"
                                    :title="selectedHelp.name"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            </div>
                            <template x-if="video.description">
                                <p class="small mb-3" x-text="video.description"></p>
                            </template>
                        </div>
                    </template>

                    <template x-for="(pdf, pdfIndex) in selectedHelp.pdfs" :key="'pdf-'+pdfIndex">
                        <div class="mb-3">
                            <div class="ratio ratio-16x9 mb-2">
                                <iframe :src="pdf.url" :title="pdf.name" class="w-100 h-100"></iframe>
                            </div>
                            <button type="button"
                                class="btn btn-sm btn-outline-primary mb-2 me-2"
                                x-on:click="openFullscreen('pdf', pdf.url, pdf.name)">
                                {{ trans('service-order::messages.open_pdf_same_screen') }}
                            </button>
                            <a :href="pdf.url" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary mb-2">
                                {{ trans('service-order::messages.open_pdf_new_tab') }}
                            </a>
                            <template x-if="pdf.description">
                                <p class="small mb-0" x-text="pdf.description"></p>
                            </template>
                        </div>
                    </template>

                    <template x-if="selectedHelp.help_text">
                        <p class="small mb-0" x-text="selectedHelp.help_text"></p>
                    </template>

                    <template x-if="selectedHelp.images.length === 0 && selectedHelp.videos.length === 0 && selectedHelp.pdfs.length === 0 && !selectedHelp.help_text">
                        <p class="text-muted mb-0">{{ trans('service-order::messages.no_help_registered') }}</p>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <div wire:ignore.self class="modal fade" id="procedureMediaFullscreenModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" x-text="previewMedia.name || selectedHelp.name"></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-dark">
                    <template x-if="previewMedia.type === 'image'">
                        <div class="h-100 d-flex justify-content-center align-items-center">
                            <img :src="previewMedia.url" :alt="previewMedia.name" class="img-fluid" style="max-height: 92vh;">
                        </div>
                    </template>
                    <template x-if="previewMedia.type === 'pdf'">
                        <div class="h-100">
                            <iframe :src="previewMedia.url" :title="previewMedia.name" class="w-100 h-100 border-0"></iframe>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

@script
    <script>
        document.addEventListener('livewire:initialized', () => {
            document.addEventListener('show.bs.modal', event => {
                const visibleModals = document.querySelectorAll('.modal.show');
                const zIndex = 1055 + (visibleModals.length * 10);

                event.target.style.zIndex = zIndex;

                setTimeout(() => {
                    const backdrops = document.querySelectorAll('.modal-backdrop:not(.modal-stack)');
                    const backdrop = backdrops[backdrops.length - 1];
                    if (!backdrop) return;

                    backdrop.style.zIndex = zIndex - 1;
                    backdrop.classList.add('modal-stack');
                }, 0);
            });

            document.addEventListener('hidden.bs.modal', () => {
                if (document.querySelectorAll('.modal.show').length > 0) {
                    document.body.classList.add('modal-open');
                }
            });
        });
    </script>
@endscript
