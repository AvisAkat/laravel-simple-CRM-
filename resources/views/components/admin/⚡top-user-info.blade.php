<?php

use Livewire\Component;

new class extends Component {
    protected $listeners = ['refreshUserInfo' => '$refresh'];
};
?>

<div>
    <a href="{{ route('admin.profile') }}" style="text-decoration: none">
        <div class="user-profile" id="topNavUserProfile" style="cursor: pointer;" title="View Profile Settings">
            <img id="topNavAvatar"
                src="https://ui-avatars.com/api/?name={{ auth()->user()->first_name }}{{ auth()->user()->last_name }}&background=4F46E5&color=fff"
                alt="User">
            <span id="topNavProfileName">{{ auth()->user()->first_name }}</span>
        </div>
    </a>
</div>