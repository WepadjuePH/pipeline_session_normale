<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class TestEmailCommand extends Command
{
    protected $signature = 'test:email';
    protected $description = 'Test email sending';

    public function handle()
    {
        $this->info('Testing email sending...');
        $this->newLine();

        $email = 'hybreltapdur7@gmail.com';
        $code = '123456';

        try {
            $this->info("Sending test email to: {$email}");
            Mail::to($email)->send(new VerificationCodeMail($code));
            $this->info('✓ Email sent successfully!');
        } catch (\Exception $e) {
            $this->error('✗ Email sending failed:');
            $this->error($e->getMessage());
            return 1;
        }

        $this->newLine();
        $this->info('Check your email for the verification code.');

        return 0;
    }
}
