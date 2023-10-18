@section('scripts')
<script>
const form = document.getElementById('kt_daily_cash_flow_report_view_form');

var validator = FormValidation.formValidation(
    form,
    {
        fields: {
            'start_date': {
                validators: {
                    notEmpty: {
                        message: 'Tanggal harus diisi'
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

const submitButton = document.getElementById('kt_daily_cash_flow_report_submit');
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
    <div class="card">
        <form id="kt_daily_cash_flow_report_view_form" class="form" method="POST" action="{{ route('daily-cash-flow-report.print') }}" enctype="multipart/form-data">
        @csrf
        @method('POST')
            <div class="card-body pt-6">
                <div class="row mb-6">
                    <div class="col-lg-6 fv-row">
                        <label class="col-form-label fw-bold fs-6 required">{{ __('Tanggal') }}</label>
                        <input name="start_date" class="date form-control form-control-lg form-control-solid" placeholder="Tanggal" autocomplete="off"/>
                    </div>
                    <div class="col-lg-6 fv-row">
                        <label class="col-form-label fw-bold fs-6">{{ __('Cabang') }}</label>
                        <select name="branch_id" id="branch_id" data-control="select2" data-placeholder="{{ __('Pilih Cabang') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg">
                            <option value="">{{ __('Pilih') }}</option>
                            @foreach($corebranch as $key => $value)
                                <option data-kt-flag="{{ $value['branch_id'] }}" value="{{ $value['branch_id'] }}" {{ $value['branch_id'] == old('branch_id', '' ?? '') ? 'selected' :'' }}>{{ $value['branch_name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button type="submit" class="btn btn-primary" id="kt_daily_cash_flow_report_submit">
                    @include('partials.general._button-indicator', ['label' => __('Cari')])
                </button>
            </div>
        </form>
    </div>
</x-base-layout>