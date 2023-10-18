
@section('scripts')
<script>
const form = document.getElementById('kt_savings_account_blockir_add_view_form');

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
            'member_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Anggota harus diisi'
                    }
                }
            },
            'savings_name': {
                validators: {
                    notEmpty: {
                        message: 'Simpanan harus diisi'
                    }
                }
            },
            'member_address': {
                validators: {
                    notEmpty: {
                        message: 'Alamat harus diisi'
                    }
                }
            },
            'member_identity_no': {
                validators: {
                    notEmpty: {
                        message: 'No. Identitas harus diisi'
                    }
                }
            },
            'savings_account_last_balance': {
                validators: {
                    notEmpty: {
                        message: 'Saldo harus diisi'
                    }
                }
            },
            'savings_account_blockir_type': {
                validators: {
                    notEmpty: {
                        message: 'Sifat Saldo Blockir harus diisi'
                    }
                }
            },
            'savings_account_blockir_amount': {
                validators: {
                    notEmpty: {
                        message: 'Saldo Blockir harus diisi'
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

const submitButton = document.getElementById('kt_savings_account_blockir_add_submit');
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
    $('#buttonck').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('savings-account-blockir.modal-member')}}",
                success: function(msg){
                    $('#kt_modal_1').modal('show');
                    $('#modal-body').html(msg);
            }
    
        });
    });

    $('#kt_savings_account_blockir_add_reset').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('savings-account-blockir.reset-add')}}",
                success: function(msg){
                    location.reload();
            }
    
        });
    }); 

    $('#savings_account_blockir_amount_view').change(function(){
        var savings_account_blockir_amount = $('#savings_account_blockir_amount_view').val();

        function_elements_add('savings_account_blockir_amount', savings_account_blockir_amount);
        $('#savings_account_blockir_amount_view').val(toRp(savings_account_blockir_amount));
        $('#savings_account_blockir_amount').val(savings_account_blockir_amount);
    });
});

function function_elements_add(name, value){
		$.ajax({
				type: "POST",
				url : "{{route('savings-account-blockir.elements-add')}}",
				data : {
                    'name'      : name, 
                    'value'     : value,
                    '_token'    : '{{csrf_token()}}'
                },
				success: function(msg){
			}
		});
	}

</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Blockir Rekening') }}</h3>
            </div>

            <a href="{{ route('savings-account-blockir.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_add_view">
            <form id="kt_savings_account_blockir_add_view_form" class="form" method="POST" action="{{ route('savings-account-blockir.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('No. Rekening') }}</label>
                        <div class="col-lg-2 fv-row">
                            <input type="text" name="savings_account_no" class="form-control form-control-lg form-control-solid" placeholder="No. Rekening" value="{{ old('savings_account_no', $corememberses['savings_account_no'] ?? '') }}" autocomplete="off" readonly/>
                            <input type="hidden" name="member_id" class="form-control form-control-lg form-control-solid" placeholder="No. Rekening" value="{{ old('member_id', $corememberses['member_id'] ?? '') }}" autocomplete="off" readonly/>
                            <input type="hidden" name="savings_account_id" class="form-control form-control-lg form-control-solid" placeholder="No. Rekening" value="{{ old('savings_account_id', $corememberses['savings_account_id'] ?? '') }}" autocomplete="off" readonly/>
                        </div>
                        <div class="col-lg-2">
                            <button type="button" id="buttonck" class="btn btn-primary">
                                {{ __('Cari Anggota') }}
                            </button>
                        </div>
                        <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('Sifat Saldo Blockir') }}</label>
                        <div class="col-lg-4 fv-row">
                            <select name="savings_account_blockir_type" id="savings_account_blockir_type" aria-label="{{ __('Pilih') }}" data-control="select2" data-placeholder="{{ __('Pilih Sifat Saldo Blockir') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                <option value="">{{ __('Pilih') }}</option>
                                @foreach($blockirtype as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('savings_account_blockir_type', $datases['savings_account_blockir_type'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('Nama Anggota') }}</label>
                        <div class="col-lg-4 fv-row">
                            <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ old('member_name', $corememberses['member_name'] ?? '') }}" autocomplete="off" readonly/>
                        </div>
                        <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('Saldo Blockir') }}</label>
                        <div class="col-lg-4 fv-row">
                            <input type="text" name="savings_account_blockir_amount_view" id="savings_account_blockir_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Saldo Blockir" value="{{ old('savings_account_blockir_amount', empty($datases ['savings_account_blockir_amount']) ? '' : number_format($datases['savings_account_blockir_amount'], 2) ?? '') }}" autocomplete="off"/>
                            <input type="hidden" name="savings_account_blockir_amount" id="savings_account_blockir_amount" class="form-control form-control-lg form-control-solid" placeholder="Saldo Blockir" value="{{ old('savings_account_blockir_amount', $datases['savings_account_blockir_amount'] ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('Simpanan') }}</label>
                        <div class="col-lg-4 fv-row">
                            <input type="text" name="savings_name" class="form-control form-control-lg form-control-solid" placeholder="Simpanan" value="{{ old('savings_name', $corememberses['savings_name'] ?? '') }}" autocomplete="off" readonly/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('Alamat') }}</label>
                        <div class="col-lg-4 fv-row">
                            <input type="text" name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" value="{{ old('member_address', $corememberses['member_address'] ?? '') }}" autocomplete="off" readonly/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('No. Identitas') }}</label>
                        <div class="col-lg-4 fv-row">
                            <input type="text" name="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No. Identitas" value="{{ old('member_identity_no', $corememberses['member_identity_no'] ?? '') }}" autocomplete="off" readonly/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('Saldo') }}</label>
                        <div class="col-lg-4 fv-row">
                            <input type="text" name="savings_account_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo" value="{{ old('savings_account_last_balance', empty($corememberses['savings_account_last_balance']) ? '' : number_format($corememberses['savings_account_last_balance'],2) ?? '') }}" autocomplete="off" readonly/>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" id="kt_savings_account_blockir_add_reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_savings_account_blockir_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal_1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Daftar Rekening Anggota</h3>
    
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                    <!--end::Close-->
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

