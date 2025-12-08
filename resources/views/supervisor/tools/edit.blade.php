@extends('layouts.supervisor')

@section('title', 'Editar herramienta')

@section('content')
    <h1 class="text-xl font-bold mb-4">Editar herramienta: {{ $tool->name }}</h1>

    @if($errors->any())
        <div class="bg-red-500/80 text-white p-3 rounded mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST"
          action="{{ route('supervisor.tools.update', $tool->id) }}"
          class="space-y-4"
          onsubmit="this.querySelector('button[type=submit]').disabled = true;">
        @csrf
        @method('PUT')

        {{-- CÓDIGO --}}
        <div>
            <label class="block font-semibold">Código *</label>
            <input type="text"
                   name="code"
                   value="{{ old('code', $tool->code) }}"
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
                   value="{{ old('name', $tool->name) }}"
                   class="w-full border p-2 rounded @error('name') border-red-500 @enderror"
                   required
                   minlength="3"
                   maxlength="100">
            
            @error('name')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- MODELO --}}
        <div>
            <label class="block font-semibold">Modelo</label>
            <input type="text"
                   name="model"
                   value="{{ old('model', $tool->model) }}"
                   class="w-full border p-2 rounded @error('model') border-red-500 @enderror"
                   maxlength="100">

            @error('model')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>

        {{-- LÍNEA --}}
        <div>
            <label class="block font-semibold">Línea de producción</label>
            <select name="line_id"
                    class="w-full border p-2 rounded @error('line_id') border-red-500 @enderror">

                <option value="">— Ninguna —</option>

                @foreach($lines as $line)
                    <option value="{{ $line->id }}"
                        {{ old('line_id', $tool->line_id) == $line->id ? 'selected' : '' }}>
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
                       {{ old('active', $tool->active) ? 'checked' : '' }}>
                <span class="ml-2">Activa</span>
            </label>
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
            Actualizar
        </button>
    </form>
@endsection
