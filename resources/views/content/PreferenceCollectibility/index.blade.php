@section('scripts')
<script>
const form = document.getElementById('kt_user_group_add_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
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

<x-base-layout>
    <div class="card mb-5 mb-xl-10">
        <div class="card-header border-0">
            <div class="card-title m-0">
                <h3 class="fw-bolder m-0">{{ __('Kode Kolektibilitas') }}</h3>
            </div>

        </div>

        <div id="kt_user_group_add_view">
            <form id="kt_user_group_add_view_form" class="form" method="POST" action="{{ route('preference-collectibility.process-edit') }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
                <div class="card-body border-top p-9">
                    <?php 
                    foreach($collectibility as $key => $val){  
                    ?>
                        <div class="row mb-6">
                            <label class="col-lg-2 col-form-label fw-bold fs-6">{{  __(($key+1).'. '.$val['collectibility_name']) }}</label>
                            <div class="col-lg-2 fv-row">
                                <input type="text" name="collectibility_bottom_{{$val['collectibility_id']}}" id="collectibility_bottom_{{$val['collectibility_id']}}" class="form-control form-control-lg form-control-solid" placeholder="" value="{{ old('collectibility_bottom', $val->collectibility_bottom ?? '') }}" autocomplete="off"/>
                            </div>
                            <label class="col-lg-1 col-form-label fw-bold fs-6 text-center">{{  __('S.D') }}</label>
                            <div class="col-lg-2 fv-row">
                                <input type="text" name="collectibility_top_{{$val['collectibility_id']}}" id="collectibility_top_{{$val['collectibility_id']}}" class="form-control form-control-lg form-control-solid" placeholder="" value="{{ old('collectibility_top', $val->collectibility_top ?? '') }}" autocomplete="off"/>
                            </div>
                            <label class="col-lg-1 col-form-label fw-bold fs-6 text-center">{{  __('PPAP (%)') }}</label>
                            <div class="col-lg-4 fv-row">
                                <input type="text" name="collectibility_ppap_{{$val['collectibility_id']}}" id="collectibility_ppap_{{$val['collectibility_id']}}" class="form-control form-control-lg form-control-solid" placeholder="" value="{{ old('collectibility_ppap', $val->collectibility_ppap ?? '') }}" autocomplete="off"/>
                            </div>
                        </div>
                    <?php 
                    } 
                    ?>
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

