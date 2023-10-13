<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pinjaman;
use App\Models\Transaction;
use App\Models\PinjamanItem;
use App\Jobs\RequestKoperasi;

class PembiayaanController extends Controller
{
    public $status='success',$pinjaman_jasa=8.5,$proteksi_pinjaman=20000;
    public $platform_fee=5000,$min_peminjaman=500000;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => []]);
    }

    public function tunai()
    {
        $temp = Pinjaman::where('user_id',\Auth::user()->id)->orderBy('id','DESC')->first();
        
        $num=0;
        $data[$num]['id'] = "";
        $num++;
        if($temp){
            foreach(PinjamanItem::where('pinjaman_id',$temp->id)->where('status',0)->get() as $k => $item){
                $data[$num]['id'] = $item->id;
                $data[$num]['bulan'] = date('d M Y',strtotime($item->bulan));
                $data[$num]['tagihan'] = format_idr($item->tagihan);
                $data[$num]['bulan_name'] = date('M',strtotime($item->bulan));
                $num++;
            }
        } 
        
        $num=0;
        $data_belum_lunas[$num]['tahun'] = date('Y');
        $num++;
        if($temp){
            foreach(PinjamanItem::where('pinjaman_id',$temp->id)->where('status',1)->get() as $k => $item){
                $data_belum_lunas[$num]['id'] = $item->id;
                $data_belum_lunas[$num]['bulan'] = date('d M Y',strtotime($item->bulan));
                $data_belum_lunas[$num]['tagihan'] = format_idr($item->tagihan);
                $data_belum_lunas[$num]['bulan_name'] = date('M',strtotime($item->bulan));
                $num++;
            } 
        }

        return response()->json(['status'=>$this->status,'data_lunas'=>$data,'data_belum_lunas'=>$data_belum_lunas], 200);
    }

    public function first()
    {
        $temp = Pinjaman::where('user_id',\Auth::user()->id)->orderBy('id','DESC')->first();
        
        $data = ['id'=>'-','bulan'=>'-','tagihan'=>'-','bulan_name'=>'-','total_pinjaman'=>'0','sisa_angsuran'=>'0'];
        if($temp){
            $data['total_pinjaman']= format_idr($temp->amount);
            $sisa_angsuran = 0;
            foreach($temp->items->where('status',0) as $k => $item){
                $data['id'] = $item->id;
                $data['bulan'] = date('d M Y',strtotime($item->bulan));
                $data['tagihan'] = format_idr($item->tagihan);
                $data['bulan_name'] = date('M',strtotime($item->bulan));
                $sisa_angsuran += $item->tagihan;
            }

            $data['sisa_angsuran'] = format_idr($sisa_angsuran);
        }

        $riwayat = [];
        foreach(Pinjaman::where('user_id',\Auth::user()->id)->orderBy('id','DESC')->get() as $pinjam){
            foreach($pinjam->items as $k =>$item){
                $riwayat[$k]['id'] = $item->id;
                $riwayat[$k]['bulan'] = date('d M Y',strtotime($item->bulan));
                $riwayat[$k]['tagihan'] = format_idr($item->tagihan);
                $riwayat[$k]['bulan_name'] = date('M',strtotime($item->bulan));
            }   
        }
        
        return response()->json(['status'=>$this->status,'message'=>'success','data'=>$data,'riwayat'=>$riwayat,'total_data'=>count($data)], 200);
    }

    public function tunaiKuota()
    {
        $kuota = \Auth::user()->pinjaman_uang;
        
        $data['kuota'] = $kuota;
        $data['kuota_idr'] = format_idr($kuota);
        $data['jasa'] = round(($this->pinjaman_jasa*2)/24,2);
        $data['bulan'] = 6;

        return response()->json(['status'=>$this->status,'data'=>$data], 200);
    }

    public function tunaiHitung(Request $r)
    {
        $this->validate($r,[
            'jumlah_pinjaman'=>'required',
        ]);

        $jumlah_pinjaman = clear_currency($r->jumlah_pinjaman);

        if($jumlah_pinjaman <$this->min_peminjaman) return response()->json(['status'=>'failed','message'=>'Minimal nominal Rp. 500.000'], 200);

        $angsuran_perbulan = 0;
        $jasa_nominal = 0;
        $data['jumlah_pinjaman'] = $r->jumlah_pinjaman;
        if($r->jumlah_pinjaman){
            $pembiayaan = clear_currency($r->jumlah_pinjaman) + $this->proteksi_pinjaman + $this->platform_fee;
            $items = [];
            $jasa = round(($this->pinjaman_jasa*2)/24,2);
            
            foreach([0=>2,1=>4,2=>6,3=>8,4=>10,5=>12] as $k=>$item){
                $pembiayaan -= $r->pembiayaan / $item;
                $items[$k]['number'] = $item;
                $items[$k]['bulan'] = date('Y-m-d',strtotime("+".($item+1)."month"));
                $items[$k]['pembiayaan'] = $pembiayaan;
                $items[$k]['cicilan_ke'] = "{$item} Bulan";
                $items[$k]['angsuran_perbulan'] = $jumlah_pinjaman / $item;
                $items[$k]['jasa'] = round($jasa,2);
                $items[$k]['jasa_nominal'] = $jumlah_pinjaman * $jasa / 100;
                $items[$k]['total'] = $items[$k]['jasa_nominal'] + $items[$k]['angsuran_perbulan'];
                $items[$k]['total_angsuran_perbulan'] = "Rp. ".($items[$k]['angsuran_perbulan']>0 ? format_idr($items[$k]['jasa_nominal'] + $items[$k]['angsuran_perbulan']) : '0'). "/bln" ;
                
                $angsuran_perbulan = $items[$k]['total'];
                $jasa_nominal = $items[$k]['jasa_nominal'];
            }
        }

        return response()->json(['status'=>$this->status,
                                'angsuran_perbulan'=>"Rp. ".format_idr($angsuran_perbulan),
                                'proteksi_pinjaman'=> "Rp. ".format_idr($this->proteksi_pinjaman),
                                'data'=>$data,
                                'platform_fee'=>"Rp. ".format_idr($this->platform_fee),
                                'items'=>$items], 200);
    }

    public function tunaiStore(Request $r)
    {
        $this->validate($r, [
            'jumlah_pinjaman'=>'required',
            'bulan'=>'required',
            'metode_pencairan'=>'required'
        ]);

        $r->jumlah_pinjaman = clear_currency($r->jumlah_pinjaman);

        if($r->jumlah_pinjaman > \Auth::user()->pinjaman_uang) return response()->json(['status'=>'failed','message'=>'Limit pinjaman kamu tidak mencukupi.'], 200);

        $data = new Pinjaman();
        $data->no_pengajuan = "P".date('my').\Auth::user()->id.str_pad((Pinjaman::count()+1),4, '0', STR_PAD_LEFT);
        $data->user_id = \Auth::user()->id;
        $data->amount = $r->jumlah_pinjaman;
        $data->angsuran = $r->bulan;
        $data->status = 0;
        $data->metode_pencairan = $r->metode_pencairan;
        $data->save();

        $transaksi = new Transaction();
        $transaksi->transaction_id = "P".date('my').\Auth::user()->id.str_pad((Transaction::count()+1),4, '0', STR_PAD_LEFT);
        $transaksi->user_id = \Auth::user()->id;
        $transaksi->price = $r->jumlah_pinjaman;
        $transaksi->description = "Pinjaman Tunai";
        $transaksi->transaction_type = 2;
        $transaksi->save();

        $angsuran_perbulan = 0;
        $jasa_nominal = 0;

        if($r->jumlah_pinjaman and $r->bulan){
            $pembiayaan = $r->jumlah_pinjaman;
            $items = [];
            $jasa = round(($this->pinjaman_jasa*2)/24,2);
            
            for($num=0;$num<$r->bulan;$num++){
                $item = new PinjamanItem();
                $item->pinjaman_id = $data->id;
                $item->bulan = date('Y-m-d',strtotime("+".($num+1)."month"));
                $item->angsuran_ke = $num+1;
                $item->angsuran_nominal = $r->jumlah_pinjaman / $r->bulan;
                $item->jasa = round($jasa,2);
                $item->jasa_nominal = $r->jumlah_pinjaman * $jasa / 100;
                $item->tagihan = $item->jasa_nominal + $item->angsuran_nominal;
                $item->save();

                $angsuran_perbulan = $item->tagihan;
                $jasa_nominal = $item->jasa_nominal;
            }
        }

        // Integration to Koperasi
        $params =
                [
                    'url'=>"/api/pembiayaan-store",
                    'transaction_type'=>1, // pulsa
                    'transaction_id'=>$transaksi->transaction_id,
                    'no_pengajuan'=>$data->no_pengajuan,
                    'pinjaman'=>$r->jumlah_pinjaman,
                    'angsuran'=>$r->bulan,
                    'jenis_pinjaman_id'=>1,
                    'angsuran_perbulan'=>$angsuran_perbulan,
                    'jasa_nominal'=>$jasa_nominal,
                    'jasa'=>$this->pinjaman_jasa,
                    'platform_fee'=>$this->platform_fee,
                    'proteksi_pinjaman'=>$this->proteksi_pinjaman
                ];

        $this->dispatch(new RequestKoperasi(\Auth::user(),$transaksi,$params)); 

        return response()->json(['status'=>$this->status,'angsuran_perbulan'=>format_idr($angsuran_perbulan),'message'=>'Pengajuan pinjaman anda berhasil disubmit, silahkan menunggu persetujuan dari kami.'], 200);
    }
}