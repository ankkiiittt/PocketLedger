@props([
    'name',
    'label',
    'type' => 'text',
    'value' => null,
    'error' => null,
    'required' => false,
    'help' => null,
    'suffix' => null,
    'inputmode' => null,
    'id' => null,
    'placeholder' => null,
])

@php
    $id = $id ?? $name;
    $errorMessage = $error ?? $errors->first($name);
    $hasError = !empty($errorMessage);
    $errorId = $hasError ? "{$id}-error" : null;
    $helpId = $help ? "{$id}-help" : null;
    $describedBy = trim("{$errorId} {$helpId}");
@endphp

<div class="form-field {{ $hasError ? 'has-error' : '' }}">
    @if ($type !== 'checkbox')
        <label for="{{ $id }}" class="form-label">
            <span>
                {{ $label }}
                @if ($required)
                    <span class="required-mark" aria-hidden="true">*</span>
                @endif
            </span>
        </label>
    @endif

    @if ($type === 'checkbox')
        <label class="form-checkbox-label" for="{{ $id }}">
            <input
                type="checkbox"
                name="{{ $name }}"
                id="{{ $id }}"
                value="{{ $value ?? '1' }}"
                {{ $attributes->merge(['class' => 'form-checkbox']) }}
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($hasError) aria-invalid="true" @endif
            >
            <span>{{ $label }}</span>
        </label>
    @elseif ($type === 'textarea')
        <textarea
            name="{{ $name }}"
            id="{{ $id }}"
            @if ($required) required @endif
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            @if ($hasError) aria-invalid="true" @endif
            {{ $attributes->merge(['class' => 'form-textarea']) }}
        >{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        <div class="input-wrapper">
            <select
                name="{{ $name }}"
                id="{{ $id }}"
                @if ($required) required @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($hasError) aria-invalid="true" @endif
                {{ $attributes->merge(['class' => 'form-select']) }}
            >
                {{ $slot }}
            </select>
        </div>
    @elseif (isset($slot) && $slot->isNotEmpty())
        <div class="input-wrapper {{ $suffix ? 'input-with-suffix' : '' }}">
            {{ $slot }}
            @if ($suffix)
                <span class="input-suffix" aria-hidden="true">{{ $suffix }}</span>
            @endif
        </div>
    @else
        <div class="input-wrapper {{ $suffix ? 'input-with-suffix' : '' }}">
            <input
                type="{{ $type }}"
                name="{{ $name }}"
                id="{{ $id }}"
                value="{{ old($name, $value) }}"
                @if ($required) required @endif
                @if ($inputmode) inputmode="{{ $inputmode }}" @endif
                @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                @if ($hasError) aria-invalid="true" @endif
                {{ $attributes->merge(['class' => 'form-input' . ($inputmode === 'decimal' ? ' input-amount' : '')]) }}
            >
            @if ($suffix)
                <span class="input-suffix" aria-hidden="true">{{ $suffix }}</span>
            @endif
        </div>
    @endif

    @if ($help && !$hasError)
        <p id="{{ $helpId }}" class="field-help">{{ $help }}</p>
    @endif

    @if ($hasError)
        <div id="{{ $errorId }}" class="field-error" role="alert">
            <x-icon name="alert-circle" size="14" />
            <span>{{ $errorMessage }}</span>
        </div>
    @endif
</div>
