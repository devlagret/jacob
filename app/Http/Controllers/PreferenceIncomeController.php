<?php

namespace App\Http\Controllers;

use App\DataTables\PreferenceIncomeDataTable;
use App\Helpers\Configuration;
use App\Models\AcctAccount;
use App\Models\PreferenceIncome;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class PreferenceIncomeController extends Controller
{
    public function index(PreferenceIncomeDataTable $dataTable)
    {
        Session::forget('data-income');
        $akun = AcctAccount::select(DB::raw("account_id, CONCAT(account_code,' - ', account_name) as account_code "))->where('data_state',0)->get()->pluck('account_code','account_id');
        $kp = Configuration::KelompokPerkiraan();
        return $dataTable->render('content.PreferenceIncome.index',compact('akun',"kp"));
    }
    public function processAdd(Request $request) {
        $request->validate(['income_name'=>'required','account_id'=>'required','income_group'=>'required'],
        ['income_name.required'=>'Harap Nama Pendapatan','account_id.required'=>'Harap Masukan Kode Pendapatan','income_group.required'=>'Harap Masukan Kelompok Pendapatan']);
        try {
            DB::beginTransaction();
            PreferenceIncome::create([
                'account_id'=>$request->account_id,
                'income_name' => $request->income_name,
                'income_percentage' => $request->income_percentage,
                'income_group' => $request->income_group,
                'created_id'=>Auth::id()
            ]);
            DB::commit();
            return redirect()->route('preference-income.index')->with(['pesan' => 'Tambah Data Pendapatan Sukses','alert' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return redirect()->route('preference-income.index')->with(['pesan' => 'Tambah Data Pendapatan Gagal','alert' => 'danger']);
        }
    }
    public function elemenAdd(Request $request) {
        $data=Session::get('data-income');
        $data[$request->name]=$request->value;
        Session::put('data-income',$data);
        return response(1);
    }
    public function processEdit(Request $request)
    {
        $request->validate(['data.*.account_id'=>'required','data.*.income_group'=>'required'],
        ['data.*.account_id.required'=>'Harap Masukan Kode Pendapatan','data.*.income_group.required'=>'Harap Masukan Kelompok Pendapatan']);
        try {
            DB::beginTransaction();
            foreach($request->data as $val){
                $income = PreferenceIncome::findOrFail($val['income_id']);
                $income->account_id =  $val['account_id'];
                $income->income_percentage =  $val['income_percentage'];
                $income->income_group =  $val['income_group'];
                $income->save();
            }
            DB::commit();
            return redirect()->route('preference-income.index')->with(['pesan' => 'Edit Data Pendapatan Sukses','alert' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return redirect()->route('preference-income.index')->with(['pesan' => 'Edit Data Pendapatan Gagal','alert' => 'danger']);
        }
    }
    public function delete($income_id)
    {
        $income                 = PreferenceIncome::findOrFail($income_id);
        $income->data_state     = 1;
        if($income->save()&&$income->delete()){
            $message = array(
                'pesan' => 'Hapus Data Pendapatan Sukses',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Hapus Data Pendapatan Gagal',
                'alert' => 'error'
            );
        }
        return redirect()->route('preference-income.index')->with($message);
    }
}
