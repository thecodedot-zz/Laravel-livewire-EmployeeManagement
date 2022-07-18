<?php

namespace App\Http\Livewire\Users;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class UserIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $username, $firstName, $lastName, $email, $password;
    public $userId;
    public $editMode = false;

    protected $rules = [
        'username' => 'required',
        'firstName' => 'required',
        'lastName' => 'required',
        'password' => 'required',
        'email' => 'required|email',
    ];

    public function addUser() {
        $this->validate();

        User::create([
            'username' => $this->username,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'password' => Hash::make($this->password)
        ]);
        $this->reset();
        $this->dispatchBrowserEvent('modal', [
            'modalId' => '#userModal',
            'actionModal' => 'hide'
        ]);
        session()->flash('message', 'User successfully added.');


    }

    public function showUserModal() {
        $this->reset();
        $this->dispatchBrowserEvent('modal', [
            'modalId' => '#userModal',
            'actionModal' => 'show'
        ]);
    }

    public function showEditModal($id) {
        $this->reset();
        $this->editMode = true;
        // find User
        $this->userId = $id;
        // load user
        $this->loadUser();
        // show Modal
        $this->dispatchBrowserEvent('modal', [
            'modalId' => '#userModal',
            'actionModal' => 'show'
        ]);
    }



    public function loadUser() {
        $user = User::find($this->userId);
        $this->username = $user->username;
        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->email = $user->email;
    }

    public function updateUser() {
        $validated = $this->validate([
            'username' => 'required',
            'firstName' => 'required',
            'lastName'  => 'required',
            'email' => 'required|email',
        ]);
        $user = User::find($this->userId);
        $user->update($validated);
        $this->reset();
        $this->dispatchBrowserEvent('modal', [
            'modalId' => '#userModal',
            'actionModal' => 'hide'
        ]);
        session()->flash('message', 'User successfully updated.');
    }

    public function deleteUser($id) {
        $user = User::find($id);
        $user->delete();

        session()->flash('message', 'User successfully deleted.');
    }

    public function closeModal() {
        $this->dispatchBrowserEvent('modal', [
            'modalId' => '#userModal',
            'actionModal' => 'hide'
        ]);
        $this->reset();
    }

    public function render()
    {
        $users = User::paginate(5);
        if(strlen($this->search) > 2) {
            $users = User::where('username', 'like', "%{$this->search}%")->paginate(5);
        }
        return view('livewire.users.user-index', [
            'users' => $users
        ])
            ->layout('layouts.main');
    }
}
