<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Segmentacao;
use App\Models\SegmentacaoCliente;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SegmentacaoClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $segmentacoesCliente = SegmentacaoCliente::with(['users', 'segmentoProduto'])->get();
        return view('admin.segmentacao-cliente.index', compact('segmentacoesCliente'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $segmentacoes = Segmentacao::where('active', 1)->orderBy('segmento')->get();
        return view('admin.segmentacao-cliente.create', compact('segmentacoes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nome' => 'required|max:255',
            'descricao' => 'nullable|string',
            'produtos_segmentos' => 'nullable|exists:segmentacao,id',
            'linha' => 'nullable|string|max:255',
            'active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['nome']);
        $validated['active'] = $request->has('active');

        SegmentacaoCliente::create($validated);

        return redirect()->route('admin.segmentacao-cliente.index')
            ->with('success', 'Segmentação de cliente criada com sucesso.');
    }

    /**
     * Display the specified resource.
     */
    public function show(SegmentacaoCliente $segmentacaoCliente)
    {
        $segmentacaoCliente->load(['users', 'segmentoProduto']);
        return view('admin.segmentacao-cliente.show', compact('segmentacaoCliente'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SegmentacaoCliente $segmentacaoCliente)
    {
        $segmentacoes = Segmentacao::where('active', 1)->orderBy('segmento')->get();
        return view('admin.segmentacao-cliente.edit', compact('segmentacaoCliente', 'segmentacoes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SegmentacaoCliente $segmentacaoCliente)
    {
        $validated = $request->validate([
            'nome' => 'required|max:255',
            'descricao' => 'nullable|string',
            'produtos_segmentos' => 'nullable|exists:segmentacao,id',
            'linha' => 'nullable|string|max:255',
            'active' => 'boolean'
        ]);

        $validated['slug'] = Str::slug($validated['nome']);
        $validated['active'] = $request->has('active');

        $segmentacaoCliente->update($validated);

        return redirect()->route('admin.segmentacao-cliente.index')
            ->with('success', 'Segmentação de cliente atualizada com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SegmentacaoCliente $segmentacaoCliente)
    {
        $segmentacaoCliente->delete();

        return redirect()->route('admin.segmentacao-cliente.index')
            ->with('success', 'Segmentação de cliente removida com sucesso.');
    }
}
