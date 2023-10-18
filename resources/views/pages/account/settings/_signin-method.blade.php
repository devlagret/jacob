<!--begin::Sign-in Method-->
<div class="card {{ $class ?? '' }}" {{ util()->putHtmlAttributes(array('id' => $id ?? '')) }}>
    <!--begin::Card header-->
    <div class="card-header border-0 cursor-pointer" role="button" data-bs-toggle="collapse" data-bs-target="#kt_account_signin_method">
        <div class="card-title m-0">
            <h3 class="fw-bolder m-0">{{ __('Ubah Password') }}</h3>
        </div>
    </div>
    <!--end::Card header-->

    <!--begin::Content-->
    <div id="kt_account_signin_method" class="collapse show">
        <div class="card-body border-top p-9">
            <div class="d-flex flex-wrap align-items-center mb-10">
                <div id="kt_signin_password_edit" class="flex-row-fluid">
                    <form id="kt_signin_change_password" class="form" method="POST" action="{{ route('user.settings-changepassword') }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="current_email" value="{{ auth()->user()->email }} "/>
                        <div class="row mb-1">
                            <div class="col-lg-6">
                                <div class="fv-row mb-0">
                                    <label for="current_password" class="form-label required fs-6 fw-bolder mb-3">{{ __('Password Lama') }}</label>
                                    <input type="password" class="form-control form-control-lg form-control-solid" name="current_password" id="current_password" placeholder="Password Lama"/>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="fv-row mb-0">
                                    <label for="password" class="form-label required fs-6 fw-bolder mb-3">{{ __('Password Baru') }}</label>
                                    <input type="password" class="form-control form-control-lg form-control-solid" name="password" id="password" placeholder="Password Baru"/>
                                </div>
                            </div>

                        </div>

                        {{-- <div class="form-text mb-5">{{ __('Password must be at least 8 character and contain symbols') }}</div> --}}

                        <div class="d-flex justify-content-end py-6">
                            <button id="kt_password_submit" type="button" class="btn btn-primary me-2 px-6">
                                @include('partials.general._button-indicator', ['label' => __('Ubah Password')])
                            </button>
                            {{-- <button id="kt_password_cancel" type="button" class="btn btn-color-gray-400 btn-active-light-primary px-6">{{ __('Cancel') }}</button> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
