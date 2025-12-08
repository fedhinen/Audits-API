@extends('layouts.supervisor')

@section('title', 'Editar línea')

@section('content')
<h1 class="text-xl font-bold mb-4">Editar línea: {{ $line->name }}</h1>

@if($errors->any())
    <div class="bg-red-500/80 text-white p-3 rounded mb-4">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST"
      action="{{ route('supervisor.lines.update', $line->id) }}"
      class="space-y-4"
      onsubmit="this.querySelector('button[type=submit]').disabled=true;">
    @csrf
    @method('PUT')

    {{-- CODE --}}
    <div>
        <label class="block font-semibold">Código *</label>
        <input type="text"
               name="code"
               value="{{ old('code', $line->code) }}"
               class="w-full border p-2 rounded @error('code') border-red-500 @enderror"
               required
               maxlength="20"
               pattern="^[A-Za-z0-9\-]+$"
               oninvalid="this.setCustomValidity('El código solo puede contener letras, números y guiones y máximo 20 caracteres.')"
               oninput="setCustomValidity('')">

        @error('code')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- NAME --}}
    <div>
        <label class="block font-semibold">Nombre *</label>
        <input type="text"
               name="name"
               value="{{ old('name', $line->name) }}"
               class="w-full border p-2 rounded @error('name') border-red-500 @enderror"
               required
               minlength="3"
               maxlength="100"
               oninvalid="this.setCustomValidity('El nombre es obligatorio y debe contener al menos 3 caracteres.')"
               oninput="setCustomValidity('')">

        @error('name')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- AREA --}}
    <div>
        <label class="block font-semibold">Área</label>
        <input type="text"
               name="area"
               value="{{ old('area', $line->area) }}"
               class="w-full border p-2 rounded @error('area') border-red-500 @enderror"
               maxlength="100"
               oninvalid="this.setCustomValidity('Máximo 100 caracteres.')"
               oninput="setCustomValidity('')">

        @error('area')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- ACTIVE --}}
    <div>
        <input type="hidden" name="active" value="0">

        <label class="flex items-center">
            <input type="checkbox"
                   name="active"
                   value="1"
                   {{ old('active', $line->active) ? 'checked' : '' }}>
            <span class="ml-2">Activa</span>
        </label>
    </div>

    <button type="submit"
            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
        Actualizar
    </button>
</form>
@endsection
