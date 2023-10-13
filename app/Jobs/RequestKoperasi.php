<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Transaction;

class RequestKoperasi extends Job
{
    protected $user,$transaction,$params;

    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(User $user,Transaction $transaction,$params)
    {
        $this->user = $user;
        $this->transaction = $transaction;
        $this->params = $params;
    }

    /**
     * Execute the job.
     *
     * @param  Mailer  $mailer
     * @return void
     */
    public function handle()
    {
        // Integration to Koperasi
        $koperasi = $this->user->koperasi;
        $this->params['url'] = $koperasi->url.$this->params['url'];;
        $this->params['token'] = $koperasi->token;
        $this->params['no_anggota'] = $this->user->no_anggota;

        $sinkron = sinkronKoperasi($this->params);
        
        echo "\nNo Transaksi : {$this->transaction->transaction_id}\n";
        echo "URL : {$this->transaction->transaction_id}\n";
        echo "--------------------------------------------\n";
        
        $this->transaction->api_response_after = $sinkron->body();
        if($sinkron->status()==200){
            $response = json_decode($sinkron->body());
            $this->transaction->status = $response->status;
            $this->transaction->keterangan_gagal = $response->message;
        }
        $this->transaction->save();
    }
}