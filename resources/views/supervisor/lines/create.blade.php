@extends('layouts.supervisor')

@section('title', 'Crear línea')

@section('content')
<h1 class="text-xl font-bold mb-4">Nueva línea de producción</h1>

@if($errors->any())
    <div class="bg-red-500/80 text-white p-3 rounded mb-4">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST"
      action="{{ route('supervisor.lines.store') }}"
      class="space-y-4"
      onsubmit="this.querySelector('button[type=submit]').disabled=true;">
    @csrf

    {{-- CODE --}}
    <div>
        <label class="block font-semibold">Código *</label>
        <input type="text"
               name="code"
               value="{{ old('code') }}"
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
               value="{{ old('name') }}"
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
               value="{{ old('area') }}"
               class="w-full border p-2 rounded @error('area') border-red-500 @enderror"
               maxlength="100"
               oninvalid="this.setCustomValidity('Máximo 100 caracteres.')"
               oninput="setCustomValidity('')">

        @error('area')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
        @enderror
    </div>

    {{-- ACTIVE --}}
    <div class="flex items-center gap-2">
        <label class="flex items-center">
            <input type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
            <span class="ml-2">Activa</span>
        </label>
    </div>

    <button type="submit"
            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
        Guardar
    </button>
</form>
@endsection
