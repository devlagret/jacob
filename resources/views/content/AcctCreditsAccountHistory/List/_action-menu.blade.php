<td class="text-end">
    <a href="{{ route('credits-account-history.detail', $model->credits_account_id) }}" class="btn btn-sm btn-warning btn-active-light-warning">
        Detail
    </a>
    <a href="{{ route('credits-account-history.print-payment-schedule', $model->credits_account_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
        Jadwal Angsuran
    </a>
</td>