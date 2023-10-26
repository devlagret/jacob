@section('scripts')
<script>
const form = document.getElementById('kt_user_group_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'user_group_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Group harus diisi'
                    }
                }
            },
            'user_group_level': {
                validators: {
                    notEmpty: {
                        message: 'User Group Level harus diisi'
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

const submitButton = document.getElementById('kt_user_gorup_add_submit');
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
@section('styles')
<style type="text/css">
.form-check.form-check-solid .form-check-input{
    background-color: rgba(100, 100, 100, 0.15) ;
    border: 1px solid rgba(0, 0, 0, 0.25) ;
}
</style>
@endsection
<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Jabatan User') }}</h3>
            </div>

            <a href="{{ route('user-group.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_user_group_add_view">
            <form id="kt_user_group_add_view_form" class="form" method="POST" action="{{ route('user-group.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Jabatan') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="user_group_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Group" value="{{ old('user_group_name', '' ?? '') }}" autocomplete="off"/>
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
                            <?php $i=0; foreach($systemmenu as $key => $val) {
                                $l=0;
                                $digits = strlen((string)$val['id_menu']);
                                if($digits == 1){
                            ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm">
                                    <input class="form-check-input parent-{{$i}}" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1"/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php   }else if($digits == 2){ ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm" style="margin-left:25px">
                                    <input class="form-check-input child1-{{$l}}-{{$i}}" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1"/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php $l++; }else if($digits == 3){ ?>
                                <div class="form-check form-check-custom form-check-success form-check-solid form-check-sm" style="margin-left:50px">
                                    <input class="form-check-input child2-{{$i}}" type="checkbox" name='checkbox_{{$val['id_menu']}}' id='checkbox_{{$val['id_menu']}}' value="1"/>
                                    <label class="form-check-label" for="checkbox_{{$val['id_menu']}}">
                                        {{$val['text']}}
                                    </label>
                                </div>
                                <br>
                            <?php   }
                           $i++; } ?>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>

                    <button type="submit" class="btn btn-primary" id="kt_user_gorup_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

