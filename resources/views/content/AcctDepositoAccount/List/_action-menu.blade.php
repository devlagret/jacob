<td class="text-end">
    <a href="{{ route('deposito-account.print-note', $model->deposito_account_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
        Kwitansi
    </a>
    @if($model->validation == 0)
        <a href="{{ route('deposito-account.validation', $model->deposito_account_id) }}" class="btn btn-sm btn-success btn-active-light-success">
            Validasi
        </a>
    @else
        <a href="{{ route('deposito-account.print-certificate-front', $model->deposito_account_id) }}" class="btn btn-sm btn-warning btn-active-light-warning">
            Cetak Depan
        </a>
        <a href="{{ route('deposito-account.print-certificate-back', $model->deposito_account_id) }}" class="btn btn-sm btn-info btn-active-light-info">
            Cetak Belakang
        </a>
    @endif
</td>