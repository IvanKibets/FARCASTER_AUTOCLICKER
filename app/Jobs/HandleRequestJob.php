<?php

namespace App\Jobs;
use App\Models\User;

class HandleRequestJob extends Job
{
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @param  Mailer  $mailer
     * @return void
     */
    public function handle()
    {
        echo "No Anggota : ". $this->user->username ."\n";
    }
}