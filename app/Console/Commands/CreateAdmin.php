<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new admin user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->ask('Enter the name of the admin user');
        $email = $this->ask('Enter the email of the admin user');
        $password = $this->secret('Enter the password for the admin user');

        // Validate input
    if (empty($name) || empty($email) || empty($password)) {
            $this->error('All fields are required. Please try again.');
            return;
        }

        // Check if the email already exists
        if (\App\Models\User::where('email', $email)->exists()) {
            $this->error('A user with this email already exists. Please try again with a different email.');
            return;
        }

        // Create the admin user
        $user = \App\Models\User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'status' => 1, // Set status to active
            'welcome_email_sent' => false, 
            'department_id' => null, // Admin user does not belong to any department
            'img_path' => null, // No profile image for admin user
        ]);
        $user->assignRole('admin');

        $this->info('Admin user created successfully!');
    }
}
