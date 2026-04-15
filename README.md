## VP Management

### Default test users

After running migrations (`php artisan migrate`), you can seed the default workflow roles with:

```bash
php artisan db:seed
```

This creates four users with password **password**:

- developer@example.com — ডেভেলপার (সুপার অ্যাডমিন)
- acl@example.com — সহকারী কমিশনার (ভূমি)
- uno@example.com — উপজেলা নির্বাহী অফিসার
- assistant@example.com — অফিস সহকারী

### Creating additional users for testing

You can add users from the CLI without exposing a public registration page. Example for an অফিস সহকারী account:

```bash
php artisan tinker --execute "\\App\\Models\\User::updateOrCreate(
    ['email' => 'tester@example.com'],
    [
        'name' => 'Test Assistant',
        'role' => \\App\\Models\\User::ROLE_OFFICE_ASSISTANT,
        'password' => \\Illuminate\\Support\\Facades\\Hash::make('secret'),
    ]
)"
```

Replace the email, name, role constant, and password as needed. Available roles are defined on `App\Models\User` (developer, assistant_commissioner, uno, office_assistant). Assign the developer role when you need full access for troubleshooting.
