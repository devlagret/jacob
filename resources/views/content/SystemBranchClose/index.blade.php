<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Jurnal</h3>
            <div class="card-toolbar">
            </div>
        </div>
        <form id="kt_end_of_days_close_view_form" class="form" method="POST" action="{{ route('branch-close.process') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
            <div class="card-body pt-6">
                <div class="table-responsive">
                    <table class="table table-rounded border gy-7 gs-7">
                        <thead>
                            <tr>
                                <td><b>No</b></td>
                                <td><b>Bukti</b></td>
                                <td><b>Uraian</b></td>
                                <td><b>Tanggal</b></td>
                                <td><b>No.Perkiraan</b></td>
                                <td><b>Perkiraan</b></td>
                                <td><b>Nominal</b></td>
                                <td><b>D/K</b></td>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $no = 1;
                                $totaldebet = 0;
                                $totalkredit = 0;
                                if(empty($journal)){
                                    echo "
                                        <tr>
                                            <td colspan='8' align='center'>Data Kosong</td>
                                        </tr>
                                    ";
                                } else {
                                    $id = 0;
                                    foreach ($journal as $key=>$val){

                                        if($val['journal_voucher_debit_amount'] <> 0 ){
                                            $nominal = $val['journal_voucher_debit_amount'];
                                            $status = "D";
                                        } else if($val['journal_voucher_credit_amount'] <> 0){
                                            $nominal = $val['journal_voucher_credit_amount'];
                                            $status = "K";
                                        }

                                        if($val['journal_voucher_id'] != $id){
                                            echo"
                                                <tr>			
                                                    <td style='text-align:left; background-color:lightgrey'>$no.</td>
                                                    <td style='text-align:left; background-color:lightgrey'>".$val['transaction_module_code']."</td>
                                                    <td style='text-align:left; background-color:lightgrey'>".$val['journal_voucher_description']."</td>
                                                    <td style='text-align:left; background-color:lightgrey'>".$val['journal_voucher_date']."</td>
                                                    <td style='text-align:left; background-color:lightgrey'>".$val['account_code']."</td>
                                                    <td style='text-align:left; background-color:lightgrey'>".$val['account_name']."</td>
                                                    <td style='text-align:right; background-color:lightgrey'>".number_format($nominal, 2 )."</td>
                                                    <td style='text-align:right; background-color:lightgrey'>".$status."</td>
                                                </tr>
                                            ";
                                            $no++;
                                        } else {
                                            echo"
                                                <tr>			
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td></td>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$val['account_code']."</td>
                                                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;".$val['account_name']."</td>
                                                    <td style='text-align:right;'>".number_format($nominal, 2 )."</td>
                                                    <td style='text-align:right;'>".$status."</td>
                                                </tr>
                                            ";
                                        }									
                                        
                                        $totaldebet     += $val['journal_voucher_debit_amount'];
                                        $totalkredit    += $val['journal_voucher_credit_amount'];	
                                        
                                        if($id != $val['journal_voucher_id']){
                                            $id = $val['journal_voucher_id'];
                                        }
                                    } 
                                }
                            ?>
                            <tr>
                                <td colspan="8"></td>
                            </tr>
                            <tr>
                                <td colspan="6" rowspan="2">
                                    <?php if(round($totaldebet) != round($totalkredit)){?>
                                        <div class="alert alert-danger alert-dismissable">                 
                                            Total Debet dan Kredit masih belum seimbang !
                                        </div>
                                    <?php } ?>
                                    </td>
                                <td align="right"><b>Total Debet</td>
                                <td align="right"><b><?php echo number_format($totaldebet, 2); ?></td>
                            </tr>
                            <tr>
                                <td align="right"><b>Total Kredit</td>
                                <td align="right"><b><?php echo number_format($totalkredit, 2); ?></b></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <input type="hidden" name="end_of_days_id" id="end_of_days_id"  value="{{ $endofdays->end_of_days_id }}"/>
                <input type="hidden" name="debit_amount" id="debit_amount"  value="{{ $totaldebet }}"/>
                <input type="hidden" name="credit_amount" id="credit_amount"  value="{{ $totalkredit }}"/>
                <?php if(round($totaldebet) == round($totalkredit) && $endofdays['end_of_days_status'] == '1'){?>
                <button type="submit" class="btn btn-primary" id="kt_close_branch_submit">
                    {{ __('Tutup Cabang')}}
                </button>
                <?php }else{?>
                <button class="btn btn-primary disabled" id="kt_close_branch_submit" disabled>
                    {{ __('Tutup Cabang')}}
                </button>
                <?php }?>
            </div>
        </form>
    </div>
</x-base-layout>