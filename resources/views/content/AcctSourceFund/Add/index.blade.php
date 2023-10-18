@section('scripts')
<script>
const form = document.getElementById('kt_source_fund_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'source_fund_code': {
                validators: {
                    notEmpty: {
                        message: 'Kode Sumber Dana harus diisi'
                    }
                }
            },
            'source_fund_name': {
                validators: {
                    notEmpty: {
                        message: 'Nama Sumber Dana harus diisi'
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

const submitButton = document.getElementById('kt_source_fund_add_submit');
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
                <h3 class="fw-bolder m-0">{{ __('Form Tambah Sumber Dana') }}</h3>
            </div>

            <a href="{{ route('source-fund.index') }}" class="btn btn-light align-self-center">
                {!! theme()->getSvgIcon("icons/duotune/arrows/arr079.svg", "svg-icon-4 me-1") !!}
                {{ __('Kembali') }}</a>
        </div>

        <div id="kt_source_fund_add_view">
            <form id="kt_source_fund_add_view_form" class="form" method="POST" action="{{ route('source-fund.process-add') }}" enctype="multipart/form-data">
            @csrf
            @method('POST')
                <div class="card-body border-top p-9">
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Kode Sumber Dana') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="source_fund_code" class="form-control form-control-lg form-control-solid" placeholder="Kode Sumber Dana" value="{{ old('source_fund_code', '' ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                    <div class="row mb-6">
                        <label class="col-lg-4 col-form-label fw-bold fs-6 required">{{ __('Nama Sumber Dana') }}</label>
                        <div class="col-lg-8 fv-row">
                            <input type="text" name="source_fund_name" class="form-control form-control-lg form-control-solid" placeholder="Nama Sumber Dana" value="{{ old('source_fund_name', '' ?? '') }}" autocomplete="off"/>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-end py-6 px-9">
                    <button type="reset" class="btn btn-white btn-active-light-primary me-2">{{ __('Batal') }}</button>
    
                    <button type="submit" class="btn btn-primary" id="kt_source_fund_add_submit">
                        @include('partials.general._button-indicator', ['label' => __('Simpan')])
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-base-layout>

