<!-- Global Confirmation Modal -->
<div class="modal fade confirm-modal" id="globalConfirmModal" tabindex="-1" aria-labelledby="confirmModalTitle" aria-hidden="true" data-bs-backdrop="static">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg border-0">
      <div class="modal-body p-4 text-center">
        <!-- Close button top-right -->
        <button type="button" class="btn-close position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>

        <!-- Dynamic Icon Badge -->
        <div class="confirm-icon-wrapper mx-auto mb-3" id="confirmModalIconWrapper">
          <i class="bi bi-trash3-fill" id="confirmModalIcon"></i>
        </div>

        <!-- Title -->
        <h5 class="modal-title fw-bold mb-2" id="confirmModalTitle">Confirm Action</h5>

        <!-- Message Body -->
        <div class="confirm-modal-body mb-3 text-secondary" id="confirmModalMessage">
          Are you sure you want to proceed with this action?
        </div>

        <!-- Optional Warning / Impact Box -->
        <div class="alert confirm-impact-box text-start small py-2 px-3 mb-4 d-none" id="confirmModalDetail">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-octagon-fill flex-shrink-0"></i>
            <span id="confirmModalDetailText">This action cannot be undone.</span>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-secondary px-4 fw-medium" data-bs-dismiss="modal" id="confirmModalCancelBtn">
            Cancel
          </button>
          <button type="button" class="btn btn-danger px-4 fw-medium d-inline-flex align-items-center justify-content-center" id="confirmModalConfirmBtn">
            <span class="spinner-border spinner-border-sm me-2 d-none" id="confirmModalSpinner" role="status" aria-hidden="true"></span>
            <span id="confirmModalConfirmBtnText">Confirm</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

