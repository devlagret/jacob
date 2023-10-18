<?php

namespace App\Helpers;

class Configuration
{
    
	public static function Month(){
		$month  = array(
            '01'    => "Januari",
            '02'    => "Februari",
            '03'    => "Maret",
            '04'    => "April",
            '05'    => "Mei",
            '06'    => "Juni",
            '07'    => "Juli",
            '08'    => "Agustus",
            '09'    => "September",
            '10'    => "Oktober",
            '11'    => "November",
            '12'    => "Desember",
        ); 

		return $month;
	}

	public static function MonthName(){
		$month_name = array(
            'January'		=> "01",
            'February'		=> "02",
            'March'			=> "03",
            'April'			=> "04",
            'May'			=> "05",
            'June'			=> "06",
            'July'			=> "07",
            'August'		=> "08",
            'September'		=> "09",
            'October'		=> "10",
            'November'		=> "11",
            'December'		=> "12",
        ); 

		return $month_name;
	}

	public static function DayName(){
		$day_name   = array(
            'Sun'   => "Minggu",
            'Mon'   => "Senin",
            'Tue'   => "Selasa",
            'Wed'   => "Rabu",
            'Thu'   => "Kamis",
            'Fri'   => "Jumat",
            'Sat'   => "Sabtu",
        ); 

		return $day_name;
	}

	public static function ReportType(){
		$report_type = array (
            1 => 'Laba Rugi', 
            2 => 'Neraca'
        );

		return $report_type;
	}

	public static function MemberGender(){
		$member_gender = array (
            9 => ' ', 
            0 => 'Perempuan', 
            1 => 'Laki - Laki'
        );

		return $member_gender;
	}

	public static function MemberStatus(){
		$member_status = array (
            0 => 'Calon Anggota', 
            1 => 'Anggota'
        );

		return $member_status;
	}

	public static function MemberStatusAktif(){
		$member_status_aktif = array (
            0 => 'Aktif', 
            1 => 'Tidak Aktif'
        );

		return $member_status_aktif;
	}

	public static function SavingsProfitSharing(){
		$savings_provit_sharing = array (
            0 => 'Tidak Dapat Bunga', 
            1 => 'Dapat Bunga'
        );

		return $savings_provit_sharing;
	}

	public static function AccountStatus(){
		$account_status = array (
            0 => 'Debit', 
            1 => 'Kredit'
        );

		return $account_status;
	}

	public static function DepositoExtraType(){
		$deposito_extra_type = array (
            0 => 'Manual', 
            1 => 'ARO'
        );

		return $deposito_extra_type;
	}

	public static function KelompokPerkiraan(){
		return collect([
            0 => 'NA - Neraca Aktiva', 
            1 => 'NP - Neraca Pasiva', 
            2 => 'RA - Rugi Laba (A)', 
            3 => 'RP - Rugi Laba (B)']);
	}

	public static function MemberCharacter(){
		$member_character = array (
            9 => ' ', 
            2 => 'Pendiri', 
            0 => 'Biasa', 
            1 => 'Luar Biasa'
        );

		return $member_character;
	}

	public static function debetsource(){
		$source = array (
            9 => ' ', 
            1 => 'Simpanan Pokok', 
            2 => 'Tabungan'
        );

		return $source;
	}

	public static function MemberIdentity(){
		$member_identity = array (
            9 => ' ', 
            0 => 'KTP', 
            1 => 'KK', 
            2 => 'SIM', 
            3 => 'AKTA'
        );

		return $member_identity;
	}

	public static function KelompokLaporanSimpanan(){
		$kelompok_laporan_simpanan = array (
            0 => 'Global', 
            1 => 'Jenis Simpanan'
        );

		return $kelompok_laporan_simpanan;
	}

	public static function KelompokLaporanSimpanan1(){
		$kelompok_laporan_simpanan = array (
            0 => 'Global', 
            1 => 'Jenis Transaksi'
        );

		return $kelompok_laporan_simpanan;
	}
	
	public static function KelompokLaporanSimpananBerjangka(){
		$kelompok_laporan_simpanan_berjangka = array (
            0 => 'Global', 
            1 => 'Jenis Waktu'
        );

		return $kelompok_laporan_simpanan_berjangka;
	}

	public static function KelompokLaporanPembiayaan(){
		$kelompok_laporan_pembiayaan = array (
            0 => 'Global', 
            1 => 'Jenis Akad', 
            2 => 'Sumber Dana'
        );

		return $kelompok_laporan_pembiayaan;
	}

	public static function FamilyRelationship(){
		$family_relationship = array (
            1 => 'Anak', 
            2 => 'Istri/Suami', 
            3 => 'Saudara', 
            4 => 'Lainnya'
        );

		return $family_relationship;
	}
	public static function ApprovalStatus(){
		$approve = array(
            0 => 'Belum Disetujui', 
            1 =>'Terima', 
            9=>'Tolak'
        );

		return $approve;
	}

	public static function BlockirType(){
		$blockir_type = array (
            9 => '', 
            0 => 'Rekening', 
            1 => 'Saldo'
        );

		return $blockir_type;
	}

	public static function BlockirStatus(){
		$blockir_status = array (
            9 => '', 
            0 => 'UnBlockir', 
            1 => 'Blockir'
        );

		return $blockir_status;
	}

	public static function AcctReportType(){
		$acct_report_type = array (
            1 => 'Title', 
            2 => 'Subtitle', 
            3 => 'Parent', 
            4 => 'Loop', 
            5 => 'Sum', 
            6 => 'GrandTotal'
        );

		return $acct_report_type;
	}

	public static function ProfitLossReportType(){
		$profit_loss_report_type = array (
            1 => 'Rugi Laba Bulanan', 
            2 => 'Rugi Laba Tahunan'
        );

		return $profit_loss_report_type;
	}

	public static function ProfitLossReportFormat(){
		$profit_loss_report_format = array (
            1 => 'USP', 
            2 => 'Koperasi', 
            3 => 'Konsolidasi'
        );

		return $profit_loss_report_format;
	}

	public static function AgunanType(){
		$agunan_type = array (
            9 => '', 
            1 => 'BPKB', 
            2 => 'Sertifikat'
        );
	}

	public static function AccountComparationReportType(){
		$account_comparation_report_type = array (
            1 => 'Komparasi Bulanan', 
            2 => 'Komparasi Tahunan'
        );

		return $account_comparation_report_type;
	}

	public static function FinancialAnalysisType(){
		$financial_analysis_type = array (
            0 => '',
            1 => 'LIKUIDITAS CASH RATIO',
            2 => 'CAR (Capital Aset Ratio)',
            3 => 'FDR (Financing to Debt Ratio)',
            4 => 'BOPO (Beban Operasional vs Pendapatan Operasional)',
		);

		return $financial_analysis_type;
	}

	public static function ManagementZakatType(){
		$management_zakat_type = array (
            9 => '', 
            0 => 'Penerimaan Zakat', 
            1 => 'Penyaluran Zakat'
        );

		return $management_zakat_type;
	}

	public static function SourceFundZakat(){
		$source_fund_zakat = array (
            9 => '', 
            0 => 'KSPPS (Internal)', 
            1 => 'Pihak Luar (Eksternal)'
        );

		return $source_fund_zakat;
	}

	public static function DistributionZakat(){
		$distribution_zakat = array (
            9 => '', 
            0 => 'Badan / Yayasan', 
            1 => 'Lainnya'
        );

		return $distribution_zakat;
	}

	public static function BranchStatus(){
		$branch_status = array (
            0 => 'Kantor Cabang', 
            1 => 'Kantor Pusat'
        );

		return $branch_status;
	}

	public static function ConsolidationReport(){
		$consolidation_report = array (
            9 => '', 
            0 => 'Cabang', 
            1 => 'Konsolidasi'
        );

		return $consolidation_report;
	}

	public static function SavingsCashMutationStatus(){
		$savings_cash_mutation_status = array (
            0 => 'Admin', 
            1 => 'Android'
        );

		return $savings_cash_mutation_status;
	}

	public static function CreditsPaymentStatus(){
		$credits_payment_status = array (
            0 => 'Admin', 
            1 => 'Android'
        );

		return $credits_payment_status;
	}

	public static function PaymentType(){
		$payment_type = array (
            1 => 'Flat', 
            2 => 'Flat Anuitas', 
            3 => 'Slidingrate', 
            4 => 'Menurun Harian'
        );

		return $payment_type;
	}

	public static function PaymentPreference(){
		$payment_preference = array (
            1 => 'Manual', 
            2 => 'Auto Debet'
        );

		return $payment_preference;
	}

	public static function CreditsPaymentPeriod(){
		$period = array (
            1 => 'Bulanan', 
            2 => 'Mingguan'
        );

		return $period;
	}

	public static function CreditsPaymentPeriodAkad(){
		$period = array (
            0 => 'Pilih Salah Satu', 
            1 => 'bulan', 
            2 => 'minggu'
        );

		return $period;
	}

	public static function MaritalStatus(){
		$marital_status = array (
            0 => '', 
            1 => 'Kawin', 
            2 => 'Belum Kawin', 
            3 => 'Duda', 
            4 =>'Janda'
        );

		return $marital_status;
	}

	public static function HomeStatus(){
		$home_status = array (
            0 => '', 
            1 => 'Sendiri', 
            2 => 'Keluarga', 
            3 => 'Sewa', 
            4 => 'KPR', 
            5 => 'Dinas', 
            6 => 'Lainnya'
        );

		return $home_status;
	}

	public static function Vehicle(){
		$vehicle = array (
            0 => '', 
            1 => 'Motor', 
            2 => 'Mobil'
        );

		return $vehicle;
	}

	public static function LastEducation(){
		$last_education = array (
            0 => '', 
            1 => 'SD', 
            2 => 'SLTP', 
            3 => 'SLTA', 
            4 => 'D3', 
            5 =>'S1', 
            6=>'S2', 
            7=>'Lainnya'
        );

		return $last_education;
	}

	public static function UnitUser(){
		$unit_user = array (
            0 => '', 
            1 => 'Sendiri', 
            2 => 'Pasangan', 
            3 => 'Anak', 
            4 => 'Lainnya'
        );

		return $unit_user;
	}

	public static function WorkingType(){
		$working_type = array (
            0 => '', 
            1 => 'Karyawan', 
            2 => 'Profesional', 
            3 => 'Non Karyawan'
        );

		return $working_type;
	}	

	public static function BusinessScale(){
		$business_scale = array (
            0 => '', 
            1 => 'Besar', 
            2 => 'Menengah', 
            3 => 'Kecil'
        );

		return $business_scale;
	}	

	public static function BusinessOwner(){
		$business_owner = array (
            0 => '', 
            1 => 'Milik Sendiri', 
            2 => 'Sewa', 
            3 => 'Lainnya'
        );

		return $business_owner;
	}	

	public static function AgunanStatus(){
		$agunan_status = array ( 
            0 => 'Aktif', 
            1 => 'Dikembalikan'
        );

		return $agunan_status;
	}

	public static function CreditsApproveStatus(){
		$approve_status = array ( 
            0 => 'Belum Aktif', 
            1 => 'Disetujui', 
            2 => 'Dibatalkan'
        );

		return $approve_status;
	}

	public static function CreditsAccountStatus(){
		$credits_account_status = array ( 
            0 => 'Aktif', 
            1 => 'Selesai', 
            2 => 'Pelunasan'
        );

		return $credits_account_status;
	}

	public static function PenaltyType(){
		$penalty_type = array (
            0 => '', 
            1 => ' Dari Pokok', 
            2 => 'Dari Bunga'
        );

		return $penalty_type;
	}

	public static function Unpush($pesan,$key){//$key >= 0 or <=25
		$msg = str_split($pesan);
		$dresult = '';
		for($j=1;$j<=strlen($pesan);$j++){
			if ((ord($msg[$j-1])<65) or (ord($msg[$j-1])>90)){
				$dresult = $dresult.$msg[$j-1];
			} else {
				$ord_msg[$j-1] = 65+fmod(ord($msg[$j-1])+65-$key,26);
				$dresult = $dresult.chr($ord_msg[$j-1]);
			}
		}
		return $dresult;
	}
	
	public static function convert($msg){
		$division	= bindec("010");
		$lenght		= strlen($msg);
		$div		= $lenght/$division;
		$ret		='';
		$block		='';
		for($i=0;$i<$div;$i++){
			$val	= substr($msg,$i*$division,$division);
			if(substr($val,1,1)=="0"){
				$val = substr($val,0,1);
			}
			$dec 	= hexdec($val);
			if(strlen($dec)==1){
				$bin='00'.$dec;
			}else if(strlen($dec)==2){
				$bin='0'.$dec;
			} else {
				$bin=$dec;
			}
			$block .= $bin;
			if (strlen($block)==6){
				$text = chr(bindec($block));
				$ret	.= $text;
				$block='';
			}
		}
		return $ret;
	}
	
	public static function Text($plain){
		$division	= bindec("010");
		$lenght		= strlen($plain);
		$div		= $lenght/$division;
		$ret		='';
		$block		='';
		for($i=0;$i<$div;$i++){
			$val	= substr($plain,$i*$division,$division);
			if($val=='00'){
				continue;
			} else {
				$dec 	= hexdec($val);
				if(strlen($dec)==1){
					$bin='00'.$dec;
				}else if(strlen($dec)==2){
					$bin='0'.$dec;
				} else {
					$bin=$dec;
				}
				$ret .= $bin;
			}
		}
		return chr(bindec($ret));
	}
	
	public static function reassembly($byte){
		$text = '';
		for($i=0;$i<(strlen($byte)/6);$i++){
			$text .= $this->Text(substr($byte,$i*6,6));
		}
		return $text;
	}
	
	public static function rearrange($text){
		for($i=0;$i<(strlen($text)/2);$i++){
			$arr[$i] = substr($text,$i*2,2);
		}
		$result = implode(":",$arr);
		return $result;
	}

	public static function numtotxt($num) {
		$tdiv 	= array("","","ratus ","ribu ", "ratus ", "juta ", "ratus ","miliar ");
		$divs 	= array( 0,0,0,0,0,0,0);
		$pos 	= 0;
		$num 	= strval(strrev(number_format($num, 2, '.',''))); 
		$answer = "";
		while (strlen($num)) {
			if ( strlen($num) == 1 || ($pos >2 && $pos % 2 == 1))  {
				$answer = static::doone(substr($num, 0, 1)) . $answer;
				$num 	= substr($num,1);
			} else {
				$answer = static::dotwo(substr($num, 0, 2)) . $answer;
				$num 	= substr($num,2);
				if ($pos < 2)
					$pos++;
			}

			if (substr($num, 0, 1) == '.') {
				if (! strlen($answer)){
					$answer = "";
				}

				$answer = "" . $answer . "";
				$num 	= substr($num,1);
				if (strlen($num) == 1 && $num == '0') {
					$answer = "" . $answer;
					$num 	= substr($num,1);
				}
			}
		    if ($pos >= 2 && strlen($num)) {
				if (substr($num, 0, 1) != 0  || (strlen($num) >1 && substr($num,1,1) != 0
					&& $pos %2 == 1)  ) {
					if ( $pos == 4 || $pos == 6 ) {
						if ($divs[$pos -1] == 0)
							$answer = $tdiv[$pos -1 ] . $answer;
					}
					$divs[$pos] = 1;
					$answer 	= $tdiv[$pos++] . $answer;
				} else {
					$pos++;
				}
			}
	    }
	    return strtoupper($answer.'rupiah');
	}
	

	public static function doone2($onestr) {
	    $tsingle = array("","satu ","dua ","tiga ","empat ","lima ",
		"enam ","tujuh ","delapan ","sembilan ");
	      return strtoupper($tsingle[$onestr]);
	}	
	 
	public static function doone($onestr) {
	    $tsingle = array("","se","dua ","tiga ","empat ","lima ", "enam ","tujuh ","delapan ","sembilan ");
	      return strtoupper($tsingle[$onestr]);
	}	

	public static function dotwo($twostr) {
	    $tdouble = array("","puluh ","dua puluh ","tiga puluh ","empat puluh ","lima puluh ", "enam puluh ","tujuh puluh ","delapan puluh ","sembilan puluh ");
	    $teen = array("sepuluh ","sebelas ","dua belas ","tiga belas ","empat belas ","lima belas ", "enam belas ","tujuh belas ","delapan belas ","sembilan belas ");
	    if ( substr($twostr,1,1) == '0') {
			$ret = static::doone2(substr($twostr,0,1));
	    } else if (substr($twostr,1,1) == '1') {
			$ret = $teen[substr($twostr,0,1)];
	    } else {
			$ret = $tdouble[substr($twostr,1,1)] . static::doone2(substr($twostr,0,1));
	    }
	    return strtoupper($ret);
	}

	public static function nominal($a){
		if(isset($a)){
			if($a==''){$a=0;}
			return number_format($a);
		}
	}
}

?>