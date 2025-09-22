<?php

namespace App\Livewire\Users;

use App\Models\User;

class Edit extends \Livewire\Component
{
    public User $user;

    public function mount(User $user)
    {
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.users.edit');
    }
}
