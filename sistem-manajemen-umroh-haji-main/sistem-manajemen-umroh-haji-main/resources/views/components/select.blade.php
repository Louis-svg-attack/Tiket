@props([
    'label' => null,
    'name',
    'options' => [],      // [value => label]
    'selected' => null,
    'placeholder' => '— Pilih —',
    'required' => false,
    'hint' => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-1">
            {{ $label }} @if($required)<span class="text-red-500">*</span>@endif
        </label>
    @endif

    <select id="{{ $name }}" name="{{ $name }}"
        {{ $attributes->except('class')->merge(['class' => 'w-full rounded-lg border-gray-300 focus:border-brand focus:ring-brand text-sm']) }}
        @if($required) required @endif>
        @if($placeholder !== false)
            <option value="">{{ $placeholder }}</option>
        @endif
        @foreach($options as $val => $text)
            <option value="{{ $val }}" @selected((string) old($name, $selected) === (string) $val)>{{ $text }}</option>
        @endforeach
    </select>

    @if($hint)<p class="text-xs text-gray-400 mt-1">{{ $hint }}</p>@endif
    @error($name)<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
</div>
