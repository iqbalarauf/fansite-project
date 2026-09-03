<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\UserRole;
use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Add New Account')] class extends Component {
    use PasswordValidationRules, ProfileValidationRules;

    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public string $role = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $validated = $this->validate([
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
            'role' => ['required', new Enum(UserRole::class)],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
        ]);

        $this->reset(['name', 'email', 'password', 'password_confirmation', 'role']);

        Flux::toast(variant: 'success', text: __('Account created.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Add New Account') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Add New Account')" :subheading="__('Registrasikan akun admin baru beserta role aksesnya')">
        <form wire:submit="save" class="space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autocomplete="name" />
            <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

            <flux:select wire:model="role" :label="__('Role')" required>
                <flux:select.option value="">{{ __('Pilih role') }}</flux:select.option>
                @foreach (\App\Enums\UserRole::cases() as $userRole)
                    <flux:select.option value="{{ $userRole->value }}">{{ $userRole->label() }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit">
                    {{ __('Create Account') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
