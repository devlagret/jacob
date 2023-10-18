<td class="text-end">
    <a href="{{ route('savings-account.print-note', $model->savings_account_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
        Kwitansi
    </a>
    @if ($model->unblock_state != 1 && collect(['25','26','27'])->contains($model->savings_id))
    <a href="{{ route('savings-account.unblock', $model->savings_account_id) }}" class="btn mt-2 btn-sm btn-warning btn-active-light-primary">
        Unblokir
    </a>
    @endif
    {{-- @if($model->validation == 0)
    <a href="{{ route('savings-account.validation', $model->savings_account_id) }}" class="btn btn-sm btn-success btn-active-light-success">
        Validasi
    </a>
    @endif --}}
</td>