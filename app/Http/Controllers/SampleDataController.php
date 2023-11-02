<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;
use App\Models\AcctCreditsAccount;
use App\Models\AcctCreditsPayment;
use App\Models\AcctDepositoProfitSharing;
use App\Models\AcctProfitLoss;
use App\Models\CoreCity;
use App\Models\CoreDusun;
use App\Models\CoreKecamatan;
use App\Models\CoreKelurahan;
use App\Models\User;
use App\Models\PreferenceCollectibility;
use App\Models\SystemLogUser;
use App\Helpers\Configuration;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SampleDataController extends Controller
{
    /**
     * Sample data calculation and formatting
     *
     * @return \Illuminate\Support\Collection
     */
    public function profits()
    {
        // $months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

        // $data = collect(json_decode(file_get_contents(resource_path('samples/sales.json'))));

        // $d = $data->groupBy(function ($data) {
        //     return Carbon::parse($data->datetime)->format('Y-m');
        // })->map(function ($data) {
        //     return [
        //         'profit'  => number_format($data->sum('profit') / 11, 2),
        //         'revenue' => number_format($data->sum('revenue') / 13, 2),
        //     ];
        // })->sortKeys()->mapWithKeys(function ($data, $key) use ($months) {
        //     return [$months[Carbon::parse($key)->format('n')] => $data];
        // });

        $d = collect([
            "Januari" => [
                "profit" => $this->getProfitLossAmount(1),
            ],
            "Februari" => [
                "profit" => $this->getProfitLossAmount(2),
            ],
            "Maret" => [
                "profit" => $this->getProfitLossAmount(3),
            ],
            "April" => [
                "profit" => $this->getProfitLossAmount(4),
            ],
            "Mei" => [
                "profit" => $this->getProfitLossAmount(5)
            ],
            "Juni" => [
                "profit" => $this->getProfitLossAmount(6)
            ],
            "Juli" => [
                "profit" => $this->getProfitLossAmount(7)
            ],
            "Agustus" => [
                "profit" => $this->getProfitLossAmount(8)
            ],
            "September" => [
                "profit" => $this->getProfitLossAmount(9)
            ],
            "Oktober" => [
                "profit" => $this->getProfitLossAmount(10)
            ],
            "November" => [
                "profit" => $this->getProfitLossAmount(11)
            ],
            "Desember" => [
                "profit" => $this->getProfitLossAmount(12)
            ],
        ]);

        return $d;
    }

    public function getProfitLossAmount($month)
    {
        $data = AcctProfitLoss::where('month_period', $month)
        ->where('year_period', date('Y'))
        ->first();

        if (empty($data)) {
            return 0;
        } else {
            return $data->profit_loss_amount;
        }

    }

    public static function getMonth()
    {
        $month = Configuration::Month()[date('m')];

        return $month;
    }
    protected static function getIsOnlineUser($user_id)
    {
        $data = SystemLogUser::where('user_id', $user_id)
        ->whereDate('log_time', date('Y-m-d'))
        ->orderBy('user_log_id','DESC')
        ->first();

        if (!empty($data)) {
            if ($data->remark == 'Logout System') {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    public static function getDataUser()
    {

        $user = User::select('system_user_group.user_group_name','system_user.username','system_user.avatar','system_user.user_id')
        ->join('system_user_group','system_user_group.user_group_id','=','system_user.user_group_id')
        ->where('system_user.user_group_id','!=',1)
        ->where('system_user.data_state',0)
        ->where('system_user.branch_id', auth()->user()->branch_id)
        ->get();

        foreach ($user as $key => $val) {
            $data[] = array(
                "user_group_name"   => $val['user_group_name'],
                "username"          => $val['username'],
                "avatar"            => $val['avatar'],
                "isOnline"          => self::getIsOnlineUser($val['user_id']),
            );
        }

        return $data;
    }



    public static function getNotifications()
    {

        $notif = array();

        $user = User::select('password_date')
        ->where('user_id', auth()->user()->user_id)
        ->first();

        $sum_day = strtotime(date('Y-m-d')) - strtotime($user['password_date']);

        $now        = new DateTime();
        $date       = new DateTime($user['password_date']);
        $sum_day    = $date->diff($now)->format("%a");

        if ($sum_day > 30) {
            $notif_item = array(
                'color' => 'danger',
                'icon' => 'icons/duotune/coding/cod009.svg',
                'title' => 'Password belum diubah lebih dari satu bulan !',
                'text' => $sum_day.' hari',
                'link' => 'user/settings',
            );

            array_push($notif, $notif_item);
        }

        $deposito = AcctDepositoProfitSharing::join('acct_deposito_account', 'acct_deposito_profit_sharing.deposito_account_id','=','acct_deposito_account.deposito_account_id')
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_due_date', date('Y-m-d'))
        ->where('acct_deposito_profit_sharing.deposito_profit_sharing_status', 0)
        ->where('acct_deposito_account.data_state', 0)
        ->get();

        if (count($deposito) > 0) {
            $notif_item = array(
                'color' => 'warning',
                'icon' => 'icons/duotune/finance/fin005.svg',
                'title' => 'Hari ini ada simpanan berjangka yang jatuh tempo !',
                'text' => count($deposito).' deposito',
                'link' => 'deposito-profit-sharing',
            );

            array_push($notif, $notif_item);
        }

        if ((date('t') - date('d')) <= 3) {
            $notif_item = array(
                'color' => 'info',
                'icon' => 'icons/duotune/general/gen058.svg',
                'title' => 'Jangan lupa proses End of Month !',
                'text' => date('t') - date('d').' hari sebelum akhir bulan',
                'link' => 'savings-profit-sharing',
            );

            array_push($notif, $notif_item);
        }

        return $notif;
    }

    public function getWeek($week, $year) {
        $dto = new DateTime();
        $result['start']	 = $dto->setISODate($year, $week, 0)->format('Y-m-d');
        $result['end']	 = $dto->setISODate($year, $week, 6)->format('Y-m-d');

        return $result;
    }

    public function getDataKolektibilitas()
    {
        $signupdate=date('Y-m-01');
        $signupweek=date("W",strtotime($signupdate));
        $year=date("Y",strtotime($signupdate));
        $currentweek = date("W");
        $coll	= PreferenceCollectibility::get();
        $currentweek = $signupweek + $coll->count()-1;
        $no=0;
        $index=0;
        for($i=$signupweek;$i<=$currentweek;$i++)
        {
            $collectibility 	= PreferenceCollectibility::get();
            $no++;
            $result = $this->getWeek($i,$year);

            $date = array (
                'start'		=> $result['start'],
                'end'		=> $result['end'],
            );

            $creditsaccount = AcctCreditsAccount::select('acct_credits_account.credits_account_id', 'acct_credits_account.credits_account_serial', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_principal_amount', 'acct_credits_account.credits_account_interest_amount', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.credits_account_last_payment_date', 'acct_credits_account.credits_account_payment_date')
            ->where('acct_credits_account.data_state', 0)
            ->where('acct_credits_account.credits_approve_status', 1)
            ->orderBy('acct_credits_account.credits_account_serial', 'ASC')
            ->get();

            for($v = 1; $v <= $collectibility->count() ; $v++){
                $total[$v]=0;
            }
            foreach ($creditsaccount as $key => $val) {

                $date1 = date_create($date['end']);
                $date2 = date_create($val['credits_account_payment_date']);

                $interval    = $date1->diff($date2);
                $tunggakan   = $interval->days;

                if($date2 >= $date1){
                    $tunggakan2 = 0;
                }else{
                    $tunggakan2 = $tunggakan;
                    // dump([$val,$tunggakan]);
                }
                foreach ($collectibility as $k => $v) {
                    if($tunggakan2 >= $v['collectibility_bottom'] && $tunggakan2 <= $v['collectibility_top']){
                        $collectibility_id = $v['collectibility_id'];
                    }
                    if($tunggakan2 > $v['collectibiliity_top']){
                        $collectibility_id = $collectibility->sortByDesc('collectibility_id')->first()->collectibility_id;
                    }
                }
                for($v = 1; $v <= $collectibility->count() ; $v++){
                    if($collectibility_id==$v){
                    $total[$v]+=$val['credits_account_last_balance'];
                }
                }

            }

            $data_kolektibilitas[$index]['minggu']		= 'Minggu ke '.$no;

            for($v = 1; $v <= $collectibility->count() ; $v++){
                if($collectibility[$v-1]['collectibility_id']==$v){
                // $total[$v]=$total[$v]+$val['credits_account_last_balance'];
                $data_kolektibilitas[$index][$v]		= $total[$v];
            }}
            $index++;
        }

        return $data_kolektibilitas;
    }
    public function test(){
        return $this->getDataKolektibilitas();
    }
    public function getDataGrafikMonth()
    {
        $date 		= date('d-m-Y');
        $max_date 	= date('t');
        $month 		= date('m');
        $year 		= date('Y');
        // print_r($date); exit;

        for ($i=1; $i <= $max_date ; $i++) {
            if($i < 10){
                $i= '0'.$i;
            }
            $date = $year.'-'.$month.'-'.$i;

            $total_pencairan 	= AcctCreditsAccount::select(DB::Raw('SUM(acct_credits_account.credits_account_amount) AS total_pencairan'))
            ->where('credits_account_date', $date)
            ->where('data_state',0)
            ->where('credits_approve_status',1)
            ->where('credits_account_status',0)
            ->first();
            $total_credits	 	= AcctCreditsPayment::select(DB::Raw('SUM(acct_credits_payment.credits_principal_last_balance) AS total_outstanding'))
            ->where('credits_payment_date', $date)
            ->where('data_state',0)
            ->groupBy('credits_account_id')
            ->groupBy('credits_principal_last_balance')
            ->orderBy('credits_account_id','DESC')
            ->orderBy('credits_principal_last_balance','DESC')
            ->first();
            // $total_payment		= AcctCreditsAccount::select(DB::Raw('SUM(acct_credits_account.credits_account_last_balance) as total_outstanding'))
            // ->join('acct_credits_payment','acct_credits_payment.credits_account_id','=','acct_credits_account.credits_account_id')
            // ->whereNotIn('acct_credits_account.credits_account_id','acct_credits_payment.credits_account_id')
            // ->where('acct_credits_payment.credits_payment_date','<=', $date)
            // ->groupBy('acct_credits_payment.credits_account_id')
            // ->where('acct_credits_account.credits_account_date','<=', $date)
            // ->where('acct_credits_account.data_state',0)
            // ->where('acct_credits_account.credits_account_status',0)
            // ->first();
            $total_akun_os		= AcctCreditsAccount::select(DB::Raw('count(acct_credits_account.credits_account_last_balance) as total_akun'))
            ->where('credits_account_date','<=', $date)
            ->where('data_state',0)
            ->where('credits_approve_status',1)
            ->where('credits_account_status',0)
            ->first();
            $total_akun 		= AcctCreditsAccount::select(DB::Raw('count(acct_credits_account.credits_account_amount) as total_akun'))
            ->where('credits_account_date', $date)
            ->where('data_state',0)
            ->where('credits_approve_status',1)
            ->where('credits_account_status',0)
            ->first();


            $total_credits_payment_amount = AcctCreditsPayment::select(DB::Raw('SUM(acct_credits_payment.credits_payment_amount) AS total_credits_payment_amount'))
            ->join('acct_credits_account','acct_credits_payment.credits_account_id','=' ,'acct_credits_account.credits_account_id')
            ->where('acct_credits_payment.data_state',0)
            ->where('acct_credits_account.credits_account_status',0)
            ->where('acct_credits_payment.credits_payment_date','<=',$date)
            ->first();
            $total_credits_account_amount = AcctCreditsAccount::select(DB::Raw('SUM(credits_account_amount) AS total_credits_account_amount'))
            ->where('data_state', 0)
			->where('credits_account_status', 0)
			->where('credits_approve_status', 1)
			->where('credits_account_date','<=', $date)
            ->first();
            //print_r($total_payment); exit;
            $total_outstanding = (int)$total_credits_account_amount['total_credits_account_amount'] - (int)$total_credits_payment_amount['total_credits_payment_amount'] ;

            if(empty($total_pencairan)){
                $total_pencairan = 0;
            } else {
                $total_pencairan = (int)$total_pencairan['total_pencairan'];
            }
            if(empty($total_outstanding)){
                $total_outstanding = 0;
            } else {
                $total_outstanding = (int)$total_outstanding;
            }
            if(empty($total_akun)){
                $total_akun = 0;
            } else {
                $total_akun = (int)$total_akun['total_akun'];
            }
            if(empty($total_akun_os)){
                $total_akun_os = 0;
            } else {
                $total_akun_os = (int)$total_akun_os['total_akun'];
            }

            $data_pencairan[$i]['day'] 			    = (int)$i;
            $data_pencairan[$i]['income']			= $total_pencairan;
            $data_pencairan[$i]['expenses']	 		= $total_outstanding;
            $data_pencairan[$i]['jumlah_akun']		= $total_akun;
            $data_pencairan[$i]['jumlah_akun_os']	= $total_akun_os;
        }

        return $data_pencairan;
    }

    public function dropdownCity(Request $request)
    {
        $city = CoreCity::select('city_id','city_name')
        ->where('province_id', $request->province_id)
        ->where('data_state',0)
        ->get();

        $data = '';
        $data .= "<option value=''>Pilih</option>\n";
        foreach($city as $key => $value){
            if ($value['city_id'] == $request->city_id) {
                $data .= "<option data-kt-flag='$value[city_id]' value='$value[city_id]' $value[city_id] selected>$value[city_name]</option>\n";
            } else {
                $data .= "<option data-kt-flag='$value[city_id]' value='$value[city_id]' $value[city_id]>$value[city_name]</option>\n";
            }
        }

        return $data;
    }

    public function dropdownKecamatan(Request $request)
    {
        $city = CoreKecamatan::select('kecamatan_id','kecamatan_name')
        ->where('city_id', $request->city_id)
        ->where('data_state',0)
        ->get();

        $data = '';
        $data .= "<option value=''>Pilih</option>\n";
        foreach($city as $key => $value){
            if ($value['kecamatan_id'] == $request->kecamatan_id) {
                $data .= "<option data-kt-flag='$value[kecamatan_id]' value='$value[kecamatan_id]' $value[kecamatan_id] selected>$value[kecamatan_name]</option>\n";
            } else {
                $data .= "<option data-kt-flag='$value[kecamatan_id]' value='$value[kecamatan_id]' $value[kecamatan_id]>$value[kecamatan_name]</option>\n";
            }
        }

        return $data;
    }

    public function dropdownKelurahan(Request $request)
    {
        $city = CoreKelurahan::select('kelurahan_id','kelurahan_name')
        ->where('kecamatan_id', $request->kecamatan_id)
        ->where('data_state',0)
        ->get();

        $data = '';
        $data .= "<option value=''>Pilih</option>\n";
        foreach($city as $key => $value){
            if ($value['kelurahan_id'] == $request->kelurahan_id) {
                $data .= "<option data-kt-flag='$value[kelurahan_id]' value='$value[kelurahan_id]' $value[kelurahan_id] selected>$value[kelurahan_name]</option>\n";
            } else {
                $data .= "<option data-kt-flag='$value[kelurahan_id]' value='$value[kelurahan_id]' $value[kelurahan_id]>$value[kelurahan_name]</option>\n";
            }
        }

        return $data;
    }

    public function dropdownDusun(Request $request)
    {
        $city = CoreDusun::select('dusun_id','dusun_name')
        ->where('kelurahan_id', $request->kelurahan_id)
        ->where('data_state',0)
        ->get();

        $data = '';
        $data .= "<option value=''>Pilih</option>\n";
        foreach($city as $key => $value){
            $data .= "<option data-kt-flag='$value[dusun_id]' value='$value[dusun_id]'>$value[dusun_name]</option>\n";
        }

        return $data;
    }
}
