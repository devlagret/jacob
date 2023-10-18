<?php

namespace App\Http\Controllers;

use App\DataTables\CoreDusunDatatable;
use App\Models\CoreCity;
use App\Models\CoreDusun;
use App\Models\CoreKecamatan;
use App\Models\CoreKelurahan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CoreDusunController extends Controller
{
    public function index(CoreDusunDatatable $dataTable)
    {
        Session::forget('data-dusun');
        $corekabupaten = CoreCity::where('data_state',0)->where('province_id', 72)->get()->pluck('city_name','city_id');
        return $dataTable->render('content.CoreDusun.List.index',['corekabupaten'=>$corekabupaten]);
    }
    public function add() {
        $sessiondata = Session::get('data-dusun');
        $corekabupaten = CoreCity::where('data_state',0)->where('province_id', 72)->get()->pluck('city_name','city_id');
        return view('content.CoreDusun.Add.index',compact('corekabupaten','sessiondata'));
    }
    public function processAdd(Request $request) {
        $request->validate(['kelurahan_id'=>'required','dusun_name'=>'required'],
        ['kelurahan_id.required'=>'Harap Masukan Kelurahan','dusun_name.required'=>'Harap Masukan Nama Dusun']);
        try {
            DB::beginTransaction();
            CoreDusun::create([
                'kelurahan_id' => $request->kelurahan_id,
                'dusun_name'=>$request->dusun_name
            ]);
            DB::commit();
            return redirect()->route('dusun.index')->with(['pesan' => 'Tambah Data Dusun Sukses','alert' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return redirect()->route('dusun.index')->with(['pesan' => 'Tambah Data Dusun Gagal','alert' => 'danger']);
        }
    }
    public function elemenAdd(Request $request) {
        $data=Session::get('data-dusun');
        $data[$request->name]=$request->value;
        Session::put('data-dusun',$data);
        return response(1);
    }
    public function edit($dusun_id)
    {
        $data = CoreDusun::with('kelurahan.kecamatan')->find($dusun_id);
        $corekabupaten = CoreCity::where('data_state',0)->where('province_id', 72)->get()->pluck('city_name','city_id');
        return view('content.CoreDusun.Edit.index', compact('data','corekabupaten'));
    }
    public function processEdit(Request $request)
    {
        $request->validate(['kelurahan_id'=>'required','dusun_name'=>'required'],
        ['kelurahan_id.required'=>'Harap Masukan Kelurahan','dusun_name.required'=>'Harap Masukan Nama Dusun']);
        try {
            DB::beginTransaction();
            $coredusun                         = CoreDusun::findOrFail($request['dusun_id']);
            $coredusun->kelurahan_id            = $request['kelurahan_id'];
            $coredusun->branch_name            = $request['branch_name'];
            $coredusun->save();
            DB::rollBack();
            return redirect()->route('dusun.index')->with(['pesan' => 'Edit Data Dusun Sukses','alert' => 'success']);
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);
            return redirect()->route('dusun.index')->with(['pesan' => 'Edit Data Dusun Gagal','alert' => 'danger']);
        }
    }
    public function getKecamatan(Request $request) {
        $data = CoreKecamatan::where('data_state',0)->where('city_id',$request->city_id)->get()->pluck('kecamatan_name','kecamatan_id');
        $response = '';
        $sessiondata = Session::get('data-dusun');
        foreach($data as $key => $val){
            $response .= "<option data-kt-flag='".$key."' value='".$key."' ".($key == old("kecamatan_id", $sessiondata["kecamatan_id"] ?? $request->last_kecamatan_id??'') ? "selected" :"")." >".$val."</option>";
        }
        return response($response);
    }
    public function getKelurahan(Request $request) {
        $data = CoreKelurahan::where('data_state',0)->where('kecamatan_id',$request->kecamatan_id)->get()->pluck('kelurahan_name','kelurahan_id');
        $response = '';
        $sessiondata = Session::get('data-dusun');
        foreach($data as $key => $val){
            $response .= '<option data-kt-flag="'.$key.'" value="'.$key.'" '.($key == old("kelurahan_id", $sessiondata["kelurahan_id"] ?? $request->last_kelurahan_id??'') ? "selected" :"" ).'>'.$val.'</option>';
        }
        return response($response);
    }
    public function delete($dusun_id)
    {
        $coredusun                 = CoreDusun::findOrFail($dusun_id);
        $coredusun->data_state     = 1;
        $coredusun->updated_id     = Auth::id();

        if($coredusun->save()){
            $message = array(
                'pesan' => 'Hapus Data Dusun Sukses',
                'alert' => 'success'
            );
        }else{
            $message = array(
                'pesan' => 'Hapus Data Dusun Gagal',
                'alert' => 'error'
            );
        }

        return redirect('branch')->with($message);
    }
}
