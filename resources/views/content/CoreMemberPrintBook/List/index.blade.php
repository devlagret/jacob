<x-base-layout>
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Cetak Buku Anggota</h3>
            <div class="card-toolbar">
            </div>
        </div>
        <div class="card-body pt-6">
            @include('content.CoreMemberPrintBook.List._table')
        </div>
    </div>
</x-base-layout>