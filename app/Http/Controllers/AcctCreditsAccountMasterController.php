<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctSavingsAccount;
use App\Models\CoreBranch;
use App\Models\PreferenceCompany;
use App\DataTables\AcctCreditsAccountMasterDataTable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AcctCreditsAccountMasterController extends Controller
{
    public function index(AcctCreditsAccountMasterDataTable $dataTable)
    {
        $sessiondata = session()->get('filter_creditsaccountmaster');

        $corebranch = CoreBranch::select('branch_id', 'branch_name')
        ->where('data_state', 0)
        ->get();

        $acctcredits = AcctCredits::select('credits_id', 'credits_name')
        ->where('data_state', 0)
        ->get();

        return $dataTable->render('content.AcctCreditsAccountMaster.List.index', compact('corebranch', 'acctcredits', 'sessiondata'));
    }

    public function filter(Request $request){
        if($request->credits_id){
            $credits_id = $request->credits_id;
        }else{
            $credits_id = null;
        }

        if($request->branch_id){
            $branch_id = $request->branch_id;
        }else{
            $branch_id = null;
        }

        $sessiondata = array(
            'start_date'    => $request->start_date,
            'end_date'      => $request->end_date,
            'credits_id'    => $credits_id,
            'branch_id'     => $branch_id
        );

        session()->put('filter_creditsaccountmaster', $sessiondata);

        return redirect('credits-account-master');
    }

    public function filterReset(){
        session()->forget('filter_creditsaccountmaster');

        return redirect('credits-account-master');
    }

    public function export(){
        $spreadsheet        = new Spreadsheet();
        $preferencecompany	= PreferenceCompany::select('company_name')->first();

		if (auth()->user()->branch_status == 1) {
            $sessiondata = session()->get('filter_creditsaccountmaster');
			if (!$sessiondata) {
				$branch_id  = '';
			}else{
                $branch_id = $sessiondata['branch_id'];
            }
		} else {
			$branch_id	= auth()->user()->branch_id;
		}

		$membergender 	                = Configuration::MemberGender();
		$memberidentity                 = Configuration::MemberIdentity();
		$memberjobtype 	                = Configuration::WorkingType();
		$acctcreditsaccountmasterdata	= AcctCreditsAccount::select('acct_credits_account.credits_account_serial', 'acct_credits_account.savings_account_id', 'core_member_working.member_working_type', 'core_member_working.member_company_name', 'acct_credits_account.member_id', 'core_member.member_name', 'core_member.member_address', 'core_member.member_gender', 'core_member.member_date_of_birth', 'core_member.member_job', 'core_member.member_identity', 'core_member.member_identity_no', 'acct_credits_account.credits_account_period', 'core_member.member_phone' ,'acct_credits_account.credits_account_date', 'acct_credits_account.credits_account_due_date', 'acct_credits_account.credits_account_principal_amount', 'acct_credits_account.credits_account_interest_amount', 'acct_credits_account.credits_account_amount', 'acct_credits_account.credits_account_interest', 'acct_credits_account.credits_account_last_balance', 'acct_credits_account.credits_id', 'acct_credits.credits_name')
		->join('core_member', 'acct_credits_account.member_id', '=', 'core_member.member_id')
		->join('core_member_working', 'acct_credits_account.member_id', '=', 'core_member_working.member_id')
		->join('acct_credits', 'acct_credits_account.credits_id', '=', 'acct_credits.credits_id')
		->where('acct_credits_account.data_state', 0);
		if ($branch_id && $branch_id != '') {
			$acctcreditsaccountmasterdata = $acctcreditsaccountmasterdata->where('acct_credits_account.branch_id', $branch_id);
		}
		$acctcreditsaccountmasterdata = $acctcreditsaccountmasterdata->orderBy('acct_credits_account.credits_account_serial', 'ASC')
        ->get();

        if(count($acctcreditsaccountmasterdata)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Master Data Pinjaman")
                                            ->setSubject("")
                                            ->setDescription("Master Data Pinjaman")
                                            ->setKeywords("Master Data Pinjaman")
                                            ->setCategory("Master Data Pinjaman");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Master Data Pinjaman");

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
			$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(20);
			$spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(20);
            
			$spreadsheet->getActiveSheet()->mergeCells("B1:V1");

			$spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);
			$spreadsheet->getActiveSheet()->getStyle('B3:V3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
			$spreadsheet->getActiveSheet()->getStyle('B3:V3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
			$spreadsheet->getActiveSheet()->getStyle('B3:V3')->getFont()->setBold(true);

			$spreadsheet->getActiveSheet()->setCellValue('B1', "Master Data Pinjaman");
			$spreadsheet->getActiveSheet()->setCellValue('B3', "No");
			$spreadsheet->getActiveSheet()->setCellValue('C3', "No. Akad");
			$spreadsheet->getActiveSheet()->setCellValue('D3', "No. Rekening");
			$spreadsheet->getActiveSheet()->setCellValue('E3', "Nama");
			$spreadsheet->getActiveSheet()->setCellValue('F3', "JNS Kel");
			$spreadsheet->getActiveSheet()->setCellValue('G3', "Tanggal Lahir");
			$spreadsheet->getActiveSheet()->setCellValue('H3', "Alamat");
			$spreadsheet->getActiveSheet()->setCellValue('I3', "Pekerjaan");
			$spreadsheet->getActiveSheet()->setCellValue('J3', "Perusahaan");
			$spreadsheet->getActiveSheet()->setCellValue('K3', "No Identitas");
			$spreadsheet->getActiveSheet()->setCellValue('L3', "Telp");
			$spreadsheet->getActiveSheet()->setCellValue('M3', "Pinjaman");
			$spreadsheet->getActiveSheet()->setCellValue('N3', "JK Waktu");
			$spreadsheet->getActiveSheet()->setCellValue('O3', "TG Pinjam");
			$spreadsheet->getActiveSheet()->setCellValue('P3', "TG JT Tempo");
			$spreadsheet->getActiveSheet()->setCellValue('Q3', "JML Plafon");
			$spreadsheet->getActiveSheet()->setCellValue('R3', "Pokok");
			$spreadsheet->getActiveSheet()->setCellValue('S3', "Margin");
			$spreadsheet->getActiveSheet()->setCellValue('T3', "ANG Pokok");
			$spreadsheet->getActiveSheet()->setCellValue('U3', "ANG Margin");
			$spreadsheet->getActiveSheet()->setCellValue('V3', "Saldo Pokok");

			$row    = 4;
			$no     = 0;
			foreach ($acctcreditsaccountmasterdata as $key => $val) {
                $savingsaccount = AcctSavingsAccount::select('savings_account_no')
                ->where('savings_account_id', $val['savings_account_id'])
                ->first();

                if(isset($savingsaccount['savings_account_no'])){
                    $savings_account_no = $savingsaccount['savings_account_no'];
                }else{
                    $savings_account_no = '';            
                }

                $no++;

                $spreadsheet->getActiveSheet()->getStyle('B' . $row . ':V' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $spreadsheet->getActiveSheet()->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('H' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('K' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('L' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('M' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('N' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('O' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('P' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('Q' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('R' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('S' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('T' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('U' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                $spreadsheet->getActiveSheet()->getStyle('V' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

                $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $no);
                $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $val['credits_account_serial']);
                $spreadsheet->getActiveSheet()->setCellValue('D' . $row, $savings_account_no);
                $spreadsheet->getActiveSheet()->setCellValue('E' . $row, $val['member_name']);
                $spreadsheet->getActiveSheet()->setCellValue('F' . $row, $membergender[$val['member_gender']]);
                $spreadsheet->getActiveSheet()->setCellValue('G' . $row, date('d-m-Y', strtotime($val['member_date_of_birth'])));
                $spreadsheet->getActiveSheet()->setCellValue('H' . $row, $val['member_address']);
                $spreadsheet->getActiveSheet()->setCellValue('I' . $row, $memberjobtype[$val['member_working_type']]);
                $spreadsheet->getActiveSheet()->setCellValue('J' . $row, $val['member_company_name']);
                $spreadsheet->getActiveSheet()->setCellValue('K' . $row, $val['member_identity_no']);
                $spreadsheet->getActiveSheet()->setCellValue('L' . $row, $val['member_phone']);
                $spreadsheet->getActiveSheet()->setCellValue('M' . $row, $val['credits_name']);
                $spreadsheet->getActiveSheet()->setCellValue('N' . $row, $val['credits_account_period']);
                $spreadsheet->getActiveSheet()->setCellValue('O' . $row, date('d-m-Y', strtotime($val['credits_account_date'])));
                $spreadsheet->getActiveSheet()->setCellValue('P' . $row, date('d-m-Y', strtotime($val['credits_account_due_date'])));
                $spreadsheet->getActiveSheet()->setCellValue('Q' . $row, number_format($val['credits_account_amount'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('R' . $row, number_format($val['credits_account_amount'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('S' . $row, number_format($val['credits_account_interest'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('T' . $row, number_format($val['credits_account_principal_amount'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('U' . $row, number_format($val['credits_account_interest_amount'], 2));
                $spreadsheet->getActiveSheet()->setCellValue('V' . $row, number_format($val['credits_account_last_balance'], 2));
					
				$row++;
			}
            
            ob_clean();
            $filename='Master Data Pinjaman.xls';
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
