<?php

namespace App\Jobs;

use App\Models\User;
use App\Models\Transaction;

class SinkronKoperasi extends Job
{
    protected $user,$params;

    /**
     * Create a new job instance.
     *
     * @param  User  $user
     * @return void
     */
    public function __construct(User $user,$params)
    {
        $this->user = $user;
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

        echo "==========================\n";
        foreach($this->params as $k => $v){
            if(is_array($v)){
                foreach($v as $ksub => $vsub){
                    echo "{$k}.{$ksub} : {$vsub}\n";    
                }
            }else
                echo "{$k} : {$v}\n";
        }
        echo "==========================\n";
        
        $sinkron = sinkronKoperasi($this->params);
        
        $sinkron->body();
        
        if($sinkron->status()==200){
            $response = json_decode($sinkron->body());
        }
    }
}