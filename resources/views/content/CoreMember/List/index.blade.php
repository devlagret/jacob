<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Daftar Anggota</h3>
            <div class="card-toolbar">
                <a type="button" href="{{ route('member.export') }}"  class="btn btn-m btn-light-primary me-2">
                    <i class="bi bi-download fs-2"></i>
                    {{ __('Export Data Anggota') }}
                </a>
                <a type="button" href="{{ route('member.add') }}"  class="btn btn-sm btn-light-primary">
                    {!! theme()->getSvgIcon("icons/duotune/general/gen035.svg", "svg-icon-2x me-1") !!}
                    {{ __('Tambah Anggota Baru') }}
                </a>
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.CoreMember.List._table')
        </div>
    </div>
</x-base-layout>