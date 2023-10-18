@section('scripts')
<script>
const form = document.getElementById('kt_journal_voucher_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'journal_voucher_date': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal harus diisi'
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

const submitButton = document.getElementById('kt_journal_voucher_add_submit');
submitButton.addEventListener('click', function (e) {
    e.preventDefault();

    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                submitButton.setAttribute('data-kt-indicator', 'on');

                submitButton.disabled = true;

                setTimeout(function () {
                    submitButton.removeAttribute('data-kt-indicator');

                    form.submit();
                }, 2000);
            }
        });
    }
});

$(document).ready(function(){
    $('#journal_voucher_amount_view').change(function(){
        var journal_voucher_amount = $('#journal_voucher_amount_view').val();

        $('#journal_voucher_amount_view').val(toRp(journal_voucher_amount));
        $('#journal_voucher_amount').val(journal_voucher_amount);
    }); 
});

function function_elements_add(name, value){
    $.ajax({
            type: "POST",
            url : "{{route('journal-voucher.elements-add')}}",
            data : {
                'name'      : name, 
                'value'     : value,
                '_token'    : '{{csrf_token()}}'
            },
            success: function(msg){
        }
    });
}

function addArray(){
    var account_id								= $("#account_id").val();
    var journal_voucher_amount					= $("#journal_voucher_amount").val();
    var journal_voucher_status					= $("#journal_voucher_status").val();
    var journal_voucher_description_item		= $("#journal_voucher_description").val();

    $.ajax({
            type: "POST",
            url : "{{route('journal-voucher.add-array')}}",
            data : {
                'account_id'                        : account_id, 
                'journal_voucher_amount'            : journal_voucher_amount,
                'journal_voucher_status'            : journal_voucher_status,
                'journal_voucher_description_item'  : journal_voucher_description_item,
                '_token'                            : '{{csrf_token()}}'
            },
            success: function(msg){
                location.reload();
        }
    });
}
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Jurnal Umum') }}</h3>
            </div>

            <a href="{{ theme()->getPageUrl('journal-voucher.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_savings_add_view">
            <form id="kt_journal_voucher_add_view_form" class="form" method="POST" action="{{ route('journal-voucher.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="journal_voucher_date" id="journal_voucher_date" class="date form-control form-control-lg form-control-solid" placeholder="Tanggal" value="{{ old('journal_voucher_date', empty($session['journal_voucher_date']) ? date('d-m-Y') : $session['journal_voucher_date'] ?? '') }}" autocomplete="off" onchange="function_elements_add(this.name, this.value)"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Keterangan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <textarea type="text" name="journal_voucher_description" id="journal_voucher_description" class="form-control form-control-lg form-control-solid" placeholder="Keterangan" autocomplete="off" onchange="function_elements_add(this.name, this.value)">{{ old('journal_voucher_description', $session['journal_voucher_description'] ?? '') }}</textarea>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Perkiraan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="account_id" id="account_id" aria-label="{{ __('Pilih No. Perkiraan') }}" data-control="select2" data-placeholder="{{ __('Pilih No. Perkiraan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($acctaccount as $key => $value)
                                <option data-kt-flag="{{ $value['account_id'] }}" value="{{ $value['account_id'] }}" {{ $value['account_id'] === old('account_id', '' ?? '') ? 'selected' :'' }}>{{ $value['account_code'] }} - {{ $value['account_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="journal_voucher_amount_view" id="journal_voucher_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Rp." value="{{ old('journal_voucher_amount_view', '' ?? '') }}" autocomplete="off"/>
                            <input type="hidden" name="journal_voucher_amount" id="journal_voucher_amount" class="form-control form-control-lg form-control-solid" placeholder="Rp." value="{{ old('journal_voucher_amount', '' ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('D/K') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="journal_voucher_status" id="journal_voucher_status" aria-label="{{ __('D/K') }}" data-control="select2" data-placeholder="{{ __('Pilih D/K') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($accountstatus as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('journal_voucher_status', '' ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary col-lg-12" onclick="addArray()">
                        Tambah
                    </button>
                    <div class="separator mt-15 mb-6"></div>
                    <div class="row mb-6">
                        <div class="table-responsive">
                            <table class="table table-rounded border gy-7 gs-7 show-border">
                                <thead>
                                    <tr align="center">
                                        <th width="25%"><b>No. Perkiraan</b></th>
                                        <th width="25%"><b>Nama Perkiraan</b></th>
                                        <th width="25%"><b>Debit</b></th>
                                        <th width="25%"><b>Kredit</b></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (empty($arrayses))
                                        <tr>
                                            <td colspan="4" style="text-align: center">Data Kosong</td>
                                        </tr>
                                    @else
                                        @foreach ($arrayses as $val)
                                            @php
                                                $accountCode = App\Http\Controllers\JournalVoucherController::getAccountCode($val['account_id']);
                                                $accountName = App\Http\Controllers\JournalVoucherController::getAccountName($val['account_id']);
                                            @endphp
                                            <tr>
                                                <td>{{ $accountCode }}</td>
                                                <td>{{ $accountName }}</td>
                                                @if ($val['journal_voucher_status'] == 0)
                                                    <td>{{ number_format($val['journal_voucher_amount'], 2) }}</td>
                                                    <td></td>
                                                @else
                                                    <td></td>
                                                    <td>{{ number_format($val['journal_voucher_amount'], 2) }}</td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <a href="{{ route('journal-voucher.reset-elements-add') }}" type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</a>
    
                    <button type="submit" class="btn btn-primary" id="kt_journal_voucher_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

