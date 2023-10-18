<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Jumlah Total Kemarin</h3>
            <div class="card-toolbar">
            </div>
        </div>
        <div class="card-body pt-6">
            <div class="table-responsive">
                <table class="table table-rounded border gy-7 gs-7">
                    <thead>
                    </thead>
                    <tbody>
                        <?php if($endofdays['end_of_days_status'] == '0'){?>
                        <tr>
                            <td width="30%"><b>Total Debit</b></td>
                            <td class="table-active"><b>{{ number_format($endofdays['debit_amount'], 2) }}</b></td>
                        </tr>
                        <tr>
                            <td width="30%"><b>Total Kredit</b></td>
                            <td class="table-active"><b>{{ number_format($endofdays['credit_amount'], 2) }}</b></td>
                        </tr>
                        <?php }else{?>
                            <tr class="table-danger">
                                <td class="text-danger"><b>Anda belum menutup cabang !</b></td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end py-6 px-9">
            <?php if($endofdays['end_of_days_status'] == '0'){?>
            <a href="{{ route('branch-open.process') }}" class="btn btn-primary" id="kt_open_branch_submit">
                {{ __('Buka Cabang')}}
            </a>
            <?php }else{?>
            <a href="" class="btn btn-primary disabled" id="kt_open_branch_submit" disabled>
                {{ __('Buka Cabang')}}
            </a>
            <?php }?>
        </div>
    </div>
</x-base-layout>