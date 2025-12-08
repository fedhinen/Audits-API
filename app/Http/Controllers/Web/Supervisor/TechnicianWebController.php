<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TechnicianWebController extends Controller
{
    public function index()
    {
        $technicians = User::technicians()->latest()->get();
        return view('supervisor.technicians.index', compact('technicians'));
    }

    public function create()
    {
        return view('supervisor.technicians.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255'
            ],
            'employee_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\-]+$/',
                'unique:users,employee_number'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email'
            ],
            'password' => [
                'required',
                'string',
                'min:6'
            ],
        ], [
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
            'employee_number.regex' => 'El número de empleado solo puede contener letras, números y guiones.',
        ]);

        $data['role'] = 'technician';
        $data['active'] = true;
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()
            ->route('supervisor.technicians.index')
            ->with('success', 'Técnico registrado correctamente.');
    }


    public function edit(User $technician)
    {
        // Solo permitir edición de usuarios con rol 'technician'
        if ($technician->role !== 'technician') {
            abort(403, 'Este usuario no es un técnico.');
        }

        return view('supervisor.technicians.edit', compact('technician'));
    }

    public function update(Request $request, User $technician)
    {
        if ($technician->role !== 'technician') {
            abort(403, 'Este usuario no es un técnico.');
        }

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255'
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($technician->id)
            ],
            'employee_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\-]+$/',
                Rule::unique('users', 'employee_number')->ignore($technician->id)
            ],
            'active' => [
                'required',
                'boolean'
            ]
        ]);

        $technician->update($data);

        return redirect()
            ->route('supervisor.technicians.index')
            ->with('success', 'Técnico actualizado correctamente.');
    }


    public function destroy(User $technician)
    {
        $technician->delete();
        return redirect()->back()->with('success', 'Técnico eliminado.');
    }
}
