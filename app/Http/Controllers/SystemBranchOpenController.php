<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\SystemEndOfDays;
use App\Models\AcctAccount;
use App\Models\AcctJournalVoucher;
use App\Models\AcctJournalVoucherItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;

class SystemBranchOpenController extends Controller
{
    public function index()
    {
        $endofdays = SystemEndOfDays::select('*')
        ->orderBy('created_at', 'DESC')
        ->first();

        return view('content.SystemBranchOpen.index', compact('endofdays'));
    }

    public function process()
    {
        $data = array(
            'end_of_days_status'    => 1,
            'debit_amount'		    => 0,
            'credit_amount'		    => 0,
            'open_at'			    => date('Y-m-d H:i:s'),
            'open_id'			    => auth()->user()->user_id,
            'created_at'		    => date('Y-m-d H:i:s'),
        );

        if(SystemEndOfDays::create($data)){
            $message = array(
                'pesan' => 'Cabang Telah Dibuka, Semangat Bekerja !',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Buka Cabang gagal',
                'alert' => 'error'
            );
        }

        return redirect('branch-open')->with($message);
    }
}
