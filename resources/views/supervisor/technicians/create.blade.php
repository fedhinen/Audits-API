@extends('layouts.supervisor')

@section('title', 'Registrar Técnico')

@section('content')
<h1 class="text-2xl font-bold mb-4">Registrar Técnico</h1>

@if($errors->any())
    <div class="bg-red-500/80 text-white p-3 rounded mb-4">
        {{ $errors->first() }}
    </div>
@endif

<form method="POST"
      action="{{ route('supervisor.technicians.store') }}"
      class="space-y-4 bg-white p-4 rounded shadow"
      onsubmit="this.querySelector('button[type=submit]').disabled=true;">
    @csrf

    {{-- NAME --}}
    <div>
        <label class="block font-medium">Nombre *</label>
        <input type="text"
               name="name"
               value="{{ old('name') }}"
               class="w-full border p-2 rounded @error('name') border-red-500 @enderror"
               required
               minlength="3"
               maxlength="255"
               oninvalid="this.setCustomValidity('Ingresa un nombre válido (mínimo 3 caracteres).')"
               oninput="setCustomValidity('')">

        @error('name')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- EMPLOYEE NUMBER --}}
    <div>
        <label class="block font-medium">Número de empleado *</label>
        <input type="text"
               name="employee_number"
               value="{{ old('employee_number') }}"
               class="w-full border p-2 rounded @error('employee_number') border-red-500 @enderror"
               required
               maxlength="20"
               pattern="^[A-Za-z0-9\-]+$"
               oninvalid="this.setCustomValidity('Solo letras, números y guiones. Máximo 20 caracteres.')"
               oninput="setCustomValidity('')">

        @error('employee_number')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- EMAIL --}}
    <div>
        <label class="block font-medium">Correo electrónico (opcional)</label>
        <input type="email"
               name="email"
               value="{{ old('email') }}"
               class="w-full border p-2 rounded @error('email') border-red-500 @enderror"
               maxlength="255">

        @error('email')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    {{-- PASSWORD --}}
    <div>
        <label class="block font-medium">Contraseña *</label>
        <input type="password"
               name="password"
               class="w-full border p-2 rounded @error('password') border-red-500 @enderror"
               required
               minlength="6"
               oninvalid="this.setCustomValidity('La contraseña debe tener mínimo 6 caracteres.')"
               oninput="setCustomValidity('')">

        @error('password')
            <p class="text-red-600 text-sm">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">
        Guardar técnico
    </button>
</form>
@endsection
