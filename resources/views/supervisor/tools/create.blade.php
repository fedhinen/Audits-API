@extends('layouts.supervisor')

@section('title', 'Crear herramienta')

@section('content')
    <h1 class="text-xl font-bold mb-4">Nueva herramienta</h1>

    @if($errors->any())
        <div class="bg-red-500/80 text-white p-3 rounded mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('supervisor.tools.store') }}"
          class="space-y-4"
          onsubmit="this.querySelector('button[type=submit]').disabled = true;">
        @csrf

        {{-- CÓDIGO --}}
        <div>
            <label class="block font-semibold">Código *</label>
            <input type="text"
                   name="code"
                   value="{{ old('code') }}"
                   class="w-full border p-2 rounded @error('code') border-red-500 @enderror"
                   required
                   maxlength="20"
                   pattern="^[A-Za-z0-9\-]+$"
                   oninvalid="this.setCustomValidity('Solo letras, números y guiones. Máximo 20 caracteres.')"
                   oninput="setCustomValidity('')">

            @error('code')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- NOMBRE --}}
        <div>
            <label class="block font-semibold">Nombre *</label>
            <input type="text"
                   name="name"
                   value="{{ old('name') }}"
                   class="w-full border p-2 rounded @error('name') border-red-500 @enderror"
                   required
                   minlength="3"
                   maxlength="100"
                   oninvalid="this.setCustomValidity('El nombre debe tener entre 3 y 100 caracteres.')"
                   oninput="setCustomValidity('')">

            @error('name')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- MODELO --}}
        <div>
            <label class="block font-semibold">Modelo</label>
            <input type="text"
                   name="model"
                   value="{{ old('model') }}"
                   class="w-full border p-2 rounded @error('model') border-red-500 @enderror"
                   maxlength="100">

            @error('model')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- LÍNEA ASIGNADA --}}
        <div>
            <label class="block font-semibold">Línea de producción</label>
            <select
                name="line_id"
                class="w-full border p-2 rounded @error('line_id') border-red-500 @enderror">
                <option value="">— Ninguna —</option>

                @foreach($lines as $line)
                    <option value="{{ $line->id }}" {{ old('line_id') == $line->id ? 'selected' : '' }}>
                        {{ $line->name }}
                    </option>
                @endforeach
            </select>

            @error('line_id')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- ACTIVO --}}
        <div>
            <input type="hidden" name="active" value="0">

            <label class="flex items-center">
                <input type="checkbox"
                       name="active"
                       value="1"
                       {{ old('active', true) ? 'checked' : '' }}>
                <span class="ml-2">Activa</span>
            </label>
        </div>

        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
            Guardar
        </button>
    </form>
@endsection
