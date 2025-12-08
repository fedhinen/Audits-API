<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CatalogService;
use App\Models\Tool;
use App\Models\ProductionLine;
use Illuminate\Validation\Rule;

class ToolWebController extends Controller
{
    protected CatalogService $catalog;

    public function __construct(CatalogService $catalog)
    {
        $this->catalog = $catalog;
    }

    public function index(Request $request)
    {
        $tools = $this->catalog->listTools(['per_page' => 100]);
        return view('supervisor.tools.index', compact('tools'));
    }

    public function create()
    {
        $lines = ProductionLine::active()->get();
        return view('supervisor.tools.create', compact('lines'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\-]+$/',
                'unique:tools,code'
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100'
            ],
            'model' => [
                'nullable',
                'string',
                'max:100'
            ],
            'line_id' => [
                'nullable',
                'exists:production_lines,id'
            ],
            'active' => 'nullable|boolean'
        ], [
            'code.required' => 'El código es obligatorio.',
            'code.regex' => 'El código solo puede contener letras, números y guiones.',
            'code.unique' => 'Ya existe una herramienta con este código.',
            'name.required' => 'El nombre es obligatorio.',
            'name.min' => 'El nombre debe tener al menos 3 caracteres.',
        ]);

        $data['active'] = $request->has('active');

        $this->catalog->createTool($data);

        return redirect()
            ->route('supervisor.tools.index')
            ->with('success', 'Herramienta creada correctamente.');
    }


    public function edit(Tool $tool)
    {
        $lines = ProductionLine::active()->get();
        return view('supervisor.tools.edit', compact('tool', 'lines'));
    }

    public function update(Request $request, Tool $tool)
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9\-]+$/',
                Rule::unique('tools', 'code')->ignore($tool->id)
            ],
            'name' => [
                'required',
                'string',
                'min:3',
                'max:100'
            ],
            'model' => [
                'nullable',
                'string',
                'max:100'
            ],
            'line_id' => [
                'nullable',
                'exists:production_lines,id'
            ],
            'active' => 'nullable|boolean'
        ]);

        $data['active'] = $request->has('active');

        $this->catalog->updateTool($tool, $data);

        return redirect()
            ->route('supervisor.tools.index')
            ->with('success', 'Herramienta actualizada correctamente.');
    }


    public function destroy(Tool $tool)
    {
        $this->catalog->deleteTool($tool);
        return redirect()->route('supervisor.tools.index')->with('success', 'Herramienta eliminada.');
    }
}
