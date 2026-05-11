<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    public $isShowDeleteConfirmationModal = false;


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

    public function allUsers()
    {
        if(auth()->user()->role === 'admin')
        {
            return User::orderBy('updated_at', 'desc')->get();
        }else {
            return User::where('role', '!=' , 'admin')->orderBy('updated_at', 'desc')->get();
        }
    }

    public function deleteUser($userId)
    {
        $user = User::find($userId);

        if (!$user) {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'User not found. Deletion failed.'
            );

            $this->closeDeleteConfirmationModal();
            return;
        }

        if ($user->delete()) {


            Activity::create([
                'type' => 'user_deleted',
                'message' => 'User deleted: ' . $user->first_name . ' ' . $user->last_name,
                'icon' => 'fa-user-times',
                'user_id' => Auth::id(),
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'User deleted successfully.'
            );
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to delete user. Please try again.'
            );
        }

        $this->closeDeleteConfirmationModal();
    }

};
?>

<div>
    <div id="users">
        <div class="section-header">
            <h1 class="section-title">User Management</h1>
            <a href="{{ route('admin.addUserForm') }}" style="text-decoration: none">
            <button class="btn btn-primary" id="addCustomerBtn">
                <i class="fas fa-plus"></i> Add Customer
            </button>
            </a>
        </div>

        <div id="userManagementGridWrapper" style="display: grid; grid-template-columns: 1fr; gap: 24px;">

            <!-- Column 2: Current Registered System Users List -->
            <div class="recent-activity" id="allUsersListCard" style="padding: 24px; display: block;">
                <h2
                    style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <i class="fas fa-users-cog" style="color: var(--teal); margin-right: 8px;"></i>Active CRM Users
                </h2>

                <div class="table-container" style="border: none; box-shadow: none;">
                    <table class="data-table">
                        <tbody>
                            <tr>
                                <th>User Details</th>
                                <th style="text-align: center;">Access Role</th>
                                <th style="text-align: center;">Account Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </tbody>
                        <tbody id="systemUsersTableBody">
                            @foreach ($this->allUsers() as $user)
                                <tr>
                                    <td>
                                        <strong>{{ $user->first_name }} {{ $user->last_name }}</strong><br>
                                        <small style="color: var(--text-secondary); font-size: 11px;">{{ $user->email }}
                                            {{ $user->phone ? '( ' . $user->phone . ' )' : '' }} </small>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="status-badge {{ $user->status }}"
                                            style="font-size: 11px; padding: 2px 8px;text-transform: capitalize;">
                                            {{ $user->role }}
                                        </span>
                                    </td>
                                    <td style="text-align: center;">
                                        <span class="status-badge {{ $user->status }}"
                                            style="font-size: 11px; padding: 2px 8px;text-transform: capitalize;">
                                            {{ $user->status }}
                                        </span>
                                    </td>
                                    <td class="all-users-actions" style="justify-content: end">
                                        <a class="action-btn edit" title="Edit" href="{{ route('admin.editUser', $user->id) }}">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="action-btn delete" title="Delete" wire:click="openDeleteConfirmationModal({{ $user->id }}, 'User', 'deleteUser')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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