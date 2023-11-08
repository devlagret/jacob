
@section('scripts')
<script>
const form = document.getElementById('kt_user_edit_view_form');

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
            'user_group_id': {
                validators: {
                    notEmpty: {
                        message: 'User Group harus diisi'
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
passwordChanged= !!false;
function openform(){
		var a = document.getElementById("passwordf").style;
		if(a.display=="none"){
			a.display = "block";
            passwordChanged= !!true;
            $('#passIsChanged').val(1);
		}else{
            $('#passIsChanged').val(0);
            passwordChanged= !!false;
			a.display = "none";
			document.getElementById("password").value ='';
			document.getElementById("re_password").value ='';
		}
}
$(form.querySelector('[name="user_group_id"]')).on('change', function () {
    validator.revalidateField('user_group_id');
});

$(form.querySelector('[name="branch_id"]')).on('change', function () {
    validator.revalidateField('branch_id');
});

const submitButton = document.getElementById('kt_user_edit_submit');
submitButton.addEventListener('click', function (e) {
    e.preventDefault();
    if(passwordChanged){
        if($('#password').val() != $('#re_password').val()){
        $('#re_password').focus();
        alert('Pastikan input ulang password sama denga password baru')
        $('#re_password').focus();
        return 0;
        }
        if($('#password').val()=='' && $('#re_password').val() ==''){
        $('#password').focus();
        alert('Password masih kosong')
        $('#password').focus();
        return 0;
        }
    }
    if (validator) {
        validator.validate().then(function (status) {
            if (status == 'Valid') {
                submitButton.setAttribute('data-kt-indicator', 'on');

                submitButton.disabled = true;

                setTimeout(function () {
                    submitButton.removeAttribute('data-kt-indicator');

                    // submitButton.disabled = false;

                    // Swal.fire({
                    //     text: "Form has been successfully submitted!",
                    //     icon: "success",
                    //     buttonsStyling: false,
                    //     confirmButtonText: "Ok, got it!",
                    //     customClass: {
                    //         confirmButton: "btn btn-primary"
                    //     }
                    // });

                    form.submit(); // Submit form
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
                <h3 class="fw-bolder m-0">{{ __('Form Ubah User') }}</h3>
            </div>

            <a href="{{ route('user.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_edit_view">
            <form id="kt_user_edit_view_form" class="form" method="POST" action="{{ route('user.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Username') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="user_id" class="form-control form-control-lg form-control-solid" placeholder="Username" value="{{ old('user_id', $user->user_id ?? '') }}"/>
                            <input type="text" name="username" class="form-control form-control-lg form-control-solid" placeholder="Username" value="{{ old('username', $user->username ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Jabatan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="user_group_id" id="user_group_id" aria-label="{{ __('Pilih User Group') }}" data-control="select2" data-placeholder="{{ __('Pilih user group..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih user group..') }}</option>
                                @foreach($usergroup as $key => $value)
                                    <option data-kt-flag="{{ $value->user_group_id }}" value="{{ $value->user_group_id }}" {{ $value->user_group_id === old('user_group_id', $user->user_group_id ?? '') ? 'selected' :'' }}>{{ $value['user_group_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Ganti Password') }}</label>
                        <div class="col-lg-8 fv-row">
                            <button type="button" class="button btn btn-primary" onclick="openform()">Ganti Password</button>
                        </div>
                    </div>
                    <div style ="display:none;"id="passwordf">
                    <input type="hidden" id="passIsChanged" name="passIsChanged" value="0"/>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Password Baru') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="password" id="password" class="form-control form-control-lg form-control-solid" placeholder="Password baru" value="{{ old('password', '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 ">{{ __('Masukan Kembali Password Baru') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="password" name="re_password" id="re_password" class="form-control form-control-lg form-control-solid" placeholder="Masukan kembali password baru" value="{{ old('re_password', '') }}" autocomplete="off"/>
                        </div>
                    </div>
                </div>
                        <input hidden type="branch_id" name="branch_id" id="branch_id" class="form-control form-control-lg form-control-solid" value="{{ $user->branch_id }}" autocomplete="off"/>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Cabang') }}</label>
                        <div class="col-lg-8 fv-row">
                            <select name="branch_id" id="branch_id" aria-label="{{ __('Pilih Cabang') }}" data-control="select2" data-placeholder="{{ __('Pilih cabang..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                                <option value="">{{ __('Pilih cabang..') }}</option>
                                @foreach($corebranch as $key => $value)
                                    <option data-kt-flag="{{ $value->branch_id }}" value="{{ $value->branch_id }}" {{ $value->branch_id === old('branch_id', $user->branch_id ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_user_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

