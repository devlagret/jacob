<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\CoreBranch;
use App\Models\AcctCredits;
use App\Models\AcctCreditsAccount;
use App\Models\AcctSourceFund;
use App\Models\PreferenceCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Helpers\Configuration;
use Elibyy\TCPDF\Facades\TCPDF;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class NominativeCreditsReportController extends Controller
{
    public function index()
    {
        $branch_id          = auth()->user()->branch_id;
        if($branch_id == 0){
            $corebranch         = CoreBranch::where('data_state', 0)
            ->get();
        }else{
            $corebranch         = CoreBranch::where('data_state', 0)
            ->where('branch_id', $branch_id)
            ->get();
        }
        $kelompok   = Configuration::KelompokLaporanPembiayaan();

        return view('content.NominativeCredits.index', compact('corebranch', 'kelompok'));
    }

    public function viewport(Request $request)
    {
        $sesi = array (
            "start_date"	=> $request->start_date,
            "end_date"	    => $request->end_date,
            "kelompok"	    => $request->kelompok,
            "branch_id"		=> $request->branch_id, 
            "view"			=> $request->view,
        );

        if($sesi['view'] == 'pdf'){
            $this->processPrinting($sesi);
        } else {
            $this->export($sesi);
        }
    }

    public function processPrinting($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('logo_koperasi')->first();
        $path               = public_path('storage/'.$preferencecompany['logo_koperasi']);
        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }

        $acctcreditsaccount = AcctCreditsAccount::with('member')
        ->where('data_state', 0)
        ->where('credits_approve_status', 1)
        ->where('credits_account_last_balance', '>', 0)
        ->where('credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])));

        if(!empty($branch_id)){
            $acctcreditsaccount = $acctcreditsaccount->where('branch_id', $branch_id);
        }
        $acctcreditsaccount = $acctcreditsaccount->orderBy('created_at', 'ASC')
        ->get();
        // dd($acctcreditsaccount);

        $acctcredits 		= AcctCredits::select('credits_id', 'credits_name')
        ->where('data_state', 0)
        ->get();

        $acctsourcefund     = AcctSourceFund::select('source_fund_id', 'source_fund_name')
        ->where('data_state', 0)
        ->get();
        
        $pdf = new TCPDF('L', PDF_UNIT, 'F4', true, 'UTF-8', false);

        $pdf::SetPrintHeader(false);
        $pdf::SetPrintFooter(false);

        $pdf::SetMargins(6, 6, 6, 6);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $pdf::AddPage('L', 'F4');

        $pdf::SetFont('helvetica', '', 9);

        $pdf::setImageScale(PDF_IMAGE_SCALE_RATIO);

        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf::setLanguageArray($l);
        }

        $export = "
        ";
        
        if ($sesi['kelompok'] == 0) {
			$export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px\">DAFTAR NOMINATIF PINJAMAN GLOBAL</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode " . $sesi['start_date'] . " S.D. " . $sesi['end_date'] . "</div></td>
                </tr>
            </table>
            <br>";
		} else if ($sesi['kelompok'] == 1) {
			$export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px\">DAFTAR NOMINATIF PINJAMAN PER JENIS KREDIT</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode " . $sesi['start_date'] . " S.D. " . $sesi['end_date'] . "</div></td>
                </tr>
            </table>
            <br>";
		} else if ($sesi['kelompok'] == 2) {
			$export .= "
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\">
                <tr>
                    <td><div style=\"text-align: center; font-size:14px\">DAFTAR NOMINATIF PINJAMAN PER JENIS SUMBER DANA</div></td>
                </tr>
                <tr>
                    <td><div style=\"text-align: center; font-size:10px\">Periode " . $sesi['start_date'] . " S.D. " . $sesi['end_date'] . "</div></td>
                </tr>
            </table>
            <br>";
		}

		if ($sesi['kelompok'] == 0) {
			$export .= "
            <br>
				<table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
				    <tr>
				        <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
				        <td width=\"6%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Kredit</div></td>
				        <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
				        <td width=\"18%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
				        <td width=\"8%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Plafon</div></td>
				        <td width=\"6%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Bunga</div></td>
				        <td width=\"8%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sisa Pokok</div></td>
				        <td width=\"8%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sisa Bunga</div></td>
				        <td width=\"8%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl Pinjam</div></td>
				        <td width=\"9%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: rigth;font-size:10;\">Jangka Waktu</div></td>
				        <td width=\"9%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl JT Tempo</div></td>
				       
				    </tr>				
				</table>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">";

            $no                     = 1;
			$totalpokokglobal       = 0;
			$totalsisapokokglobal   = 0;
            $totalsisamarginglobal  = 0;
			$totalbasilglobal       = 0;
			$totalsaldoglobal       = 0;
			$totalsaldobunga        = 0;
			$totalProvisiGbl	    = 0;
			$totalSurveiGbl 	    = 0;
			$totalAsuransiGbl	    = 0;
			$totalSimpananWjbGbl	= 0;
			$totalAdministrasiGbl	= 0;
			$totalMateraiGbl	    = 0;
			$totalCadanganResGlb	= 0;
			$totalSimpananPokokGlb	= 0;

			if (!empty($acctcreditsaccount)) {
				foreach ($acctcreditsaccount as $key => $val) {
					$month 	= date('m', strtotime($sesi['end_date']));
					$year	= date('Y', strtotime($sesi['end_date']));
					$period = $month . $year;

					$credits_account_interest_last_balance = ($val['credits_account_interest_amount'] * $val['credits_account_period']) - ($val['credits_account_payment_to'] * $val['credits_account_interest_amount']);

					$export .= "
                    <tr>
                        <td width=\"3.4%\"><div style=\"text-align: left;\">" . $no . "</div></td>
                        <td width=\"8%\"><div style=\"text-align: left;\">" . $val['credits_account_serial'] . "</div></td>
                        <td width=\"17%\"><div style=\"text-align: left;\">" . $val->member->member_name . "</div></td>
                        <td width=\"17%\"><div style=\"text-align: left;\">" . $val->member->member_address . "</div></td>
                        <td width=\"9%\"><div style=\"text-align: right;\">" . number_format($val['credits_account_amount'], 2) . "</div></td>
                        <td width=\"3.7%\"><div style=\"text-align: right;\">" . number_format($val['credits_account_interest'], 2) . "</div></td>
                        <td width=\"9%\"><div style=\"text-align: right;\">" . number_format($val['credits_account_last_balance'], 2) . "</div></td>
                        <td width=\"8%\"><div style=\"text-align: right;\">" . number_format($credits_account_interest_last_balance, 2) . "</div></td>
                        <td width=\"8%\"><div style=\"text-align: right;\">" . $val['credits_account_date'] . "</div></td>
                        <td width=\"10%\"><div style=\"text-align: center;\">" . $val['credits_account_period'] . "</div></td>
                        <td width=\"7%\"><div style=\"text-align: right;\">" . $val['credits_account_due_date'] . "</div></td>
                        </tr>";

					$totalbasilglobal       += $val['credits_account_amount'];
					$totalsaldoglobal       += $val['credits_account_last_balance'];
					$totalsaldobunga        += $credits_account_interest_last_balance;
					$totalProvisiGbl	    += $val['credits_account_provisi'];
					$totalSurveiGbl 	    += $val['credits_account_komisi'];
					$totalAsuransiGbl	    += $val['credits_account_insurance'];
					$totalSimpananWjbGbl	+= $val['credits_account_stash'];
					$totalAdministrasiGbl	+= $val['credits_account_adm_cost'];
					$totalMateraiGbl	    += $val['credits_account_materai'];
					$totalCadanganResGlb	+= $val['credits_account_risk_reserve'];
					$totalSimpananPokokGlb	+= $val['credits_account_principal'];
					$no++;
				}
                /**
                <td colspan =\"4\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalProvisiGbl, 2) . "</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalSurveiGbl, 2) . "</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalAsuransiGbl, 2) . "</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalSimpananWjbGbl, 2) . "</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalAdministrasiGbl, 2) . "</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalMateraiGbl, 2) . "</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"> " . number_format($totalCadanganResGlb, 2) . "</div></td>
                <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"> " . number_format($totalSimpananPokokGlb, 2) . "</div></td>
                */
				$export .= "
                <br>
                <tr>
                    <td colspan =\"3\"><div style=\"font-size:
                    10;text-align:left;font-style:italic\"></div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Subtotal    </div></td>
                    <td  style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalbasilglobal, 2) . "</div></td>
                    <td colspan =\"2\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsaldoglobal, 2) . "</div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsaldobunga, 2) . "</div></td>

                </tr>
                <br>";

                $totalpokokglobal       += $totalbasilglobal;
				$totalsisapokokglobal   += $totalsaldoglobal;
				$totalsisamarginglobal  += $totalsaldobunga;
			}

			$export .= "
                <br>
                <tr>
                    <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\"></div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\"> </div></td>
                    <td  style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>PLAFON</b></div></td>
                    <td colspan =\"2\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>SISA POKOK</b></div></td>
                    <td  style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>SISA BUNGA</b></div></td>
                </tr>
                <tr>
                    <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : " . date('Y-m-d H:i:s') . "  " . auth()->user()->username . "</div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Total </div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalbasilglobal, 2) . "</div></td>
                    <td colspan =\"2\"  style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsaldoglobal, 2) . "</div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsaldobunga, 2) . "</div></td>
                </tr>
			</table>";
		} else if ($sesi['kelompok'] == 1) {
			$export .= "
            <br>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                    <td width=\"8%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Kredit</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Pokok</div></td>
                    <td width=\"7%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Bunga</div></td>
                    <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sisa Pokok</div></td>
                    <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sisa Bunga</div></td>
                    <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl Realisasi</div></td>	
                        <td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Jangka Waktu</div></td>			       
                    <td width=\"7%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl JT Tempo</div></td>
                </tr>				
            </table>";

			$totalpokokglobal 		= 0;
			$totalsisapokokglobal	= 0;
			$totalsisamarginglobal 	= 0;
			foreach ($acctcredits as $kCredits => $vCredits) {
                $acctcreditsaccount_credits = AcctCreditsAccount::with('member')
                ->where('credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                ->where('credits_account_last_balance', '>', 0)
                ->where('credits_approve_status', 1)
                ->where('data_state', 0);
                if (!empty($vCredits['credits_id'])) {
                    $acctcreditsaccount_credits = $acctcreditsaccount_credits->where('credits_id', $vCredits['credits_id']);
                }
                if (!empty($branch_id)) {
                    $acctcreditsaccount_credits = $acctcreditsaccount_credits->where('branch_id', $branch_id);
                }
                $acctcreditsaccount_credits = $acctcreditsaccount_credits->orderby('credits_account_serial', 'ASC')
                ->orderBy('member_id', 'ASC')
                // ->orderBy('member_name', 'ASC')
                // ->orderBy('member_address', 'ASC')
                ->orderBy('credits_account_last_balance', 'ASC')
                ->orderBy('credits_account_date', 'ASC')
                ->orderBy('credits_account_due_date', 'ASC')
                ->orderBy('credits_account_amount', 'ASC')
                ->orderBy('credits_account_interest_last_balance', 'ASC')
                ->orderBy('credits_account_interest', 'ASC')
                ->orderBy('credits_account_period', 'ASC')
                ->orderBy('credits_account_provisi', 'ASC')
                ->orderBy('credits_account_komisi', 'ASC')
                ->orderBy('credits_account_insurance', 'ASC')
                ->orderBy('credits_account_stash', 'ASC')
                ->orderBy('credits_account_adm_cost', 'ASC')
                ->orderBy('credits_account_materai', 'ASC')
                ->orderBy('credits_account_risk_reserve', 'ASC')
                ->orderBy('credits_account_principal', 'ASC')
                ->get();

				if (!empty($acctcreditsaccount_credits)) {
					$export .= "
                    <br>
                    <table>
                        <tr>
                            <td colspan =\"7\" width=\"100%\" style=\"border-bottom: 1px solid black;\"><div style=\"font-size:10\">" . $vCredits['credits_name'] . "</div></td>
                        </tr>
                        <br>";

                        $nov                = 1;
                        $totalpokok 		= 0;
                        $totalsisapokok 	= 0;
                        $totalsisamargin 	= 0;
                        foreach ($acctcreditsaccount_credits as $k => $v) {
                            $month 	= date('m', strtotime($sesi['end_date']));
                            $year	= date('Y', strtotime($sesi['end_date']));
                            $period = $month . $year;

                            $credits_account_interest_last_balance = ($v['credits_account_interest_amount'] * $v['credits_account_period']) - ($v['credits_account_payment_to'] * $v['credits_account_interest_amount']);

                            $export .= "
                            <tr>
                                <td width=\"3%\"><div style=\"text-align: left;\">" . $nov . "</div></td>
                                <td width=\"8%\"><div style=\"text-align: left;\">" . $v['credits_account_serial'] . "</div></td>
                                <td width=\"10%\"><div style=\"text-align: left;\">" . $v->member->member_name . "</div></td>
                                <td width=\"12%\"><div style=\"text-align: left;\">" . $v->member->member_address . "</div></td>
                                <td width=\"10%\"><div style=\"text-align: right;\">" . number_format($v['credits_account_amount'], 2) . "</div></td>
                                <td width=\"7%\"><div style=\"text-align: right;\">" . number_format($v['credits_account_interest'], 2) . "</div></td>
                                <td width=\"10%\"><div style=\"text-align: right;\">" . number_format($v['credits_account_last_balance'], 2) . "</div></td>
                                <td width=\"10%\"><div style=\"text-align: right;\">" . number_format($credits_account_interest_last_balance, 2) . "</div></td>
                                <td width=\"10%\"><div style=\"text-align: right;\">" . $v['credits_account_date']. "</div></td>			
                                <td width=\"5%\"><div style=\"text-align: right;\">" . $v['credits_account_period']. "</div></td>
                                <td width=\"7%\"><div style=\"text-align: right;\">" . $v['credits_account_due_date']. "</div></td>
                            </tr>";

                            $totalpokok         += $v['credits_account_amount'];
                            $totalsisapokok     += $v['credits_account_last_balance'];
                            $totalsisamargin    += $credits_account_interest_last_balance;
                            $nov++;
                        }

                        $export .= "
                        <br>
                        <tr>
                            <td colspan =\"3\"><div style=\"font-size:
                            10;text-align:left;font-style:italic\"></div></td>
                            <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Subtotal </div></td>
                            <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalpokok, 2) . "</div></td>
                            <td colspan =\"2\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsisapokok, 2) . "</div></td>
                            <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsisamargin, 2) . "</div></td>
                        </tr>
                        <br>
                    <table>";

					$totalpokokglobal       += $totalpokok;
					// $totalmarginglobal      += $totalmargin;
					$totalsisapokokglobal   += $totalsisapokok;
					$totalsisamarginglobal  += $totalsisamargin;
				}
			}

			$export .= "
            <table>
                <tr>
                    <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\"></div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\"> </div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>POKOK</b></div></td>
                    <td colspan =\"2\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>SISA POKOK</b></div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>SISA BUNGA</b></div></td>
                </tr>
                <tr>
                    <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : " . date('Y-m-d H:i:s') . "  " . auth()->user()->username . "</div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Total </div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalpokokglobal, 2) . "</div></td>
                    <td colspan =\"3\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsisapokokglobal, 2) . "</div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsisamarginglobal, 2) . "</div></td>
                </tr>
            </table>";
		} else if ($sesi['kelompok'] == 2) {
			$export .= "
            <br>
            <table cellspacing=\"0\" cellpadding=\"1\" border=\"0\" width=\"100%\">
                <tr>
                    <td width=\"3%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: left;font-size:10;\">No.</div></td>
                    <td width=\"8%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">No. Kredit</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Nama</div></td>
                    <td width=\"12%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: center;font-size:10;\">Alamat</div></td>
                    <td width=\"10%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Pokok</div></td>
                    <td width=\"7%\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Bunga</div></td>
                    <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sisa Pokok</div></td>
                    <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Sisa Bunga</div></td>
                    <td width=\"10%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl Realisasi</div></td>
                    <td width=\"5%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Jangka Waktu</div></td>			       
                    <td width=\"7%\"style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"text-align: right;font-size:10;\">Tgl JT Tempo</div></td>
                </tr>				
            </table>";

			$totalpokokglobal 		= 0;
			$totalsisapokokglobal	= 0;
			$totalsisamarginglobal 	= 0;
			foreach ($acctsourcefund as $kSF => $vSF) {
                $acctcreditsaccount_sourcefund = AcctCreditsAccount::with('member')
                ->where('credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                ->where('credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                ->where('source_fund_id', $vSF['source_fund_id'])
                ->where('credits_account_last_balance', '>', 0)
                ->where('credits_approve_status', 1)
                ->where('data_state', 0);
                if (!empty($branch_id)) {
                    $acctcreditsaccount_sourcefund = $acctcreditsaccount_sourcefund->where('branch_id', $branch_id);
                }
                $acctcreditsaccount_sourcefund = $acctcreditsaccount_sourcefund->orderBy('credits_account_serial', 'ASC')
                ->orderBy('member_id', 'ASC')
                // ->orderBy('member_name', 'ASC')
                // ->orderBy('member_address', 'ASC')
                ->orderBy('credits_account_date', 'ASC')
                ->orderBy('credits_account_due_date', 'ASC')
                ->orderBy('credits_account_interest', 'ASC')
                ->orderBy('credits_account_last_balance', 'ASC')
                ->orderBy('credits_account_amount', 'ASC')
                ->orderBy('credits_account_interest_last_balance', 'ASC')
                ->orderBy('credits_account_period', 'ASC')
                ->orderBy('credits_account_provisi', 'ASC')
                ->orderBy('credits_account_komisi', 'ASC')
                ->orderBy('credits_account_insurance', 'ASC')
                ->orderBy('credits_account_stash', 'ASC')
                ->orderBy('credits_account_adm_cost', 'ASC')
                ->orderBy('credits_account_materai', 'ASC')
                ->orderBy('credits_account_risk_reserve', 'ASC')
                ->orderBy('credits_account_principal', 'ASC')
                ->get();

				if (!empty($acctcreditsaccount_sourcefund)) {
					$export .= "
                    <table>
                        <br>
                        <tr>
                            <td colspan =\"7\" width=\"100%\" style=\"border-bottom: 1px solid black;\"><div style=\"font-size:10\"><b>" . $vSF['source_fund_name'] . "</b></div></td>
                        </tr>
                        <br>
                    </table>";

                        $nov                = 1;
                        $totalbasilperjenis = 0;
                        $totalsaldoperjenis = 0;
                        $totalpokok 		= 0;
                        $totalsisapokok 	= 0;
                        $totalsisamargin 	= 0;
                        foreach ($acctcreditsaccount_sourcefund as $k => $v) {
                            $month 	= date('m', strtotime($sesi['end_date']));
                            $year	= date('Y', strtotime($sesi['end_date']));
                            $period = $month . $year;

                            $credits_account_interest_last_balance = ($v['credits_account_interest_amount'] * $v['credits_account_period']) - ($v['credits_account_payment_to'] * $v['credits_account_interest_amount']);

                            $export .= "
                            <table>
                                <tr>
                                    <td width=\"3%\"><div style=\"text-align: left;\">" . $nov . "</div></td>
                                    <td width=\"8%\"><div style=\"text-align: left;\">" . $v['credits_account_serial'] . "</div></td>
                                    <td width=\"10%\"><div style=\"text-align: left;\">" . $v->member->member_name . "</div></td>
                                    <td width=\"12%\"><div style=\"text-align: left;\">" . $v->member->member_address . "</div></td>
                                    <td width=\"10%\"><div style=\"text-align: right;\">" . number_format($v['credits_account_amount'], 2) . "</div></td>
                                    <td width=\"7%\"><div style=\"text-align: right;\">" . number_format($v['credits_account_interest'], 2) . "</div></td>
                                    <td width=\"10%\"><div style=\"text-align: right;\">" . number_format($v['credits_account_last_balance'], 2) . "</div></td>
                                    <td width=\"10%\"><div style=\"text-align: right;\">" . number_format($credits_account_interest_last_balance, 2) . "</div></td>
                                    <td width=\"10%\"><div style=\"text-align: right;\">" . $v['credits_account_date'] . "</div></td>
                                    <td width=\"5%\"><div style=\"text-align: right;\">" . $v['credits_account_period'] . "</div></td>
                                    <td width=\"7%\"><div style=\"text-align: right;\">" . $v['credits_account_due_date'] . "</div></td>
                                </tr>
                            </table>";

						$totalpokok         += $v['credits_account_amount'];
						$totalsisapokok     += $v['credits_account_last_balance'];
						$totalsisamargin    += $credits_account_interest_last_balance;
						$nov++;
					}

					$export .= "
                    <table>
                        <br>
                        <tr>
                            <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\"></div></td>
                            <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Subtotal </div></td>
                            <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalpokok, 2) . "</div></td>
                            <td colspan =\"2\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsisapokok, 2) . "</div></td>
                            <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">" . number_format($totalsisamargin, 2) . "</div></td>
                        </tr>
                        <br>
                    </table>";

					$totalpokokglobal       += $totalpokok;
					$totalsisapokokglobal   += $totalsisapokok;
					$totalsisamarginglobal  += $totalsisamargin;
				} else {
					continue;
				}
			}

			$export .= "
            <table>
                <tr>
                    <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\"></div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\"> </div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>POKOK</b></div></td>
                    <td colspan =\"2\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>SISA POKOK</b></div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\"><b>SISA BUNGA</b></div></td>
                </tr>
                <tr>
                    <td colspan =\"3\"><div style=\"font-size:10;text-align:left;font-style:italic\">Printed : ".date('d-m-Y H:i:s')."  ". auth()->user()->username ."</div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;font-weight:bold;text-align:center\">Total </div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalpokokglobal, 2)."</div></td>
                    <td colspan =\"2\" style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalsisapokokglobal, 2)."</div></td>
                    <td style=\"border-bottom: 1px solid black;border-top: 1px solid black\"><div style=\"font-size:10;text-align:right\">".number_format($totalsisamarginglobal, 2)."</div></td>
                
                    </tr>
            </table>";
		}

        //$pdf::Image( $path, 4, 4, 40, 20, 'PNG', '', 'LT', false, 300, 'L', false, false, 1, false, false, false);
        $pdf::writeHTML($export, true, false, false, false, '');

        $filename = 'Laporan Nominatif Pinjaman.pdf';
        $pdf::Output($filename, 'I');
    }

    public function export($sesi){
        $branch_id          = auth()->user()->branch_id;
        $branch_status      = auth()->user()->branch_status;
        $preferencecompany	= PreferenceCompany::select('company_name')->first();
        $spreadsheet        = new Spreadsheet();

        if($branch_status == 1){
            if($sesi['branch_id'] == '' || $sesi['branch_id'] == 0){
                $branch_id = '';
            } else {
                $branch_id = $sesi['branch_id'];
            }
        }
        $acctcreditsaccount = AcctCreditsAccount::with('member')
        ->where('data_state', 0)
        ->where('credits_approve_status', 1)
        ->where('credits_account_last_balance', '>', 0)
        ->where('credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
        ->where('credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])));

        if(!empty($branch_id)){
            $acctcreditsaccount = $acctcreditsaccount->where('branch_id', $branch_id);
        }
        $acctcreditsaccount = $acctcreditsaccount->orderBy('created_at', 'ASC')
        ->get();

        $acctcredits 		= AcctCredits::select('credits_id', 'credits_name')
        ->where('data_state', 0)
        ->get();

        $acctsourcefund     = AcctSourceFund::select('source_fund_id', 'source_fund_name')
        ->where('data_state', 0)
        ->get();

        if(count($acctcreditsaccount)>=0){
            $spreadsheet->getProperties()->setCreator($preferencecompany['company_name'])
                                            ->setLastModifiedBy($preferencecompany['company_name'])
                                            ->setTitle("Laporan Nominatif Pinjaman")
                                            ->setSubject("")
                                            ->setDescription("Laporan Nominatif Pinjaman")
                                            ->setKeywords("Laporan, Nominatif, Simp, Bjk")
                                            ->setCategory("Laporan Nominatif Pinjaman");
                                    
            $sheet = $spreadsheet->getActiveSheet(0);
            $spreadsheet->getActiveSheet()->setTitle("Laporan Nominatif Pinjaman");

            $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(5);
            $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(20);
            $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(30);
            $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(40);
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

            $spreadsheet->getActiveSheet()->mergeCells("B1:J1");
            $spreadsheet->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $spreadsheet->getActiveSheet()->getStyle('B1')->getFont()->setBold(true)->setSize(16);

            if ($sesi['kelompok'] == 0) {
                $spreadsheet->getActiveSheet()->setCellValue('B3', "No");
                $spreadsheet->getActiveSheet()->setCellValue('C3', "No. Kredit");
                $spreadsheet->getActiveSheet()->setCellValue('D3', "Nama");
                $spreadsheet->getActiveSheet()->setCellValue('E3', "Alamat");
                $spreadsheet->getActiveSheet()->setCellValue('F3', "Plafon");
                $spreadsheet->getActiveSheet()->setCellValue('G3', "Sisa Pokok");
                $spreadsheet->getActiveSheet()->setCellValue('H3', "Tanggal Pinjam");
                $spreadsheet->getActiveSheet()->setCellValue('I3', "Jangka Waktu");
                $spreadsheet->getActiveSheet()->setCellValue('J3', "Tanggal Jatuh Tempo");
                $spreadsheet->getActiveSheet()->setCellValue('K3', "Biaya Provisi");
                $spreadsheet->getActiveSheet()->setCellValue('L3', "Biaya Survei");
                $spreadsheet->getActiveSheet()->setCellValue('M3', "Biaya Asuransi");
                $spreadsheet->getActiveSheet()->setCellValue('N3', "Biaya Simpanan Wajib");
                $spreadsheet->getActiveSheet()->setCellValue('O3', "Biaya Administrasi");
                $spreadsheet->getActiveSheet()->setCellValue('P3', "Biaya Materai");
                $spreadsheet->getActiveSheet()->setCellValue('Q3', "Biaya Cadangan Resiko");
                $spreadsheet->getActiveSheet()->setCellValue('R3', "Biaya Simpanan Pokok");
                $spreadsheet->getActiveSheet()->setCellValue('S3', "Nama Sales");
                $spreadsheet->getActiveSheet()->setCellValue('B1', "DAFTAR NOMINATIF PEMBIAYAAN GLOBAL");
            } else if ($sesi['kelompok'] == 1) {
                $spreadsheet->getActiveSheet()->setCellValue('B3', "No");
                $spreadsheet->getActiveSheet()->setCellValue('C3', "No. Kredit");
                $spreadsheet->getActiveSheet()->setCellValue('D3', "Nama");
                $spreadsheet->getActiveSheet()->setCellValue('E3', "Alamat");
                $spreadsheet->getActiveSheet()->setCellValue('F3', "Pokok");
                $spreadsheet->getActiveSheet()->setCellValue('G3', "Bunga");
                $spreadsheet->getActiveSheet()->setCellValue('H3', "Sisa Pokok");
                $spreadsheet->getActiveSheet()->setCellValue('I3', "Sisa Bunga");
                $spreadsheet->getActiveSheet()->setCellValue('J3', "Tanggal Realisasi");
                $spreadsheet->getActiveSheet()->setCellValue('K3', "Jangka Waktu");
                $spreadsheet->getActiveSheet()->setCellValue('L3', "Jatuh Tempo");
                $spreadsheet->getActiveSheet()->setCellValue('M3', "Biaya Provisi");
                $spreadsheet->getActiveSheet()->setCellValue('N3', "Biaya Survei");
                $spreadsheet->getActiveSheet()->setCellValue('O3', "Biaya Asuransi");
                $spreadsheet->getActiveSheet()->setCellValue('P3', "Biaya Simpanan Wajib");
                $spreadsheet->getActiveSheet()->setCellValue('Q3', "Biaya Administrasi");
                $spreadsheet->getActiveSheet()->setCellValue('R3', "Biaya Materai");
                $spreadsheet->getActiveSheet()->setCellValue('S3', "Biaya Cadangan Resiko");
                $spreadsheet->getActiveSheet()->setCellValue('T3', "Biaya Simpanan Pokok");
                $spreadsheet->getActiveSheet()->setCellValue('U3', "Nama Sales");
                $spreadsheet->getActiveSheet()->setCellValue('B1', "DAFTAR NOMINATIF PEMBIAYAAN PER JENIS KREDIT");
            } else {
                $spreadsheet->getActiveSheet()->setCellValue('B3', "No");
                $spreadsheet->getActiveSheet()->setCellValue('C3', "No. Kredit");
                $spreadsheet->getActiveSheet()->setCellValue('D3', "Nama");
                $spreadsheet->getActiveSheet()->setCellValue('E3', "Alamat");
                $spreadsheet->getActiveSheet()->setCellValue('F3', "Pokok");
                $spreadsheet->getActiveSheet()->setCellValue('G3', "Bunga");
                $spreadsheet->getActiveSheet()->setCellValue('H3', "Sisa Pokok");
                $spreadsheet->getActiveSheet()->setCellValue('I3', "Sisa Bunga");
                $spreadsheet->getActiveSheet()->setCellValue('J3', "Tanggal Realisasi");
                $spreadsheet->getActiveSheet()->setCellValue('K3', "Jangka Waktu");
                $spreadsheet->getActiveSheet()->setCellValue('L3', "Jatuh Tempo");
                $spreadsheet->getActiveSheet()->setCellValue('M3', "Biaya Provisi");
                $spreadsheet->getActiveSheet()->setCellValue('N3', "Biaya Survei");
                $spreadsheet->getActiveSheet()->setCellValue('O3', "Biaya Asuransi");
                $spreadsheet->getActiveSheet()->setCellValue('P3', "Biaya Simpanan Wajib");
                $spreadsheet->getActiveSheet()->setCellValue('Q3', "Biaya Administrasi");
                $spreadsheet->getActiveSheet()->setCellValue('R3', "Biaya Materai");
                $spreadsheet->getActiveSheet()->setCellValue('S3', "Biaya Cadangan Resiko");
                $spreadsheet->getActiveSheet()->setCellValue('T3', "Biaya Simpanan Pokok");
                $spreadsheet->getActiveSheet()->setCellValue('U3', "Nama Sales");
                $spreadsheet->getActiveSheet()->setCellValue('B1', "DAFTAR NOMINATIF PEMBIAYAAN PER JENIS SUMBER DANA");
            }
            $spreadsheet->getActiveSheet()->setCellValue('B2', "Periode : " . $sesi['start_date'] . " S.D " . $sesi['end_date']);

            $row                      = 4;
            $no                     = 0;
            $totalplafon	        = 0;
            $totalsisapokok         = 0;
            $totalProvisi	        = 0;
            $totalSurvei	        = 0;
            $totalAsurasi	        = 0;
            $totalSimpananWajib     = 0;
            $totalAdministrasi 	    = 0;
            $totalMaterai 		    = 0;
            $totalCadanganResiko    = 0;
            $totalSimpananPokok	    = 0;
            if ($sesi['kelompok'] == 0) {
                $spreadsheet->getActiveSheet()->getStyle('B3:S3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B3:S3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B3:S3')->getFont()->setBold(true);
                foreach ($acctcreditsaccount as $key => $val) {
                    $no++;
                    $spreadsheet->setActiveSheetIndex(0);
                    $spreadsheet->getActiveSheet()->getStyle('B' . $row . ':S' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                    $spreadsheet->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('H' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('K' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('L' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('M' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('N' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('O' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('P' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('Q' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('R' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                    $spreadsheet->getActiveSheet()->getStyle('S' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                    $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $no);
                    $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $val['credits_account_serial']);
                    $spreadsheet->getActiveSheet()->setCellValue('D' . $row, $val->member->member_name);
                    $spreadsheet->getActiveSheet()->setCellValue('E' . $row, $val->member->member_address);
                    $spreadsheet->getActiveSheet()->setCellValue('F' . $row, number_format($val['credits_account_amount'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('G' . $row, number_format($val['credits_account_last_balance'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('H' . $row, $val['credits_account_date']);
                    $spreadsheet->getActiveSheet()->setCellValue('I' . $row, $val['credits_account_period']);
                    $spreadsheet->getActiveSheet()->setCellValue('J' . $row, $val['credits_account_due_date']);
                    $spreadsheet->getActiveSheet()->setCellValue('K' . $row, number_format($val['credits_account_provisi'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('L' . $row, number_format($val['credits_account_komisi'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('M' . $row, number_format($val['credits_account_insurance'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('N' . $row, number_format($val['credits_account_stash'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('O' . $row, number_format($val['credits_account_adm_cost'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('P' . $row, number_format($val['credits_account_materai'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('Q' . $row, number_format($val['credits_account_risk_reserve'], 2));
                    $spreadsheet->getActiveSheet()->setCellValue('R' . $row, number_format($val['credits_account_principal'], 2));

                    $totalplafon	        += $val['credits_account_amount'];
                    $totalsisapokok         += $val['credits_account_last_balance'];
                    $totalProvisi 	        += $val['credits_account_provisi'];
                    $totalSurvei	        += $val['credits_account_komisi'];
                    $totalAsurasi	        += $val['credits_account_insurance'];
                    $totalSimpananWajib	    += $val['credits_account_stash'];
                    $totalAdministrasi	    += $val['credits_account_adm_cost'];
                    $totalMaterai	        += $val['credits_account_materai'];
                    $totalCadanganResiko	+= $val['credits_account_risk_reserve'];
                    $totalSimpananPokok	    += $val['credits_account_principal'];
                    $row++;
                }
            } else if ($sesi['kelompok'] == 1) {
                $spreadsheet->getActiveSheet()->getStyle('B3:U3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B3:U3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B3:U3')->getFont()->setBold(true);

                $i                      = 4;
                $jumlahpokok 	        = 0;
                $jumlahsisapokok        = 0;
                $jumlahsisabunga        = 0;
                $jumlahProvisi	        = 0;
                $jumlahSurvei	        = 0;
                $jumlahAsuransi	        = 0;
                $jumlahSimpananWajib    = 0;
                $jumlahAdministrasi 	= 0;
                $jumlahMaterai 		    = 0;
                $jumlahCadanganResiko   = 0;
                $jumlahSimpananPokok	= 0;

                foreach ($acctcredits as $k => $v) {
                    $acctcreditsaccount_credits = AcctCreditsAccount::with('member')
                    ->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                    ->where('acct_credits_account.credits_account_last_balance', '>', 0)
                    ->where('acct_credits_account.credits_approve_status', 1)
                    ->where('acct_credits_account.data_state', 0);
                    if (!empty($v['credits_id'])) {
                        $acctcreditsaccount_credits = $acctcreditsaccount_credits->where('acct_credits_account.credits_id', $v['credits_id']);
                    }
                    if (!empty($branch_id)) {
                        $acctcreditsaccount_credits = $acctcreditsaccount_credits->where('acct_credits_account.branch_id', $branch_id);
                    }
                    $acctcreditsaccount_credits = $acctcreditsaccount_credits->orderby('acct_credits_account.credits_account_serial', 'ASC')
                    ->orderBy('acct_credits_account.member_id', 'ASC')
                    // ->orderBy('core_member.member_name', 'ASC')
                    // ->orderBy('core_member.member_address', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_last_balance', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_date', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_due_date', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_amount', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_interest_last_balance', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_interest', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_period', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_provisi', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_komisi', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_insurance', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_stash', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_adm_cost', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_materai', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_risk_reserve', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_principal', 'ASC')
                    ->get();

                    if (!empty($acctcreditsaccount_credits)) {
                        $spreadsheet->getActiveSheet()->getStyle('B' . $i)->getFont()->setBold(true)->setSize(14);
                        $spreadsheet->getActiveSheet()->getStyle('B' . $i . ':U' . $i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B' . $i . ':U' . $i);

                        $spreadsheet->getActiveSheet()->setCellValue('B' . $i, $v['credits_name']);

                        $nov                    = 0;
                        $row                    = $i + 1;
                        $subtotalpokok 		    = 0;
                        $subtotalsisapokok	    = 0;
                        $subtotalsisabunga	    = 0;
                        $subtotalProvisi	    = 0;
                        $subtotalSurvei	        = 0;
                        $subtotalAsurasi	    = 0;
                        $subtotalSimpananWajib  = 0;
                        $subtotalAdministrasi 	= 0;
                        $subtotalMaterai 		= 0;
                        $subtotalCadanganResiko = 0;
                        $subtotalSimpananPokok	= 0;

                        foreach ($acctcreditsaccount_credits as $key => $val) {
                            $no++;
                            $spreadsheet->setActiveSheetIndex(0);
                            $spreadsheet->getActiveSheet()->getStyle('B' . $row . ':U' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $spreadsheet->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('H' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('K' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('L' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('M' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('N' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('O' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('P' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('Q' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('R' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('S' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('T' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('U' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                            $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $no);
                            $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $val['credits_account_serial']);
                            $spreadsheet->getActiveSheet()->setCellValue('D' . $row, $val->member->member_name);
                            $spreadsheet->getActiveSheet()->setCellValue('E' . $row, $val->member->member_address);
                            $spreadsheet->getActiveSheet()->setCellValue('F' . $row, number_format($val['credits_account_amount'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('G' . $row, number_format($val['credits_account_interest'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('H' . $row, number_format($val['credits_account_last_balance'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('I' . $row, number_format($val['credits_account_interest_last_balance'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('J' . $row, $val['credits_account_date']);
                            $spreadsheet->getActiveSheet()->setCellValue('K' . $row, $val['credits_account_period']);
                            $spreadsheet->getActiveSheet()->setCellValue('L' . $row, $val['credits_account_due_date']);
                            $spreadsheet->getActiveSheet()->setCellValue('M' . $row, number_format($val['credits_account_provisi'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('N' . $row, number_format($val['credits_account_komisi'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('O' . $row, number_format($val['credits_account_insurance'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('P' . $row, number_format($val['credits_account_stash'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('Q' . $row, number_format($val['credits_account_adm_cost'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('R' . $row, number_format($val['credits_account_materai'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('S' . $row, number_format($val['credits_account_risk_reserve'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('T' . $row, number_format($val['credits_account_principal'], 2));
                            $row++;

                            $subtotalpokok 		    += $val['credits_account_amount'];
                            $subtotalsisapokok	    += $val['credits_account_last_balance'];
                            $subtotalsisabunga	    += $val['credits_account_interest_last_balance'];
                            $subtotalProvisi 	    += $val['credits_account_provisi'];
                            $subtotalSurvei	        += $val['credits_account_komisi'];
                            $subtotalAsurasi	    += $val['credits_account_insurance'];
                            $subtotalSimpananWajib	+= $val['credits_account_stash'];
                            $subtotalAdministrasi	+= $val['credits_account_adm_cost'];
                            $subtotalMaterai	    += $val['credits_account_materai'];
                            $subtotalCadanganResiko	+= $val['credits_account_risk_reserve'];
                            $subtotalSimpananPokok	+= $val['credits_account_principal'];
                            $i                      = $row;
                        }
                        $m = $row;

                        $spreadsheet->getActiveSheet()->getStyle('B' . $m . ':U' . $m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $spreadsheet->getActiveSheet()->getStyle('B' . $m . ':U' . $m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B' . $m . ':E' . $m);

                        $spreadsheet->getActiveSheet()->setCellValue('B' . $m, 'SubTotal');
                        $spreadsheet->getActiveSheet()->setCellValue('F' . $m, number_format($subtotalpokok, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('H' . $m, number_format($subtotalsisapokok, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('I' . $m, number_format($subtotalsisabunga, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('K' . $m, number_format($subtotalProvisi, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('L' . $m, number_format($subtotalSurvei, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('M' . $m, number_format($subtotalAsurasi, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('N' . $m, number_format($subtotalSimpananWajib, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('O' . $m, number_format($subtotalAdministrasi, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('P' . $m, number_format($subtotalMaterai, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('Q' . $m, number_format($subtotalCadanganResiko, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('R' . $m, number_format($subtotalSimpananPokok, 2));

                        $i                      = $m + 1;
                        $jumlahpokok 	        += $subtotalpokok;
                        $jumlahsisapokok        += $subtotalsisapokok;
                        $jumlahsisabunga        += $subtotalsisabunga;
                        $jumlahProvisi	        += $subtotalProvisi;
                        $jumlahSurvei	        += $subtotalSurvei;
                        $jumlahAsuransi	        += $subtotalAsurasi;
                        $jumlahSimpananWajib    += $subtotalSimpananWajib;
                        $jumlahAdministrasi 	+= $subtotalAdministrasi;
                        $jumlahMaterai 		    += $subtotalMaterai;
                        $jumlahCadanganResiko   += $subtotalCadanganResiko;
                        $jumlahSimpananPokok	+= $subtotalSimpananPokok;
                    }
                }
                $row = $i;
            } else if ($sesi['kelompok'] == 2) {
                $spreadsheet->getActiveSheet()->getStyle('B3:U3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $spreadsheet->getActiveSheet()->getStyle('B3:U3')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $spreadsheet->getActiveSheet()->getStyle('B3:U3')->getFont()->setBold(true);

                $i = 4;
                foreach ($acctsourcefund as $k => $v) {
                    $acctcreditsaccount_sourcefund = AcctCreditsAccount::with('member')
                    ->where('acct_credits_account.credits_account_date', '<=', date('Y-m-d', strtotime($sesi['end_date'])))
                    ->where('acct_credits_account.credits_account_date', '>=', date('Y-m-d', strtotime($sesi['start_date'])))
                    ->where('acct_credits_account.source_fund_id', $v['source_fund_id'])
                    ->where('acct_credits_account.credits_account_last_balance', '>', 0)
                    ->where('acct_credits_account.credits_approve_status', 1)
                    ->where('acct_credits_account.data_state', 0);
                    if (!empty($branch_id)) {
                        $acctcreditsaccount_sourcefund = $acctcreditsaccount_sourcefund->where('acct_credits_account.branch_id', $branch_id);
                    }
                    $acctcreditsaccount_sourcefund = $acctcreditsaccount_sourcefund->orderBy('acct_credits_account.credits_account_serial', 'ASC')
                    ->orderBy('acct_credits_account.member_id', 'ASC')
                    // ->orderBy('core_member.member_name', 'ASC')
                    // ->orderBy('core_member.member_address', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_date', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_due_date', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_interest', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_last_balance', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_amount', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_interest_last_balance', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_period', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_provisi', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_komisi', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_insurance', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_stash', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_adm_cost', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_materai', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_risk_reserve', 'ASC')
                    ->orderBy('acct_credits_account.credits_account_principal', 'ASC')
                    ->get();

                    if (!empty($acctcreditsaccount_sourcefund)) {
                        $spreadsheet->getActiveSheet()->getStyle('B' . $i)->getFont()->setBold(true)->setSize(14);
                        $spreadsheet->getActiveSheet()->getStyle('B' . $i . ':U' . $i)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                        $spreadsheet->getActiveSheet()->mergeCells('B' . $i . ':U' . $i);

                        $spreadsheet->getActiveSheet()->setCellValue('B' . $i, $v['source_fund_name']);

                        $nov                    = 0;
                        $row                    = $i + 1;
                        $subtotalpokok 		    = 0;
                        $jumlahpokok 		    = 0;
                        $jumlahsisapokok        = 0;
                        $jumlahsisabunga	    = 0;
                        $jumlahProvisi  	    = 0;
                        $jumlahSurvei    	    = 0;
                        $jumlahAsuransi         = 0;
                        $jumlahSimpananWajib    = 0;
                        $jumlahAdministrasi     = 0;
                        $jumlahMaterai          = 0;
                        $jumlahCadanganResiko   = 0;
                        $jumlahSimpananPokok    = 0;
                        $subtotalsisapokok	    = 0;
                        $subtotalsisabunga	    = 0;
                        $subtotalProvisi	    = 0;
                        $subtotalSurvei	        = 0;
                        $subtotalAsurasi	    = 0;
                        $subtotalSimpananWajib  = 0;
                        $subtotalAdministrasi 	= 0;
                        $subtotalMaterai 		= 0;
                        $subtotalCadanganResiko = 0;
                        $subtotalSimpananPokok	= 0;

                        foreach ($acctcreditsaccount_sourcefund as $key => $val) {
                            $no++;
                            $spreadsheet->setActiveSheetIndex(0);
                            $spreadsheet->getActiveSheet()->getStyle('B' . $row . ':U' . $row)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                            $spreadsheet->getActiveSheet()->getStyle('B' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                            $spreadsheet->getActiveSheet()->getStyle('C' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('D' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('E' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('F' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('G' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('H' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('I' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                            $spreadsheet->getActiveSheet()->getStyle('J' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('K' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('L' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('M' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('N' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('O' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('P' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('Q' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('R' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('S' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('T' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                            $spreadsheet->getActiveSheet()->getStyle('U' . $row)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);

                            $spreadsheet->getActiveSheet()->setCellValue('B' . $row, $no);
                            $spreadsheet->getActiveSheet()->setCellValue('C' . $row, $val['credits_account_serial']);
                            $spreadsheet->getActiveSheet()->setCellValue('D' . $row, $val->member->member_name);
                            $spreadsheet->getActiveSheet()->setCellValue('E' . $row, $val->member->member_address);
                            $spreadsheet->getActiveSheet()->setCellValue('F' . $row, number_format($val['credits_account_amount'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('G' . $row, number_format($val['credits_account_interest'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('H' . $row, number_format($val['credits_account_last_balance'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('I' . $row, number_format($val['credits_account_interest_last_balance'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('J' . $row, $val['credits_account_date']);
                            $spreadsheet->getActiveSheet()->setCellValue('K' . $row, $val['credits_account_period']);
                            $spreadsheet->getActiveSheet()->setCellValue('L' . $row, $val['credits_account_due_date']);
                            $spreadsheet->getActiveSheet()->setCellValue('M' . $row, number_format($val['credits_account_provisi'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('N' . $row, number_format($val['credits_account_komisi'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('O' . $row, number_format($val['credits_account_insurance'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('P' . $row, number_format($val['credits_account_stash'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('Q' . $row, number_format($val['credits_account_adm_cost'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('R' . $row, number_format($val['credits_account_materai'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('S' . $row, number_format($val['credits_account_risk_reserve'], 2));
                            $spreadsheet->getActiveSheet()->setCellValue('T' . $row, number_format($val['credits_account_principal'], 2));
                            
                            $row++;
                            $subtotalpokok 		    += $val['credits_account_amount'];
                            $subtotalsisapokok	    += $val['credits_account_last_balance'];
                            $subtotalsisabunga	    += $val['credits_account_interest_last_balance'];
                            $subtotalProvisi 	    += $val['credits_account_provisi'];
                            $subtotalSurvei	        += $val['credits_account_komisi'];
                            $subtotalAsurasi	    += $val['credits_account_insurance'];
                            $subtotalSimpananWajib	+= $val['credits_account_stash'];
                            $subtotalAdministrasi	+= $val['credits_account_adm_cost'];
                            $subtotalMaterai	    += $val['credits_account_materai'];
                            $subtotalCadanganResiko	+= $val['credits_account_risk_reserve'];
                            $subtotalSimpananPokok	+= $val['credits_account_principal'];
                            $i = $row;
                        }

                        $m = $row;
                        
                        $spreadsheet->getActiveSheet()->mergeCells('B' . $m . ':E' . $m);
                        $spreadsheet->getActiveSheet()->getStyle('B' . $m . ':U' . $m)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                        $spreadsheet->getActiveSheet()->getStyle('B' . $m . ':U' . $m)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                        $spreadsheet->getActiveSheet()->setCellValue('B' . $m, 'SubTotal');
                        $spreadsheet->getActiveSheet()->setCellValue('F' . $m, number_format($subtotalpokok, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('H' . $m, number_format($subtotalsisapokok, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('I' . $m, number_format($subtotalsisabunga, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('K' . $m, number_format($subtotalProvisi, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('L' . $m, number_format($subtotalSurvei, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('M' . $m, number_format($subtotalAsurasi, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('N' . $m, number_format($subtotalSimpananWajib, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('O' . $m, number_format($subtotalAdministrasi, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('P' . $m, number_format($subtotalMaterai, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('Q' . $m, number_format($subtotalCadanganResiko, 2));
                        $spreadsheet->getActiveSheet()->setCellValue('R' . $m, number_format($subtotalSimpananPokok, 2));
                        $i = $m + 1;
                    }
                }
                $row = $i;
            }

            $n = $row;
            if ($sesi['kelompok'] == 0) {
                $spreadsheet->getActiveSheet()->mergeCells('B' . $n . ':E' . $n);
                $spreadsheet->getActiveSheet()->getStyle('B' . $n . ':S' . $n)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                $spreadsheet->getActiveSheet()->getStyle('B' . $n . ':S' . $n)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $spreadsheet->getActiveSheet()->setCellValue('B' . $n, 'Total');
                $spreadsheet->getActiveSheet()->setCellValue('F' . $n, number_format($totalplafon, 2));
                $spreadsheet->getActiveSheet()->setCellValue('G' . $n, number_format($totalsisapokok, 2));
                $spreadsheet->getActiveSheet()->setCellValue('K' . $n, number_format($totalProvisi, 2));
                $spreadsheet->getActiveSheet()->setCellValue('L' . $n, number_format($totalSurvei, 2));
                $spreadsheet->getActiveSheet()->setCellValue('M' . $n, number_format($totalAsurasi, 2));
                $spreadsheet->getActiveSheet()->setCellValue('N' . $n, number_format($totalSimpananWajib, 2));
                $spreadsheet->getActiveSheet()->setCellValue('O' . $n, number_format($totalAdministrasi, 2));
                $spreadsheet->getActiveSheet()->setCellValue('P' . $n, number_format($totalMaterai, 2));
                $spreadsheet->getActiveSheet()->setCellValue('Q' . $n, number_format($totalCadanganResiko, 2));
                $spreadsheet->getActiveSheet()->setCellValue('R' . $n, number_format($totalSimpananPokok, 2));
            } else {
                $spreadsheet->getActiveSheet()->mergeCells('B' . $n . ':E' . $n);
                $spreadsheet->getActiveSheet()->getStyle('B' . $n . ':U' . $n)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
                $spreadsheet->getActiveSheet()->getStyle('B' . $n . ':U' . $n)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                $spreadsheet->getActiveSheet()->setCellValue('B' . $n, 'Total');
                $spreadsheet->getActiveSheet()->setCellValue('F' . $n, number_format($jumlahpokok, 2));
                $spreadsheet->getActiveSheet()->setCellValue('H' . $n, number_format($jumlahsisapokok, 2));
                $spreadsheet->getActiveSheet()->setCellValue('I' . $n, number_format($jumlahsisabunga, 2));
                $spreadsheet->getActiveSheet()->setCellValue('K' . $n, number_format($jumlahProvisi, 2));
                $spreadsheet->getActiveSheet()->setCellValue('L' . $n, number_format($jumlahSurvei, 2));
                $spreadsheet->getActiveSheet()->setCellValue('M' . $n, number_format($jumlahAsuransi, 2));
                $spreadsheet->getActiveSheet()->setCellValue('N' . $n, number_format($jumlahSimpananWajib, 2));
                $spreadsheet->getActiveSheet()->setCellValue('O' . $n, number_format($jumlahAdministrasi, 2));
                $spreadsheet->getActiveSheet()->setCellValue('P' . $n, number_format($jumlahMaterai, 2));
                $spreadsheet->getActiveSheet()->setCellValue('Q' . $n, number_format($jumlahCadanganResiko, 2));
                $spreadsheet->getActiveSheet()->setCellValue('R' . $n, number_format($jumlahSimpananPokok, 2));
            }
                
            ob_clean();
            $filename='Laporan Nominatif Pinjaman.xls';
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
