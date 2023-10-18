
@section('scripts')
<script>
    // $(document).ready(function() {
    //     const form = document.getElementById('kt_user_add_view_form');
    //     form.addEventListener('reset', function() {
    //         $('#user_group_id').val("").trigger("change");
    //     });
    // });
    const form = document.getElementById('kt_user_add_view_form');

    // form.addEventListener('reset', function() {
    //     $('#user_group_id').select2("val", "");
    //     $('#user_group_id').val("").trigger("change");
    //     $("#user_group_id").html("").trigger("change");
    //     // $("#user_group_id").empty().trigger('change');
    // });

    var validator = FormValidation.formValidation(
        form,
        {
            fields: {
                'username': {
                    validators: {
                        notEmpty: {
                            message: 'Username harus diisi'
                        }
                    }
                },
                'password': {
                    validators: {
                        notEmpty: {
                            message: 'Password harus diisi'
                        }
                    }
                },
                'user_group_id': {
                    validators: {
                        notEmpty: {
                            message: 'User Group harus diisi'
                        }
                    }
                },
                'branch_id': {
                    validators: {
                        notEmpty: {
                            message: 'Cabang harus diisi'
                        }
                    }
                },
                'branch_status': {
                    validators: {
                        notEmpty: {
                            message: 'Cabang harus diisi'
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

    $(form.querySelector('[name="user_group_id"]')).on('change', function () {
        validator.revalidateField('user_group_id');
    });

    $(form.querySelector('[name="branch_id"]')).on('change', function () {
        validator.revalidateField('branch_id');
    });

    const submitButton = document.getElementById('kt_user_add_submit');
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
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah User') }}</h3>
            </div>

            <a href="{{ route('user.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_add_view">
            <form id="kt_user_add_view_form" class="form" method="POST" action="{{ route('user.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Username') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="username" class="form-control form-control-lg form-control-solid" placeholder="Username" value="{{ old('username', '' ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Password') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="password" class="form-control form-control-lg form-control-solid" placeholder="Password" value="{{ old('password', '' ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('User Group') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="user_group_id" id="user_group_id" aria-label="{{ __('Pilih User Group') }}" data-control="select2" data-placeholder="{{ __('Pilih user group..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih user group..') }}</option>
                                @foreach($usergroup as $key => $value)
                                    <option data-kt-flag="{{ $value->user_group_id }}" value="{{ $value->user_group_id }}" {{ $value->user_group_id === old('user_group_id', '' ?? '') ? 'selected' :'' }}>{{ $value['user_group_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kantor') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="branch_id" id="branch_id" aria-label="{{ __('Pilih kantor') }}" data-control="select2" data-placeholder="{{ __('Pilih kantor..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih kantor..') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value->branch_id }}" value="{{ $value->branch_id }}" {{ $value->branch_id === old('branch_id', '' ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Branch Status') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="branch_status" id="branch_status" aria-label="{{ __('Branch Status') }}" data-control="select2" data-placeholder="{{ __('Branch Status..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Branch Status..') }}</option>
                                @foreach($branchstatus as $key => $value)
                                    <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('branch_status', '' ?? '') ? 'selected' :'' }}>{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2" id="reset_button">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_user_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

