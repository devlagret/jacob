@section('scripts')
<script>
const form = document.getElementById('kt_savings_account_monitor_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'savings_account_no': {
                validators: {
                    notEmpty: {
                        message: 'No. Rekening harus diisi'
                    }
                }
            },
        },
        
        plugins: {
            trigger: new FormValidation.plugins.Trigger(),
            bootstrap: new FormValidation.plugins.Bootstrap5({
                rowSelector: '.fv-row',
                eleInvalidClass: '',
                eleValidClass: ''
            })
        }
    }
);


$(document).ready(function(){
    $('#open_modal_button').click(function(){
        $.ajax({
            type: "GET",
            url : "{{route('savings-account-monitor.modal-savings-account')}}",
            success: function(msg){
                $('#kt_modal_savings_account').modal('show');
                $('#modal-body').html(msg);
            }
        });
    });
});

const submitButton = document.getElementById('kt_member_monitor_submit_syncronize');
submitButton.addEventListener('click', function (e) {
    e.preventDefault();

    $('#view').val('syncronize');

    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                submitButton.setAttribute('data-kt-indicator', 'on');

                submitButton.disabled = true;

                setTimeout(function () {
                    submitButton.removeAttribute('data-kt-indicator');

                    window.open("{{ route('savings-account-monitor.syncronize-data') }}",'_self')
                }, 2000);
            }
        });
    }
});

const submitButton2 = document.getElementById('kt_member_monitor_submit_preview');
submitButton2.addEventListener('click', function (e) {
    e.preventDefault();

    $('#view').val('preview');

    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                submitButton2.setAttribute('data-kt-indicator', 'on');

                submitButton2.disabled = true;

                setTimeout(function () {
                    submitButton2.removeAttribute('data-kt-indicator');

                    form.submit();
                }, 2000);
            }
        });
    }
});

function searchDate(){
    var start_date  = document.getElementById("start_date").value;
    var end_date    = document.getElementById("end_date").value;

    $.ajax({
        type: "POST",
        url : "{{route('savings-account-monitor.filter')}}",
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
            <h3 class="card-title">Cetak Monitor Tabungan</h3>
            <div class="card-toolbar">
            </div>
        </div>

        <div id="kt_savings_account_mutation_view">
            <form id="kt_savings_account_monitor_view_form" class="form" method="POST" action="{{ route('savings-account-monitor.print') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
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
                                    <a href="{{ route('savings-account-monitor.reset-filter') }}" class="btn btn-danger me-2" id="kt_member_mutation_reset"  name="kt_member_mutation_reset" value="batal">
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
                    <div class="row mb-6">
                        <div class="table-responsive">
                            <table class="table table-rounded border gy-7 gs-7 show-border">
                                <thead>
                                    <tr align="center">
                                        <th><b>No</b></th>
                                        <th><b>No. Rekening</b></th>
                                        <th><b>Jenis Tabungan</b></th>
                                        <th><b>Tgl Transaksi</b></th>
                                        <th><b>Sandi</b></th>
                                        <th><b>Debet</b></th>
                                        <th><b>Kredit</b></th>
                                        <th><b>Saldo</b></th>
                                        <th><b>Operator</b></th>
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
                                            <td>{{ $val['savings_name'] }}</td>
                                            <td>{{ date('d-m-Y', strtotime($val['today_transaction_date'])) }}</td>
                                            <td>{{ $val['mutation_code'] }}</td>
                                            <td style="text-align: right">{{ number_format($val['mutation_out'], 2) }}</td>
                                            <td style="text-align: right">{{ number_format($val['mutation_in'], 2) }}</td>
                                            <td style="text-align: right">{{ number_format($val['last_balance'], 2) }}</td>
                                            <td>{{ $val['operated_name'] }}</td>
                                        </tr>
                                    <?php $no++ ?>
                                    @endforeach
                                    <?php }else{?>
                                        <tr>
                                            <td colspan="9" style="text-align: center">Data Kosong</td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="view" id="view">
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="submit" class="btn btn-primary me-2" id="kt_member_monitor_submit_preview">
                        @include('partials.general._button-indicator', ['label' => __('Preview')])
                    </button>
                    <button type="submit" class="btn btn-danger" id="kt_member_monitor_submit_syncronize">
                        @include('partials.general._button-indicator', ['label' => __('Syncronize Data')])
                    </button>
                </div>
            </form>
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