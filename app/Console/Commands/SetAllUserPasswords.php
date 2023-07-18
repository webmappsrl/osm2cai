<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Helper\ProgressBar;


class SetAllUserPasswords extends Command
{
    protected $signature = 'osm2cai:setpasswords {password}';
    protected $description = 'Set all user passwords to a fixed value.';

    public function handle()
    {
        if (config('app.env') !== 'local') {
            $this->error('This command can only be executed in the local environment.');
            return;
        }

        $password = $this->argument('password');

        $users = User::all();
        $totalUsers = $users->count();
        $progressBar = $this->output->createProgressBar($totalUsers);
        $progressBar->start();

        foreach ($users as $user) {
            $user->update([
                'password' => Hash::make($password)
            ]);
            $progressBar->advance();

        }
        $progressBar->finish();
        $this->line('');

        $this->info('Passwords updated successfully.');
    }
}
