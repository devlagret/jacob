<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctMutation;
use App\DataTables\AcctMutationDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;

class AcctMutationController extends Controller
{
    public function index(AcctMutationDataTable $dataTable)
    {
        return $dataTable->render('content.AcctMutation.List.index');
    }

    public function add()
    {
        $config         = theme()->getOption('page', 'view');
        $accountstatus  = Configuration::AccountStatus();

        return view('content.AcctMutation.Add.index', compact('accountstatus'));
    }

    public function processAdd(Request $request)
    {
        $fields = request()->validate([
            'mutation_code'     =>['required'],
            'mutation_name'     =>['required'],
            'mutation_function' =>['required'],
            'mutation_status'   =>['required'],
        ]);
        
        $mutation  = array(
            'mutation_code'         => $fields['mutation_code'],
            'mutation_name'         => $fields['mutation_name'],
            'mutation_function'     => $fields['mutation_function'],
            'mutation_status'       => $fields['mutation_status'],
            'mutation_module'       => "",
            'created_id'            => auth()->user()->user_id,
        );
        
        if(AcctMutation::create($mutation)){
            $message = array(
                'pesan' => 'Mutasi berhasil ditambah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Mutasi gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('mutation')->with($message);
    }

    public function edit($mutation_id)
    {
        $config         = theme()->getOption('page', 'mutation-edit');

        $mutation       = AcctMutation::findOrFail($mutation_id);
        $accountstatus  = Configuration::AccountStatus();

        return view('content.AcctMutation.Edit.index', compact('mutation', 'accountstatus'));
    }

    public function processEdit(Request $request)
    {
        $fields = $request->validate([
            'mutation_id'           => 'required',
            'mutation_code'         => 'required',
            'mutation_name'         => 'required',
            'mutation_function'     => 'required',
            'mutation_status'       => 'required',
        ]);

        $mutation                       = AcctMutation::findOrFail($fields['mutation_id']);
        $mutation->mutation_code        = $fields['mutation_code'];
        $mutation->mutation_name        = $fields['mutation_name'];
        $mutation->mutation_function    = $fields['mutation_function'];
        $mutation->mutation_status      = $fields['mutation_status'];
        $mutation->updated_id           = auth()->user()->user_id;

        if($mutation->save()){
            $message = array(
                'pesan' => 'Mutasi berhasil diubah',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Mutasi gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('mutation')->with($message);
    }

    public function delete($mutation_id)
    {
        $mutation               = AcctMutation::findOrFail($mutation_id);
        $mutation->data_state   = 1;
        if($mutation->save()){
            $message = array(
                'pesan' => 'Mutasi berhasil dihapus',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Mutasi gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('mutation')->with($message);
    }
}
