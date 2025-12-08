@extends('layouts.supervisor')

@section('title', 'Editar asignación')

@section('content')
<h1 class="text-xl font-bold mb-4">Editar asignación</h1>

@if($errors->any())
    <div class="bg-red-500 text-white p-3 rounded mb-4">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST"
      action="{{ route('supervisor.assignments.update', $assignment->id) }}"
      class="space-y-4"
      onsubmit="this.querySelector('button[type=submit]').disabled = true;">
    @csrf
    @method('PUT')

    {{-- Técnico --}}
    <div>
        <label class="block font-medium">Técnico *</label>
        <select name="technician_id"
                class="w-full border p-2 rounded @error('technician_id') border-red-500 @enderror"
                required>
            @foreach($technicians as $t)
                <option value="{{ $t->id }}"
                    {{ old('technician_id', $assignment->technician_id) == $t->id ? 'selected' : '' }}>
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
                onchange="window.location='?line_id='+this.value">
            @foreach($lines as $line)
                <option value="{{ $line->id }}"
                    {{ request('line_id', old('line_id', $assignment->line_id)) == $line->id ? 'selected' : '' }}>
                    {{ $line->name }}
                </option>
            @endforeach
        </select>

        @error('line_id')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Herramientas --}}
    @php
        $selectedLine = request('line_id', old('line_id', $assignment->line_id));

        $dynamicTools = \App\Models\Tool::where('line_id', $selectedLine)
            ->where('active', true)
            ->get();

        $toolsSelected = old('tools', $toolsSelected ?? []);
    @endphp

    <div>
        <label class="block font-medium">Herramientas</label>

        @if($dynamicTools->isEmpty())
            <p class="text-gray-500 text-sm">No hay herramientas activas para esta línea.</p>
        @else
            <div class="grid grid-cols-2 gap-2">
                @foreach($dynamicTools as $tool)
                    <label class="flex items-center gap-2">
                        <input type="checkbox"
                               name="tools[]"
                               value="{{ $tool->id }}"
                               {{ in_array($tool->id, $toolsSelected) ? 'checked' : '' }}>
                        {{ $tool->name }}
                    </label>
                @endforeach
            </div>
        @endif

        @error('tools')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Turno --}}
    <div>
        <label class="block font-medium">Turno *</label>
        <select name="shift"
                class="w-full border p-2 rounded @error('shift') border-red-500 @enderror"
                required>
            <option value="A" {{ old('shift', $assignment->shift) == 'A' ? 'selected' : '' }}>Mañana</option>
            <option value="B" {{ old('shift', $assignment->shift) == 'B' ? 'selected' : '' }}>Tarde</option>
            <option value="C" {{ old('shift', $assignment->shift) == 'C' ? 'selected' : '' }}>Noche</option>
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
               value="{{ old('assigned_at', \Carbon\Carbon::parse($assignment->assigned_at)->toDateString()) }}"
               class="w-full border p-2 rounded @error('assigned_at') border-red-500 @enderror"
               required>

        @error('assigned_at')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="block font-medium">Fecha límite</label>
        <input type="date"
               name="due_at"
               value="{{ old('due_at', optional($assignment->due_at)->toDateString()) }}"
               class="w-full border p-2 rounded @error('due_at') border-red-500 @enderror">

        @error('due_at')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Notas --}}
    <div>
        <label class="block font-medium">Notas</label>
        <textarea name="notes"
                  maxlength="500"
                  class="w-full border p-2 rounded @error('notes') border-red-500 @enderror"
                  oninvalid="this.setCustomValidity('Máximo 500 caracteres.')"
                  oninput="setCustomValidity('')">{{ old('notes', $assignment->notes) }}</textarea>

        @error('notes')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- Submit --}}
    <button type="submit"
            class="bg-blue-600 text-white px-4 py-2 rounded">
        Actualizar
    </button>
</form>

@endsection
