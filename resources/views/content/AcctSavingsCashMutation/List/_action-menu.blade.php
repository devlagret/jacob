<td class="text-end">
    <a href="{{ route('savings-cash-mutation.print-note', $model->savings_cash_mutation_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
        Kwitansi
    </a>
    {{-- @if($model->validation == 0)
    <a href="{{ route('savings-cash-mutation.validation', $model->savings_cash_mutation_id) }}" class="btn btn-sm btn-success btn-active-light-success">
        Validasi
    </a>
    @endif --}}
</td>