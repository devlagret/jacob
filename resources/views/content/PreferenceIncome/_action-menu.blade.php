    <td class="text-center">
        @if (!$model->income_status)
        <a type="button" href="{{ route('preference-income.delete', $model->income_id) }}" class="btn btn-sm btn-danger btn-active-light-danger">
            Hapus
        </a>
        @endif
    </td>