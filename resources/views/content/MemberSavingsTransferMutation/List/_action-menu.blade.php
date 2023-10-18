@if ($model->validation == 0)
    <td class="text-end">
        <a type="button" href="{{ route('member-savings-transfer-mutation.validation', $model->member_transfer_mutation_id) }}" class="btn btn-sm btn-success btn-active-light-success">
            Validasi
        </a>
    </td>
@else
    <td class="text-end">
        <a type="button" href="{{ route('member-savings-transfer-mutation.print-mutation', $model->member_transfer_mutation_id) }}" class="btn btn-sm btn-info btn-active-light-info">
            Kwitansi
        </a>
    </td>
@endif