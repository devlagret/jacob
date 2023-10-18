@section('scripts')
<script>
const form = document.getElementById('kt_savings_close_book_add_view_form');

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

const submitButton = document.getElementById('kt_savings_close_book_add_submit');
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
        {{-- <div class="card-header">
            <h3 class="card-title">Daftar User</h3>
            <div class="card-toolbar">
                <a type="button" href="{{ route('user.add') }}"  class="btn btn-sm btn-light-primary">
                    {!! theme()->getSvgIcon("icons/duotune/general/gen035.svg", "svg-icon-2x me-1") !!}
                    {{ __('Tambah User Baru') }}
                </a>
            </div>
        </div> --}}
        <form id="kt_savings_close_book_add_view_form" class="form" method="POST" action="{{ route('savings-close-book.process-add') }}" enctype="multipart/form-data">
        @csrf
        @method('POST')
            <div class="card-body pt-6">
                <div class="row mb-6">
                    <label class="col-lg-12 col-form-label fw-bold fs-6 required">{{ __('Tanggal') }}</label>
                    <div class="col-lg-6 fv-row">
                        <input name="start_date" class="date form-control form-control-lg form-control-solid" placeholder="Tanggal" autocomplete="off"/>
                    </div>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end py-6 px-9">
                <button type="submit" class="btn btn-primary" id="kt_savings_close_book_add_submit">
                    @include('partials.general._button-indicator', ['label' => __('Simpan')])
                </button>
            </div>
        </form>
    </div>
</x-base-layout>