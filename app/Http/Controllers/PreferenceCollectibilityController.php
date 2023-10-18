<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PreferenceCollectibility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class PreferenceCollectibilityController extends Controller
{
    public function index()
    {
        $collectibility = PreferenceCollectibility::get();
        
        return view('content.PreferenceCollectibility.index', compact('collectibility'));
    }

    public function processEdit(Request $request)
    {
        $collectibility = PreferenceCollectibility::get();

        $allrequest = $request->all();

        DB::beginTransaction();
        
        try {
            foreach($collectibility as $key => $val){
                $data = PreferenceCollectibility::findOrFail($val['collectibility_id']);
                $data->collectibility_bottom = $allrequest['collectibility_bottom_'.$val['collectibility_id']];
                $data->collectibility_top    = $allrequest['collectibility_top_'.$val['collectibility_id']];
                $data->collectibility_ppap   = $allrequest['collectibility_ppap_'.$val['collectibility_id']];
                $data->save();
            }
        
            DB::commit();
            $message = array(
                'pesan' => 'Kode Kolekbilitas berhasil diubah',
                'alert' => 'success'
            );
        } catch (\Exception $e) {
            DB::rollback();
            $message = array(
                'pesan' => 'Kode Kolekbilitas gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('preference-collectibility')->with($message);
    }
}
