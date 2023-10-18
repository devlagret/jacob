
@php
    use App\Helpers\Configuration;

    $membergender = Configuration::MemberGender();
    $paymentperiod = Configuration::CreditsPaymentPeriod();
    $paymenttype = Configuration::PaymentType();
    $paymentpreference = Configuration::PaymentPreference();
@endphp

@section('scripts')
<script>
const form = document.getElementById('kt_credits_account_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'member_no': {
                validators: {
                    notEmpty: {
                        message: 'Nomor Anggota harus diisi'
                    }
                }
            },
            'credits_id': {
                validators: {
                    notEmpty: {
                        message: 'Jenis Pinjaman harus diisi'
                    }
                }
            },
            'payment_period': {
                validators: {
                    notEmpty: {
                        message: 'Angsuran Tiap harus diisi'
                    }
                }
            },
            'sumberdana': {
                validators: {
                    notEmpty: {
                        message: 'Sumber Dana harus diisi'
                    }
                }
            },
            'payment_type_id': {
                validators: {
                    notEmpty: {
                        message: 'Jenis Angsuran harus diisi'
                    }
                }
            },
            'credit_account_period': {
                validators: {
                    notEmpty: {
                        message: 'Jangka Waktu harus diisi'
                    }
                }
            },
            'office_id': {
                validators: {
                    notEmpty: {
                        message: 'Business Office (BO) harus diisi'
                    }
                }
            },
            'credit_account_interest': {
                validators: {
                    notEmpty: {
                        message: 'Bunga harus diisi'
                    }
                }
            },
            'credits_account_last_balance_principal_view': {
                validators: {
                    notEmpty: {
                        message: 'Plafon Pinjaman diisi'
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

const submitButton = document.getElementById('kt_credits_account_add_submit');
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
    // var loopkomisi = 1;
	// var loopadm = 1;
	// var loopnot = 1;
	// var loopins = 1;
	// var loop_principal = 1;
	// var loop_margin = 1;
	// var loop_payment = 1;
    var payment_type_id = $('#payment_type_id').val();

    if (payment_type_id == 1) {
        $('#credit_account_payment_amount_view').prop('readonly', false);
    } else if (payment_type_id == 2) {
        $('#credit_account_payment_amount_view').prop('readonly', false);
    } else if (payment_type_id == 3) {
        $('#credit_account_payment_amount_view').prop('readonly', true);
    } else if (payment_type_id == 4) {
        $('#credit_account_payment_amount_view').prop('readonly', true);
    }

    $('#kt_credits_account_add_reset').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('credits-account.reset-elements-add')}}",
                success: function(msg){
                    location.reload();
            }

        });
    }); 

    $('#button_modal_member').click(function(){
        $.ajax({
                type: "GET",
                url : "{{route('credits-account.modal-member')}}",
                success: function(msg){
                    $('#kt_modal').modal('show');
                    $('.modal-title').html("Daftar Anggota");
                    $('#modal-body').html(msg);
            }
    
        });
    });

    $('#button_modal_angunan').click(function(){
        $('#kt_modal_angunan').modal('show');
    });

    $('#credit_account_date').change(function(){
        var credit_account_date = $('#credit_account_date').val();

        function_elements_add('credit_account_date', credit_account_date);
        duedatecalc();
    });

    $('#credit_account_sales_name').change(function(){
        var credit_account_sales_name = $('#credit_account_sales_name').val();

        function_elements_add('credit_account_sales_name', credit_account_sales_name);
        duedatecalc();
    });

    $('#credit_account_period').change(function(){
        var credit_account_period   = $('#credit_account_period').val();
        var credits_id              = $("#credits_id").val();
        var payment_type_id         = $("#payment_type_id").val();

        if (payment_type_id == 1) {
            angsuranflat();
        } else if (payment_type_id == 2) {
            angsurananuitas();
        } else if (payment_type_id == 3) {
            angsuranflat();
        } else if (payment_type_id == 4) {
            angsuranflat();
        }

        function_elements_add('credit_account_period', credit_account_period);
        duedatecalc();
    });

    $('#credits_account_last_balance_principal_view').change(function(){
        var credits_account_last_balance_principal  = $('#credits_account_last_balance_principal_view').val();
        var payment_type_id                         = $("#payment_type_id").val();

        // if (loop_principal == 0) {
        //     loop_principal = 1;
        //     return;
        // }
        // if (loop_principal == 1) {
        //     loop_principal = 0;
            $('#credits_account_last_balance_principal').val(credits_account_last_balance_principal);
            $('#credits_account_last_balance_principal_view').val(toRp(credits_account_last_balance_principal));

            function_elements_add('credits_account_last_balance_principal', credits_account_last_balance_principal);

            if (payment_type_id == 1) {
                angsuranflat();
            } else if (payment_type_id == 2) {
                angsurananuitas();
            } else if (payment_type_id == 3) {
                angsuranflat();
            } else if (payment_type_id == 4) {
                angsuranflat();
            }

            receivedamount();
        // } else {
        //     loop_principal = 1;
        //     return;
        // }
    });

    $('#credit_account_payment_amount_view').change(function(){
        var credit_account_payment_amount   = $('#credit_account_payment_amount_view').val();
        var payment_type_id                 = $("#payment_type_id").val();
        var interest                        = $("#credits_account_interest_amount").val();

        // if (loop_payment == 0) {
        //     loop_payment = 1;
        //     return;
        // }

        // if (loop_payment == 1) {
        //     loop_payment = 0;
            $('#credit_account_payment_amount').val(credit_account_payment_amount);
            $('#credit_account_payment_amount_view').val(toRp(credit_account_payment_amount));

            function_elements_add('credit_account_payment_amount', credit_account_payment_amount);

            if (payment_type_id == 1 || payment_type_id == 3 || payment_type_id == 4) {
                if (interest > 0 || interest != '') {
                    hitungbungaflat();
                }
            } else if (payment_type_id == 2) {
                if (interest > 0 || interest != '') {
                    hitungbungaflatanuitas();
                }
            }
        // } else {
        //     loop_payment = 1;
        //     return;
        // }
    });

    $('#credit_account_interest').change(function(){
        var credit_account_interest = $('#credit_account_interest').val();
        var payment_type_id         = $("#payment_type_id").val();

        // if (loop_margin == 0) {
        //     loop_margin = 1;
        //     return;
        // }

        // if (loop_margin == 1) {
        //     loop_margin = 0;
            $('#credit_account_interest').val(credit_account_interest);

            function_elements_add('credit_account_interest', credit_account_interest);

            if (payment_type_id == 1) {
                angsuranflat();
            } else if (payment_type_id == 2) {
                angsurananuitas();
            } else if (payment_type_id == 3) {
                angsuranflat();
            } else if (payment_type_id == 4) {
                angsuranflat();
            }

        // } else {
        //     loop_margin = 1;
        //     return;
        // }
    });

    $('#credit_account_provisi_view').change(function(){
        var credit_account_provisi = $('#credit_account_provisi_view').val();

        // if (loopkomisi == 0) {
        //     loopkomisi = 1;
        //     return;
        // }
        // if (loopkomisi == 1) {
        //     loopkomisi = 0;
            $('#credit_account_provisi').val(credit_account_provisi);
            $('#credit_account_provisi_view').val(toRp(credit_account_provisi));

            function_elements_add('credit_account_provisi', credit_account_provisi);
            receivedamount();
        // } else {
        //     loopkomisi = 1;
        //     return;
        // }
    });

    $('#credit_account_adm_cost_view').change(function(){
        var credit_account_adm_cost = $('#credit_account_adm_cost_view').val();

        // if (loopadm == 0) {
        //     loopadm = 1;
        //     return;
        // }
        // if (loopadm == 1) {
        //     loopadm = 0;
            $('#credit_account_adm_cost').val(credit_account_adm_cost);
            $('#credit_account_adm_cost_view').val(toRp(credit_account_adm_cost));

            function_elements_add('credit_account_adm_cost', credit_account_adm_cost);
            receivedamount();
        // } else {
        //     loopadm = 1;
        //     return;
        // }
    });

    $('#credit_account_insurance_view').change(function(){
        var credit_account_insurance = $('#credit_account_insurance_view').val();

        // if (loopins == 0) {
        //     loopins = 1;
        //     return;
        // }
        // if (loopins == 1) {
        //     loopins = 0;
            $('#credit_account_insurance').val(credit_account_insurance);
            $('#credit_account_insurance_view').val(toRp(credit_account_insurance));

            function_elements_add('credit_account_insurance', credit_account_insurance);
            receivedamount();
        // } else {
        //     loopins = 1;
        //     return;
        // }
    });

    $('#credit_account_notary_view').change(function(){
        var credit_account_notary = $('#credit_account_notary_view').val();

        // if (loopins == 0) {
        //     loopins = 1;
        //     return;
        // }
        // if (loopins == 1) {
        //     loopins = 0;
            $('#credit_account_notary').val(credit_account_notary);
            $('#credit_account_notary_view').val(toRp(credit_account_notary));

            function_elements_add('credit_account_notary', credit_account_notary);
            receivedamount();
        // } else {
        //     loopins = 1;
        //     return;
        // }
    });

});

function function_elements_add(name, value){
    $.ajax({
            type: "POST",
            url : "{{route('credits-account.elements-add')}}",
            data : {
                'name'      : name, 
                'value'     : value,
                '_token'    : '{{csrf_token()}}'
            },
            success: function(msg){
        }
    });
}

$('#bpkb_taksiran_view').change(function(){
    var bpkb_taksiran = $('#bpkb_taksiran_view').val();

    $('#bpkb_taksiran_view').val(toRp(bpkb_taksiran));
    $('#bpkb_taksiran').val(bpkb_taksiran);
});

$('#bpkb_gross_view').change(function(){
    var bpkb_gross = $('#bpkb_gross_view').val();

    $('#bpkb_gross_view').val(toRp(bpkb_gross));
    $('#bpkb_gross').val(bpkb_gross);
});

$('#shm_taksiran_view').change(function(){
    var shm_taksiran = $('#shm_taksiran_view').val();

    $('#shm_taksiran_view').val(toRp(shm_taksiran));
    $('#shm_taksiran').val(shm_taksiran);
});

$('#atmjamsostek_taksiran_view').change(function(){
    var atmjamsostek_taksiran = $('#atmjamsostek_taksiran_view').val();

    $('#atmjamsostek_taksiran_view').val(toRp(atmjamsostek_taksiran));
    $('#atmjamsostek_taksiran').val(atmjamsostek_taksiran);
});

function angsuranflat() {
    var bunga       = $("#credit_account_interest").val();
    var jangka      = $("#credit_account_period").val();
    var pembiayaan  = $("#credits_account_last_balance_principal").val();
    var persbunga   = parseInt(bunga) / 100;

    if (pembiayaan == '') {
        var totalangsuran   = 0;
        var angsuranpokok   = 0;
        var angsuranbunga2  = 0;
    } else {
        var angsuranpokok   = Math.ceil(pembiayaan / jangka);
        var angsuranbunga   = Math.floor((pembiayaan * bunga) / 100);
        var totalangsuran   = angsuranpokok + angsuranbunga;
        var angsuranbunga2  = totalangsuran - angsuranpokok;
    }

    $('#credit_account_payment_amount').val(totalangsuran);
    $('#credits_account_principal_amount').val(angsuranpokok);
    $('#credits_account_interest_amount').val(angsuranbunga2);
    $('#credit_account_payment_amount_view').val(toRp(totalangsuran));
    $('#credits_account_principal_amount_view').val(toRp(angsuranpokok));
    $('#credits_account_interest_amount_view').val(toRp(angsuranbunga2));

    var ntotalangsuran = 'credit_account_payment_amount';
    var nangsuranpokok = 'credits_account_principal_amount';
    var nangsuranbunga = 'credits_account_interest_amount';

    function_elements_add(ntotalangsuran, totalangsuran);
    function_elements_add(nangsuranpokok, angsuranpokok);
    function_elements_add(nangsuranbunga, angsuranbunga2);
}

function angsurananuitas() {
    var bunga       = $("#credit_account_interest").val();
    var jangka      = $("#credit_account_period").val();
    var pembiayaan  = $("#credits_account_last_balance_principal").val();
    var persbunga   = bunga / 100;

    if (pembiayaan == '') {
        var totalangsuran   = 0;
        var angsuranpokok   = 0;
        var angsuranbunga2  = 0;
    } else {
        if (bunga == 0) {
            var totalangsuran   = 0;
            var angsuranpokok   = 0;
            var angsuranbunga2  = 0;
        } else {
            var bungaA          = Math.pow((1 + parseInt(persbunga)), jangka);
            var bungaB          = Math.pow((1 + parseInt(persbunga)), jangka) - 1;
            var bungaC          = bungaA / bungaB;
            var totalangsuran   = pembiayaan * persbunga * bungaC;
            var angsuranbunga2  = (pembiayaan * bunga) / 100;
            var angsuranpokok   = totalangsuran - angsuranbunga2;
            var totangsuran     = Math.round((pembiayaan * (persbunga)) + pembiayaan / jangka);

            $.ajax({
                type: "POST",
                url: "{{ route('credits-account.rate4') }}",
                data: {
                    'nprest': jangka,
                    'vlrparc': totangsuran,
                    'vp': pembiayaan
                },
                success: function(rate) {
                    var angsuranbunga2  = pembiayaan * rate;
                    var angsuranpokok   = totangsuran - angsuranbunga2;
                    var totalangsuran   = angsuranbunga2 + angsuranpokok;

                    $('#credit_account_payment_amount').val(totalangsuran);
                    $('#credits_account_principal_amount').val(angsuranpokok);
                    $('#credits_account_interest_amount').val(angsuranbunga2);
                    $('#credit_account_payment_amount_view').val(toRp(totalangsuran));
                    $('#credits_account_principal_amount_view').val(toRp(angsuranpokok));
                    $('#credits_account_interest_amount_view').val(toRp(angsuranbunga2));
                }
            });
        }
    }

    var ntotalangsuran = 'credit_account_payment_amount';
    var nangsuranpokok = 'credits_account_principal_amount';
    var nangsuranbunga = 'credits_account_interest_amount';

    function_elements_add(ntotalangsuran, totalangsuran);
    function_elements_add(nangsuranpokok, angsuranpokok);
    function_elements_add(nangsuranbunga, angsuranbunga2);
}

function duedatecalc(data) {
    var angsuran    = $("#payment_period").val();
    var date2       = $("#credit_account_date").val();
    var day2        = date2.substring(0, 2);
    var month2      = date2.substring(3, 5);
    var year2       = date2.substring(6, 10);
    var date        = year2 + '-' + month2 + '-' + day2;
    var date1       = new Date(date);
    var period      = $("#credit_account_period").val();

    if (angsuran == 1) {
        var a           = moment(date1);
        var b           = a.add(period, 'month');
        var tmp         = date1.setMonth(date1.getMonth() + period);
        var endDate     = new Date(tmp);
        var name        = 'credit_account_due_date';
        var value       = b.format('DD-MM-YYYY');
        var testDate    = new Date(date);
        var tmp2        = testDate.setMonth(testDate.getMonth() + 1);
        var date_first  = testDate.toISOString();
        var day2        = date_first.substring(8, 10);
        var month2      = date_first.substring(5, 7);
        var year2       = date_first.substring(0, 4);
        var first       = day2 + '-' + month2 + '-' + year2;
        var name2       = 'credit_account_payment_to';
        var value2      = first;

        $('#credit_account_due_date').val(b.format('DD-MM-YYYY'));
        $('#credit_account_payment_to').val(first);
        function_elements_add(name, value);
        function_elements_add(name2, value2);
    } else {
        var week        = period * 7;
        var testDate    = new Date(date1);
        var tmp         = testDate.setDate(testDate.getDate() + week);
        var date_tmp    = testDate.toISOString();
        var day         = date_tmp.substring(8, 10);
        var month       = date_tmp.substring(5, 7);
        var year        = date_tmp.substring(0, 4);
        var name        = 'credit_account_due_date';
        var value       = day + '-' + month + '-' + year;
        var testDate2   = new Date(date1);
        var tmp2        = testDate2.setDate(testDate2.getDate() + 7);
        var date_first  = testDate2.toISOString();
        var day2        = date_first.substring(8, 10);
        var month2      = date_first.substring(5, 7);
        var year2       = date_first.substring(0, 4);
        var first       = day2 + '-' + month2 + '-' + year2;
        var name2       = 'credit_account_payment_to';
        var value2      = first;

        $('#credit_account_due_date').val(value);
        $('#credit_account_payment_to').val(first);
        function_elements_add(name, value);
        function_elements_add(name2, value2);
    }
}

function receivedamount() {
    var pinjaman        = $("#credits_account_last_balance_principal").val();
    var by_admin        = $("#credit_account_adm_cost").val();
    var by_provisi      = $("#credit_account_provisi").val();
    var by_notary      = $("#credit_account_notary").val();
    var by_insurance    = $("#credit_account_insurance").val();
    
    if (by_admin == '') {
        by_admin = 0;
    }

    if (by_provisi == '') {
        by_provisi = 0;
    }

    if (by_notary == '') {
        by_notary = 0;
    }

    if (by_insurance == '') {
        by_insurance = 0;
    }

 

    var terima_bersih = parseInt(pinjaman) - (parseInt(by_admin) + parseInt(by_provisi) + parseInt(by_notary) + parseInt(by_insurance) );

    $('#credit_account_amount_received').val(terima_bersih);
    $('#credit_account_amount_received_view').val(toRp(terima_bersih));

    var name = 'credit_account_amount_received';

    function_elements_add(name, terima_bersih);
}

function hitungbungaflat() {
    var jumlah_angsuran = $("#credit_account_payment_amount").val();
    var angsuranpokok   = $("#credits_account_principal_amount").val();
    var pinjaman        = $("#credits_account_last_balance_principal").val();
    var period          = $("#credit_account_period").val();
    var interest        = $("#credits_account_interest_amount").val();
    var angsuranbunga   = parseInt(jumlah_angsuran) - parseInt(angsuranpokok);
    var bunga           = (parseInt(angsuranbunga) * 12) / parseInt(pinjaman);
    var bunga_perbulan  = (parseInt(bunga) * 100) / 12;
    var bungafix        = bunga_perbulan.toFixed(3);

    $('#credit_account_interest').val(bungafix);
    $('#credits_account_interest_amount').val(angsuranbunga);
    $('#credits_account_interest_amount_view').val(toRp(angsuranbunga));

    var name    = 'credit_account_interest';
    var name3   = 'credits_account_interest_amount';

    function_elements_add(name, bungafix);
    function_elements_add(name3, angsuranbunga);
}

function hitungbungaflatanuitas() {
    var jumlah_angsuran = $("#credit_account_payment_amount").val();
    var angsuranpokok   = $("#credits_account_principal_amount").val();
    var pinjaman        = $("#credits_account_last_balance_principal").val();
    var period          = $("#credit_account_period").val();
    var interest        = $("#credits_account_interest_amount").val();
    var angsuranpokok   = pinjaman / period;
    var angsuranbunga   = parseInt(jumlah_angsuran) - parseInt(angsuranpok());
    var bunga           = (parseInt(angsuranbunga) * 12) / parseInt(pinjaman);
    var bunga_perbulan  = (parseInt(bunga) * 100) / 12;
    var bungafix        = bunga_perbulan.toFixed(3);

    $('#credit_account_interest').val(bungafix);
    $('#credits_account_interest_amount').val(angsuranbunga);
    $('#credits_account_interest_amount_view').val(toRp(angsuranbunga));

    var name = 'credit_account_interest';
    var name3 = 'credits_account_interest_amount';

    function_elements_add(name, bungafix);
    function_elements_add(name3, angsuranbunga);
}

function change_payment_type_id(value) {

    if (value == 1) {
        $('#credit_account_payment_amount_view').prop('readonly', false);
    } else if (value == 2) {
        $('#credit_account_payment_amount_view').prop('readonly', false);
    } else if (value == 3) {
        $('#credit_account_payment_amount_view').prop('readonly', true);
    } else if (value == 4) {
        $('#credit_account_payment_amount_view').prop('readonly', true);
    }
    
    function_elements_add('payment_type_id', value);
}
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah') }}</h3>
            </div>

            <a href="{{ route('credits-account.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_credits_account_add_view_form" class="form" method="POST" action="{{ route('credits-account.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Data Anggota</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6 required">{{ __('No. Anggota') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="member_no" id="member_no" class="form-control form-control-lg form-control-solid" placeholder="No. Anggota" value="{{ old('member_no', $coremember['member_no'] ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="member_id" id="member_id" class="form-control form-control-lg form-control-solid" value="{{ old('member_id', $coremember['member_id'] ?? '') }}"/>
                                </div>
                                <div class="col-lg-2 fv-row">
                                    <a type="button" id="button_modal_member" class="btn btn-sm btn-primary btn-active-light-primary m-1">
                                        Cari Anggota
                                    </a>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Nama Anggota') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="member_name" id="member_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Anggota" value="{{ old('member_name', $coremember['member_name'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Tanggal Lahir') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="member_date_of_birth" id="member_date_of_birth" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Lahir" value="{{ old('member_date_of_birth', empty($coremember['member_date_of_birth']) ? '' : date('d-m-Y', strtotime($coremember['member_date_of_birth'])) ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Jenis Kelamin') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="member_gender" id="member_gender" class="form-control form-control-lg form-control-solid" placeholder="Jenis Kelamin" value="{{ old('member_gender', ($coremember==null?'':($coremember['member_gender']==null?:$membergender[$coremember['member_gender']]))?:'') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('No. Telepon') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="member_phone" id="member_phone" class="form-control form-control-lg form-control-solid" placeholder="No. Telepon" value="{{ old('member_phone', $coremember['member_phone'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Alamat') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <textarea type="text" name="member_address" id="member_address" class="form-control form-control-lg form-control-solid" placeholder="Alamat" autocomplete="off" readonly>{{ old('member_address', $coremember['member_address'] ?? '') }}</textarea>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('Nama Ibu') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="member_mother" id="member_mother" class="form-control form-control-lg form-control-solid" placeholder="Nama Ibu" value="{{ old('member_mother', $coremember['member_mother'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row">
                                <label class="col-lg-2 col-form-label fw-bold fs-6">{{ __('No. Identitas') }}</label>
                                <div class="col-lg-10 fv-row">
                                    <input type="text" name="member_identity_no" id="member_identity_no" class="form-control form-control-lg form-control-solid" placeholder="No. Identitas" value="{{ old('member_identity_no', $coremember['member_identity_no'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                        </div>
                        <div class="separator my-16"></div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <b class="col-lg-12 fw-bold fs-3 text-primary">Data Pinjaman</b>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jenis Pinjaman') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="credits_id" id="credits_id" data-control="select2" data-placeholder="{{ __('Pilih Jenis Pinjaman') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($creditid as $key => $value)
                                            <option data-kt-flag="{{ $value['credits_id'] }}" value="{{ $value['credits_id'] }}" {{ $value['credits_id'] == old('credits_id', $datasession['credits_id'] ?? '') ? 'selected' :'' }}>{{ $value['credits_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Angsuran Tiap') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="payment_period" id="payment_period" data-control="select2" data-placeholder="{{ __('Pilih Angsuran Tiap') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($paymentperiod as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('payment_period', $datasession['payment_period'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Realisasi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credit_account_date" id="credit_account_date" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Realisasi" value="{{ old('credit_account_date', empty($datasession['credit_account_date']) ? date('d-m-Y') : $datasession['credit_account_date'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Tanggal Angsuran I') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credit_account_payment_to" id="credit_account_payment_to" class="form-control form-control-lg form-control-solid" placeholder="Tanggal Angsuran I" value="{{ old('credit_account_payment_to', empty($datasession['credit_account_payment_to']) ? date('d-m-Y') : $datasession['credit_account_payment_to'] ?? '') }}" autocomplete="off" readonly/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Agunan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <a type="button" class="btn btn-sm btn-primary btn-active-light-primary m-1" id="button_modal_angunan">
                                        Tambah Angunan
                                    </a>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Business Office (BO)') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="office_id" id="office_id" data-control="select2" data-placeholder="{{ __('Pilih Business Office (BO)') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($coreoffice as $key => $value)
                                            <option data-kt-flag="{{ $value['office_id'] }}" value="{{ $value['office_id'] }}" {{ $value['office_id'] == old('office_id', $datasession['office_id'] ?? '') ? 'selected' :'' }}>{{ $value['office_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Plafon Pinjaman') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_last_balance_principal_view" id="credits_account_last_balance_principal_view" class="form-control form-control-lg form-control-solid" placeholder="Plafon Pinjaman" value="{{ old('credits_account_last_balance_principal_view', empty($datasession['credits_account_last_balance_principal']) ? '' : number_format($datasession['credits_account_last_balance_principal'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credits_account_last_balance_principal" id="credits_account_last_balance_principal" class="form-control form-control-lg form-control-solid" value="{{ old('credits_account_last_balance_principal', $datasession['credits_account_last_balance_principal'] ?? '') }}"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Pokok') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credits_account_principal_amount_view" id="credits_account_principal_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Pokok" value="{{ old('credits_account_principal_amount_view', empty($datasession['credits_account_principal_amount']) ? '' : number_format($datasession['credits_account_principal_amount'],2) ?? '') }}" autocomplete="off" readonly/>
                                    <input type="hidden" name="credits_account_principal_amount" id="credits_account_principal_amount" class="form-control form-control-lg form-control-solid" value="{{ old('credits_account_principal_amount', $datasession['credits_account_principal_amount'] ?? '') }}"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jumlah Angsuran') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credit_account_payment_amount_view" id="credit_account_payment_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Jumlah Angsuran" value="{{ old('credit_account_payment_amount_view', empty($datasession['credit_account_payment_amount']) ? '' : number_format($datasession['credit_account_payment_amount'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credit_account_payment_amount" id="credit_account_payment_amount" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_payment_amount', $datasession['credit_account_payment_amount'] ?? '') }}"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Provisi dan Komisi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credit_account_provisi_view" id="credit_account_provisi_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Provisi" value="{{ old('credit_account_provisi_view', empty($datasession['credit_account_provisi']) ? '' : number_format($datasession['credit_account_provisi'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credit_account_provisi" id="credit_account_provisi" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_provisi', $datasession['credit_account_provisi'] ?? '') }}"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Notaris') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credit_account_notary_view" id="credit_account_notary_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Asuransi" value="{{ old('credit_account_notary_view', empty($datasession['credit_account_notary']) ? '' : number_format($datasession['credit_account_notary'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credit_account_notary" id="credit_account_notary" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_notary', $datasession['credit_account_notary'] ?? '') }}"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Terima Bersih') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credit_account_amount_received_view" id="credit_account_amount_received_view" class="form-control form-control-lg form-control-solid" placeholder="Terima Bersih" value="{{ old('credit_account_amount_received_view', empty($datasession['credit_account_amount_received']) ? '' : (is_string($datasession['credit_account_amount_received'])? $datasession['credit_account_amount_received'] : number_format($datasession['credit_account_amount_received'],2)) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credit_account_amount_received" id="credit_account_amount_received" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_amount_received', $datasession['credit_account_amount_received'] ?? '') }}"/>  
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row mb-6">
                                <div class="col-lg-12 fw-bold fs-3 text-white">_</div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Sumber Dana') }}</label>
                                <div class="col-lg-8 fv-row">
                                    @if (!empty($coremember))
                                        <select name="sumberdana" id="sumberdana" data-control="select2" data-placeholder="{{ __('Pilih Sumber Dana') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                            <option value="">{{ __('Pilih') }}</option>
                                            @foreach($sumberdana as $key => $value)
                                                <option data-kt-flag="{{ $value['source_fund_id'] }}" value="{{ $value['source_fund_id'] }}" {{ $value['source_fund_id'] == old('sumberdana', $datasession['sumberdana'] ?? '') ? 'selected' :'' }}>{{ $value['source_fund_name'] }}</option>
                                            @endforeach
                                        </select>
                                    @else
                                        <select name="sumberdana" id="sumberdana" data-control="select2" data-placeholder="{{ __('Pilih Sumber Dana') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" disabled>
                                            <option value="">{{ __('Pilih') }}</option>
                                            @foreach($sumberdana as $key => $value)
                                                <option data-kt-flag="{{ $value['source_fund_id'] }}" value="{{ $value['source_fund_id'] }}" {{ $value['source_fund_id'] == old('sumberdana', $datasession['sumberdana'] ?? '') ? 'selected' :'' }}>{{ $value['source_fund_name'] }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jenis Angsuran') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="payment_type_id" id="payment_type_id" data-control="select2" data-placeholder="{{ __('Pilih Jenis Angsuran') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="change_payment_type_id(this.value)">
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($paymenttype as $key => $value)
                                            <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key == old('payment_type_id', $datasession['payment_type_id'] ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jangka waktu') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="credit_account_period" id="credit_account_period" class="form-control form-control-lg form-control-solid" placeholder="Jangka waktu" autocomplete="off" onchange="function_elements_add(this.name, this.value)" value="{{ old('credit_account_period', $datasession['credit_account_period'] ?? '') }}">
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Jatuh Tempo') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="credit_account_due_date" id="credit_account_due_date" class="form-control form-control-lg form-control-solid" placeholder="Jatuh Tempo" autocomplete="off" value="{{ old('credit_account_due_date', date('d-m-Y') ?? '') }}" readonly>
                                </div>
                            </div>
                            <div class="row mb-6"><div style="height: 3.5em"></div></div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="credit_account_interest" id="credit_account_interest" onchange="function_elements_add(this.name, this.value)" class="form-control form-control-lg form-control-solid" placeholder="%" autocomplete="off" value="{{ old('credit_account_interest', $datasession['credit_account_interest'] ?? '') }}">
                                    {{-- <input type="hidden" name="credit_account_interest" id="credit_account_interest" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_interest',  $datasession['credit_account_interest'] ?? '') }}"> --}}
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Angsuran Bunga') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="credits_account_interest_amount_view" id="credits_account_interest_amount_view" class="form-control form-control-lg form-control-solid" placeholder="Angsuran Bunga" autocomplete="off" value="{{ old('credits_account_interest_amount_view', empty($datasession['credits_account_interest_amount']) ? '' : number_format($datasession['credits_account_interest_amount'],2) ?? '') }}">
                                    <input type="hidden" name="credits_account_interest_amount" id="credits_account_interest_amount" class="form-control form-control-lg form-control-solid" value="{{ old('credits_account_interest_amount', $datasession['credits_account_interest_amount'] ?? '') }}">
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Administrasi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input name="credit_account_adm_cost_view" id="credit_account_adm_cost_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Administrasi" autocomplete="off" value="{{ old('credit_account_adm_cost_view', empty($datasession['credit_account_adm_cost']) ? '' : number_format($datasession['credit_account_adm_cost'],2) ?? '') }}">
                                    <input type="hidden" name="credit_account_adm_cost" id="credit_account_adm_cost" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_adm_cost', $datasession['credit_account_adm_cost'] ?? '') }}">
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Biaya Asuransi') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <input type="text" name="credit_account_insurance_view" id="credit_account_insurance_view" class="form-control form-control-lg form-control-solid" placeholder="Biaya Asuransi" value="{{ old('credit_account_insurance_view', empty($datasession['credit_account_insurance']) ? '' : number_format($datasession['credit_account_insurance'],2) ?? '') }}" autocomplete="off"/>
                                    <input type="hidden" name="credit_account_insurance" id="credit_account_insurance" class="form-control form-control-lg form-control-solid" value="{{ old('credit_account_insurance', $datasession['credit_account_insurance'] ?? '') }}"/>
                                </div>
                            </div>
                            <div class="row mb-6">
                                <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('No. Simpanan') }}</label>
                                <div class="col-lg-8 fv-row">
                                    <select name="savings_account_id" id="savings_account_id" data-control="select2" data-placeholder="{{ __('Pilih No. Simpanan') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg" onchange="function_elements_add(this.name, this.value)">
                                        <option value="">{{ __('Pilih') }}</option>
                                        @foreach($acctsavingsaccount as $key => $value)
                                            <option data-kt-flag="{{ $value['savings_account_id'] }}" value="{{ $value['savings_account_id'] }}" {{ $value['savings_account_id'] == old('savings_account_id', $datasession['savings_account_id'] ?? '') ? 'selected' :'' }}>{{ $value['savings_account_no'] }} - {{ $value['member_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" id="kt_credits_account_add_reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_credits_account_add_submit">
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

    <div class="modal fade" tabindex="-1" id="kt_modal_angunan">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Angunan</h3>
    
                    <!--begin::Close-->
                    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
                        <span class="bi bi-x-lg"></span>
                    </div>
                    <!--end::Close-->
                </div>
    
                <div class="modal-body" id="modal-body">
                    @include('content.AcctCreditsAccount.Add.AcctCreditsAgunan.index')
                </div>
    
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

</x-base-layout>
