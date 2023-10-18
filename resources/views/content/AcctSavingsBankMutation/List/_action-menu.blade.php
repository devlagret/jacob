<td class="text-end">
    <a href="{{ route('savings-bank-mutation.print-note', $model->savings_bank_mutation_id) }}" class="btn btn-sm btn-primary btn-active-light-primary">
        Kwitansi
    </a>
    {{-- @if($model->validation == 0)
    <a href="{{ route('savings-bank-mutation.validation', $model->savings_bank_mutation_id) }}" class="btn btn-sm btn-success btn-active-light-success">
        Validasi
    </a>
    @endif --}}
</td>