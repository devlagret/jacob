<?php

namespace App\Http\Controllers;

use App\DataTables\RestoreDataDataTable;
use App\Http\Controllers\Controller;
use GrahamCampbell\ResultType\Success;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class RestoreDataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

    }
    public function index(){
        Session::forget('restore-table');
        abort_unless(Auth::id()==37, 401);
        $table = collect();
        $dbName = config('app.db');
        $tables = collect(DB::select('SHOW TABLES'))
        ->whereNotIn('Tables_in_'.$dbName,[
            'preference_company',
            'purchase_payment_giro',
            'sales_collection_expense',
            'sales_collection_item',
            'sales_invoice_expense',
            'sales_invoice_item',
            'sales_order_item',
            'system_change_log',
            'system_log_user',
            'system_menu',
            'system_menu_mapping',
            'acct_journal_voucher',
            'acct_journal_voucher_item',
            'acct_account_balance_detail',
            'acct_account_balance',
            'invt_stock_adjustment',
            'invt_stock_adjustment_item',
            'acct_account',
            'acct_balance_sheet_report',
            'acct_profit_loss_report',
            'core_city',
            'core_province',
            'preference_transaction_module',
            'acct_account_mutation',
            'acct_account_opening_balance',
            'acct_profit_loss',
            'acct_recalculate_log',
            'acct_savings_account_detail',
            'acct_savings_account_detail_temp',
            'acct_savings_account_temp',
            'acct_savings_close_book_log',
            'acct_savings_profit_sharing_log',
            'acct_savings_profit_sharing_temp',
            'acct_savings_syncronize_log',
            'activity_log',
            'ci_sessions',
            'core_member_working',
            'failed_jobs',
            'migrations',
            'model_has_permissions',
            'model_has_roles',
            'password_resets',
            'permissions',
            'preference_collectibility',
            'preference_inventory',
            'preference_ppob',
            'role_has_permissions',
            'roles',
            'savings_profit_sharing_log',
            'settings',
            'shu_last_year',
            'system_end_of_days',
            'system_activity_log',
            'system_period_log',
            'system_user_dusun',
            'user_infos',
            ])
        ->pluck('Tables_in_'.$dbName)->flatten();
        foreach($tables as $val){
            // $header = collect(DB::select('DESCRIBE '.$table))->pluck('Field');
            $data = DB::table($val)->where('data_state','!=',0)->count();
            if($data != 0){
                $table->push([$val=>$data]);
            }
        }
        $table = $table->collapse()->sortDesc();
        return view('content.RestoreData.index',compact('table'));
    }
    public function table($table, RestoreDataDataTable $datatable) {
        abort_unless(Auth::id()==37, 401);
        Session::put('restore-table',$table);
        $pk = collect(DB::select("SHOW KEYS FROM ".$table." WHERE Key_name = 'PRIMARY'"))->pluck('Column_name')[0];
        return $datatable->render('content.RestoreData.Table.index',compact('pk','table'));
    }
    public function restore($table,$col,$id){
        abort_unless(Auth::id()==37, 401);
        try{
        $data = DB::table($table)->where($col,$id);
        $data->update(['data_state'=>0]);
        return redirect()->route('restore.table', ['table' => $table])->with(['alert'=>'success','pesan'=>'Restore Data Berhasil']);
        }catch(\Illuminate\Database\QueryException $e){
            error_log($e);
            return redirect()->route('restore.table', ['table' => $table])->with(['pesan' => 'Restore Data Gagal','alert' => 'success']);
        }
    }
    public function forceDelete($table,$col,$id) {
        return [$table,$col,$id];
        abort_unless(Auth::id()==37, 401);
        try{
        $data = DB::table($table)->where($col,$id);
        $data->forceDelete();
        return redirect()->route('restore.table', ['table' => $table])->with(['alert'=>'success','pesan'=>'Hapus Berhasil']);
        }catch(\Illuminate\Database\QueryException $e){
            error_log($e);
            return redirect()->route('restore.table', ['table' => $table])->with(['alert'=>'danger','pesan'=>'Hapus Gagal']);
        }
    }
}
