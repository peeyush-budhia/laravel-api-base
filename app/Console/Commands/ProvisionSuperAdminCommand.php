<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AuditEvent;
use App\Enums\Role as RoleEnum;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use App\Policies\PasswordPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

final class ProvisionSuperAdminCommand extends Command
{
    protected $signature = 'app:provision-super-admin
        {email? : Email address for the super administrator}
        {--first-name= : First name for the super administrator}
        {--last-name= : Last name for the super administrator}';

    protected $description = 'Securely provision the initial production super administrator';

    public function handle(PasswordPolicy $passwordPolicy): int
    {
        $role = Role::query()
            ->where('name', RoleEnum::SUPER_ADMIN->value)
            ->where('guard_name', 'sanctum')
            ->first();

        if (! $role) {
            $this->components->error(
                'The super-admin role does not exist. Run php artisan db:seed --force first.',
            );

            return SymfonyCommand::FAILURE;
        }

        if ($this->superAdminExists()) {
            $this->components->error('A super administrator already exists.');

            return SymfonyCommand::FAILURE;
        }

        $data = [
            'email' => $this->argument('email') ?? $this->ask('Email address'),
            'first_name' => $this->option('first-name') ?: $this->ask('First name'),
            'last_name' => $this->option('last-name') ?: $this->ask('Last name'),
            'password' => $this->secret('Password'),
            'password_confirmation' => $this->secret('Confirm password'),
        ];

        $validator = Validator::make($data, [
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(User::class, 'email'),
            ],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'password' => [
                'required',
                'string',
                'confirmed',
                $passwordPolicy->validationRule(),
            ],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return SymfonyCommand::INVALID;
        }

        $validated = $validator->validated();

        $user = DB::transaction(function () use ($validated): ?User {
            Role::query()
                ->where('name', RoleEnum::SUPER_ADMIN->value)
                ->where('guard_name', 'sanctum')
                ->lockForUpdate()
                ->firstOrFail();

            if ($this->superAdminExists()) {
                return null;
            }

            $user = User::query()->create([
                'email' => $validated['email'],
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'password' => $validated['password'],
                'status' => UserStatus::ACTIVE,
            ]);

            $user->assignRole(RoleEnum::SUPER_ADMIN->value);
            $user->auditRelationshipChange(
                AuditEvent::RolesSynced,
                'roles',
                [],
                [RoleEnum::SUPER_ADMIN->value],
            );

            return $user;
        });

        if (! $user) {
            $this->components->error('A super administrator already exists.');

            return SymfonyCommand::FAILURE;
        }

        $this->components->info(
            "Super administrator {$user->email} was created successfully.",
        );

        return SymfonyCommand::SUCCESS;
    }

    private function superAdminExists(): bool
    {
        return User::query()
            ->withTrashed()
            ->whereHas(
                'roles',
                fn ($query) => $query
                    ->where('name', RoleEnum::SUPER_ADMIN->value)
                    ->where('guard_name', 'sanctum'),
            )
            ->exists();
    }
}
