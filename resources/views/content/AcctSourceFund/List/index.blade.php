<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Sumber Dana</h3>
            <div class="card-toolbar">
                <a type="button" href="{{ route('source-fund.add') }}"  class="btn btn-sm btn-light-primary">
                    {!! theme()->getSvgIcon("icons/duotune/general/gen035.svg", "svg-icon-2x me-1") !!}
                    {{ __('Tambah Sumber Dana Baru') }}
                </a>
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.AcctSourceFund.List._table')
        </div>
    </div>
</x-base-layout>