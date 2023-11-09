<td class="text-end">
    <a href="{{ route('credits-account.print-note', $model->credits_account_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
        Kwitansi
    </a>
    {{-- @if($model->validation == 0)
    <a href="{{ route('savings-account.validation', $model->savings_account_id) }}" class="btn btn-sm btn-success btn-active-light-success">
        Validasi
    </a>
    @endif --}}
</td>