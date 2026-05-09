{{-- Delete Confirmation Modal --}}
<div id="deleteConfirmationModal" class="modal {{ $isShowDeleteConfirmationModal ? 'active' : '' }}">
    <div class="modal-content lead-card" style="padding: 20px">

        <div class="" style="text-align: center; margin-bottom: 12px;">
            <div class="lead-info ">
                <h3>Are you sure you want to delete {{ $deleteName }}?</h3>
            </div>
        </div>
        <div class="lead-actions" style="justify-content: center; gap: 12px; margin-top: 20px;">
            <button class="btn btn-primary btn-sm" wire:click="closeDeleteConfirmationModal()">
                <i class="fas fa-cancel"></i> No, Keep It
            </button>
            <button class="btn btn-danger btn-sm" wire:click="{{ $deleteMethod.'('. $deleteId .')' }}">
                <i class="fas fa-trash"></i> Yes, Delete
            </button>
        </div>

    </div>
</div>