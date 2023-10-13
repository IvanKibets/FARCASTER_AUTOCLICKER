<?php 

use Illuminate\Support\Facades\Http;

function status($status){
  switch($status){
      case 1:
        return "Sukses";
        break;
      case 2:
        return "Batal"; 
        break;
      case 3:
        return "Gagal"; 
        break;
      default:
        return 'Pending';
      break;
    }
}

function hitung_umur($tanggal_lahir,$pembulatan=1,$today=''){
  $birthDate = new \DateTime($tanggal_lahir);
  
  if($today)
    $today = new \DateTime($today);
  else
    $today = new \DateTime("today");

  if ($birthDate > $today) { 
    return 0;
  }
  $tahun = $today->diff($birthDate)->y;

  if($pembulatan==1) { // Nears birthday
      if($today->diff($birthDate)->m > 6)
          $tahun++;
      elseif($today->diff($birthDate)->m == 6 and $today->diff($birthDate)->d>0)
          $tahun++;
  }
  if($pembulatan==2 and $today->diff($birthDate)->m > 12) $tahun++; // Actual Birthday
  
  if($pembulatan==3) return $today->diff($birthDate)->y .' Tahun '. $today->diff($birthDate)->m .' Bulan '. $today->diff($birthDate)->d.' Hari';
  if($pembulatan==4) return $today->diff($birthDate)->days;

  return $tahun;
}

function clear_currency($nominal)
{
    if($nominal == "") return 0;

    $nominal = str_replace('Rp. ', '', $nominal);
    $nominal = str_replace(' ', '', $nominal);
    $nominal = str_replace('.', '', $nominal);
    $nominal = str_replace(',', '', $nominal);
    $nominal = str_replace('-', '', $nominal);
    $nominal = str_replace('(', '', $nominal);
    $nominal = str_replace(')', '', $nominal);

    return (int)$nominal;
}

function log_activity($subject)
{
  $var['get_content'] = json_decode(file_get_contents('php://input'));
  $var[$_SERVER['REQUEST_METHOD']] = $_SERVER['REQUEST_METHOD']=='POST' ? json_encode($_POST) : json_encode($_GET);

  $data = new \App\Models\LogActivity();
  $data->subject = $subject;
  $data->url = Illuminate\Support\Facades\URL::current();
  $data->method = $_SERVER['REQUEST_METHOD'];
  $data->ip = get_IP_address();
  $data->var = json_encode($var);
  $data->agent = $_SERVER['HTTP_USER_AGENT'];
  $data->user_id = auth()->check() ? auth()->user()->id : 1;
  $data->save();
}

function get_IP_address()
{
    foreach (array('HTTP_CLIENT_IP',
                   'HTTP_X_FORWARDED_FOR',
                   'HTTP_X_FORWARDED',
                   'HTTP_X_CLUSTER_CLIENT_IP',
                   'HTTP_FORWARDED_FOR',
                   'HTTP_FORWARDED',
                   'REMOTE_ADDR') as $key){
        if (array_key_exists($key, $_SERVER) === true){
            foreach (explode(',', $_SERVER[$key]) as $IPaddress){
                $IPaddress = trim($IPaddress); // Just to be safe

                if (filter_var($IPaddress,
                               FILTER_VALIDATE_IP,
                               FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
                    !== false) {

                    return $IPaddress;
                }
            }
        }
    }
}

function format_idr($number)
{
    return number_format($number,0,0,'.');
}

function nomor_handphone($nohp) {
  
  $nohp = str_replace(" ","",$nohp);
  // kadang ada penulisan no hp (0274) 778787
  $nohp = str_replace("(","",$nohp);
  // kadang ada penulisan no hp (0274) 778787
  $nohp = str_replace(")","",$nohp);
  // kadang ada penulisan no hp 0811.239.345
  $nohp = str_replace(".","",$nohp);
  // cek apakah no hp mengandung karakter + dan 0-9
  if(!preg_match('/[^+0-9]/',trim($nohp))){
      // cek apakah no hp karakter 1-3 adalah +62
      if(substr(trim($nohp), 0, 3)=='+62'){
        $nohp = str_replace_first('+62','0',$nohp);
      }
      if(substr(trim($nohp), 0, 2)=='62'){
        $nohp = str_replace_first('62','0',$nohp);
      }
      // cek apakah no hp karakter 1 adalah 0
      // elseif(substr(trim($nohp), 0, 1)=='0'){
      //     $hp = '+62'.substr(trim($nohp), 1);
      // }
  }
  return $nohp;
}

/**
 * Replace first string
 */
function str_replace_first($search, $replace, $subject) {
  $pos = strpos($subject, $search);
  if ($pos !== false) {
      return substr_replace($subject, $replace, $pos, strlen($search));
  }
  return $subject;
}

function digiflazz($param){
  $username = env('DIGIFLAZZ_USERNAME');  
  $apiKey = env('DIGIFLAZZ_KEY_PROD');
  // $apiKey = env('DIGIFLAZZ_KEY_DEV');

  $url = "";
  if($param['action']=='topup'){
    $url = 'transaction';
    $data = [
        "username"=> $username,
        "buyer_sku_code"=> $param['product'],
        "customer_no"=> $param['no'],
        "ref_id"=> $param['ref_id'],
        "sign"=> md5("$username$apiKey" . $param['ref_id']),
        "testing"=> ($param['type']=="demo"?true:false),
        "msg"=>$param['id']
      ];

    if(isset($param['commands'])) $data['commands'] = $param['commands'];
    
    $data = json_encode($data);
  }

  if($param['action']=='cek-tagihan-token'){
    $url = 'transaction';
    $data = json_encode(
      [
        "username"=> $username,
        "customer_no"=> $param['no'],
        "commands"=>$param['commands'],
        "ref_id"=> $param['ref_id'],
        "sign"=> md5("$username$apiKey" . $param['ref_id']),
        "testing"=> ($param['type']=="demo"?true:false),
        "msg"=>$param['id']
      ]);
  }
  
  $header = array(
    'Content-Type: application/json',
  );
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, "https://api.digiflazz.com/v1/{$url}");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_POST, 1);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
  $result = curl_exec($ch);
  
  return $result;
}

?>