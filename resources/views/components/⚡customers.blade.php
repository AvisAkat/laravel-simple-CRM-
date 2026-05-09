<?php

use Livewire\Component;
use App\Models\Customer;


new class extends Component {
    public $isCustomerModalOpen = false;
    public $isCustomerEditMode = false;
    public $isShowDeleteConfirmationModal = false;

    public $customerId, $customerName, $customerEmail, $customerPhone, $customerCompany, $customerStatus, $customerPriority, $customerNotes;

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

    public function openCustomerModal($customerId = null)
    {
        if ($customerId) {
            $this->isCustomerEditMode = true;
            // Load customer data for editing (not implemented in this snippet)
            $customer = Customer::findOrFail($customerId);

            $this->customerId = $customerId;
            $this->customerName = $customer->full_name;
            $this->customerEmail = $customer->email;
            $this->customerPhone = $customer->phone;
            $this->customerCompany = $customer->company;
            $this->customerStatus = $customer->status;
            $this->customerPriority = $customer->priority;
            $this->customerNotes = $customer->notes;


        } else {
            $this->customerId = $this->customerName = $this->customerEmail = $this->customerPhone = $this->customerCompany = $this->customerStatus = $this->customerPriority = $this->customerNotes = null;
            $this->isCustomerEditMode = false;
        }
        $this->isCustomerModalOpen = true;
    }

    public function closeCustomerModal()
    {
        $this->isCustomerModalOpen = false;
    }

    public function createCustomer()
    {
        // Validate
        $customer = $this->validate([
            'customerName' => 'required|string|max:255',
            'customerEmail' => 'required|email|unique:customers,email',
            'customerPhone' => 'nullable|string|max:10',
            'customerCompany' => 'nullable|string|max:255',
            'customerStatus' => 'required|in:lead,active,closed',
            'customerPriority' => 'required|max:255|in:high,medium,low',
            'customerNotes' => 'nullable|string',
        ], [
            'customerName.required' => 'Full name is required.',
            'customerEmail.required' => 'Email is required.',
            'customerEmail.email' => 'Please enter a valid email address.',
            'customerEmail.unique' => 'This email is already associated with another customer.',
            'customerPhone.max' => 'The phone number cannot exceed 10 characters.',
            'customerCompany.max' => 'The company name cannot exceed 255 characters.',
            'customerStatus.required' => 'Please select a status for the customer.',
            'customerStatus.in' => 'Invalid status selected. Please choose lead, active, or closed.',
            'customerPriority.required' => 'Please select a priority level for the customer.',
            'customerPriority.in' => 'Invalid priority level selected. Please choose high, medium, or low.',
            'customerNotes.max' => 'The notes cannot exceed 255 characters.',
        ]);

        // Create customer
        $created = Customer::create([
            'full_name' => $customer['customerName'],
            'email' => $customer['customerEmail'],
            'phone' => $customer['customerPhone'],
            'company' => $customer['customerCompany'],
            'status' => $customer['customerStatus'],
            'priority' => $customer['customerPriority'],
            'notes' => $customer['customerNotes'],
            'created_by' => auth()->user()->id,
            'updated_by' => auth()->user()->id,
        ]);

        if ($created) {
            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Customer added successfully'
            );

        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to add customer. Please try again.'
            );
        }

        $this->closeCustomerModal();
    }

    public function allCustomers()
    {
        return Customer::orderBy('created_at', 'desc')->get();
    }

    public function deleteCustomer($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        if ($customer) {
            $customer->delete();

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Customer deleted successfully'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Customer not found. Deletion failed.'
            );
        }

        $this->closeDeleteConfirmationModal();
    }

    public function updateCustomer()
    {
        // Validate
        $customer = $this->validate([
            'customerName' => 'required|string|max:255',
            'customerEmail' => 'required|email|unique:customers,email,' . $this->customerId,
            'customerPhone' => 'nullable|string|max:10',
            'customerCompany' => 'nullable|string|max:255',
            'customerStatus' => 'required|in:lead,active,closed',
            'customerPriority' => 'required|max:255|in:high,medium,low',
            'customerNotes' => 'nullable|string',
        ], [
            'customerName.required' => 'Full name is required.',
            'customerEmail.required' => 'Email is required.',
            'customerEmail.email' => 'Please enter a valid email address.',
            'customerEmail.unique' => 'This email is already associated with another customer.',
            'customerPhone.max' => 'The phone number cannot exceed 10 characters.',
            'customerCompany.max' => 'The company name cannot exceed 255 characters.',
            'customerStatus.required' => 'Please select a status for the customer.',
            'customerStatus.in' => 'Invalid status selected. Please choose lead, active, or closed.',
            'customerPriority.required' => 'Please select a priority level for the customer.',
            'customerPriority.in' => 'Invalid priority level selected. Please choose high, medium, or low.',
            'customerNotes.max' => 'The notes cannot exceed 255 characters.',
        ]);

        $existingCustomer = Customer::findOrFail($this->customerId);
        if ($existingCustomer) {
            $existingCustomer->update([
                'full_name' => $customer['customerName'],
                'email' => $customer['customerEmail'],
                'phone' => $customer['customerPhone'],
                'company' => $customer['customerCompany'],
                'status' => $customer['customerStatus'],
                'priority' => $customer['customerPriority'],
                'notes' => $customer['customerNotes'],
                'updated_by' => auth()->user()->id,
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Customer updated successfully'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Customer not found. Update failed.'
            );
        }

        $this->closeCustomerModal();

    }

};
?>

<div>
    <div class="content-section" id="customers">
        <div class="section-header">
            <h1 class="section-title">Customers</h1>
            <button class="btn btn-primary" id="addCustomerBtn" wire:click="openCustomerModal()">
                <i class="fas fa-plus"></i> Add Customer
            </button>
        </div>
        <!-- Customer Search & Filter -->
        <div class="search-filter-container">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="customerSearch" placeholder="Search customers by name, email, or company...">
            </div>
            <div class="filter-select-wrapper">
                <i class="fas fa-filter"></i>
                <select id="customerPriorityFilter">
                    <option value="All">All Priorities</option>
                    <option value="High">High Priority</option>
                    <option value="Medium">Medium Priority</option>
                    <option value="Low">Low Priority</option>
                </select>
            </div>
        </div>

        <!-- Customers Table -->
        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Company</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="customersTableBody">
                    @foreach ($this->allCustomers() as $customer)
                        <tr>
                            <td>
                                <strong>{{ $customer->full_name }}</strong>
                                <span style="text-transform: uppercase;"
                                    class="priority-tag {{ $customer->priority }}">{{ $customer->priority }}</span>
                                <br><small style="color: var(--text-secondary)">{{ $customer->notes }}</small>
                            </td>
                            <td>{{ $customer->email }}</td>
                            <td>{{ $customer->phone }}</td>
                            <td>{{ $customer->company }}</td>
                            <td><span class="status-badge {{ $customer->status }}">{{ $customer->status }}</span></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="action-btn edit" wire:click="openCustomerModal({{ $customer->id }})"
                                        title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="action-btn delete"
                                        wire:click="openDeleteConfirmationModal({{ $customer->id }}, 'Customer', 'deleteCustomer')"
                                        title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Customer Modal -->
    <div class="modal {{ $isCustomerModalOpen ? 'active' : '' }}" id="customerModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="customerModalTitle">{{ $isCustomerEditMode ? 'Update Customer' : 'Add Customer' }}</h2>
                <button class="close-btn" id="closeCustomerModal" wire:click="closeCustomerModal()">&times;</button>
            </div>
            <form wire:submit="{{ $isCustomerEditMode ? 'updateCustomer()' : 'createCustomer()' }}" id="customerForm">
                @if ($isCustomerEditMode)
                    <input type="hidden" id="customerId">
                @endif
                <div class="form-group">
                    <label for="customerName">Full Name *</label>
                    <input type="text" id="customerName" wire:model="customerName" value="{{ old('customerName') }}" placeholder="Enter full name">
                    @error('customerName')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="customerEmail">Email *</label>
                    <input type="email" id="customerEmail" wire:model="customerEmail" value="{{ old('customerEmail') }}" placeholder="Enter email">
                    @error('customerEmail')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="customerPhone">Phone Number</label>
                    <input type="tel" id="customerPhone" wire:model="customerPhone" value="{{ old('customerPhone') }}" placeholder="Enter phone number">
                    @error('customerPhone')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="customerCompany">Company Name</label>
                    <input type="text" id="customerCompany" value="{{ old('customerCompany') }}" wire:model="customerCompany"
                        placeholder="Enter company name">
                    @error('customerCompany')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="customerStatus">Status</label>
                    <select id="customerStatus" wire:model="customerStatus">
                        <option value="">Select Status</option>
                        <option value="lead">Lead</option>
                        <option value="active">Active</option>
                        <option value="closed">Closed</option>
                    </select>
                    @error('customerStatus')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="customerPriority">Priority Level *</label>
                    <select id="customerPriority" wire:model="customerPriority">
                        <option value="">Select Priority</option>
                        <option value="high">High Priority</option>
                        <option value="medium">Medium Priority</option>
                        <option value="low">Low Priority</option>
                    </select>
                    @error('customerPriority')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="customerNotes">Notes</label>
                    <textarea id="customerNotes" wire:model="customerNotes" rows="3"
                        placeholder="Add any notes...">{{ old('customerNotes') }}</textarea>
                    @error('customerNotes')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelCustomerBtn"
                        wire:click="closeCustomerModal()">Cancel</button>
                    <button type="submit"
                        class="btn btn-primary">{{ $isCustomerEditMode ? 'Save' : 'Add Customer' }}</button>
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

</div>