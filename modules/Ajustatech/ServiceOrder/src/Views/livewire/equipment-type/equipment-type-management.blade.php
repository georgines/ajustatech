<x-slot name="page_title">{{ $title }}</x-slot>
<div
  x-on:equipment-type-preview-reset.window="resetPreviewMedia()"
  x-data="{
  previewMedia: { type: '', url: '', name: '', content: '', loading: false, blobUrl: null },
  resetPreviewMedia() {
    if (this.previewMedia.blobUrl && typeof URL !== 'undefined') {
      URL.revokeObjectURL(this.previewMedia.blobUrl);
    }

    this.previewMedia = { type: '', url: '', name: '', content: '', loading: false, blobUrl: null };
  },
  async openFullscreen(type, url, name = '', content = '', modalId = 'documentPreviewModal') {
    this.resetPreviewMedia();

    this.previewMedia = {
      type: type || '',
      url: '',
      name: name || '',
      content: content || '',
      loading: type === 'pdf' && !!url,
      blobUrl: null
    };

    if (!modalId || !window.bootstrap) return;

    const modalEl = document.getElementById(modalId);
    if (!modalEl) return;

    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    if (type !== 'pdf' || !url) {
      this.previewMedia.loading = false;
      return;
    }

    try {
      const response = await fetch(url, { credentials: 'same-origin' });
      if (!response.ok) {
        throw new Error('Preview request failed');
      }

      const fileBlob = await response.blob();
      const pdfBlob = fileBlob.type === 'application/pdf' ? fileBlob : new Blob([fileBlob], { type: 'application/pdf' });
      const blobUrl = URL.createObjectURL(pdfBlob);

      this.previewMedia.url = blobUrl;
      this.previewMedia.blobUrl = blobUrl;
    } catch (error) {
      this.previewMedia.url = '';
    } finally {
      this.previewMedia.loading = false;
    }
  },
  confirmDelete(method, index) {
    const runDelete = () => $wire.call(method, index);
    if (window.Swal) {
      Swal.fire({
        icon: 'warning',
        title: @js(trans('service-order::messages.equipment_type_confirm_delete')),
        showCancelButton: true,
        confirmButtonText: @js(trans('service-order::messages.confirm_yes')),
        cancelButtonText: @js(trans('service-order::messages.confirm_no')),
        customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-danger' },
        buttonsStyling: false
      }).then(result => { if (result.isConfirmed) runDelete(); });
    } else if (confirm(@js(trans('service-order::messages.equipment_type_confirm_delete')))) {
      runDelete();
    }
  }
}">
  <div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">{{ trans('service-order::messages.equipment_type_form_title') }}</h5>
      <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#linkedItemPickerModal">{{ trans('service-order::messages.equipment_type_add_linked_item') }}</button>
    </div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label" for="equipmentTypeName">{{ trans('service-order::messages.equipment_type_name') }}</label>
          <input id="equipmentTypeName" type="text" class="form-control @error('name') is-invalid @enderror" maxlength="120" wire:model.defer="name" placeholder="{{ trans('service-order::messages.equipment_type_name_placeholder') }}">
          @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-12">
          <label class="form-label" for="equipmentTypeDescription">{{ trans('service-order::messages.equipment_type_description') }}</label>
          <textarea id="equipmentTypeDescription" class="form-control @error('description') is-invalid @enderror" rows="3" maxlength="2000" wire:model.defer="description"></textarea>
          @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
      </div>
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">{{ trans('service-order::messages.equipment_type_fixed_pdf_documents') }}</h6></div>
    <div class="card-body">
      @forelse ($pdfDocuments as $index => $document)
        @php
          $pdfUrl = !empty($document['temporary_preview_url']) ? (string) $document['temporary_preview_url'] : null;
          if (!$pdfUrl && ($document['file'] ?? null) && method_exists($document['file'], 'temporaryUrl')) {
            try {
              $pdfUrl = $document['file']->temporaryUrl();
            } catch (\Throwable $exception) {
              $pdfUrl = null;
            }
          } elseif (!$pdfUrl && !empty($document['id']) && !empty($document['existing_disk']) && !empty($document['existing_path'])) {
            $pdfUrl = route('service-order-equipment-types-document-file', ['id' => $document['id']]);
          } elseif (!$pdfUrl && !empty($document['existing_disk']) && !empty($document['existing_path'])) {
            try {
              $pdfUrl = \Illuminate\Support\Facades\Storage::disk($document['existing_disk'])->url($document['existing_path']);
            } catch (\Throwable $exception) {
              $pdfUrl = null;
            }
          }
        @endphp
        <div class="border rounded p-3 mb-2">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h6 class="mb-1">{{ $document['title'] ?: trans('service-order::messages.equipment_type_document_without_title') }}</h6>
              @if (!empty($document['description']))<small class="d-block mt-1">{{ $document['description'] }}</small>@endif
            </div>
            <div class="d-flex gap-1">
              @if (count($pdfDocuments) > 1)
                @if ($index > 0)
                  <button type="button" class="btn btn-sm btn-icon" wire:click="movePdfDocumentUp({{ $index }})" title="{{ trans('service-order::messages.move_up') }}"><i class="ti ti-arrow-up"></i></button>
                @endif
                @if ($index < count($pdfDocuments) - 1)
                  <button type="button" class="btn btn-sm btn-icon" wire:click="movePdfDocumentDown({{ $index }})" title="{{ trans('service-order::messages.move_down') }}"><i class="ti ti-arrow-down"></i></button>
                @endif
              @endif
              <button type="button" class="btn btn-sm btn-icon" wire:click="duplicatePdfDocument({{ $index }})" title="{{ trans('service-order::messages.duplicate') }}"><i class="ti ti-copy"></i></button>
              <button type="button" class="btn btn-sm btn-icon" wire:click="startEditPdfDocument({{ $index }})" title="{{ trans('service-order::messages.edit') }}"><i class="ti ti-pencil"></i></button>
              <button type="button" class="btn btn-sm btn-icon" x-on:click="confirmDelete('removePdfDocument', {{ $index }})" title="{{ trans('service-order::messages.delete') }}"><i class="ti ti-trash"></i></button>
            </div>
          </div>
          @if ($pdfUrl)
            <div class="mt-2"><button type="button" class="btn btn-sm btn-label-secondary" x-on:click="openFullscreen('pdf', @js($pdfUrl), @js($document['title'] ?? ''))">{{ trans('service-order::messages.equipment_type_view_document') }}</button></div>
          @endif
        </div>
      @empty
        <p class="text-muted mb-0">{{ trans('service-order::messages.equipment_type_empty_section') }}</p>
      @endforelse
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">{{ trans('service-order::messages.equipment_type_editable_documents') }}</h6></div>
    <div class="card-body">
      @forelse ($editableDocuments as $index => $document)
        <div class="border rounded p-3 mb-2">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h6 class="mb-1">{{ $document['title'] ?: trans('service-order::messages.equipment_type_document_without_title') }}</h6>
              <small class="text-muted d-block">{{ trans('service-order::messages.equipment_type_template_label') }}</small>
              <small class="d-block mt-1 text-truncate">{{ $document['content'] ?? '' }}</small>
            </div>
            <div class="d-flex gap-1">
              @if (count($editableDocuments) > 1)
                @if ($index > 0)
                  <button type="button" class="btn btn-sm btn-icon" wire:click="moveEditableDocumentUp({{ $index }})" title="{{ trans('service-order::messages.move_up') }}"><i class="ti ti-arrow-up"></i></button>
                @endif
                @if ($index < count($editableDocuments) - 1)
                  <button type="button" class="btn btn-sm btn-icon" wire:click="moveEditableDocumentDown({{ $index }})" title="{{ trans('service-order::messages.move_down') }}"><i class="ti ti-arrow-down"></i></button>
                @endif
              @endif
              <button type="button" class="btn btn-sm btn-icon" wire:click="duplicateEditableDocument({{ $index }})" title="{{ trans('service-order::messages.duplicate') }}"><i class="ti ti-copy"></i></button>
              <button type="button" class="btn btn-sm btn-icon" wire:click="startEditEditableDocument({{ $index }})" title="{{ trans('service-order::messages.edit') }}"><i class="ti ti-pencil"></i></button>
              <button type="button" class="btn btn-sm btn-icon" x-on:click="confirmDelete('removeEditableDocument', {{ $index }})" title="{{ trans('service-order::messages.delete') }}"><i class="ti ti-trash"></i></button>
            </div>
          </div>
          <div class="mt-2"><button type="button" class="btn btn-sm btn-label-secondary" x-on:click="openFullscreen('template', '', @js($document['title'] ?? ''), @js($document['content'] ?? ''))">{{ trans('service-order::messages.equipment_type_view_document') }}</button></div>
        </div>
      @empty
        <p class="text-muted mb-0">{{ trans('service-order::messages.equipment_type_empty_section') }}</p>
      @endforelse
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-header"><h6 class="mb-0">{{ trans('service-order::messages.equipment_type_text_fields') }}</h6></div>
    <div class="card-body">
      @forelse ($textFields as $index => $field)
        <div class="border rounded p-3 mb-2">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h6 class="mb-1">{{ $field['label'] ?? '' }}</h6>
              <small class="text-muted d-block">{{ ($field['placeholder'] ?? '') !== '' ? $field['placeholder'] : '-' }}</small>
            </div>
            <div class="d-flex gap-1">
              @if (count($textFields) > 1)
                @if ($index > 0)
                  <button type="button" class="btn btn-sm btn-icon" wire:click="moveTextFieldUp({{ $index }})" title="{{ trans('service-order::messages.move_up') }}"><i class="ti ti-arrow-up"></i></button>
                @endif
                @if ($index < count($textFields) - 1)
                  <button type="button" class="btn btn-sm btn-icon" wire:click="moveTextFieldDown({{ $index }})" title="{{ trans('service-order::messages.move_down') }}"><i class="ti ti-arrow-down"></i></button>
                @endif
              @endif
              <button type="button" class="btn btn-sm btn-icon" wire:click="duplicateTextField({{ $index }})" title="{{ trans('service-order::messages.duplicate') }}"><i class="ti ti-copy"></i></button>
              <button type="button" class="btn btn-sm btn-icon" wire:click="startEditTextField({{ $index }})" title="{{ trans('service-order::messages.edit') }}"><i class="ti ti-pencil"></i></button>
              <button type="button" class="btn btn-sm btn-icon" x-on:click="confirmDelete('removeTextField', {{ $index }})" title="{{ trans('service-order::messages.delete') }}"><i class="ti ti-trash"></i></button>
            </div>
          </div>
        </div>
      @empty
        <p class="text-muted mb-0">{{ trans('service-order::messages.equipment_type_empty_section') }}</p>
      @endforelse
    </div>
  </div>

  <div class="card mb-4">
    <div class="card-header"><h6 class="mb-0">{{ trans('service-order::messages.equipment_type_image_fields') }}</h6></div>
    <div class="card-body">
      @forelse ($imageFields as $index => $field)
        @php $imageUrl = !empty($field['existing_disk']) && !empty($field['existing_path']) ? \Illuminate\Support\Facades\Storage::disk($field['existing_disk'])->url($field['existing_path']) : null; @endphp
        <div class="border rounded p-3 mb-2">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h6 class="mb-1">{{ $field['label'] ?? '' }}</h6>
              <small class="text-muted d-block">{{ trans('service-order::messages.equipment_type_image_added_on_service_order') }}</small>
            </div>
            <div class="d-flex gap-1">
              @if (count($imageFields) > 1)
                @if ($index > 0)
                  <button type="button" class="btn btn-sm btn-icon" wire:click="moveImageFieldUp({{ $index }})" title="{{ trans('service-order::messages.move_up') }}"><i class="ti ti-arrow-up"></i></button>
                @endif
                @if ($index < count($imageFields) - 1)
                  <button type="button" class="btn btn-sm btn-icon" wire:click="moveImageFieldDown({{ $index }})" title="{{ trans('service-order::messages.move_down') }}"><i class="ti ti-arrow-down"></i></button>
                @endif
              @endif
              <button type="button" class="btn btn-sm btn-icon" wire:click="duplicateImageField({{ $index }})" title="{{ trans('service-order::messages.duplicate') }}"><i class="ti ti-copy"></i></button>
              <button type="button" class="btn btn-sm btn-icon" wire:click="startEditImageField({{ $index }})" title="{{ trans('service-order::messages.edit') }}"><i class="ti ti-pencil"></i></button>
              <button type="button" class="btn btn-sm btn-icon" x-on:click="confirmDelete('removeImageField', {{ $index }})" title="{{ trans('service-order::messages.delete') }}"><i class="ti ti-trash"></i></button>
            </div>
          </div>
        </div>
      @empty
        <p class="text-muted mb-0">{{ trans('service-order::messages.equipment_type_empty_section') }}</p>
      @endforelse
    </div>
  </div>

  <div class="d-flex justify-content-end gap-2 mb-4">
    <a href="{{ route('service-order-equipment-types-show') }}" class="btn btn-label-secondary">{{ trans('service-order::messages.cancel') }}</a>
    <button type="button" class="btn btn-primary" wire:click="save">{{ $mode === 'edit' ? trans('service-order::messages.update') : trans('service-order::messages.save') }}</button>
  </div>

  <div wire:ignore.self class="modal fade" id="linkedItemPickerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">{{ trans('service-order::messages.equipment_type_add_linked_item') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <div class="row g-2">
          <div class="col-12 col-md-6">
            <button type="button" class="btn btn-label-secondary text-start p-3 py-4 w-100 d-flex flex-column justify-content-between gap-2" data-bs-toggle="modal" data-bs-target="#addPdfDocumentModal" data-bs-dismiss="modal">
              <span class="d-inline-flex align-items-center justify-content-center rounded bg-white bg-opacity-75 border p-2 mb-2">
                <i class="ti ti-file-description fs-5"></i>
              </span>
              <div>
                <div class="fw-semibold">{{ trans('service-order::messages.equipment_type_fixed_pdf_documents') }}</div>
                <small class="text-muted">{{ trans('service-order::messages.equipment_type_add_action') }}</small>
              </div>
            </button>
          </div>
          <div class="col-12 col-md-6">
            <button type="button" class="btn btn-label-secondary text-start p-3 py-4 w-100 d-flex flex-column justify-content-between gap-2" data-bs-toggle="modal" data-bs-target="#addEditableDocumentModal" data-bs-dismiss="modal">
              <span class="d-inline-flex align-items-center justify-content-center rounded bg-white bg-opacity-75 border p-2 mb-2">
                <i class="ti ti-file-text fs-5"></i>
              </span>
              <div>
                <div class="fw-semibold">{{ trans('service-order::messages.equipment_type_editable_documents') }}</div>
                <small class="text-muted">{{ trans('service-order::messages.equipment_type_add_action') }}</small>
              </div>
            </button>
          </div>
          <div class="col-12 col-md-6">
            <button type="button" class="btn btn-label-secondary text-start p-3 py-4 w-100 d-flex flex-column justify-content-between gap-2" data-bs-toggle="modal" data-bs-target="#addTextFieldModal" data-bs-dismiss="modal">
              <span class="d-inline-flex align-items-center justify-content-center rounded bg-white bg-opacity-75 border p-2 mb-2">
                <i class="ti ti-forms fs-5"></i>
              </span>
              <div>
                <div class="fw-semibold">{{ trans('service-order::messages.equipment_type_text_fields') }}</div>
                <small class="text-muted">{{ trans('service-order::messages.equipment_type_add_action') }}</small>
              </div>
            </button>
          </div>
          <div class="col-12 col-md-6">
            <button type="button" class="btn btn-label-secondary text-start p-3 py-4 w-100 d-flex flex-column justify-content-between gap-2" data-bs-toggle="modal" data-bs-target="#addImageFieldModal" data-bs-dismiss="modal">
              <span class="d-inline-flex align-items-center justify-content-center rounded bg-white bg-opacity-75 border p-2 mb-2">
                <i class="ti ti-photo fs-5"></i>
              </span>
              <div>
                <div class="fw-semibold">{{ trans('service-order::messages.equipment_type_image_fields') }}</div>
                <small class="text-muted">{{ trans('service-order::messages.equipment_type_add_action') }}</small>
              </div>
            </button>
          </div>
        </div>
      </div>
    </div></div>
  </div>

  <div wire:ignore.self class="modal fade" id="addPdfDocumentModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">{{ $editingPdfIndex !== null ? trans('service-order::messages.edit') : trans('service-order::messages.equipment_type_add_pdf_modal_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">{{ trans('service-order::messages.equipment_type_document_title') }}</label><input type="text" class="form-control @error('newPdfTitle') is-invalid @enderror" wire:model.defer="newPdfTitle">@error('newPdfTitle')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="mb-3"><label class="form-label">{{ trans('service-order::messages.equipment_type_document_description') }}</label><textarea class="form-control @error('newPdfDescription') is-invalid @enderror" wire:model.defer="newPdfDescription"></textarea></div><div><label class="form-label">{{ trans('service-order::messages.equipment_type_pdf_file') }}</label><input type="file" class="form-control @error('newPdfFile') is-invalid @enderror" accept="application/pdf" wire:model="newPdfFile">@error('newPdfFile')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div><div class="modal-footer"><button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ trans('service-order::messages.cancel') }}</button><button type="button" class="btn btn-primary" wire:click="createPdfDocumentFromModal">{{ $editingPdfIndex !== null ? trans('service-order::messages.update') : trans('service-order::messages.add') }}</button></div></div></div></div>

  <div wire:ignore.self class="modal fade" id="addEditableDocumentModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">{{ $editingEditableIndex !== null ? trans('service-order::messages.edit') : trans('service-order::messages.equipment_type_add_template_modal_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="row g-3"><div class="col-12 col-md-6"><label class="form-label">{{ trans('service-order::messages.equipment_type_document_title') }}</label><input type="text" class="form-control @error('newEditableTitle') is-invalid @enderror" wire:model.defer="newEditableTitle"></div><div class="col-12 col-md-6"><label class="form-label">{{ trans('service-order::messages.equipment_type_variables_hint') }}</label><input type="text" class="form-control @error('newEditableVariables') is-invalid @enderror" wire:model.defer="newEditableVariables"></div><div class="col-12"><label class="form-label">{{ trans('service-order::messages.equipment_type_document_description') }}</label><textarea class="form-control" wire:model.defer="newEditableDescription"></textarea></div><div class="col-12"><label class="form-label">{{ trans('service-order::messages.equipment_type_template_content') }}</label><textarea class="form-control @error('newEditableContent') is-invalid @enderror" rows="5" wire:model.defer="newEditableContent"></textarea></div></div></div><div class="modal-footer"><button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ trans('service-order::messages.cancel') }}</button><button type="button" class="btn btn-primary" wire:click="createEditableDocumentFromModal">{{ $editingEditableIndex !== null ? trans('service-order::messages.update') : trans('service-order::messages.add') }}</button></div></div></div></div>

  <div wire:ignore.self class="modal fade" id="addTextFieldModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">{{ $editingTextIndex !== null ? trans('service-order::messages.edit') : trans('service-order::messages.equipment_type_add_text_field_modal_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">{{ trans('service-order::messages.equipment_type_field_label') }}</label><input type="text" class="form-control @error('newTextLabel') is-invalid @enderror" wire:model.defer="newTextLabel"></div><div class="mb-3"><label class="form-label">{{ trans('service-order::messages.equipment_type_field_placeholder') }}</label><input type="text" class="form-control" wire:model.defer="newTextPlaceholder"></div><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="newTextRequired" wire:model.defer="newTextRequired"><label class="form-check-label" for="newTextRequired">{{ trans('service-order::messages.required_field') }}</label></div></div><div class="modal-footer"><button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ trans('service-order::messages.cancel') }}</button><button type="button" class="btn btn-primary" wire:click="createTextFieldFromModal">{{ $editingTextIndex !== null ? trans('service-order::messages.update') : trans('service-order::messages.add') }}</button></div></div></div></div>

  <div wire:ignore.self class="modal fade" id="addImageFieldModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">{{ $editingImageIndex !== null ? trans('service-order::messages.edit') : trans('service-order::messages.equipment_type_add_image_field_modal_title') }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">{{ trans('service-order::messages.equipment_type_field_label') }}</label><input type="text" class="form-control @error('newImageLabel') is-invalid @enderror" wire:model.defer="newImageLabel"></div><div class="alert alert-secondary mb-3">{{ trans('service-order::messages.equipment_type_image_added_on_service_order') }}</div><div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="newImageRequired" wire:model.defer="newImageRequired"><label class="form-check-label" for="newImageRequired">{{ trans('service-order::messages.required_field') }}</label></div></div><div class="modal-footer"><button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">{{ trans('service-order::messages.cancel') }}</button><button type="button" class="btn btn-primary" wire:click="createImageFieldFromModal">{{ $editingImageIndex !== null ? trans('service-order::messages.update') : trans('service-order::messages.add') }}</button></div></div></div></div>

  <div wire:ignore.self class="modal fade" id="documentPreviewModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-fullscreen"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" x-text="previewMedia.name || @js(trans('service-order::messages.equipment_type_document_preview_title'))"></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" :class="previewMedia.type === 'pdf' ? 'bg-dark' : ''"><template x-if="previewMedia.type === 'pdf' && previewMedia.loading"><div class="h-100 d-flex align-items-center justify-content-center"><div class="spinner-border text-light" role="status"><span class="visually-hidden">Loading...</span></div></div></template><template x-if="previewMedia.type === 'pdf' && !previewMedia.loading && previewMedia.url"><div class="h-100"><iframe :src="previewMedia.url" class="w-100 h-100 border-0"></iframe></div></template><template x-if="previewMedia.type === 'template'"><div class="container-fluid py-3"><div class="card shadow-none border"><div class="card-body"><pre class="mb-0" style="white-space: pre-wrap;" x-text="previewMedia.content || ''"></pre></div></div></div></template><template x-if="previewMedia.type === 'pdf' && !previewMedia.loading && !previewMedia.url"><div class="alert alert-secondary mb-0">{{ trans('service-order::messages.equipment_type_pending_file_preview') }}</div></template></div></div></div></div>
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

  document.addEventListener('hidden.bs.modal', event => {
    if (document.querySelectorAll('.modal.show').length > 0) {
      document.body.classList.add('modal-open');
    }

    if (event.target?.id === 'documentPreviewModal') {
      window.dispatchEvent(new CustomEvent('equipment-type-preview-reset'));
    }
  });

  Livewire.on('equipment-type-modal-close', event => {
    const payload = Array.isArray(event) ? event[0] : event;
    const modalEl = document.getElementById(payload?.modalId);
    if (!modalEl || !window.bootstrap) return;
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();
  });
  Livewire.on('equipment-type-modal-open', event => {
    const payload = Array.isArray(event) ? event[0] : event;
    const modalEl = document.getElementById(payload?.modalId);
    if (!modalEl || !window.bootstrap) return;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  });
});
</script>
@endscript
