<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\AccountDataTable;
use App\Helpers\Configuration;
use App\Models\AcctAccount;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class AccountController extends Controller
{
    public function index(AccountDataTable $dataTable)
    {
        return $dataTable->render('content.Account.List.index');
    }

    public function add()
    {
        $kelompokperkiraan = Configuration::KelompokPerkiraan();
        $accountstatus = Configuration::AccountStatus();
        
        return view('content.Account.Add.index', compact('kelompokperkiraan','accountstatus'));
    }

    public function processAdd(Request $request)
    {
        $data = array(
            'account_code' => $request->account_code,
            'account_name' => $request->account_name,
            'account_type_id' => $request->account_type_id,
            'account_group' => $request->account_group,
            'account_status' => $request->account_status,
            'account_default_status' => $request->account_status,
            'created_id' => auth()->user()->user_id,
        );

        if (AcctAccount::create($data)) {
            $message = array(
                'pesan' => 'Perkiraan berhasil ditambah',
                'alert' => 'success'
            );
        } else {
            $message = array(
                'pesan' => 'Perkiraan gagal ditambah',
                'alert' => 'error'
            );
        }

        return redirect('account')->with($message);
    }

    public function edit($account_id)
    {
        $account = AcctAccount::where('account_id', $account_id)
        ->first();
        $kelompokperkiraan = Configuration::KelompokPerkiraan();
        $accountstatus = Configuration::AccountStatus();

        return view('content.Account.Edit.index', compact('account','kelompokperkiraan','accountstatus'));
    }

    public function processEdit(Request $request)
    {
        $table                  = AcctAccount::findOrFail($request->account_id);
        $table->account_code    = $request->account_code;
        $table->account_name    = $request->account_name;
        $table->account_type_id = $request->account_type_id;
        $table->account_group   = $request->account_group;
        $table->account_status  = $request->account_status;
        $table->updated_id      = auth()->user()->user_id;

        if ($table->save()) {
            $message = array(
                'pesan' => 'Perkiraan berhasil diubah',
                'alert' => 'success'
            );
        } else {
            $message = array(
                'pesan' => 'Perkiraan gagal diubah',
                'alert' => 'error'
            );
        }

        return redirect('account')->with($message);
    }

    public function delete($account_id)
    {
        $table                  = AcctAccount::findOrFail($account_id);
        $table->data_state      = 1;
        $table->updated_id      = auth()->user()->user_id;

        if ($table->save()) {
            $message = array(
                'pesan' => 'Perkiraan berhasil dihapus',
                'alert' => 'success'
            );
        } else {
            $message = array(
                'pesan' => 'Perkiraan gagal dihapus',
                'alert' => 'error'
            );
        }

        return redirect('account')->with($message);
    }

    public function export()
    {
        $acct_account = AcctAccount::where('data_state',0)
        ->orderBy('account_code', 'ASC')
        ->get();
        if(!empty($acct_account)){
            $spreadsheet = new Spreadsheet();
            
            $spreadsheet->getProperties()->setCreator("SIS")
                                ->setLastModifiedBy("SIS")
                                ->setTitle("Master Data Anggota")
                                ->setSubject("")
                                ->setDescription("Master Data Anggota")
                                ->setKeywords("Master, Data, Anggota")
                                ->setCategory("Master Data Anggota");
                                
            $spreadsheet->setActiveSheetIndex(0);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(60);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(30);
            
            
            $spreadsheet->getActiveSheet()->mergeCells("B1:E1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:E3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:E3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:E3')->getFont()->setBold(true);	
            $spreadsheet->getActiveSheet()->setCellValue('B1',"Master Data Nomer Perkiraan");
            

            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"No Perkiraan");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Nama Perkiraan");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Golongan Perkiraan");
            
            $j=4;
            $no=0;
            
            foreach($acct_account as $key=>$val){
                if(is_numeric($key)){
                    $no++;
                    $spreadsheet->setActiveSheetIndex(0);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j.':E'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    
                    if( $val['account_code'] ==  $val['account_group']){
                    $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getFont()->setBold(true);
                    $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getFont()->setBold(true);
                    $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getFont()->setBold(true);
                    $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getFont()->setBold(true);
                    }

                    $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                    $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['account_code']);
                    $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['account_name']);
                    $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $val['account_group']);
                    
                }else{
                    continue;
                }
                $j++;
            }
            
            $filename='Master Daftar Perkiraan.xls';
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');
                            
            $objWriter = IOFactory::createWriter($spreadsheet, 'Excel5');  
            $objWriter->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }
    }
}
