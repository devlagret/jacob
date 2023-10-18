
@section('scripts')
<script>
const form = document.getElementById('kt_member_savings_transfer_mutation_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'member_no': {
                validators: {
                    notEmpty: {
                        message: 'No. Anggota harus diisi'
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
            'member_mandatory_savings_view': {
                validators: {
                    notEmpty: {
                        message: 'Jumlah Simpanan Wajib harus diisi'
                    }
                }
            },
            'savings_account_no': {
                validators: {
                    notEmpty: {
                        message: 'No. Rekening harus diisi'
                    }
                }
            },
            'savings_account_from_no': {
                validators: {
                    notEmpty: {
                        message: 'Nama harus diisi'
                    }
                }
            },
            'member_transfer_mutation_date': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal Transfer harus diisi'
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

const submitButton = document.getElementById('kt_member_savings_transfer_mutation_add_submit');
submitButton.addEventListener('click', function (e) {
    e.preventDefault();

    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                submitButton.setAttribute('data-kt-indicator', 'on');

                submitButton.disabled = true;

                setTimeout(function () {
                    submitButton.removeAttribute('data-kt-indicator');

                    form.submit(); // Submit form
                }, 2000);
            }
        });
    }
});

$(document).ready(function(){
    $('#member_mandatory_savings_view').change(function(){
        var member_mandatory_savings = $('#member_mandatory_savings_view').val();

        function_elements_add('member_mandatory_savings', member_mandatory_savings);
        $('#member_mandatory_savings_view').val(toRp(member_mandatory_savings));
        $('#member_mandatory_savings').val(member_mandatory_savings);
    });
    $('#button_modal').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('member-savings-transfer-mutation.modal-member')}}",
                success: function(msg){
                    $('#kt_modal').modal('show');
                    $('.modal-title').html("<h3 class='modal-title'>Daftar Anggota</h3>");
                    $('#modal-body').html(msg);
            }
    
        });
    });
    $('#button_modal_savings').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('member-savings-transfer-mutation.modal-savings-account')}}",
                success: function(msg){
                    $('#kt_modal').modal('show');
                    $('.modal-title').html("<h3 class='modal-title'>Daftar Simpanan</h3>");
                    $('#modal-body').html(msg);
            }
    
        });
    });

    $('#kt_member_savings_transfer_mutation_add_reset').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('member-savings-transfer-mutation.reset-elements-add')}}",
                success: function(msg){
                    location.reload();
            }

        });
    }); 

});

function function_elements_add(name, value){
    $.ajax({
            type: "POST",
            url : "{{route('member-savings-transfer-mutation.elements-add')}}",
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
                <h3 class="fw-bolder m-0">{{ __('Form Tambah') }}</h3>
            </div>

            <a href="{{ route('member-savings-transfer-mutation.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_member_savings_transfer_mutation_add_view_form" class="form" method="POST" action="{{ route('member-savings-transfer-mutation.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Tanggal Transfer') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_transfer_mutation_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Transfer" value="{{ date('d-m-Y') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Data Anggota</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Anggota') }}</label>
                                <div class="col-lg-4 fv-row">
                                    <input type="text" name="member_no" class="form-control form-control-lg form-control-solid" placeholder="No. Anggota" value="{{ old('member_no', $memberses['member_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-4">
                                    <button type="button" id="button_modal" class="btn btn-primary">
                                        {{ __('Cari Anggota') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ old('member_name', $memberses['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <textarea  name="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" autocomplete="off" readonly>{{ old('member_address', $memberses['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kabupaten') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="city_name" class="form-control form-control-lg form-control-solid" placeholder="Kabupaten" autocomplete="off" value="{{ old('city_name', $memberses['city_name'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Kecamatan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input  name="kecamatan_name" class="form-control form-control-lg form-control-solid" placeholder="Kecamatan" autocomplete="off" value="{{ old('kecamatan_name', $memberses['kecamatan_name'] ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo Simpanan Wajib') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mandatory_savings_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simpanan Wajib" autocomplete="off" value="{{ old('member_mandatory_savings_last_balance', empty($memberses['member_mandatory_savings_last_balance']) ? '' : number_format($memberses['member_mandatory_savings_last_balance'],2) ?? '') }}" readonly>
                                    <input type="hidden" name="member_mandatory_savings_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simpanan Wajib" autocomplete="off" value="{{ old('member_mandatory_savings_last_balance', $memberses['member_mandatory_savings_last_balance'] ?? '') }}" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Data Simpanan</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('No. Rekening') }}</label>
                                <div class="col-lg-4 fv-row">
                                    <input type="text" name="savings_account_no" class="form-control form-control-lg form-control-solid" placeholder="No. Rekening" value="{{ old('savings_account_no', $savingsaccount['savings_account_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                                <div class="col-lg-4">
                                    <button type="button" id="button_modal_savings" class="btn btn-primary">
                                        {{ __('Cari Rekening') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Simpanan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_name" class="form-control form-control-lg form-control-solid" placeholder="Simpanan" value="{{ old('savings_name', $savingsaccount['savings_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_account_from_no" class="form-control form-control-lg form-control-solid" placeholder="Nama" value="{{ old('savings_account_from_no', $savingsaccount['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Saldo Simp.') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="savings_account_last_balance_view" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp." value="{{ old('savings_account_last_balance', empty($savingsaccount['savings_account_last_balance']) ? '' : number_format($savingsaccount['savings_account_last_balance'],2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="savings_account_last_balance" class="form-control form-control-lg form-control-solid" placeholder="Saldo Simp." value="{{ old('savings_account_last_balance', $savingsaccount['savings_account_last_balance'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Sandi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="hidden" name="mutation_id" class="form-control form-control-lg form-control-solid" placeholder="Sandi" value="{{ old('mutation_id', $acctmutation['mutation_id'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="text" name="mutation_name" class="form-control form-control-lg form-control-solid" placeholder="Sandi" value="{{ old('mutation_name', $acctmutation['mutation_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jumlah Simpanan Wajib') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_mandatory_savings_view" id="member_mandatory_savings_view" class="form-control form-control-lg form-control-solid" placeholder="Rp." value="{{ old('member_mandatory_savings', empty($datases['member_mandatory_savings']) ? '' : number_format($datases['member_mandatory_savings'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="member_mandatory_savings" id="member_mandatory_savings" class="form-control form-control-lg form-control-solid" placeholder="Rp." value="{{ old('member_mandatory_savings', $datases['member_mandatory_savings'] ?? '') }}" autocomplete="off"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" id="kt_member_savings_transfer_mutation_add_reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_member_savings_transfer_mutation_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" tabindex="-1" id="kt_modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title"></h3>
    
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