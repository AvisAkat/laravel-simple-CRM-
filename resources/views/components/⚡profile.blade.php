<?php

use Livewire\Component;
use App\Models\User;
use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

new class extends Component {

    protected $listeners = ['refreshUserInfo' => '$refresh'];

    public $user;
    public $profileFirstName;
    public $profileLastName;
    public $profileEmail;
    public $profilePhone;

    public function updateUserProfile($userId)
    {
        $user = User::findOrFail($userId);

        $validatedData = $this->validate([
            'profileFirstName' => 'required|string|max:255',
            'profileLastName' => 'required|string|max:255',
            'profileEmail' => 'required|email|unique:users,email,' . $userId,
            'profilePhone' => 'nullable|string|max:25',
        ], [
            'profileFirstName.required' => 'First name is required.',
            'profileLastName.required' => 'Last name is required.',
            'profileEmail.required' => 'Email address is required.',
            'profileEmail.email' => 'Please provide a valid email address.',
            'profileEmail.unique' => 'This email address is already taken.',
        ]);

        $user->first_name = $validatedData['profileFirstName'];
        $user->last_name = $validatedData['profileLastName'];
        $user->email = $validatedData['profileEmail'];
        $user->phone = $validatedData['profilePhone'] ?? $user->phone;
        $updated = $user->save();

        $this->user = $user;

        sleep(1);

        if ($updated) {
            Activity::create([
                'type' => 'profile_updated',
                'message' => 'User profile updated: ' . $user->first_name . ' ' . $user->last_name,
                'icon' => 'fa-user-edit',
                'user_id' => Auth::id(),
            ]);

            $this->dispatch(
                'notify',
                type: 'success',
                message: 'Personal details updated successfully.'
            );
            $this->dispatch('refreshUserInfo'); // Refresh user info in the top bar
        } else {
            $this->dispatch(
                'notify',
                type: 'error',
                message: 'Failed to update personal details. Please try again.'
            );
        }
    }

    public function mount()
    {
        $this->user = auth()->user();
        $this->profileFirstName = $this->user->first_name;
        $this->profileLastName = $this->user->last_name;
        $this->profileEmail = $this->user->email;
        $this->profilePhone = $this->user->phone;
    }
};
?>

<div>
    <!-- Profile Page Section -->
    <div id="profile">
        <h1 class="section-title">Profile Settings</h1>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
            <!-- Left Column: Summary Info Badge Card -->
            <div class="recent-activity" style="text-align: center; padding: 32px 24px; height: fit-content;">
                <div style="position: relative; display: inline-block; margin-bottom: 16px;">
                    <img id="profileCardAvatar"
                        src="https://ui-avatars.com/api/?name={{ $user->first_name }}{{ $user->last_name }}&background=4F46E5&color=fff"
                        alt="User Profile Avatar"
                        style="width: 100px; height: 100px; border-radius: 50%; border: 4px solid var(--indigo); box-shadow: 0 4px 10px var(--shadow-color);">
                    <div
                        style="position: absolute; bottom: 0; right: 0; background-color: var(--teal); width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; border: 2px solid var(--bg-card); font-size: 12px;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <h2 id="profileCardName"
                    style="font-size: 20px; font-weight: 700; margin-bottom: 4px; color: var(--text-primary);">
                    {{ $user->first_name }} {{ $user->last_name }}
                </h2>
                <p id="profileCardRole"
                    style="font-size: 14px; color: var(--text-secondary); font-weight: 500; margin-bottom: 16px;">
                    @if ($user->role == 'admin')
                        System Administrator
                    @elseif ($user->role == 'agent')
                        Agent
                    @elseif ($user->role == 'manager')
                        Manager
                    @endif
                </p>

                <div
                    style="text-align: left; background-color: var(--bg-primary); padding: 16px; border-radius: 10px; border: 1px solid var(--border-color); font-size: 13px;">
                    <p style="margin-bottom: 8px; color: var(--text-secondary);"><i class="fas fa-building"
                            style="color: var(--indigo); width: 20px;"></i> <span id="profileCardCompany">SimpleCRM
                            Corp</span>
                    </p>
                    <p style="margin-bottom: 8px; color: var(--text-secondary);"><i class="fas fa-envelope"
                            style="color: var(--indigo); width: 20px;"></i> <span
                            id="profileCardEmail">{{ $user->email }}</span>
                    </p>
                    <p style="color: var(--text-secondary);"><i class="fas fa-toggle-on"
                            style="color: var(--indigo); width: 20px;"></i> CRM Status: <span
                            class="status-badge active"
                            style="padding: 2px 8px; font-size: 11px;text-transform: capitalize">{{ $user->status }}</span>
                    </p>
                </div>
            </div>

            <!-- Right Column: Edit Profile Parameters Form Card -->
            <div class="recent-activity" style="padding: 24px;">
                <h2
                    style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                    <i class="fas fa-edit" style="color: var(--indigo); margin-right: 8px;"></i>Update Account
                    Information
                </h2>

                <form id="profileUpdateForm" wire:submit="updateUserProfile({{ $user->id }})"
                    style="padding: 0; margin: 0;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                        <div class="form-group">
                            <label for="profileFullNameInput">First Name</label>
                            <input type="text" id="profileFullNameInput" wire:model="profileFirstName"
                                value="{{ old('profileFirstName') ?? $user->first_name }}"
                                placeholder="Enter First name">
                            @error('profileFirstName')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="profileLastNameInput">Last Name</label>
                            <input type="text" id="profileLastNameInput" wire:model="profileLastName"
                                value="{{ old('profileLastName') ?? $user->last_name }}" placeholder="Enter Last name">
                            @error('profileLastName')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="profileEmailInput">Email Address</label>
                        <input type="email" id="profileEmailInput" wire:model="profileEmail"
                            value="{{ old('profileEmail') ?? $user->email }}" placeholder="Enter email">
                        @error('profileEmail')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label for="profilePhoneInput">Phone Number</label>
                        <input type="tel" id="profilePhoneInput" wire:model="profilePhone"
                            value="{{ old('profilePhone') ?? $user->phone }}" placeholder="Enter phone">
                        @error('profilePhone')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Profile Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>