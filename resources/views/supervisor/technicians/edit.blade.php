@extends('layouts.supervisor')

@section('title', 'Editar técnico')

@section('content')
<h1 class="text-2xl font-bold mb-4">Editar Técnico</h1>

@if($errors->any())
    <div class="bg-red-500/80 text-white p-3 rounded mb-4">
        {{ $errors->first() }}
    </div>
@endif

<form action="{{ route('supervisor.technicians.update', $technician->id) }}"
      method="POST"
      class="bg-white p-6 rounded shadow-md space-y-4"
      onsubmit="this.querySelector('button[type=submit]').disabled=true;">
    @csrf
    @method('PUT')

    {{-- NAME --}}
    <div>
        <label class="block mb-1 font-medium">Nombre *</label>
        <input type="text"
               name="name"
               value="{{ old('name', $technician->name) }}"
               class="w-full border p-2 rounded @error('name') border-red-500 @enderror"
               required
               minlength="3"
               maxlength="255">

        @error('name')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- EMAIL --}}
    <div>
        <label class="block mb-1 font-medium">Email</label>
        <input type="email"
               name="email"
               value="{{ old('email', $technician->email) }}"
               class="w-full border p-2 rounded @error('email') border-red-500 @enderror"
               maxlength="255">

        @error('email')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- EMPLOYEE NUMBER --}}
    <div>
        <label class="block mb-1 font-medium">Número de empleado *</label>
        <input type="text"
               name="employee_number"
               value="{{ old('employee_number', $technician->employee_number) }}"
               class="w-full border p-2 rounded @error('employee_number') border-red-500 @enderror"
               required
               maxlength="20"
               pattern="^[A-Za-z0-9\-]+$">

        @error('employee_number')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- ACTIVE --}}
    <div>
        <label class="block mb-1 font-medium">Activo</label>
        <select name="active"
                class="w-full border p-2 rounded @error('active') border-red-500 @enderror"
                required>
            <option value="1" {{ old('active', $technician->active) == 1 ? 'selected' : '' }}>Sí</option>
            <option value="0" {{ old('active', $technician->active) == 0 ? 'selected' : '' }}>No</option>
        </select>

        @error('active')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        Actualizar
    </button>

    <a href="{{ route('supervisor.technicians.index') }}" class="ml-4 text-gray-600 underline">Cancelar</a>
</form>
@endsection
