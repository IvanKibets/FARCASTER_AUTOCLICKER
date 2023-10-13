<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Koperasi;
use Illuminate\Support\Facades\Hash;

class MigrateAnggota extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:anggota';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data anggota';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $koperasi = Koperasi::get();
        foreach($koperasi as $item){

            $client = new \GuzzleHttp\Client(['base_uri' => $item->url]);

            $headers = [
                // 'Authorization' => 'Bearer 2|FR7C7kExqtDBwp5B9DhBir9E8e3OUdTLm2fxX83c'
              ];
            $options = [
            'multipart' => [
                [
                'name' => 'sign',
                'contents' => 'c3aa6a5a2613a7149c592edd3dc7d7de'
                ],
                [
                'name' => 'cmd',
                'contents' => 'get-anggota'
                ]
            ]];

            $request = new \GuzzleHttp\Psr7\Request('POST', '/api/get-anggota', $headers);
            $response = $client->sendAsync($request, $options)->wait();
            
            foreach(json_decode($response->getBody()) as $res){
                echo $res->no_anggota. " / {$res->nama}\n";

                $user = User::where('no_anggota',$res->no_anggota)->first();
                if(!$user) $user = new User();

                $user->no_anggota = $res->no_anggota;
                $user->username = $res->no_anggota;
                $user->name = $res->nama;
                $user->koperasi_id = $item->id;
                $user->password = Hash::make('12345678');
                $user->simpanan_pokok = $res->simpanan_pokok;
                $user->simpanan_wajib = $res->simpanan_wajib;
                $user->simpanan_sukarela = $res->simpanan_sukarela;
                $user->simpanan_lain_lain = $res->simpanan_lain_lain;
                $user->save();
            }
        }

        $this->info("Done");
    }
}