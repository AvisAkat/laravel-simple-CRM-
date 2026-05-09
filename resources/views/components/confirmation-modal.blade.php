{{-- General Confirmation Modal --}}
<div id="confirmationModal" class="modal {{ $isConfirmationModal ? 'active' : '' }}">
    <div class="modal-content lead-card" style="padding: 20px">

        <div class="" style="text-align: center; margin-bottom: 12px;">
            <div class="lead-info ">
                <h3>{{ $confirmationTitle }}?</h3>
                <p>{{ $confirmationMessage }}</p>
            </div>
        </div>
        <div class="lead-actions" style="justify-content: center; gap: 12px; margin-top: 20px;">
            <button class="btn btn-primary btn-sm" wire:click="closeConfirmationModal()">
                <i class="fas fa-cancel"></i> No
            </button>
            <button class="btn btn-danger btn-sm" wire:click="{{ $confirmationMethod.'('. $Id .')' }}">
                <i class="fas fa-trash"></i> Yes
            </button>
        </div>

    </div>
</div>