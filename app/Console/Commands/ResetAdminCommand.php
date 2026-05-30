<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\SecurityQuestion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class ResetAdminCommand extends Command
{
    protected $signature = 'admin:reset {--force}';
    protected $description = 'Reset admin account security question answer';

    public function handle(): int
    {
        $adminUsername = 'disdukcapil';
        $adminPassword = 'disdukcapil123';
        $adminAnswer = 'liverpool';

        $this->info('=== Reset Admin Account ===');

        $user = User::where('username', $adminUsername)->first();

        if (! $user) {
            $this->error("Admin user '{$adminUsername}' not found. Creating new admin...");

            $adminRole = Role::where('name', 'Admin')->first();
            if (! $adminRole) {
                $this->error('Admin role not found. Run RolePermissionSeeder first.');
                return self::FAILURE;
            }

            $question = SecurityQuestion::where('question', 'like', '%sepak bola%')->first();
            if (! $question) {
                $this->warn('Security question not found, using first available');
                $question = SecurityQuestion::first();
            }

            $user = User::create([
                'name' => 'Administrator',
                'username' => $adminUsername,
                'password' => Hash::make($adminPassword),
                'security_question_id' => $question->id,
                'security_question_answer' => $adminAnswer,
            ]);

            $user->assignRole($adminRole);
            $this->info("Admin user created: {$adminUsername} / {$adminPassword}");
            $this->info("Security answer: {$adminAnswer}");
            $this->info('Security question: ' . $question->question);

            return self::SUCCESS;
        }

        $this->info("Found admin user: {$user->username} ({$user->name})");
        $this->info("Current answer length: " . strlen($user->getAttributes()['security_question_answer'] ?? ''));

        $question = SecurityQuestion::where('question', 'like', '%sepak bola%')->first()
            ?? SecurityQuestion::first();

        $user->security_question_id = $question->id;
        $user->security_question_answer = $adminAnswer;
        $user->save();

        $this->info("Security question updated to: {$question->question}");
        $this->info("Security answer reset to: {$adminAnswer}");

        $fresh = User::where('username', $adminUsername)->first();
        $this->info("Verified - raw answer length: " . strlen($fresh->getAttributes()['security_question_answer'] ?? ''));
        $this->info("Verified - decrypted: " . $fresh->security_question_answer);

        return self::SUCCESS;
    }
}
