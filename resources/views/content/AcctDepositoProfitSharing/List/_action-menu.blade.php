<td class="text-end">
    @if($model->deposito_profit_sharing_status == 0 && $model->deposito_account_status == 0)
    <a href="{{ route('deposito-profit-sharing.update', $model->deposito_profit_sharing_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
        Proses Bunga
    </a>
    @endif
</td>