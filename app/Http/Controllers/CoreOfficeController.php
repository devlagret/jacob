<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\CoreOfficeDataTable;
use App\Models\CoreBranch;
use App\Models\User;
use App\Models\CoreOffice;

class CoreOfficeController extends Controller
{
    public function index(CoreOfficeDataTable $dataTable)
    {
        return $dataTable->render('content.CoreOffice.List.index');
    }

    public function add()
    {
        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state',0)
        ->get();
        $user = User::select('user_id','username')
        ->where('data_state',0)
        ->get();

        return view('content.CoreOffice.Add.index',compact('corebranch','user'));
    }

    public function processAdd(Request $request)
    {
        $fields = $request->validate([
            'office_code'   => ['required'],
            'office_name'   => ['required'],
            'branch_id'     => ['required'],
            'user_id'       => ['required'],
        ]);

        $office = array(
            'office_code'   => $fields['office_code'],
            'office_name'   => $fields['office_name'],
            'branch_id'     => $fields['branch_id'],
            'user_id'       => $fields['user_id'],
            'created_id'    => auth()->user()->user_id,
        );

        if(CoreOffice::create($office)){
            $message = array(
                'pesan' => 'Kode Business Office (BO) berhasil ditambah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Business Office (BO) gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('office')->with($message);
    }

    public function edit($id)
    {
        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state',0)
        ->get();
        $user = User::select('user_id','username')
        ->where('data_state',0)
        ->get();
        $office = CoreOffice::select('office_id','office_code','office_name','branch_id','user_id')
        ->where('data_state', 0)
        ->where('office_id', $id)
        ->first();

        return view('content.CoreOffice.Edit.index',compact('corebranch','user','office'));
    }

    public function processEdit(Request $request)
    {
        $fields = $request->validate([
            'office_id'     => ['required'],
            'office_code'   => ['required'],
            'office_name'   => ['required'],
            'branch_id'     => ['required'],
            'user_id'       => ['required'],
        ]);

        $office                 = CoreOffice::findOrFail($fields['office_id']);
        $office->office_code    = $fields['office_code'];
        $office->office_name    = $fields['office_name'];
        $office->branch_id      = $fields['branch_id'];
        $office->user_id        = $fields['user_id'];
        $office->updated_id     = auth()->user()->user_id;

        if($office->save()){
            $message = array(
                'pesan' => 'Kode Business Office (BO) berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Business Office (BO) gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('office')->with($message);
    }

    public function delete($id)
    {
        $office                 = CoreOffice::findOrFail($id);
        $office->data_state     = 1;
        $office->updated_id     = auth()->user()->user_id;

        if($office->save()){
            $message = array(
                'pesan' => 'Kode Business Office (BO) berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Kode Business Office (BO) gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('office')->with($message);
    }
}
