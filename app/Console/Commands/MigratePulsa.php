<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\ProductOperator;

class MigratePulsa extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:pulsa';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi data pulsa';

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
        $username = env('DIGIFLAZZ_USERNAME');
        $apiKey = env('DIGIFLAZZ_KEY_PROD');
        
        $data = json_encode(array( 
            'cmd' => 'prepaid',
            'username' => $username, // konstan
            'sign' => md5("$username$apiKey" . "pricelist")
        ));
        $header = array(
        'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.digiflazz.com/v1/price-list');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        $result = json_decode($result);
        foreach($result->data as $item){
            echo $item->product_name."\n";
            
            $product_type = ProductType::where('name',$item->category)->first();

            $pulsa = Product::where('kode_produk',$item->buyer_sku_code)->first();
            if(!$pulsa) $pulsa = new Product();
            if($product_type) $pulsa->product_type_id = $product_type->id;
            $product_operator = ProductOperator::where('name',$item->brand)->where('product_type_id',$product_type->id)->first();
            if($product_operator) $pulsa->product_operator_id = $product_operator->id;

            $pulsa->kode_produk = $item->buyer_sku_code;
            $pulsa->harga = $item->price;
            $pulsa->harga_jual = $item->price+200;
            $pulsa->type = $item->category;
            $pulsa->keterangan  = $item->product_name;
            $pulsa->keterangan_detail = $item->desc;
            $pulsa->save();

            echo "Product Code : {$item->buyer_sku_code}\n";
            echo "Product Description : {$item->product_name}\n";
            echo "Harga : {$item->price}\n\n";
        }

        // Pascabayar
        $data = json_encode(array( 
            'cmd' => 'pasca',
            'username' => $username, // konstan
            'sign' => md5("$username$apiKey" . "pricelist")
        ));
        $header = array(
        'Content-Type: application/json',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.digiflazz.com/v1/price-list');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($ch);
        $result = json_decode($result);
        foreach($result->data as $item){
            echo $item->product_name."\n";
            
            $product_type = ProductType::where('name',$item->category)->first();

            $pulsa = Product::where('kode_produk',$item->buyer_sku_code)->first();
            $product_operator = ProductOperator::where('name',$item->brand)->where(function($table) use($product_type){
                if($product_type) $table->where('product_type_id',$product_type->id);
            })->first();;

            if(!$pulsa) $pulsa = new Product();
            if($product_type) $pulsa->product_type_id = $product_type->id;
            if($product_operator) $pulsa->product_operator_id = $product_operator->id;

            $pulsa->kode_produk = $item->buyer_sku_code;
            $pulsa->harga = $item->admin;
            $pulsa->commission = $item->commission;
            $pulsa->type = $item->category;
            $pulsa->keterangan  = $item->product_name;
            $pulsa->keterangan_detail = $item->desc;
            $pulsa->data_json = json_encode($item);
            $pulsa->save();

            echo "Product Code : {$item->buyer_sku_code}\n";
            echo "Product Description : {$item->product_name}\n";
            echo "Harga : {$item->admin}\n\n";
        }

        $this->info("Done");
    }
}
