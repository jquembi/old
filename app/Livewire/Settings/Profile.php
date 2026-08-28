<?php

namespace App\Livewire\Settings;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public $name;
    public $email;
    public $foto;
    public $hasUnverifiedEmail = false;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->hasUnverifiedEmail = !$user->hasVerifiedEmail();
    }

    public function render()
    {
        return view('livewire.settings.profile', [
            'user' => Auth::user()->loadMissing(['roles', 'provincia', 'municipio', 'criadoPor']),
        ]);
    }

    public function updateProfileInformation()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . Auth::id(),
        ]);

        $user = Auth::user();
        $user->name = $this->name;
        $user->email = $this->email;
        $user->save();

        $this->dispatch('profile-updated');
    }

    public function uploadFoto()
    {
        $this->validate([
            'foto' => 'required|image|max:2048|mimes:jpeg,png,webp',
        ]);

        $user = Auth::user();

        // Remover foto antiga se existir
        if ($user->foto_perfil) {
            Storage::disk('public')->delete($user->foto_perfil);
        }

        // Guardar nova foto
        $path = $this->foto->store('profile-photos', 'public');
        $user->foto_perfil = $path;
        $user->save();

        $this->foto = null;
        $this->dispatch('profile-updated');
    }

    public function removerFoto()
    {
        $user = Auth::user();

        if ($user->foto_perfil) {
            Storage::disk('public')->delete($user->foto_perfil);
            $user->foto_perfil = null;
            $user->save();
        }

        $this->dispatch('profile-updated');
    }

    public function resendVerificationNotification()
    {
        $user = Auth::user();
        
        if ($user->hasVerifiedEmail()) {
            return;
        }

        $user->sendEmailVerificationNotification();

        session()->flash('status', 'verification-link-sent');
    }
}
