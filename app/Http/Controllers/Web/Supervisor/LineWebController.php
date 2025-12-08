<?php

namespace App\Http\Controllers\Web\Supervisor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\CatalogService;
use App\Models\ProductionLine;
use Illuminate\Validation\Rule;

class LineWebController extends Controller
{
    protected CatalogService $catalog;

    public function __construct(CatalogService $catalog)
    {
        $this->catalog = $catalog;
    }

    public function index(Request $request)
    {
        $lines = $this->catalog->listLines(['per_page' => 100]);
        return view('supervisor.lines.index', compact('lines'));
    }

    public function create()
    {
        return view('supervisor.lines.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'  => [
                'required',
                'string',
                'min:6',
                'max:20',
                'regex:/^[A-Za-z0-9\-]+$/',
                'unique:production_lines,code'
            ],
            'name'  => [
                'required',
                'string',
                'max:100'
            ],
            'area'  => [
                'nullable',
                'string',
                'max:100'
            ],
            'active' => 'nullable|boolean'
        ], [
            'code.required' => 'El código es obligatorio.',
            'code.regex'    => 'El código solo puede contener letras, números y guiones.',
            'code.unique'   => 'Ya existe una línea con este código.',
            'name.required' => 'El nombre de la línea es obligatorio.',
        ]);

        // Checkbox puede no venir → normalizar
        $data['active'] = $request->has('active');

        $this->catalog->createLine($data);

        return redirect()
            ->route('supervisor.lines.index')
            ->with('success', 'Línea creada correctamente.');
    }

    public function edit(ProductionLine $line)
    {
        return view('supervisor.lines.edit', compact('line'));
    }

    public function update(Request $request, ProductionLine $line)
    {
        $data = $request->validate([
            'code'  => [
                'required',
                'string',
                'min:6',
                'max:20',
                'regex:/^[A-Za-z0-9\-]+$/',
                Rule::unique('production_lines', 'code')->ignore($line->id)
            ],
            'name'  => [
                'required',
                'string',
                'max:100'
            ],
            'area'  => [
                'nullable',
                'string',
                'max:100'
            ],
            'active' => 'nullable|boolean'
        ]);

        $data['active'] = $request->has('active');

        $this->catalog->updateLine($line, $data);

        return redirect()
            ->route('supervisor.lines.index')
            ->with('success', 'Línea actualizada correctamente.');
    }

    public function destroy(ProductionLine $line)
    {
        $this->catalog->deleteLine($line);
        return redirect()->route('supervisor.lines.index')->with('success', 'Línea eliminada.');
    }
}
