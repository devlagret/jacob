@if ($model->credits_approve_status == 0)
    <td class="text-end">
        <a type="button" href="{{ route('credits-account.print-note', $model->credits_account_id) }}" class="btn btn-sm btn-primary btn-active-light-primary m-1">
            Kwitansi
        </a>
        {{-- <a type="button" href="{{ route('credits-account.print-akad', $model->credits_account_id) }}" class="btn btn-sm btn-warning btn-active-light-warning m-1">
            Akad
        </a> --}}
        <a type="button" href="{{ route('credits-account.edit-date', $model->credits_account_id) }}" class="btn btn-sm btn-dark btn-active-light-dark m-1">
            Edit Tanggal
        </a>
        <a type="button" href="{{ route('credits-account.print-schedule', $model->credits_account_id) }}" class="btn btn-sm btn-info btn-active-light-info m-1">
            Jadwal Angsuran
        </a>
        <a type="button" href="{{ route('credits-account.detail', $model->credits_account_id) }}" class="btn btn-sm btn-secondary btn-active-light-secondary m-1">
            Detail
        </a>
        {{-- <a type="button" href="{{ route('credits-account.print-schedule-member', $model->credits_account_id) }}" class="btn btn-sm btn-dark btn-active-light-dark m-1">
            Jadwal Angsuran Untuk Anggota
        </a>
  --}}
        {{-- <a type="button" href="{{ route('credits-account.print-agunan', $model->credits_account_id) }}" class="btn btn-sm btn-success btn-active-light-success m-1">
            Tanda Terima Jaminan
        </a>  --}}
       
    </td>
@elseif ($model->credits_approve_status == 1)
    <td class="text-end">
        <a type="button" href="{{ route('credits-account.print-note', $model->credits_account_id) }}" class="btn btn-sm btn-primary btn-active-light-primary m-1">
            Kwitansi
        </a>
        {{-- <a type="button" href="{{ route('credits-account.print-akad', $model->credits_account_id) }}" class="btn btn-sm btn-warning btn-active-light-warning m-1">
            Akad
        </a> --}}
        <a type="button" href="{{ route('credits-account.print-schedule', $model->credits_account_id) }}" class="btn btn-sm btn-info btn-active-light-info m-1">
            Jadwal Angsuran
        </a>
        <a type="button" href="{{ route('credits-account.detail', $model->credits_account_id) }}" class="btn btn-sm btn-secondary btn-active-light-secondary m-1">
            Detail
        </a>
        {{-- <a type="button" href="{{ route('credits-account.print-schedule-member', $model->credits_account_id) }}" class="btn btn-sm btn-dark btn-active-light-dark m-1">
            Jadwal Angsuran Untuk Anggota
        </a> --}}
        
        {{-- <a type="button" href="{{ route('credits-account.print-agunan', $model->credits_account_id) }}" class="btn btn-sm btn-success btn-active-light-success m-1">
            Tanda Terima Jaminan
        </a> --}}
    </td>
@else
<td class="text-end">
    <a type="button" href="{{ route('credits-account.print-note', $model->credits_account_id) }}" class="btn btn-sm btn-primary btn-active-light-primary m-1">
        Kwitansi
    </a>
    <a type="button" href="{{ route('credits-account.print-akad', $model->credits_account_id) }}" class="btn btn-sm btn-warning btn-active-light-warning m-1">
        Akad
    </a>
    <a type="button" href="{{ route('credits-account.print-schedule', $model->credits_account_id) }}" class="btn btn-sm btn-info btn-active-light-info m-1">
        Jadwal Angsuran
    </a>
    <a type="button" href="{{ route('credits-account.detail', $model->credits_account_id) }}" class="btn btn-sm btn-secondary btn-active-light-secondary m-1">
        Detail
    </a>
    {{-- <a type="button" href="{{ route('credits-account.print-schedule-member', $model->credits_account_id) }}" class="btn btn-sm btn-dark btn-active-light-dark m-1">
        Jadwal Angsuran Untuk Anggota
    </a> --}}
    {{-- <a type="button" href="{{ route('credits-account.print-agunan', $model->credits_account_id) }}" class="btn btn-sm btn-success btn-active-light-success m-1">
        Tanda Terima Jaminan
    </a> --}}
    <a type="button" href="{{ route('credits-account.delete', $model->credits_account_id) }}" class="btn btn-sm btn-danger btn-active-light-danger m-1">
        Hapus
    </a>
</td>
@endif
