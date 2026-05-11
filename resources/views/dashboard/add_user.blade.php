@extends('layout.pages-layout')
@section('pageTitle', isset($pageTitle) ? $pageTitle : 'Page Title Here')
@section('content')

    <div>
        <div id="users">
            <h1 class="section-title">User Management</h1>

            <div id="userManagementGridWrapper" style="display: grid; grid-template-columns: 1fr; gap: 24px;">
                <!-- Column 1: Add New System User Form -->
                <div class="recent-activity" id="addUserFormCard" style="padding: 24px; height: fit-content;">
                    <h2
                        style="font-size: 18px; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                        <i class="fas fa-user-plus" style="color: var(--indigo); margin-right: 8px;"></i>Add New User
                    </h2>

                    <form id="addNewUserForm"
                        action="{{ $isEditUser ? route('admin.updateUser') : route('admin.addUser') }}" method="POST"
                        style="padding: 0; margin: 0;">
                        @csrf
                        @if ($isEditUser)
                            <input type="hidden" name="userId" value="{{ $user->id }}" />
                        @endif
                        <div class="form-group">
                            <label for="newFirstName">First Name *</label>
                            <input type="text" id="newFirstName" name="firstName"
                                value="{{ old('firstName') ?? optional($user)->first_name }}" placeholder="e.g. Jane">
                            @error('firstName')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="newLastName">Last Name *</label>
                            <input type="text" id="newLastName" name="lastName"
                                value="{{ old('lastName') ?? optional($user)->last_name }}" placeholder="e.g. Doe">
                            @error('lastName')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="newUserEmail">Email Address *</label>
                            <input type="email" id="newUserEmail" name="email"
                                value="{{ old('email') ?? optional($user)->email }}" placeholder="e.g. jane@mail.com">
                            @error('email')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="phoneInput">Phone Number</label>
                            <input type="tel" id="phoneInput" name="phone"
                                value="{{ old('phone') ?? optional($user)->phone }}" placeholder="Enter phone">
                            @error('phone')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label for="newUserRole">Status</label>
                            <select id="newUserRole" name="status">
                                <option value="">Select ...</option>
                                <option value="active" {{ (old('status') == 'active' ? 'selected' : optional($user)->status == 'active') ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ (old('status') == 'inactive' ? 'selected' : optional($user)->status == 'inactive') ? 'selected' : '' }}>In-Active</option>
                            </select>
                            @error('status')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                        @if (auth()->user()->role === 'admin')
                            <div class="form-group" style="margin-bottom: 20px;">
                                <label for="newUserRole">System Access Role *</label>
                                <select id="newUserRole" name="role">
                                    <option value="">Select ...</option>
                                    <option value="admin" {{ (old('role') == 'admin' ? 'selected' : optional($user)->role == 'admin') ? 'selected' : '' }}>Administrator (Full Access)
                                    </option>
                                    <option value="manager" {{ (old('role') == 'manager' ? 'selected' : optional($user)->role == 'manager') ? 'selected' : '' }}>Manager (Edit Leads &amp;
                                        Customers)</option>
                                    <option value="agent" {{ (old('role') == 'agent' ? 'selected' : optional($user)->role == 'agent') ? 'selected' : '' }}>Agent (Read/Write Tasks)</option>
                                </select>
                                @error('role')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                        <div style="text-align: center;margin-top: 15px">
                            <button type="submit" class="btn btn-primary" style="width: 50%; justify-content: center;">
                                <i class="fas fa-plus"></i>
                                {{ $isEditUser ? 'Update System User' : 'Register System User' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


@endsection