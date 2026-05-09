<?php

use Livewire\Component;
use App\Models\Lead;
use App\Models\Customer;

new class extends Component {
    public $isLeadModalOpen = false;
    public $isLeadEditMode = false;
    public $isShowDeleteConfirmationModal = false;
    //General Confirmation Modal
    public $isConfirmationModal = false;
    public $confirmationTitle, $confirmationMessage, $confirmationMethod, $Id;

    public $leadId, $leadName, $leadEmail, $leadPhone, $leadCompany, $leadStatus, $leadNotes;

    //Delete Confirmation Modal
    public $deleteId, $deleteName, $deleteMethod;

    public function openDeleteConfirmationModal($Id, $name = null, $method)
    {
        $this->deleteId = $Id;
        $this->deleteName = $name;
        $this->deleteMethod = $method;
        $this->isShowDeleteConfirmationModal = true;
    }

    public function closeDeleteConfirmationModal()
    {
        $this->deleteId = $this->deleteName = $this->deleteMethod = null;
        $this->isShowDeleteConfirmationModal = false;
    }

    //General Confirmation Modal
    public function openConfirmationModal($Id, $name = null, $method)
    {

        $this->Id = $Id;
        $this->confirmationTitle = "Are you sure you want to convert $name to Customer";
        $this->confirmationMessage = "This action will move all lead information to a new customer record. You can edit the customer details after conversion.";
        $this->confirmationMethod = $method;
        $this->isConfirmationModal = true;
    }

    public function closeConfirmationModal()
    {
        $this->Id = $this->confirmationTitle = $this->confirmationMessage = $this->confirmationMethod = null;
        $this->isConfirmationModal = false;
    }

    public function openLeadModal($leadId = null)
    {
        if ($leadId) {
            $lead = Lead::findorFail($leadId);

            $this->leadId = $leadId;
            $this->leadName = $lead->full_name;
            $this->leadEmail = $lead->email;
            $this->leadPhone = $lead->phone;
            $this->leadCompany = $lead->company;
            $this->leadStatus = $lead->status;
            $this->leadNotes = $lead->notes;

            $this->isLeadEditMode = true;

        } else {
            $this->leadId = $this->leadName = $this->leadEmail = $this->leadPhone = $this->leadCompany = $this->leadStatus = $this->leadNotes = null;
            $this->isLeadEditMode = false;
        }
        $this->isLeadModalOpen = true;
    }

    public function closeLeadModal()
    {
        $this->leadId = $this->leadName = $this->leadEmail = $this->leadPhone = $this->leadCompany = $this->leadStatus = $this->leadNotes = null;
        $this->isLeadModalOpen = false;
    }

    public function createLead()
    {
        $validatedData = $this->validate([
            'leadName' => 'required|string|max:255',
            'leadEmail' => 'required|email|unique:leads,email',
            'leadPhone' => 'nullable|string|max:20',
            'leadCompany' => 'nullable|string|max:255',
            'leadStatus' => 'required|string|in:new,contacted,qualified,proposal',
            'leadNotes' => 'nullable|string',
        ], [
            'leadName.required' => 'Please enter the full name.',
            'leadEmail.required' => 'Please enter the email address.',
            'leadEmail.email' => 'Please enter a valid email address.',
            'leadEmail.unique' => 'This email is already associated with another lead.',
            'leadStatus.required' => 'Please select a status for the lead.',
            'leadStatus.in' => 'Invalid status selected. Please choose from new, contacted, qualified, or proposal.',
        ]);

        $created = Lead::create([
            'full_name' => $validatedData['leadName'],
            'email' => $validatedData['leadEmail'],
            'phone' => $validatedData['leadPhone'],
            'company' => $validatedData['leadCompany'],
            'status' => $validatedData['leadStatus'],
            'notes' => $validatedData['leadNotes'],
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        if ($created) {
            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Lead added successfully.'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to add lead. Please try again.'
            );
        }

        $this->closeLeadModal();
    }

    public function allLeads()
    {
        return Lead::where('converted', false)->orderBy('created_at', 'desc')->get();
    }

    public function updateLead()
    {
        $validatedData = $this->validate([
            'leadName' => 'required|string|max:255',
            'leadEmail' => 'required|email|unique:leads,email,' . $this->leadId,
            'leadPhone' => 'nullable|string|max:20',
            'leadCompany' => 'nullable|string|max:255',
            'leadStatus' => 'required|string|in:new,contacted,qualified,proposal',
            'leadNotes' => 'nullable|string',
        ], [
            'leadName.required' => 'Please enter the full name.',
            'leadEmail.required' => 'Please enter the email address.',
            'leadEmail.email' => 'Please enter a valid email address.',
            'leadEmail.unique' => 'This email is already associated with another lead.',
            'leadStatus.required' => 'Please select a status for the lead.',
            'leadStatus.in' => 'Invalid status selected. Please choose from new, contacted, qualified, or proposal.',
        ]);

        $lead = Lead::findorFail($this->leadId);

        $updated = $lead->update([
            'full_name' => $validatedData['leadName'],
            'email' => $validatedData['leadEmail'],
            'phone' => $validatedData['leadPhone'],
            'company' => $validatedData['leadCompany'],
            'status' => $validatedData['leadStatus'],
            'notes' => $validatedData['leadNotes'],
            'updated_by' => auth()->user()->id,
        ]);

        if ($updated) {
            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Lead updated successfully.'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to update lead. Please try again.'
            );
        }

        $this->closeLeadModal();
    }

    public function deleteLead()
    {
        $lead = Lead::findorFail($this->deleteId);

        if ($lead->delete()) {
            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Lead deleted successfully.'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to delete lead. Please try again.'
            );
        }

        $this->closeDeleteConfirmationModal();
    }

    public function convertLead($Id)
    {
        $lead = Lead::findorFail($Id);

        // Create a new customer record based on the lead information
        $created = Customer::create([
            'full_name' => $lead->full_name,
            'email' => $lead->email,
            'phone' => $lead->phone,
            'company' => $lead->company,
            'notes' => $lead->notes,
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        if (!$created) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to convert lead. Please try again.'
            );
            $this->closeConfirmationModal();
            return;
        }
        $lead->converted = true;

        if ($lead->save()) {
            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Lead converted to customer successfully.'
            );

            $this->closeConfirmationModal();
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to convert lead. Please try again.'
            );
            $this->closeConfirmationModal();
        }
    }


};
?>

<div>
    <!-- Leads Section -->
    <section class="content-section" id="leads">
        <div class="section-header">
            <h1 class="section-title">Leads</h1>
            <button class="btn btn-primary" id="addLeadBtn" wire:click="openLeadModal()">
                <i class="fas fa-plus"></i> Add Lead
            </button>
        </div>

        <!-- Leads Grid -->
        <div class="leads-grid" id="leadsGrid">
            @foreach($this->allLeads() as $lead)
                <div class="lead-card">
                    <div class="lead-header">
                        <div class="lead-info">
                            <h3>{{ $lead->full_name }}</h3>
                            <p>{{ $lead->company }}</p>
                        </div>
                        <span class="lead-status {{ $lead->status }}">{{ ucfirst($lead->status) }}</span>
                    </div>
                    <div class="lead-details">
                        <p><i class="fas fa-envelope"></i> {{ $lead->email }}</p>
                        <p><i class="fas fa-phone"></i> {{ $lead->phone }}</p>
                        <p><i class="fas fa-sticky-note"></i> {{ $lead->notes }}</p>
                    </div>
                    <div class="lead-actions">
                        <button class="btn btn-primary btn-sm"
                            wire:click="openConfirmationModal({{ $lead->id }}, '{{ $lead->full_name }}', 'convertLead')">
                            <i class="fas fa-check"></i> Convert
                        </button>
                        <button class="btn btn-secondary btn-sm" wire:click="openLeadModal({{ $lead->id }})">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-sm"
                            wire:click="openDeleteConfirmationModal({{ $lead->id }}, 'Lead', 'deleteLead')">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </section>


    <!-- Add/Edit Lead Modal -->
    <div class="modal {{ $isLeadModalOpen ? 'active' : '' }} " id="leadModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="leadModalTitle">{{ $isLeadEditMode ? 'Update' : 'Add' }} Lead</h2>
                <button class="close-btn" id="closeLeadModal" wire:click="closeLeadModal()">&times;</button>
            </div>
            <form wire:submit="{{ $isLeadEditMode ? 'updateLead()' : 'createLead()' }}" id="leadForm">
                @if ($isLeadEditMode)
                    <input type="hidden" id="leadId" wire:model="leadId">
                @endif
                <div class="form-group">
                    <label for="leadName">Full Name *</label>
                    <input type="text" wire:model="leadName" id="leadName" value="{{ old('leadName') }}"
                        placeholder="Enter full name">
                    @error('leadName')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="leadEmail">Email *</label>
                    <input type="email" wire:model="leadEmail" id="leadEmail" value="{{ old('leadEmail') }}"
                        placeholder="Enter email">
                    @error('leadEmail')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="leadPhone">Phone Number</label>
                    <input type="tel" wire:model="leadPhone" id="leadPhone" value="{{ old('leadPhone') }}"
                        placeholder="Enter phone number">
                    @error('leadPhone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="leadCompany">Company Name</label>
                    <input type="text" wire:model="leadCompany" id="leadCompany" value="{{ old('leadCompany') }}"
                        placeholder="Enter company name">
                    @error('leadCompany')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="leadStatus">Status</label>
                    <select id="leadStatus" wire:model="leadStatus">
                        <option value="">Select ....</option>
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="qualified">Qualified</option>
                        <option value="proposal">Proposal Sent</option>
                    </select>
                    @error('leadStatus')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="leadNotes">Notes</label>
                    <textarea id="leadNotes" wire:model="leadNotes" rows="3"
                        placeholder="Add any notes...">{{ old('leadNotes') }}</textarea>
                    @error('leadNotes')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelLeadBtn"
                        wire:click="closeLeadModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">{{ $isLeadEditMode ? 'Save' : 'Add Lead' }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @include('components.delete-confirmation-modal', [
        'isShowDeleteConfirmationModal' => $isShowDeleteConfirmationModal,
        'deleteName' => $deleteName,
        'deleteMethod' => $deleteMethod,
        'deleteId' => $deleteId,
    ])


     {{-- General Confirmation Modal --}}
    @include('components.confirmation-modal', [
        'isConfirmationModal' => $isConfirmationModal,
        'confirmationTitle' => $confirmationTitle,
        'confirmationMessage' => $confirmationMessage,
        'confirmationMethod' => $confirmationMethod,
        'Id' => $Id,
    ])
</div>