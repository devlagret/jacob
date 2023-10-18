<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctDeposito;
use App\Models\AcctDepositoAccount;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use App\DataTables\AcctDepositoAccountMasterDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctDepositoAccountMasterController extends Controller
{
    public function index(AcctDepositoAccountMasterDataTable $dataTable)
    {
        $sessiondata = session()->get('filter_depositoaccountmaster');

        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state', 0)
        ->get();

        $acctdeposito = AcctDeposito::select('deposito_id', 'deposito_name')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctDepositoAccountMaster.List.index', compact('corebranch', 'acctdeposito', 'sessiondata'));
    }

    public function filter(Request $request){
        if($request->deposito_id){
            $deposito_id = $request->deposito_id;
        }else{
            $deposito_id = null;
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = null;
        }

        $sessiondata = array(
            'deposito_id' => $deposito_id,
            'branch_id'  => $branch_id
        );

        session()->put('filter_depositoaccountmaster', $sessiondata);

        return redirect('deposito-account-master');
    }

    public function filterReset(){
        session()->forget('filter_depositoaccountmaster');

        return redirect('deposito-account-master');
    }

    public function export(){
        $spreadsheet        = new Spreadsheet();
        $preferencecompany	= PreferenceCompany::select('company_name')->first();

        $depositoaccount = AcctDepositoAccount::select('acct_deposito_account.deposito_account_id', 'acct_deposito_account.member_id', 'core_member.member_name', 'acct_deposito_account.deposito_id', 'acct_deposito.deposito_code', 'acct_deposito.deposito_name', 'acct_deposito_account.deposito_account_extra_type', 'acct_deposito_account.deposito_account_no', 'acct_deposito_account.deposito_account_date', 'acct_deposito_account.deposito_account_amount', 'acct_deposito_account.deposito_account_due_date', 'acct_deposito_account.deposito_account_serial_no', 'acct_deposito_account.deposito_account_interest_amount', 'acct_deposito_account.validation', 'acct_deposito_account.validation_id', 'acct_deposito_account.validation_at')
        ->join('core_member', 'acct_deposito_account.member_id', '=', 'core_member.member_id')
        ->join('acct_deposito', 'acct_deposito_account.deposito_id', '=', 'acct_deposito.deposito_id')
        ->where('acct_deposito_account.deposito_account_status', 0)
        ->where('acct_deposito_account.data_state', 0)
        ->orderBy('acct_deposito_account.deposito_account_no', 'ASC')
        ->get();

        if(count($depositoaccount)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Master Data Simpanan Berjangka")
                                            ->setSubject("")
                                            ->setDescription("Master Data Simpanan Berjangka")
                                            ->setKeywords("Master Data Simpanan Berjangka")
                                            ->setCategory("Master Data Simpanan Berjangka");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Master Data Simpanan Berjangka");

            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);			
            $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(20);			
            
            $spreadsheet->getActiveSheet()->mergeCells("B1:K1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B3:K3')->getFont()->setBold(true);	

            $spreadsheet->getActiveSheet()->setCellValue('B1',"Master Data Simpanan Berjangka");	
            $spreadsheet->getActiveSheet()->setCellValue('B3',"No");
            $spreadsheet->getActiveSheet()->setCellValue('C3',"Nama Anggota");
            $spreadsheet->getActiveSheet()->setCellValue('D3',"Jenis Simp Berjangka");
            $spreadsheet->getActiveSheet()->setCellValue('E3',"Jenis Perpanjangan");
            $spreadsheet->getActiveSheet()->setCellValue('F3',"No. SimKa");
            $spreadsheet->getActiveSheet()->setCellValue('G3',"No. seri");
            $spreadsheet->getActiveSheet()->setCellValue('H3',"Tgl Buka");
            $spreadsheet->getActiveSheet()->setCellValue('I3',"JT Tempo");
            $spreadsheet->getActiveSheet()->setCellValue('J3',"Nominal");
            $spreadsheet->getActiveSheet()->setCellValue('K3',"Bagi Hasil");
            
            $j  = 4;
            $no = 0;
            foreach($depositoaccount as $key=>$val){
                if($val['deposito_account_extra_type'] == '1'){
                    $type_extra = 'ARO';
                }else{
                    $type_extra = 'Manual';
                }

                $no++;
                
                $spreadsheet->getActiveSheet()->getStyle('B'.$j.':K'.$j)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('F'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('G'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('H'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('I'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('J'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('K'.$j)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $spreadsheet->getActiveSheet()->setCellValue('B'.$j, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C'.$j, $val['member_name']);
                $spreadsheet->getActiveSheet()->setCellValue('D'.$j, $val['deposito_name']);
                $spreadsheet->getActiveSheet()->setCellValue('E'.$j, $type_extra);
                $spreadsheet->getActiveSheet()->setCellValue('F'.$j, $val['deposito_account_no']);
                $spreadsheet->getActiveSheet()->setCellValue('G'.$j, $val['deposito_account_serial_no']);
                $spreadsheet->getActiveSheet()->setCellValue('H'.$j, $val['deposito_account_date']);
                $spreadsheet->getActiveSheet()->setCellValue('I'.$j, $val['deposito_account_due_date']);	
                $spreadsheet->getActiveSheet()->setCellValue('J'.$j, number_format($val['deposito_account_amount'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('K'.$j, $val['deposito_account_interest_amount']);				
                    
                $j++;
            }
            
            ob_clean();
            $filename='Master Data Simpanan Berjangka.xls';
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
