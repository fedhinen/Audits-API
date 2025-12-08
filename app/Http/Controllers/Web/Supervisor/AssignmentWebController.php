<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AssignmentService;
use App\Models\Assignment;
use App\Models\User;
use App\Models\ProductionLine;
use App\Models\Tool;

class AssignmentWebController extends Controller
{
    protected AssignmentService $assignment;

    public function __construct(AssignmentService $assignment)
    {
        $this->assignment = $assignment;
    }

    public function index(Request $request)
    {
        $assignments = $this->assignment->list(['per_page' => 100]);
        return view('supervisor.assignments.index', compact('assignments'));
    }

    public function create()
    {
        $technicians = User::technicians()->active()->get();
        $lines = ProductionLine::active()->get();
        $tools = collect();

        return view('supervisor.assignments.create', compact('technicians', 'lines', 'tools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'technician_id' => [
                'required',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    if (!User::where('id', $value)->where('role', 'technician')->where('active', true)->exists()) {
                        $fail('El técnico seleccionado no es válido o está inactivo.');
                    }
                }
            ],
            'line_id' => 'required|exists:production_lines,id',
            'shift'   => 'required|in:A,B,C',
            'assigned_at' => 'required|date',
            'due_at'  => 'nullable|date|after_or_equal:assigned_at',
            'notes'   => 'nullable|string|max:500',
            'tools'   => 'nullable|array',
            'tools.*' => 'exists:tools,id',
        ]);

        $data['supervisor_id'] = auth()->id();

        $assignment = $this->assignment->create($data);

        $assignment->tools()->sync($request->input('tools', []));

        return redirect()->route('supervisor.assignments.index')
            ->with('success', 'Asignación creada correctamente.');
    }


    public function edit(Assignment $assignment)
    {
        $technicians = User::technicians()->active()->get();
        $lines = ProductionLine::active()->get();

        $tools = Tool::where('line_id', $assignment->line_id)
                    ->where('active', true)
                    ->get();

        $toolsSelected = $assignment->tools()
            ->pluck('tools.id')
            ->toArray();

        return view('supervisor.assignments.edit', compact(
            'assignment', 'technicians', 'lines', 'tools', 'toolsSelected'
        ));
    }

    public function update(Request $request, Assignment $assignment)
    {
        $data = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'line_id'       => 'required|exists:production_lines,id',
            'shift'         => 'required|in:A,B,C',
            'assigned_at'   => 'required|date',
            'due_at'        => 'nullable|date|after_or_equal:assigned_at',
            'notes'         => 'nullable|string|max:500',
            'tools'         => 'nullable|array',
            'tools.*'       => 'exists:tools,id',
        ]);

        // IMPORTANTE: pasar también las tools al servicio
        $data['tools'] = $request->input('tools', []);

        $this->assignment->update($assignment, $data);

        return redirect()
            ->route('supervisor.assignments.index')
            ->with('success', 'Asignación actualizada correctamente.');
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();

        return redirect()->route('supervisor.assignments.index')->with('success', 'Asignación eliminada.');
    }
}
