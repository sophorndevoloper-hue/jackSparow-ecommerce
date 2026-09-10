@props([
    'prefix' => 'brand',
    'label' => null,
    'currentLogo' => null,
    'entityName' => null,
    'inputName' => 'logo',
    'removeInputName' => 'remove_logo',
    'accept' => 'image/png,image/jpeg,image/webp,image/svg+xml',
    'aspectRatio' => 1,
])

@php
    $prefix = strtolower($prefix);
    $capPrefix = ucfirst($prefix);
    $label = $label ?? ($capPrefix . ' Logo (Image)');
    $modalId = $prefix . 'CropperModal';
    $targetImageId = ($prefix === 'make') ? 'cropperTargetImage' : ($prefix . 'CropperTargetImage');
    $fileInputId = $prefix . 'LogoInput';
    $removeInputId = ($prefix === 'make') ? 'removeLogoInput' : ('remove' . $capPrefix . 'LogoInput');
    $previewCardId = ($prefix === 'make') ? 'logoPreviewCard' : ($prefix . 'LogoPreviewCard');
    $previewImgId = ($prefix === 'make') ? 'logoPreviewImg' : ($prefix . 'LogoPreviewImg');
    $previewNameId = ($prefix === 'make') ? 'logoPreviewName' : ($prefix . 'LogoPreviewName');
    $previewMetaId = ($prefix === 'make') ? 'logoPreviewMeta' : ($prefix . 'LogoPreviewMeta');
    $statusBadgeId = ($prefix === 'make') ? 'logoStatusBadge' : ($prefix . 'LogoStatusBadge');
    $controllerKey = 'cropper_' . $prefix;
@endphp

@once
<!-- Cropper.js Stylesheet -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css">
<style>
    .image-cropper-workspace {
        min-height: 380px;
        max-height: 480px;
        background-color: #0b132b;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        border-radius: 8px;
    }
    .image-cropper-workspace img {
        max-width: 100%;
        max-height: 460px;
        display: block;
    }
    .cropper-modal-btn {
        background-color: #1e293b;
        color: #f8fafc;
        border: 1px solid #334155;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.15s ease;
    }
    .cropper-modal-btn:hover {
        background-color: #334155;
        color: #ffffff;
        border-color: #475569;
    }
    .cropper-modal-btn.active {
        background-color: #0d6efd;
        color: #ffffff;
        border-color: #0d6efd;
    }
    .cropper-view-box, .cropper-face {
        border-radius: 4px;
    }
</style>
<!-- Cropper.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
@endonce

<div class="{{ $prefix }}-logo-upload-wrapper">
    <label class="form-label fw-bold small mb-1">{{ $label }}</label>
    
    <!-- File Input -->
    <input type="file" name="{{ $inputName }}" id="{{ $fileInputId }}" class="form-control" accept="{{ $accept }}">
    <div class="form-text small">Recommended: PNG, WEBP, SVG, or JPG (Max 2MB).</div>

    <!-- Hidden input to flag removal of existing logo on edit -->
    <input type="hidden" name="{{ $removeInputName }}" id="{{ $removeInputId }}" value="0">

    <!-- Live Preview Card -->
    <div id="{{ $previewCardId }}" class="mt-3 p-3 border rounded bg-light shadow-sm" style="{{ $currentLogo ? '' : 'display: none !important;' }}">
        <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded border p-1 bg-white shadow-sm d-flex align-items-center justify-content-center overflow-hidden" style="width: 60px; height: 60px; min-width: 60px;">
                    <img id="{{ $previewImgId }}" src="{{ $currentLogo ?? '' }}" alt="Logo Preview" class="w-100 h-100 object-fit-contain">
                </div>
                <div>
                    <strong class="d-block small text-dark" id="{{ $previewNameId }}">{{ $currentLogo ? ($entityName ?? 'Current Logo') : 'Selected Logo' }}</strong>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge bg-success-subtle text-success border border-success-subtle" id="{{ $statusBadgeId }}">
                            {{ $currentLogo ? 'Current Logo' : 'Ready' }}
                        </span>
                        <small class="text-muted" id="{{ $previewMetaId }}"></small>
                    </div>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-primary d-flex align-items-center gap-1" onclick="window.{{ $controllerKey }}.open()">
                    <i class="bi bi-crop"></i> Crop &amp; Edit
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.{{ $controllerKey }}.remove()" title="Remove logo">
                    <i class="bi bi-trash"></i> Remove
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CROPPER & IMAGE EDITOR MODAL -->
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg text-white" style="background-color: #0f172a;">
            <div class="modal-header border-bottom border-secondary" style="background-color: #020617;">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="{{ $modalId }}Label">
                    <i class="bi bi-crop text-primary"></i> Edit &amp; Crop {{ $capPrefix }} Logo
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-3">
                <!-- Workspace -->
                <div class="image-cropper-workspace {{ $prefix }}-cropper-workspace shadow-inner mb-3">
                    <img id="{{ $targetImageId }}" src="" alt="Cropper Workspace">
                </div>

                <!-- Control Toolbar -->
                <div class="row g-2 align-items-center">
                    <!-- Ratio Buttons -->
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px;">Aspect Ratio</label>
                        <div class="btn-group btn-group-sm w-100">
                            <button type="button" class="btn cropper-modal-btn active" onclick="window.{{ $controllerKey }}.setRatio(1, this)">
                                1:1 (Square)
                            </button>
                            <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.setRatio(4/3, this)">
                                4:3
                            </button>
                            <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.setRatio(16/9, this)">
                                16:9
                            </button>
                            <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.setRatio(NaN, this)">
                                Free
                            </button>
                        </div>
                    </div>

                    <!-- Transform Tools -->
                    <div class="col-12 col-md-6">
                        <label class="form-label text-muted small text-uppercase fw-bold mb-1" style="font-size: 11px;">Transformations</label>
                        <div class="d-flex gap-1 flex-wrap">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.rotate(-90)" title="Rotate Counter-Clockwise">
                                    <i class="bi bi-arrow-counterclockwise"></i> -90&deg;
                                </button>
                                <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.rotate(90)" title="Rotate Clockwise">
                                    <i class="bi bi-arrow-clockwise"></i> +90&deg;
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.flip('h')" title="Flip Horizontal">
                                    <i class="bi bi-symmetry-vertical"></i> Flip H
                                </button>
                                <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.flip('v')" title="Flip Vertical">
                                    <i class="bi bi-symmetry-horizontal"></i> Flip V
                                </button>
                            </div>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.zoom(0.1)" title="Zoom In">
                                    <i class="bi bi-zoom-in"></i>
                                </button>
                                <button type="button" class="btn cropper-modal-btn" onclick="window.{{ $controllerKey }}.zoom(-0.1)" title="Zoom Out">
                                    <i class="bi bi-zoom-out"></i>
                                </button>
                                <button type="button" class="btn cropper-modal-btn text-warning" onclick="window.{{ $controllerKey }}.reset()" title="Reset">
                                    <i class="bi bi-arrow-repeat"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top border-secondary justify-content-between" style="background-color: #020617;">
                <button type="button" class="btn btn-outline-secondary btn-sm text-white" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm px-4 fw-bold text-white shadow" onclick="window.{{ $controllerKey }}.applySave()">
                    <i class="bi bi-check2-circle me-1"></i> Apply &amp; Save Logo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const prefix = '{{ $prefix }}';
    const capPrefix = '{{ $capPrefix }}';
    let instance = null;
    let flipH = 1;
    let flipV = 1;
    let rawImageSrc = @json($currentLogo ?? '');
    let fileName = @json($entityName ? str()->slug($entityName).'-logo.png' : 'logo.png');

    const fileInput = document.getElementById('{{ $fileInputId }}');
    const previewCard = document.getElementById('{{ $previewCardId }}');
    const previewImg = document.getElementById('{{ $previewImgId }}');
    const previewName = document.getElementById('{{ $previewNameId }}');
    const previewMeta = document.getElementById('{{ $previewMetaId }}');
    const statusBadge = document.getElementById('{{ $statusBadgeId }}');
    const removeInput = document.getElementById('{{ $removeInputId }}');
    const modalEl = document.getElementById('{{ $modalId }}');
    const targetImg = document.getElementById('{{ $targetImageId }}');

    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;

            fileName = file.name;
            if (removeInput) removeInput.value = '0';

            const reader = new FileReader();
            reader.onload = function(evt) {
                rawImageSrc = evt.target.result;
                if (previewImg) previewImg.src = rawImageSrc;
                if (previewName) previewName.innerText = file.name;
                if (previewMeta) previewMeta.innerText = (file.size / 1024).toFixed(1) + ' KB';
                if (statusBadge) {
                    statusBadge.innerText = 'New Upload';
                    statusBadge.className = 'badge bg-primary-subtle text-primary border border-primary-subtle';
                }
                if (previewCard) previewCard.style.removeProperty('display');
            };
            reader.readAsDataURL(file);
        });
    }

    const controller = {
        open: function() {
            if (!rawImageSrc) {
                alert('Please select an image first.');
                return;
            }
            if (targetImg) targetImg.src = rawImageSrc;

            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            modalEl.addEventListener('shown.bs.modal', function onShown() {
                modalEl.removeEventListener('shown.bs.modal', onShown);

                if (instance) instance.destroy();
                flipH = 1;
                flipV = 1;

                instance = new Cropper(targetImg, {
                    aspectRatio: {{ $aspectRatio }},
                    viewMode: 1,
                    autoCropArea: 0.9,
                    responsive: true,
                    restore: false,
                    checkCrossOrigin: false,
                    background: true,
                });
            });
        },
        setRatio: function(ratio, btn) {
            if (!instance) return;
            instance.setAspectRatio(ratio);
            modalEl.querySelectorAll('.cropper-modal-btn').forEach(b => {
                if (b.innerText.includes('1:1') || b.innerText.includes('4:3') || b.innerText.includes('16:9') || b.innerText.includes('Free')) {
                    b.classList.remove('active');
                }
            });
            if (btn) btn.classList.add('active');
        },
        rotate: function(deg) {
            if (instance) instance.rotate(deg);
        },
        flip: function(axis) {
            if (!instance) return;
            if (axis === 'h') {
                flipH = -flipH;
                instance.scaleX(flipH);
            } else {
                flipV = -flipV;
                instance.scaleY(flipV);
            }
        },
        zoom: function(ratio) {
            if (instance) instance.zoom(ratio);
        },
        reset: function() {
            if (instance) {
                instance.reset();
                flipH = 1;
                flipV = 1;
            }
        },
        applySave: function() {
            if (!instance) return;

            const canvas = instance.getCroppedCanvas({
                maxWidth: 1600,
                maxHeight: 1600,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (!canvas) return;

            canvas.toBlob(function(blob) {
                if (!blob) return;

                let baseName = fileName.replace(/\.[^/.]+$/, "") || (prefix + '-logo');
                let newFileName = baseName + '-cropped.png';

                const editedFile = new File([blob], newFileName, {
                    type: 'image/png',
                    lastModified: Date.now()
                });

                try {
                    const dt = new DataTransfer();
                    dt.items.add(editedFile);
                    if (fileInput) fileInput.files = dt.files;
                } catch (e) {
                    console.warn('DataTransfer not supported', e);
                }

                const croppedDataUrl = canvas.toDataURL('image/png');
                rawImageSrc = croppedDataUrl;
                if (previewImg) previewImg.src = croppedDataUrl;
                if (previewName) previewName.innerText = newFileName;
                if (previewMeta) previewMeta.innerText = (blob.size / 1024).toFixed(1) + ' KB (Cropped)';
                if (statusBadge) {
                    statusBadge.innerText = 'Cropped & Ready';
                    statusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle';
                }

                if (removeInput) removeInput.value = '0';
                if (previewCard) previewCard.style.removeProperty('display');

                const modalInstance = bootstrap.Modal.getInstance(modalEl);
                if (modalInstance) modalInstance.hide();
            }, 'image/png', 0.95);
        },
        remove: function() {
            if (fileInput) fileInput.value = '';
            rawImageSrc = '';
            if (previewImg) previewImg.src = '';
            if (previewCard) previewCard.style.display = 'none';
            if (removeInput) removeInput.value = '1';
        }
    };

    window['{{ $controllerKey }}'] = controller;

    // Backward-compatible global function aliases
    window['open' + capPrefix + 'Cropper'] = function() { controller.open(); };
    window['removeSelected' + (prefix === 'make' ? '' : capPrefix) + 'Logo'] = function() { controller.remove(); };
    window['apply' + (prefix === 'make' ? '' : capPrefix) + 'CropperSave'] = function() { controller.applySave(); };
})();
</script>

