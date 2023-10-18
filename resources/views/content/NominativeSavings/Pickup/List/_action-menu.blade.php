@if (!$model->pickup_status)
    <td class="text-center">
        <a type="button" href="{{ route('nomv-sv-pickup.add', $model->savings_cash_mutation_id) }}" class="btn btn-sm btn-success btn-active-light-success">
            Proses
        </a>
    </td>
@else
    <td class="text-center">
        Telah Disetorkan
    </td>
@endif