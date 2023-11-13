<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctSavings;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use App\DataTables\AcctSavingsAccountMasterDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctSavingsAccountMasterController extends Controller
{
    public function index(AcctSavingsAccountMasterDataTable $dataTable)
    {
        $sessiondata = session()->get('filter_savingsaccountmaster');

        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $corebranch         = CoreBranch::where('data_state', 0)
            ->get();
        }else{
            $corebranch         = CoreBranch::where('data_state', 0)
            ->where('branch_id', $branch_id)
            ->get();
        }

        $acctsavings = AcctSavings::select('savings_id', 'savings_name')
        ->where('savings_status', 0)
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctSavingsAccountMaster.List.index', compact('corebranch', 'acctsavings', 'sessiondata'));
    }

    public function filter(Request $request){
        if($request->savings_id){
            $savings_id = $request->savings_id;
        }else{
            $savings_id = null;
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = null;
        }

        $sessiondata = array(
            'savings_id' => $savings_id,
            'branch_id'  => $branch_id
        );

        session()->put('filter_savingsaccountmaster', $sessiondata);

        return redirect('savings-account-master');
    }

    public function filterReset(){
        session()->forget('filter_savingsaccountmaster');

        return redirect('savings-account-master');
    }

    public function export(){
        $spreadsheet        = new Spreadsheet();
        $preferencecompany	= PreferenceCompany::select('company_name')->first();

        $savingsaccount = AcctSavingsAccount::withoutGlobalScopes()
        ->select('acct_savings_account.savings_account_id', 'acct_savings_account.member_id', 'core_member.member_name', 'acct_savings_account.savings_id', 'acct_savings.savings_code', 'acct_savings.savings_name', 'acct_savings_account.savings_account_no', 'acct_savings_account.savings_account_date', 'acct_savings_account.savings_account_first_deposit_amount', 'acct_savings_account.savings_account_last_balance', 'acct_savings_account.validation', 'acct_savings_account.validation_at')
        ->join('core_member', 'acct_savings_account.member_id', '=', 'core_member.member_id')
        ->join('acct_savings', 'acct_savings_account.savings_id', '=', 'acct_savings.savings_id')
        ->where('acct_savings_account.data_state', 0)
        ->where('acct_savings.savings_status', 0)
        ->orderBy('acct_savings_account.savings_account_no', 'ASC')
        ->get();

        if(count($savingsaccount)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Master Data Tabungan")
                                            ->setSubject("")
                                            ->setDescription("Master Data Tabungan")
                                            ->setKeywords("Master Data Tabungan")
                                            ->setCategory("Master Data Tabungan");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Master Data Tabungan");

            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);		
            
            $spreadsheet->getActiveSheet()->mergeCells("B1:H1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:H3')->getFont()->setBold(true);	

            $spreadsheet->getActiveSheet()->setCellValue('B1',"Master Data Tabungan");	
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Jenis Tabungan");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"No. Rekening");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"Tanggal Buka");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"Setoran Awal");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Saldo");
            
            $row  = 4;
            $no   = 0;
            foreach($savingsaccount as $key => $val){
                $no++;
                $spreadsheet->setActiveSheetIndex(0);
                $spreadsheet->getActiveSheet()->getStyle('B'.$row.':H'.$row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('F'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('G'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('H'.$row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $spreadsheet->getActiveSheet()->setCellValue('B'.$row, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$row, $val['member_name']);
                $spreadsheet->getActiveSheet()->setCellValue('D'.$row, $val['savings_name']);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$row, $val['savings_account_no']);
                $spreadsheet->getActiveSheet()->setCellValue('F'.$row, date('d-m-Y', strtotime($val['savings_account_date'])));
                $spreadsheet->getActiveSheet()->setCellValue('G'.$row, number_format($val['savings_account_first_deposit_amount'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('H'.$row, number_format($val['savings_account_last_balance'], 2));	
        
                $row++;
            }
            
            ob_clean();
            $filename='Master Data Tabungan.xls';
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="'.$filename.'"');
            header('Cache-Control: max-age=0');

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xls');
            $writer->save('php://output');
        }else{
            echo "Maaf data yang di eksport tidak ada !";
        }
    }
}
