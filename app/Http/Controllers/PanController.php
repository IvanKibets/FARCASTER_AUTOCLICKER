<?php

namespace App\Http\Controllers;

use App\Models\LogApiAri;
use Illuminate\Http\Request;

use App\Models\Pengajuan;
use App\Models\Kepesertaan;
use App\Models\KepesertaanTemp;
use App\Models\Polis;
use App\Models\UnderwritingLimit;
use App\Models\LogApiPan;
use App\Models\RateBroker;
use Illuminate\Support\Facades\DB;

class PanController extends Controller
{
    public $json = [
        
    ];
    public $polis_id = 75,$polis; // 6012303000036 - PT. PROTEKSI ANTAR NUSA QQ PT. BANK RIAU KEPRI SYARIAH (PERSERODA)
    public $branch = [
        101 => 'Cabang Utama',
        102 => 'Cabang Tembilahan',
        103 => 'Cabang Tanjung Pinang',
        104 => 'Cabang Dumai',
        105 => 'Cabang Selat Panjang',
        106 => 'Cabang Batam',
        107 => 'Cabang Pekanbaru',
        108 => 'Cabang Bengkalis',
        109 => 'Cabang Bangkinang',
        110 => 'Cabang Air Molek',
        111 => 'Cabang Tanjung Balai Karimun',
        112 => 'Cabang Pangkalan Kerinci',
        113 => 'Cabang Bagan Siapi-api',
        114 => 'Cabang Taluk Kuantan',
        115 => 'Cabang Pasir Pangaraian',
        116 => 'Cabang Siak Sri Indrapura',
        117 => 'Cabang Ranai',
        118 => 'Capem Tangkerang',
        119 => 'Capem Rumbai',
        120 => 'Capem Senapelan',
        121 => 'Capem Perawang',
        122 => 'Capem Duri',
        123 => 'Capem Tanjung Batu',
        124 => 'Capem Sei Pakning',
        125 => 'Capem Dabo Singkep',
        128 => 'Capem Ujung Batu',
        129 => 'Capem Bagan Batu',
        130 => 'Capem Sorek',
        132 => 'Capem Lubuk Baja',
        133 => 'Capem Belilas',
        134 => 'Capem Panam',
        135 => 'Cabang Bintan',
        136 => 'Kedai Marpoyan',
        137 => 'Capem Kandis',
        138 => 'Capem Lipat Kain',
        139 => 'Capem Petapahan',
        140 => 'Kedai Pasar Sail',
        141 => 'Kedai Sungai Apit',
        142 => 'Kedai Pasar Air Tiris',
        143 => 'Kedai Pasar Kuok',
        144 => 'Capem Tuanku Tambusai',
        145 => 'Kedai Jalan Durian',
        146 => 'Capem Tanjung Uban',
        147 => 'Kedai Pasar Minas',
        148 => 'Capem Sei Guntung',
        149 => 'Capem Jl Riau',
        150 => 'Kedai Pasar Ukui',
        151 => 'Kedai Pasar Bukit Kapur',
        152 => 'Kedai Pasar Sedanau',
        153 => 'Capem Dalu-dalu',
        154 => 'Capem Kota Tengah',
        155 => 'Capem Baserah',
        156 => 'Kedai Pasar Pangkalan Kerinci',
        157 => 'Kedai Pasar Peranap',
        158 => 'Kedai Pasar Pinggir',
        159 => 'Kedai Pasar Sukaramai',
        160 => 'Capem Lubuk Dalam',
        161 => 'Capem Batu Aji',
        162 => 'Kedai Pasar Tanjung Pinang',
        163 => 'Kedai Dayun',
        164 => 'Kedai Kabun',
        165 => 'Capem Ahmad Yani',
        166 => 'Kedai Pasar Pagi Arengka',
        167 => 'Kedai Tanjung Samak',
        168 => 'Kedai Pasar Lubuk Jambi',
        169 => 'Capem Ujung Tanjung',
        170 => 'Capem Tarempa',
        171 => 'Kedai Pasar Rengat',
        172 => 'Kedai Sei Lala',
        173 => 'Kedai Muara Lembu',
        174 => 'Capem Daik Lingga',
        175 => 'Capem Kota Baru',
        176 => 'Kedai Kuala Kilan',
        177 => 'Kedai Pasar Tandun',
        178 => 'Capem Flamboyan',
        179 => 'Kedai Rantau Kasai',
        180 => 'Capem Bintan Center',
        181 => 'Kedai Batupanjang Rupat',
        182 => 'Kedai Meral',
        183 => 'Kedai Midai',
        184 => 'Kedai Serasan',
        185 => 'Kedai Teluk Belitung Merbau',
        186 => 'Capem Botania',
        187 => 'Kedai Bandar Sei Kijang',
        188 => 'Kedai Pujud',
        189 => 'Kedai Sabak Auh',
        190 => 'Kedai Sungai Sembilan',
        191 => 'Cabang Jakarta',
        820 => 'Cabang Syariah Pekanbaru',
        821 => 'Cabang Syariah Tanjung Pinang',
        822 => 'Capem Syariah Tembilahan',
        823 => 'Capem Syariah Duri',
        824 => 'Capem Syariah Batam',
        825 => 'Capem Syariah Teluk Kuantan',
        826 => 'Capem Syariah Panam',
        827 => 'Capem Syariah Tanjung Balai Karimun',
        828 => 'Capem Syariah Pasir Pangaraian',
        830 => 'Kedai Syariah Kubu',
        831 => 'Kedai Syariah Bantan'
    ];

    public $pekerjaan = [
        1 => 'Pegawai Negeri Sipil termasuk Pensiunan',
        2 => 'Pegawai BUMN / BUMD / Swasta termasuk Pensiunan',
        3 => 'Pengajar dan Dosen',
        4 => 'Pedagang',
        5 => 'Petani dan Nelayan',
        6 => 'Pengrajin / Buruh / Pembantu Rumah Tangga',
        7 => 'Pengurus dan Pegawai Yayasan / LSM / Organisasi',
        8 => 'Ulama / Pendeta / Pem Organisasi dan Kelompok Agama',
        9 => 'Pelajar dan Mahasiswa',
        10 => 'Profesional dan Konsultan',
        11 => 'Pengusaha dan Wiraswasta',
        12 => 'Lain - Lain',
        13 => 'Ibu Rumah Tangga',
        14 => 'Pengurus atau Anggota Partai Politik',
        15 => 'Pejabat atau Pegawai Penyedia Jasa Keuangan',
        16 => 'Kepala Negara / Wakil Kepala Negara / Menteri / Pejabat Setingkat',
        17 => 'Hakim Agung / Hakim / Hakim Konsti / Jaksa / Penitra',
        18 => 'Pemeriksa Bea Cukai / Pemeriksa Pajak / Auditor',
        19 => 'Kepala dan Wakil Pemerintahan TK I dan TK II',
        20 => 'Pejabat Ekse / Ketua dan Anggota Legislatif / Ketua Parpol TK I',
        21 => 'Pejabat Eselon I dan II / Kepala Dep Keuangan / Bea Cukai',
        22 => 'Pimpinan BI / Direksi dan Komisaris BUMN dan BUMD',
        23 => 'TNI / POLRI / Hakim / Jaksa / Penyidik / Pengadilan',
        24 => 'Pejabat pemberi Perizinan / Kepala Unit Masyarakat',
        25 => 'Pimpinan Perguruan Tinggi Negeri',
        26 => 'Pimpinan Proyek / Bendahara Proyek',
        27 => 'Teroris / Organisasi Teroris',
        28 => 'Anggota Dewan Gub BI / Anggota Dewan Komisaris OJK',
        29 => 'Dir RSUD A / B / Wakil Dir RSUDA/Dir RS Khusus A',
        30 => 'Anggota DPRD / Lembaga Sejenis di Daerah',
        31 => 'Anggota MPR / DPR / DPD',
        32 => 'Pihak yang terkait dengan PEP (Pegawai Kontrak/Honorer)',
        33 => 'PJBT, Peg, Petugas Bidang Perizinan, Pengadaan',
        34 => 'Eselon II Instansi Pemerintah / Lembaga Negara',
        35 => 'Eselon I dan Pejabat setara Pusat, Militer, POLRI',
        36 => 'Anggota Badan Pemeriksa Keuangan (BPK)',
        37 => 'Pejabat Sektor Migas, Mineral dan Batu Bara',
        38 => 'Pejabat Pembuat Regulasi',
        39 => 'Anggota Komisi Yudisial / Dewan Pertimbangan Presiden',
        40 => 'Komnas HAM / KPK / KPI / PPU / KPAI / Komisi sesuai UU',
        41 => 'Duta Besar',
        42 => 'Pegawai Negeri Sipil termasuk Pensiunan (Top up)',
        43 => 'Karyawan Internal Bank Riau Kepri',
    ];

    public $jenis_pembiayaan = [
        111 => 'Murabahah non KUK Konsumsi',
        112 => 'Murabahah non KUK Konsumsi ANN',
        113 => 'Murabahah Konsumsi Pegawai BRS',
        114 => 'Murabahah Kepemilikan Emas',
        122 => 'Murabahah non KUK Konsumsi Flat LS',
        123 => 'Murabahah non KUK Konsumsi Annuitas LS',
        301 => 'Kredit Aneka Guna Plus',
        306 => 'Kredit KPR Bank Riau / Anuitas',
        307 => 'Kredit KPR Bank Riau / Flat',
        311 => 'Kredit KKB Roda Dua (Flat)',
        312 => 'Kredit KKB Roda Empat (Flat)',
        321 => 'Kredit KAG - Efektif Anuitas',
        322 => 'Kredit Aneka Guna (Menurun)',
        323 => 'KAG Electro',
        326 => 'Kredit Karyawan Bank Riau',
        327 => 'Kredit KAG Pra Pensiun',
        331 => 'Kredit Eks Kartu Kredit',
        332 => 'Eks Kartu Kredit',
        355 => 'MMQ Konsumtif',
        358 => 'MMQ Konsumtif Pegawai BRK',
        359 => 'MMQ Konsumtif LS',
        401 => 'Ijarah',
        404 => 'Pembiayaan Gadai / Rahn Emas',
        405 => 'Ijarah Multi Jasa Umrah',
        406 => 'Ijarah Multi Jasa Lainnya',
        408 => 'Ijarah Flat LS',
        410 => 'Ijarah Multi Jasa Lainnya LS',
        411 => 'PEMBIAYAAN GADAI/RAHN EMAS LS',
        606 => 'Kredit KPR BRK Anuitas Restruk',
        607 => 'KAG Efektif Anuitas Restruk',
        610 => 'KPR Bank Riau / Flat Restruk',
        674 => 'RECVD Kredit KPR Bank Riau / Anuits',
        675 => 'RECVD Kredit KPR Bank Riau / Flat',
        678 => 'RECVD Kredit KAG Efektif Anuitas',
        755 => 'RECVD MMQ Konsumtif',
        801 => 'RECVD Ijarah'
    ];
    public $jenis_pengajuan = [
        1 => 'CAC / Free Cover',
        2 => 'CBC (Case By Case)'
    ];
    public $benefit = [
        1 => 'All Cover',
        2 => 'Jiwa'
    ];
    public $packet = [
        '01' => 'Karyawan Bank Riaukepri (PA+ND)',
        '02' => 'Karyawan Bank Riaukepri (PA+ND+PHK)',
        '03' => 'Karyawan Bank Riaukepri (PA+ND+PHK+WP)',
        '04' => 'PNS, Pegawai BUMN, BUMD, TNI/POLRI (PA+ND)',
        '05' => 'PNS, Pegawai BUMN, BUMD, TNI/POLRI (PA+ND+PHK)',
        '06' => 'PNS, Pegawai BUMN, BUMD, TNI/POLRI PA+ND+PHK+WP)',
        '07' => 'CPNS, Pegawai Swasta, Pegawai Kontrak/Honorer (PA+ND)',
        '08' => 'CPNS, Pegawai Swasta, Pegawai Kontrak/Honorer (PA+ND+PHK)',
        '09' => 'CPNS, Pegawai Swasta, Pegawai Kontrak/Honorer (PA+ND+PHK+WP)',
        '10' => 'Wiraswasta Profesional (PA+ND)',
        '11' => 'DPRD (PAW)',
        '12' => 'PENSIUNAN',
        '13' => 'PRAPENSIUN',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['store']]);
        $this->json = [
                        'status'=>200,
                        'kode_response'=>0,
                        'message'=> 'Pengajuan Polis Asuransi Berhasil',
                        'nomor_rekening'=> '-',
                        'nomor_akad' => '-',
                        'no_polis' => '-',
                        'link_sertifikat' => '-'
                    ];
        $this->polis = Polis::find($this->polis_id);
    }

    public function store(Request $r)
    {
        log_activity('[PAN] Store');

        if($r->header('Auth-Key') != '70d73cd6253cef8e7b5414c324a8ce9ea16051b870d73cd6253cef8e7b5414c324a8ce9ea16051b8'){
            
            $this->json['kode_response'] = 10;
            $this->json['message'] = 'Invalid Authenticator Code';

            return response()->json($this->json);
        }
        // Log Api
        $log = new LogApiPan();
        $log->request = $r->getContent();
        $log->save();
        $r = json_decode($r->getContent());
        // Start Transaction
        DB::beginTransaction();
        try {

            $validate = [];
            foreach($r as $k => $val){
                $validate[$k] = $val;
            }

            // $validator = \Validator::make($r->all(), 
            $validator = \Validator::make($validate, 
                    [ 
                        'id_transaksi'=>'required',
                        'id_pengajuan'=>'required',
                        'kode_broker'=>'required',
                        'kode_cabang'=>'required',
                        'nomor_akad'=>'required',
                        'nomor_rekening'=>'required',
                        'nama'=>'required',
                        'ktp'=>'required',
                        'npwp'=>'required',
                        'jenis_kelamin'=>'required',
                        'pekerjaan'=>'required',
                        'tgl_lahir'=>'required',
                        'tgl_buka'=>'required',
                        'tenor'=>'required',
                        'jenis_Pembiayaan'=>'required',
                        'jenis_pengajuan'=>'required',
                        'plafond'=>'required',
                        'bunga'=>'required',
                        'premi_yang_dibayarkan'=>'required',
                        'benefit'=>'required',
                        'packet'=>'required'
                    ]);
            $period = (int)($r->tenor  / 12);
            // check rate
            $rate_broker = RateBroker::where(['packet'=>$r->packet,'period'=>$period])->first();
            $usia =  hitung_umur(date('Y-m-d',strtotime($r->tgl_lahir)));

            $uw = UnderwritingLimit::whereRaw("{$r->plafond} BETWEEN min_amount and max_amount")->where(['usia'=>$usia,'polis_id'=>$this->polis_id])->first();
            
            if ($validator->fails()) {
                foreach($validator->errors()->all() as $k => $val){
                    $this->json['errors'][$k] = $val;
                }
                $this->json['kode_response'] = 10;
                $this->json['message'] = 'Pengajuan Polis Asuransi Gagal';

                return response()->json($this->json);
            }
            
            if($rate_broker==""){
                $this->json['kode_response'] = 10;
                $this->json['message'] = 'Pengajuan Polis Asuransi Gagal, Rate tidak ditemukan';

                return response()->json($this->json);
            }

            if(!$uw) $uw = UnderwritingLimit::where(['usia'=>$usia,'polis_id'=>$this->polis_id])->orderBy('max_amount','ASC')->first();
            if(!$uw){
                $this->json['kode_response'] = 10;
                $this->json['message'] = "Mohon melengkapi SPK dan Copy KTP";
                return response()->json($this->json);
            }

            if($uw->keterangan=='NM' || $uw->keterangan=="") {
                $this->json['kode_response'] = 10;
                $this->json['message'] = "Mohon melengkapi SPK dan Copy KTP";
                return response()->json($this->json);
            }

            if(in_array($uw->keterangan,['A','B','C','D','E','E + FS'])){
                $this->json['kode_response'] = 10;
                $this->json['message'] = 'Mohon melengkapi hasil medis '. $uw->keterangan;
                return response()->json($this->json);
            }

            // check no pengajuan
            $pengajuan = Pengajuan::where(['source_id'=>1,'source'=>2, 'status'=>6])->first();
            if(!$pengajuan){
                $pengajuan = new Pengajuan();
                $pengajuan->source = 2;
                $pengajuan->source_id = 1; // PAN
                // $pengajuan->masa_asuransi = $this->masa_asuransi;
                // $pengajuan->perhitungan_usia = $this->perhitungan_usia;
                $pengajuan->polis_id = $this->polis_id; 
                $pengajuan->status = 6; // Draft API
                $pengajuan->total_akseptasi = Kepesertaan::where(['polis_id'=>$this->polis_id,'is_temp'=>1])->count();;
                $pengajuan->total_approve = 0;
                $pengajuan->total_reject = 0;
                $pengajuan->no_pengajuan =  date('dmy').str_pad((Pengajuan::count()+1),6, '0', STR_PAD_LEFT);
                // $pengajuan->account_manager_id = \Auth::user()->id;
                $pengajuan->save();
            }

            // $peserta = Kepesertaan::where(['polis_id'=>$this->polis_id, 'pengajuan_id'=>$pengajuan->id,'is_temp'=>1,'no_ktp'=>$r->ktp])->first();
            $peserta = KepesertaanTemp::where(['polis_id'=>$this->polis_id, 'pengajuan_id'=>$pengajuan->id,'is_temp'=>1,'no_ktp'=>$r->ktp])->first();            

            if(!$peserta){
                $peserta = new KepesertaanTemp();
                $peserta->tanggal_mulai = date('Y-m-d',strtotime($r->tgl_buka));
                $peserta->tanggal_akhir = date('Y-m-d',strtotime("+{$r->tenor} months",strtotime($r->tgl_buka)));
                $peserta->polis_id = $this->polis_id;
                $peserta->pengajuan_id = $pengajuan->id;
                $peserta->is_temp = 1;
                $peserta->status_polis = 'Akseptasi';
                $peserta->cab = $r->kode_cabang;
                $peserta->no_akad_kredit = $r->nomor_akad;
                $peserta->nomor_rekening = $r->nomor_rekening;
                $peserta->nama = $r->nama;
                $peserta->no_ktp = $r->ktp;
                $peserta->npwp = $r->npwp;
                $peserta->jenis_kelamin = $r->jenis_kelamin=='L' ? 'Laki-laki' : 'Perempuan';
                $peserta->pekerjaan = @$this->pekerjaan[$r->pekerjaan];
                $peserta->tanggal_lahir = date('Y-m-d',strtotime($r->tgl_lahir));
                $peserta->masa_bulan = $r->tenor;
                $peserta->jenis_pembiayaan = @$this->jenis_pembiayaan[$r->jenis_Pembiayaan];
                $peserta->jenis_pengajuan = @$this->jenis_pengajuan[$r->jenis_pengajuan];
                $peserta->basic = $r->plafond;
                $peserta->bunga = $r->bunga;
                $peserta->kontribusi = $r->plafond*($rate_broker->ajri/1000);
                $peserta->benefit = @$this->benefit[$r->benefit];
                $peserta->packet = @$this->packet[$r->packet];    
                $peserta->usia = $usia;
                $peserta->rate = $rate_broker->ajri;
                $peserta->ari_kontribusi = 0;
                $peserta->ari_rate = $rate_broker->ari;
                $peserta->status_akseptasi =  0;
                $peserta->save();
                
                $peserta->ul = $uw->keterangan;

                $running_number = $this->polis->running_number_peserta+1;
                $no_peserta = (isset($this->polis->produk->id) ? $this->polis->produk->id : '0') ."-". date('ym').str_pad($running_number,7, '0', STR_PAD_LEFT).'-'.str_pad($this->polis->running_number,3, '0', STR_PAD_LEFT);
                $peserta->no_peserta = $no_peserta;

                // save running number
                $this->polis->running_number_peserta = $running_number;
                $this->polis->save();

                $this->json['no_peserta'] = $peserta->no_peserta;
                $this->json['no_polis'] = $this->polis->no_polis;
                $this->json['link_sertifikat'] = 'https://ajrius.id/generate-sertifikat/'.$peserta->no_peserta;   
                
                /**
                 * Jika benefit All Cover
                 */
                if($r->benefit==1){
                    // request to ARI
                    $param = '{
                        "EffDate": "'.date('m/d/Y',strtotime($r->tgl_buka)).'",
                        "ExpDate": "'.date('m/d/Y',strtotime($peserta->tanggal_akhir)).'",
                        "Tenor": "'.$r->tenor.'",
                        "ContractNo": "'.$r->nomor_rekening.'", 
                        "ParticipantName": "'.$r->nama.'", 
                        "ParticipantDOB": "'.date('m/d/Y',strtotime($r->tgl_lahir)).'", 
                        "ParticipantIdentityNo": "'.$r->ktp.'", 
                        "ParticipantGender": "'.$r->jenis_kelamin.'", 
                        "ParticipantPhone": "",
                        "ParticipantEmail": "", 
                        "ParticipantAddress": "",
                        "SIAmount": "'.$r->plafond.'",
                        "LenderName": "",
                        "LenderAddress": "",
                        "Remarks": "",
                        "ProductId": "13",
                        "SubProductId":"23",
                        "PremiumRate": "'.$rate_broker->ari.'", 
                        "PremiumAmount":"'.$r->premi_yang_dibayarkan.'",
                        "IdPengajuan": "'.$r->id_pengajuan.'",
                        "IdTransaksi": "'.$r->id_transaksi.'",
                        "KodeBenefit": "'.$r->benefit.'",
                        "KodeBroker": "'.$r->kode_broker.'",
                        "KodeCabang": "'.$r->kode_cabang.'",
                        "KodePacket": "'.$r->packet.'",
                        "KodePekerjaan": "'.$r->pekerjaan.'",
                        "KodePembiayaan": "'.$r->jenis_Pembiayaan.'",
                        "KodePengajuan": "'.$r->jenis_pengajuan.'",
                        "NomorAkad": "'.$r->nomor_akad.'",
                        "NPWP": "'.$r->npwp.'"
                    }';

                    // log API ARI
                    $log_ari = new LogApiAri();
                    $log_ari->request = $param;
                    $log_ari->kepesertaan_id = $peserta->id;
                    $log_ari->save();

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => env('ARI_API_URL_PRODUCTION') .'/policybrk/submission',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS =>$param,
                        CURLOPT_HTTPHEADER => array(
                            'x-api-key: '.env('ARI_API_KEY'),
                            'x-api-secret: '.env('ARI_API_SCRET'),
                            'Content-Type: application/json'
                        ),
                    ));

                    $response = curl_exec($curl);
                    
                    $log_ari->response = $response;
                    $log_ari->save();
                    $response_ari =  json_decode($response);
                    if($response_ari->ErrorCode==200){
                        $peserta->ari_sertifikat = env('ARI_API_URL_PRODUCTION').$response_ari->Data->URLCertificate;
                        $peserta->ari_data = json_encode($response_ari->Data);
                    }
                    curl_close($curl);
                }
                
                $peserta->save();
            }else{
                $peserta->cab = $r->kode_cabang;
                $peserta->no_akad_kredit = $r->nomor_akad;
                $peserta->nomor_rekening = $r->nomor_rekening;
                $peserta->nama = $r->nama;
                $peserta->no_ktp = $r->ktp;
                $peserta->npwp = $r->npwp;
                $peserta->jenis_kelamin = $r->jenis_kelamin=='L' ? 'Laki-laki' : 'Perempuan';
                $peserta->pekerjaan = @$this->pekerjaan[$r->pekerjaan];
                $peserta->tanggal_lahir = date('Y-m-d',strtotime($r->tgl_lahir));
                $peserta->masa_bulan = $r->tenor;
                $peserta->jenis_pembiayaan = @$this->jenis_pembiayaan[$r->jenis_Pembiayaan];
                $peserta->jenis_pengajuan = @$this->jenis_pengajuan[$r->jenis_pengajuan];
                $peserta->basic = $r->plafond;
                $peserta->bunga = $r->bunga;
                $peserta->kontribusi = $r->premi_yang_dibayarkan;
                $peserta->benefit = @$this->benefit[$r->benefit];
                $peserta->packet = @$this->packet[$r->packet];    
                $peserta->usia = $usia;
                $peserta->save();
                /**
                 * Jika benefit All Cover
                 */
                if($r->benefit==1){
                    // request to ARI
                    $param = '{
                        "EffDate": "'.date('m/d/Y',strtotime($r->tgl_buka)).'",
                        "ExpDate": "'.date('m/d/Y',strtotime($peserta->tanggal_akhir)).'",
                        "Tenor": "'.$r->tenor.'",
                        "ContractNo": "'.$r->nomor_rekening.'", 
                        "ParticipantName": "'.$r->nama.'", 
                        "ParticipantDOB": "'.date('m/d/Y',strtotime($r->tgl_lahir)).'", 
                        "ParticipantIdentityNo": "'.$r->ktp.'", 
                        "ParticipantGender": "'.$r->jenis_kelamin.'", 
                        "ParticipantPhone": "",
                        "ParticipantEmail": "", 
                        "ParticipantAddress": "",
                        "SIAmount": "'.$r->plafond.'",
                        "LenderName": "",
                        "LenderAddress": "",
                        "Remarks": "",
                        "ProductId": "13",
                        "SubProductId":"23",
                        "PremiumRate": "'.$rate_broker->ari.'", 
                        "PremiumAmount":"'.$r->premi_yang_dibayarkan.'",
                        "IdPengajuan": "'.$r->id_pengajuan.'",
                        "IdTransaksi": "'.$r->id_transaksi.'",
                        "KodeBenefit": "'.$r->benefit.'",
                        "KodeBroker": "'.$r->kode_broker.'",
                        "KodeCabang": "'.$r->kode_cabang.'",
                        "KodePacket": "'.$r->packet.'",
                        "KodePekerjaan": "'.$r->pekerjaan.'",
                        "KodePembiayaan": "'.$r->jenis_Pembiayaan.'",
                        "KodePengajuan": "'.$r->jenis_pengajuan.'",
                        "NomorAkad": "'.$r->nomor_akad.'",
                        "NPWP": "'.$r->npwp.'"
                    }';
                    // log API ARI
                    $log_ari = new LogApiAri();
                    $log_ari->request = $param;
                    $log_ari->kepesertaan_id = $peserta->id;
                    $log_ari->save();

                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => env('ARI_API_URL_PRODUCTION') .'/policybrk/submission',
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => '',
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => 'POST',
                        CURLOPT_POSTFIELDS =>$param,
                        CURLOPT_HTTPHEADER => array(
                            'x-api-key: '.env('ARI_API_KEY'),
                            'x-api-secret: '.env('ARI_API_SCRET'),
                            'Content-Type: application/json'
                        ),
                    ));

                    $response = curl_exec($curl);
                    
                    $log_ari->response = $response;
                    $log_ari->save();
                    $response_ari =  json_decode($response);
                    if($response_ari->ErrorCode==200){
                        $peserta->ari_sertifikat = env('ARI_API_URL_PRODUCTION').$response_ari->Data->URLCertificate;
                        $peserta->ari_data = json_encode($response_ari->Data);
                    }
                    curl_close($curl);
                }
                
                $peserta->save();

                $this->json['no_peserta'] = $peserta->no_peserta;
                $this->json['no_polis'] = $this->polis->no_polis;
                $this->json['link_sertifikat'] = 'https://ajrius.id/generate-sertifikat/'.$peserta->no_peserta;   
            }

            $log->kepesertaan_id = $peserta->id;
            $log->save();

            DB::commit();
            // all good
        } catch (\Exception $e) {
            DB::rollback();
            $this->json['error'] = $e;
        }

        $this->json['nomor_akad'] = $r->nomor_akad;
        
        return response()->json($this->json);
    }
}