@section('bladeScripts')
<script>
const form = document.getElementById('income-form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'income_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Pendapatan harus diisi'
                    }
                }
            },
            'income_group': {
                validators: {
                    notEmpty: {
                        message: 'Kelompok Pendapatan harus diisi'
                    }
                }
            },
            'account_id': {
                validators: {
                    notEmpty: {
                        message: 'No. Perkiraan harus diisi'
                    }
                }
            }
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

const submitButton = document.getElementById('kt_income_add_submit');
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

function function_elements_add(name, value){
    $.ajax({
            type: "POST",
            url : "{{route('pc.elements-add')}}",
            data : {'name':name,'value': value,'_token': '{{csrf_token()}}'
            },
            success: function(msg){}
    });
}

$(document).ready(function(){
    $('#income_percentage').change(function (e) { 
        if($(this).val()>100){
            $(this).val(100);
        }
        if($(this).val()<0){
            $(this).val(0);
        }
    });
});
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Konfigurasi Perusahaan') }}</h3>
            </div>
        </div>

        <div id="preference-company">
            <form id="income-form" class="form" method="POST" action="{{ route('pc.process-edit') }}" enctype="multipart/form-data">
            @csrf
                <div class="card-body border-top p-9">
                    @if (0)
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('Kode Simpanan Pokok') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('account_mutation_adm_id', $saving, ($data->principal_savings_id??''), ["name"=>"account[principal_savings_id]",'id'=>"account[principal_savings_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Bunga Pinjaman') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('account_interest_id', $acc, ($data->account_interest_id??''), ["name"=>"account[account_interest_id]",'id'=>"account[account_interest_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Bunga Deposito') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('deposito_profit_sharing_id', $acc, ($data->deposito_profit_sharing_id??''), ["name"=>"account[deposito_profit_sharing_id]",'id'=>"account[savings_profit_sharing_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Bunga Tabungan') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('savings_profit_sharing_id', $acc, ($data->savings_profit_sharing_id??''), ["name"=>"account[savings_profit_sharing_id]",'id'=>"account[savings_profit_sharing_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Denda Angsuran') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('account_credits_payment_fine', $acc, ($data->account_credits_payment_fine??''), ["name"=>"account[account_credits_payment_fine]",'id'=>"account[account_credits_payment_fine]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Penalty') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('account_penalty_id', $acc, ($data->account_penalty_id??''), ["name"=>"account[account_penalty_id]",'id'=>"account[account_penalty_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Biaya Notaris') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('account_notary_cost_id', $acc, ($data->account_notary_cost_id??''), ["name"=>"account[account_notary_cost_id]",'id'=>"account[account_notary_cost_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Biaya Asuransi') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('account_insurance_cost_id', $acc, ($data->account_insurance_cost_id??''), ["name"=>"account[account_insurance_cost_id]",'id'=>"account[account_insurance_cost_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Biaya Admin Mutasi') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('account_mutation_adm_id', $acc, ($data->account_mutation_adm_id??''), ["name"=>"account[account_mutation_adm_id]",'id'=>"account[account_mutation_adm_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="row mb-6">
                            <label class="col-lg-3 col-form-label fw-bold fs-6 required">{{ __('COA Pajak Tabungan') }}</label>
                            <div class="col-auto pt-3">:</div>
                            <div class="col-lg-8 fv-row">
                                {{ Form::select('account_savings_tax_id', $acc, ($data->account_savings_tax_id??''), ["name"=>"account[account_savings_tax_id]",'id'=>"account[account_savings_tax_id]","aria-label"=>"Kelompok","data-control"=>"select2", "data-placeholder"=>"Pilih Kelompok..", "data-allow-clear"=>"true",'class'=>"form-select form-select-solid form-select-lg"]) }}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>

                    <button type="submit" class="btn btn-primary" id="kt_income_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Tambah')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

