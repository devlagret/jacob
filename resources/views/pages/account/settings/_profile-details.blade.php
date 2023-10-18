@section('scripts')
    <script>
        var submitButton = document.querySelector('#kt_account_profile_details_submit');
        var form = document.querySelector('#kt_account_profile_details_form');
        validator = FormValidation.formValidation(
            form,
            {
                fields: {
                    'first_name': {
                        validators: {
                            notEmpty: {
                                message: 'Nama depan tidak boleh kosong'
                            }
                        }
                    },
                    'last_name': {
                        validators: {
                            notEmpty: {
                                message: 'Nama belakang tidak boleh kosong'
                            }
                        }
                    },
                    'username': {
                        validators: {
                            notEmpty: {
                                message: 'Username tidak boleh kosong'
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

        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            validator.validate().then(function (status) {
                if (status === 'Valid') {
                    submitButton.setAttribute('data-kt-indicator', 'on');
                    submitButton.disabled = true;
                    form.submit();
                } else {
                    submitButton.disabled = false;
                }
            });
        });

        var submitButton2 = document.querySelector('#kt_password_submit');
        var form2 = document.querySelector('#kt_signin_change_password');
        validator2 = FormValidation.formValidation(
            form2,
            {
                fields: {
                    'current_password': {
                        validators: {
                            notEmpty: {
                                message: 'Password lama tidak boleh kosong'
                            }
                        }
                    },
                    'password': {
                        validators: {
                            notEmpty: {
                                message: 'Password baru tidak boleh kosong'
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

        submitButton2.addEventListener('click', function (e) {
            e.preventDefault();

            validator2.validate().then(function (status) {
                if (status === 'Valid') {
                    submitButton2.setAttribute('data-kt-indicator', 'on');
                    submitButton2.disabled = true;
                    form2.submit();
                } else {
                    submitButton2.disabled = false;
                }
            });
        });
    </script>
@endsection
<!--begin::Basic info-->
<div class="card {{ $class }}">
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_profile_details" aria-expanded="true" aria-controls="kt_account_profile_details">
        <!--begin::Card title-->
        <div class="card-title m-0">
            <h3 class="fw-bolder m-0">{{ __('Profile Details') }}</h3>
        </div>
        <!--end::Card title-->
    </div>
    <!--begin::Card header-->

    <!--begin::Content-->
    <div id="kt_account_profile_details" class="collapse show">
        <!--begin::Form-->
        <form id="kt_account_profile_details_form" class="form" method="POST" action="{{ route('user.settings-update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <!--begin::Card body-->
            <div class="card-body border-top p-9">
                <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Foto Profil') }}</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8">
                        <!--begin::Image input-->
                        <div class="image-input image-input-outline {{ isset($info) && $info->avatar ? '' : 'image-input-empty' }}" data-kt-image-input="true" style="background-image: url({{ asset(theme()->getMediaUrlPath() . 'avatars/blank.png') }})">
                            <!--begin::Preview existing avatar-->
                            <div class="image-input-wrapper w-125px h-125px" style="background-image: {{ isset($info) && $info->avatar_url ? 'url('.asset('storage/images/'.auth()->user()->avatar).')' : 'none' }};"></div>
                            <!--end::Preview existing avatar-->

                            <!--begin::Label-->
                            <label class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="change" data-bs-toggle="tooltip" title="Change avatar">
                                <i class="bi bi-pencil-fill fs-7"></i>

                                <!--begin::Inputs-->
                                <input type="file" name="avatar" accept=".png, .jpg, .jpeg"/>
                                <input type="hidden" name="avatar_remove"/>
                                <!--end::Inputs-->
                            </label>
                            <!--end::Label-->

                            <!--begin::Cancel-->
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="cancel" data-bs-toggle="tooltip" title="Cancel avatar">
                                <i class="bi bi-x fs-2"></i>
                            </span>
                            <!--end::Cancel-->

                            <!--begin::Remove-->
                            <span class="btn btn-icon btn-circle btn-active-color-primary w-25px h-25px bg-body shadow" data-kt-image-input-action="remove" data-bs-toggle="tooltip" title="Remove avatar">
                                <i class="bi bi-x fs-2"></i>
                            </span>
                            <!--end::Remove-->
                        </div>
                        <!--end::Image input-->

                        <!--begin::Hint-->
                        <div class="form-text">File yang dizinkan: png, jpg, jpeg.</div>
                        <!--end::Hint-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-bold fs-6">{{ __('Nama Panjang') }}</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8">
                        <!--begin::Row-->
                        <div class="row">
                            <!--begin::Col-->
                            <div class="col-lg-6 fv-row">
                                <input type="text" name="first_name" class="form-control form-control-lg form-control-solid mb-3 mb-lg-0" placeholder="Nama depan" value="{{ old('first_name', auth()->user()->first_name ?? '') }}"/>
                            </div>
                            <!--end::Col-->

                            <!--begin::Col-->
                            <div class="col-lg-6 fv-row">
                                <input type="text" name="last_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Belakang" value="{{ old('last_name', auth()->user()->last_name ?? '') }}"/>
                            </div>
                            <!--end::Col-->
                        </div>
                        <!--end::Row-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label required fw-bold fs-6">{{ __('Username') }}</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="username" class="form-control form-control-lg form-control-solid" placeholder="Username" value="{{ old('username', auth()->user()->username ?? '') }}"/>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label fw-bold fs-6">
                        <span class="">{{ __('No. Telp') }}</span>

                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="{{ __('Phone number must be active') }}"></i>
                    </label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">
                        <input type="tel" name="phone" class="form-control form-control-lg form-control-solid" placeholder="No. Telp" value="{{ old('phone', auth()->user()->phone ?? '') }}"/>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->

                <!--begin::Input group-->
                <div class="row mb-6">
                    <!--begin::Label-->
                    <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Email') }}</label>
                    <!--end::Label-->

                    <!--begin::Col-->
                    <div class="col-lg-8 fv-row">
                        <input type="text" name="email" class="form-control form-control-lg form-control-solid" placeholder="Email" value="{{ old('email', auth()->user()->email ?? '') }}"/>
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Input group-->

                <!-- <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-bold fs-6">
                        <span class="required">{{ __('Country') }}</span>

                        <i class="fas fa-exclamation-circle ms-1 fs-7" data-bs-toggle="tooltip" title="{{ __('Country of origination') }}"></i>
                    </label>

                    <div class="col-lg-8 fv-row">
                        <select name="country" aria-label="{{ __('Select a Country') }}" data-control="select2" data-placeholder="{{ __('Select a country...') }}" class="form-select form-select-solid form-select-lg fw-bold">
                            <option value="">{{ __('Select a Country...') }}</option>
                            @foreach(\App\Core\Data::getCountriesList() as $key => $value)
                                <option data-kt-flag="{{ $value['flag'] }}" value="{{ $key }}" {{ $key === old('country', $info->country ?? '') ? 'selected' :'' }}>{{ $value['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-bold fs-6">{{ __('Language') }}</label>

                    <div class="col-lg-8 fv-row">
                        <select name="language" aria-label="{{ __('Select a Language') }}" data-control="select2" data-placeholder="{{ __('Select a language...') }}" class="form-select form-select-solid form-select-lg">
                            <option value="">{{ __('Select a Language...') }}</option>
                            @foreach(\App\Core\Data::getLanguagesList() as $key => $value)
                                <option data-kt-flag="{{ $value['country']['flag'] }}" value="{{ $key }}" {{ $key === old('language', $info->language ?? '') ? 'selected' :'' }}>{{ $value['name'] }}</option>
                            @endforeach
                        </select>

                        <div class="form-text">
                            {{ __('Please select a preferred language, including date, time, and number formatting.') }}
                        </div>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label required fw-bold fs-6">{{ __('Time Zone') }}</label>

                    <div class="col-lg-8 fv-row">
                        <select name="timezone" aria-label="{{ __('Select a Timezone') }}" data-control="select2" data-placeholder="{{ __('Select a timezone..') }}" class="form-select form-select-solid form-select-lg">
                            <option value="">{{ __('Select a Timezone..') }}</option>
                            @foreach(\App\Core\Data::getTimeZonesList() as $key => $value)
                                <option data-bs-offset="{{ $value['offset'] }}" value="{{ $key }}" {{ $key === old('timezone', $info->timezone ?? '') ? 'selected' :'' }}>{{ $value['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label  fw-bold fs-6">{{ __('Currency') }}</label>

                    <div class="col-lg-8 fv-row">
                        <select name="currency" aria-label="{{ __('Select a Currency') }}" data-control="select2" data-placeholder="{{ __('Select a currency..') }}" class="form-select form-select-solid form-select-lg">
                            <option value="">{{ __('Select a currency..') }}</option>
                            @foreach(\App\Core\Data::getCurrencyList() as $key => $value)
                                <option data-kt-flag="{{ $value['country']['flag'] }}" value="{{ $key }}" {{ $key === old('currency', $info->currency ?? '') ? 'selected' :'' }}><b>{{ $key }}</b>&nbsp;-&nbsp;{{ $value['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row mb-6">
                    <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Communication') }}</label>

                    <div class="col-lg-8 fv-row">
                        <div class="d-flex align-items-center mt-3">
                            <label class="form-check form-check-inline form-check-solid me-5">
                                <input type="hidden" name="communication[email]" value="0">
                                <input class="form-check-input" name="communication[email]" type="checkbox" value="1" {{ old('marketing', $info->communication['email'] ?? '') ? 'checked' : '' }}/>
                                <span class="fw-bold ps-2 fs-6">
                                    {{ __('Email') }}
                                </span>
                            </label>

                            <label class="form-check form-check-inline form-check-solid">
                                <input type="hidden" name="communication[phone]" value="0">
                                <input class="form-check-input" name="communication[phone]" type="checkbox" value="1" {{ old('email', $info->communication['phone'] ?? '') ? 'checked' : '' }}/>
                                <span class="fw-bold ps-2 fs-6">
                                    {{ __('Phone') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="row mb-0">
                    <label class="col-lg-4 col-form-label fw-bold fs-6">{{ __('Allow Marketing') }}</label>

                    <div class="col-lg-8 d-flex align-items-center">
                        <div class="form-check form-check-solid form-switch fv-row">
                            <input type="hidden" name="marketing" value="0">
                            <input class="form-check-input w-45px h-30px" type="checkbox" id="allowmarketing" name="marketing" value="1" {{ old('marketing', $info->marketing ?? '') ? 'checked' : '' }}/>
                            <label class="form-check-label" for="allowmarketing"></label>
                        </div>
                    </div>
                </div> -->
                
            </div>

            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>

                <button type="button" class="btn btn-primary" id="kt_account_profile_details_submit">
                    @include('partials.general._button-indicator', ['label' => __('Simpan')])
                </button>
            </div>
            <!--end::Actions-->
        </form>
        <!--end::Form-->
    </div>
    <!--end::Content-->
</div>
<!--end::Basic info-->
