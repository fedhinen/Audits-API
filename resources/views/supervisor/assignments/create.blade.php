@extends('layouts.supervisor')

@section('title', 'Nueva asignación')

@section('content')
<h1 class="text-xl font-bold mb-4">Nueva asignación</h1>

@if($errors->any())
    <div class="bg-red-500 text-white p-3 rounded mb-4">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST"
      action="{{ route('supervisor.assignments.store') }}"
      class="space-y-4"
      onsubmit="this.querySelector('button[type=submit]').disabled = true;">
@csrf

    {{-- Técnico --}}
    <div>
        <label class="block font-medium">Técnico *</label>
        <select name="technician_id"
                class="w-full border p-2 rounded @error('technician_id') border-red-500 @enderror"
                required>
            <option value="">Seleccione...</option>
            @foreach($technicians as $t)
                <option value="{{ $t->id }}" {{ old('technician_id') == $t->id ? 'selected' : '' }}>
                    {{ $t->name }} ({{ $t->email }})
                </option>
            @endforeach
        </select>

        @error('technician_id')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Línea --}}
    <div>
        <label class="block font-medium">Línea *</label>
        <select name="line_id"
                class="w-full border p-2 rounded @error('line_id') border-red-500 @enderror"
                required
                onchange="this.form.submit()">
            <option value="">Seleccione...</option>

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

    {{-- Tools dinámicas --}}
    @if(old('line_id'))
        @php
            $tools = \App\Models\Tool::where('line_id', old('line_id'))
                ->where('active', true)->get();
        @endphp

        <div>
            <label class="block font-medium">Herramientas a auditar</label>
            <div class="grid grid-cols-2 gap-2">

                @foreach($tools as $tool)
                    <label class="flex items-center gap-2">
                        <input type="checkbox"
                               name="tools[]"
                               value="{{ $tool->id }}"
                               {{ in_array($tool->id, old('tools', [])) ? 'checked' : '' }}>
                        {{ $tool->name }}
                    </label>
                @endforeach

            </div>

            @error('tools')
                <p class="text-red-600 text-sm">{{ $message }}</p>
            @enderror
        </div>
    @endif

    {{-- Shift --}}
    <div>
        <label class="block font-medium">Turno *</label>
        <select name="shift"
                required
                class="w-full border p-2 rounded @error('shift') border-red-500 @enderror">
            <option value="A" {{ old('shift') == 'A' ? 'selected' : '' }}>Mañana</option>
            <option value="B" {{ old('shift') == 'B' ? 'selected' : '' }}>Tarde</option>
            <option value="C" {{ old('shift') == 'C' ? 'selected' : '' }}>Noche</option>
        </select>

        @error('shift')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Fechas --}}
    <div>
        <label class="block font-medium">Fecha asignación *</label>
        <input type="date"
               name="assigned_at"
               class="w-full border p-2 rounded @error('assigned_at') border-red-500 @enderror"
               value="{{ old('assigned_at') }}"
               required>

        @error('assigned_at')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-medium">Fecha límite</label>
        <input type="date"
               name="due_at"
               class="w-full border p-2 rounded @error('due_at') border-red-500 @enderror"
               value="{{ old('due_at') }}">

        @error('due_at')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Notes --}}
    <div>
        <label class="block font-medium">Notas</label>
        <textarea
            name="notes"
            maxlength="500"
            class="w-full border p-2 rounded @error('notes') border-red-500 @enderror"
            oninvalid="this.setCustomValidity('Máximo 500 caracteres.')"
            oninput="setCustomValidity('')">{{ old('notes') }}</textarea>

        @error('notes')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit"
            class="bg-green-600 text-white px-4 py-2 rounded">
        Guardar
    </button>
</form>
@endsection
