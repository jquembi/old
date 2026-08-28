<?php

use Livewire\Component;

new class extends Component {}; ?>

<div>
@if(auth()->user()->podeGerirUsuarios())
<section class="mt-10 space-y-6">
    <div class="relative mb-5">
        <flux:heading>Eliminar conta</flux:heading>
        <flux:subheading>Eliminar a sua conta e todos os seus dados permanentemente</flux:subheading>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" data-test="delete-user-button">
            Eliminar conta
        </flux:button>
    </flux:modal.trigger>

    <livewire:pages::settings.delete-user-modal />
</section>
@endif
</div>
