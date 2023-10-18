<td class="text-center">
    <input type="hidden" name="data[{{$model->income_id}}][income_id]" id="income_id_table_{{$model->income_id}}" value="{{$model->income_id}}">
    <select name="data[{{$model->income_id}}][account_id]" id="account_id_table_{{$model->income_id}}" aria-label="{{ __('No. Perkiraan') }}" data-control="select2" data-placeholder="{{ __('Pilih No. Perkiraan..') }}" data-allow-clear="true" class="form-select form-select-solid form-select-lg select-acc" onchange="function_elements_add(this.name, this.value)">
        <option value="">{{ __('Pilih No. Perkiraan...') }}</option>
        @foreach($akun as $key => $value)
        <option data-kt-flag="{{ $key }}" value="{{ $key }}" {{ $key === old('account_id', $sessiondata['account_id'] ?? $model->account_id) ? 'selected' :'' }}>{{ $value }}</option>
        @endforeach
    </select>
</td>