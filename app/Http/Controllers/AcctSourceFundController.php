<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctSourceFund;
use App\DataTables\AcctSourceFundDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AcctSourceFundController extends Controller
{
    public function index(AcctSourceFundDataTable $dataTable)
    {
        return $dataTable->render('content.AcctSourceFund.List.index');
    }

    public function add()
    {
        $config     = theme()->getOption('page', 'source-fund');

        return view('content.AcctSourceFund.Add.index');
    }

    public function processAdd(Request $request)
    {
        $fields = request()->validate([
            'source_fund_code'  =>['required'],
            'source_fund_name'  =>['required'],
        ]);
        
        $sourcefund  = array(
            'source_fund_code'  => $fields['source_fund_code'],
            'source_fund_name'  => $fields['source_fund_name'],
            'created_id'        => auth()->user()->user_id,
        );
        
        if(AcctSourceFund::create($sourcefund)){
            $message = array(
                'pesan' => 'Sumber Dana berhasil ditambah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Sumber Dana gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('source-fund')->with($message);
    }

    public function edit($source_fund_id)
    {
        $sourcefund    = AcctSourceFund::findOrFail($source_fund_id);

        return view('content.AcctSourceFund.Edit.index', compact('sourcefund'));
    }

    public function processEdit(Request $request)
    {
        $fields = $request->validate([
            'source_fund_id'     => 'required',
            'source_fund_code'   => 'required',
            'source_fund_name'   => 'required'
        ]);

        $sourcefund                     = AcctSourceFund::findOrFail($fields['source_fund_id']);
        $sourcefund->source_fund_code   = $fields['source_fund_code'];
        $sourcefund->source_fund_name   = $fields['source_fund_name'];
        $sourcefund->updated_id         = auth()->user()->user_id;

        if($sourcefund->save()){
            $message = array(
                'pesan' => 'Sumber Dana berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Sumber Dana gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('source-fund')->with($message);
    }

    public function delete($source_fund_id)
    {
        $sourcefund               = AcctSourceFund::findOrFail($source_fund_id);
        $sourcefund->data_state   = 1;
        if($sourcefund->save()){
            $message = array(
                'pesan' => 'Sumber Dana berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Sumber Dana gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('source-fund')->with($message);
    }
}
