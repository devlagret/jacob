@section('scripts')
<script>

$(document).ready(function(){
    $('#open_modal_button').click(function(){
        $.ajax({
            type: "GET",
            url : "{{route('savings-account-srh.modal-savings-account')}}",
            success: function(msg){
                $('#kt_modal_savings_account').modal('show');
                $('#modal-body').html(msg);
            }
        });
    });
});

function searchDate(){
    var start_date  = document.getElementById("start_date").value;
    var end_date    = document.getElementById("end_date").value;

    $.ajax({
        type: "POST",
        url : "{{route('savings-account-srh.filter')}}",
        data : {
            'start_date'    : start_date, 
            'end_date'      : end_date,
            '_token'        : '{{csrf_token()}}'
        },
        success: function(msg){
            location.reload();
        }
    });
}
</script>
@endsection
<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Saldo Rata - Rata Harian</h3>
            <div class="card-toolbar">
            </div>
        </div>

        <div id="kt_savings_account_srh_view">
            <div class="card-body pt-6">
                <div class="row mb-6">
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No Rekening') }}</label>
                            <div class="col-lg-4 fv-row">
                                <input type="hidden" name="savings_account_id" class="form-control form-control-lg form-control-solid" placeholder="No Rekening" value="{{ old('savings_account_id', $acctsavingsaccount['savings_account_id'] ?? '') }}" autocomplete="off" readonly/>
                                <input type="text" name="savings_account_no" class="form-control form-control-lg form-control-solid" placeholder="No Rekening" value="{{ old('savings_account_no', $acctsavingsaccount['savings_account_no'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                            <div class="col-lg-4 fv-row">
                                <button type="button" id="open_modal_button" class="btn btn-primary">
                                    {{ __('Cari Rekening') }}
                                </button>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Nama') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('member_name', $acctsavingsaccount['member_name'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input type="text" name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" value="{{ old('member_address', $acctsavingsaccount['member_address'] ?? '') }}" autocomplete="off" readonly/>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Mulai') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="start_date" id="start_date" class="date form-control form-control-solid form-select-lg" placeholder="Pilih tanggal" value="{{ old('start_date', $sessiondata['start_date'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Akhir') }}</label>
                            <div class="col-lg-8 fv-row">
                                <input name="end_date" id="end_date" class="date form-control form-control-solid form-select-lg" placeholder="Pilih tanggal" value="{{ old('end_date', $sessiondata['end_date'] ?? '') }}"/>
                            </div>
                        </div>
                        <div class="row mb-6">
                            <div class="col-lg-4">
                            </div>
                            <div class="col-lg-8">
                                <a href="{{ route('savings-account-srh.reset-filter') }}" class="btn btn-danger me-2" id="kt_savings_account_srh_reset"  name="kt_savings_account_srh_reset" value="batal">
                                    <i class="bi bi-x fs-2"></i> {{__('Batal')}}
                                </a>
                                <a class="btn btn-success" id="kt_member_mutation_date" name="kt_member_mutation_date" value="cari" onclick="searchDate()">
                                    <i class="bi bi-search"></i> {{__('Cari')}}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <br>
                <br>
                <br>
                <div class="table-responsive">
                    <div class="row mb-6">
                        <table class="table table-rounded border gy-7 gs-7 show-border">
                            <thead>
                                <tr align="center">
                                    <th><b>No</b></th>
                                    <th><b>No. Rekening</b></th>
                                    <th><b>Nama</b></th>
                                    <th><b>Mutasi</b></th>
                                    <th><b>Tanggal</b></th>
                                    <th><b>Deskripsi</b></th>
                                    <th><b>Saldo Awal</b></th>
                                    <th><b>Mutasi Masuk</b></th>
                                    <th><b>Mutasi Keluar</b></th>
                                    <th><b>Saldo Akhir</b></th>
                                    <th><b>SRH</b></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $no = 1;
                                if(count($acctsavingsaccountdetail)>0){
                                ?> 
                                @foreach($acctsavingsaccountdetail as $key => $val)
                                    <tr>
                                        <td style="text-align: center">{{ $no }}</td>
                                        <td>{{ $val['savings_account_no'] }}</td>
                                        <td>{{ $val['member_name'] }}</td>
                                        <td>{{ $val['mutation_name'] }}</td>
                                        <td>{{ date('d-m-Y', strtotime($val['today_transaction_date'])) }}</td>
                                        <td>{{ $val['transaction_code'] }}</td>
                                        <td style="text-align: right">{{ number_format($val['opening_balance'], 2) }}</td>
                                        <td style="text-align: right">{{ number_format($val['mutation_in'], 2) }}</td>
                                        <td style="text-align: right">{{ number_format($val['mutation_out'], 2) }}</td>
                                        <td style="text-align: right">{{ number_format($val['last_balance'], 2) }}</td>
                                        <td style="text-align: right">{{ number_format($val['daily_average_balance'], 2) }}</td>
                                    </tr>
                                <?php $no++ ?>
                                @endforeach
                                <?php }else{?>
                                    <tr>
                                        <td colspan="11" style="text-align: center">Data Kosong</td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal_savings_account">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Daftar Rekening</h3>
    
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                </div>
    
                <div class="modal-body" id="modal-body">
                </div>
    
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-base-layout>