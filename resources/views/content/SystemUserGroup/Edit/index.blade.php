<?php
use \App\Http\Controllers\SystemUserGroupController;
?>
@section('scripts')
<script>
const form = document.getElementById('kt_user_group_edit_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'user_group_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Jabatan Harus Diisi'
                    }
                }
            },
            'user_group_level': {
                validators: {
                    notEmpty: {
                        message: 'Privelege Menu'
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

const submitButton = document.getElementById('kt_user_group_edit_submit');
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

function check_all(){
    $(':checkbox').each(function() {
        this.checked = true;
    });
}
function uncheck_all(){
    $(':checkbox').each(function() {
        this.checked = false;
    });
}
</script>
@endsection

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Edit Jabatan User') }}</h3>
            </div>

            <a href="{{ route('user-group.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_group_edit_view">
            <form id="kt_user_group_edit_view_form" class="form" method="POST" action="{{ route('user-group.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Jabatan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="hidden" name="user_group_id" class="form-control form-control-lg form-control-solid" placeholder="Nama Group" value="{{ old('user_group_id', $usergroup->user_group_id ?? '') }}" autocomplete="off"/>
                            <input type="text" name="user_group_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Group" value="{{ old('user_group_name', $usergroup->user_group_name ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Privilege Menu') }}</label>
                        <div class="col-lg-8 fv-row">
                            <a onclick="check_all()" name="Find" class="btn btn-sm btn-info" title="Check All"> Cek Semua</a>
                            <a onclick="uncheck_all()" name="Find" class="btn btn-sm btn-info" title="UnCheck All"> Hapus Cek Semua</a>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6"></label>
                        <div class="col-lg-8 fv-row">
                            <?php foreach($systemmenu as $key => $val) {
                                $digits = strlen((string)$val['id_menu']);
                                if($digits == 1 && SystemUserGroupController::getMenuMappingStatus($usergroup['user_group_level'], $val['id_menu'])!=0){
                            ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm">
                                    <input class="form-check-input" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1" checked/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php   }else if($digits == 1 && SystemUserGroupController::getMenuMappingStatus($usergroup['user_group_level'], $val['id_menu'])==0){ ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm">
                                    <input class="form-check-input" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1"/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php   }else if($digits == 2 && SystemUserGroupController::getMenuMappingStatus($usergroup['user_group_level'], $val['id_menu'])!=0){ ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm" style="margin-left:25px">
                                    <input class="form-check-input" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1" checked/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php   }else if($digits == 2 && SystemUserGroupController::getMenuMappingStatus($usergroup['user_group_level'], $val['id_menu'])==0){ ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm" style="margin-left:25px">
                                    <input class="form-check-input" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1"/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php   }else if($digits == 3 && SystemUserGroupController::getMenuMappingStatus($usergroup['user_group_level'], $val['id_menu'])!=0){ ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm" style="margin-left:50px">
                                    <input class="form-check-input" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1" checked/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php   }else if($digits == 3 && SystemUserGroupController::getMenuMappingStatus($usergroup['user_group_level'], $val['id_menu'])==0){ ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm" style="margin-left:50px">
                                    <input class="form-check-input" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1"/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php   }
                            } ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>

                    <button type="submit" class="btn btn-primary" id="kt_user_group_edit_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

