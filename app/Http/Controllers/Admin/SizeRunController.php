<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SizeRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SizeRunController extends Controller
{
    public function index()
    {
        $sizeRuns = SizeRun::withCount('items')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.size-runs.index', compact('sizeRuns'));
    }

    public function create()
    {
        return view('admin.size-runs.form', [
            'sizeRun' => new SizeRun(),
        ]);
    }

    public function store(Request $request)
    {
        [$data, $items] = $this->validatedPayload($request);

        DB::transaction(function () use ($data, $items) {
            $sizeRun = SizeRun::create($data);
            $sizeRun->items()->createMany($items);
        });

        return redirect()->route('admin.size-runs.index')
            ->with('success', 'Size Run criado com sucesso.');
    }

    public function edit(SizeRun $sizeRun)
    {
        $sizeRun->load([
            'items' => fn($query) => $query->orderBy('sort_order')->orderBy('id'),
        ]);

        return view('admin.size-runs.form', compact('sizeRun'));
    }

    public function update(Request $request, SizeRun $sizeRun)
    {
        [$data, $items] = $this->validatedPayload($request, $sizeRun->id);

        DB::transaction(function () use ($sizeRun, $data, $items) {
            $sizeRun->update($data);
            $sizeRun->items()->delete();
            $sizeRun->items()->createMany($items);
        });

        return redirect()->route('admin.size-runs.index')
            ->with('success', 'Size Run atualizado com sucesso.');
    }

    public function destroy(SizeRun $sizeRun)
    {
        $sizeRun->delete();

        return redirect()->route('admin.size-runs.index')
            ->with('success', 'Size Run removido com sucesso.');
    }

    private function validatedPayload(Request $request, ?int $sizeRunId = null): array
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('size_runs', 'name')->ignore($sizeRunId),
            ],
            'title' => ['required', 'string', 'max:120'],
            'size_label_left' => ['required', 'string', 'max:60'],
            'size_label_right' => ['required', 'string', 'max:60'],
            'note' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'active' => ['nullable', 'boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.left_value' => ['nullable', 'string', 'max:30'],
            'items.*.right_value' => ['nullable', 'string', 'max:30'],
            'items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $items = collect($data['items'] ?? [])
            ->map(function (array $item, int $index) {
                $leftValue = trim((string) ($item['left_value'] ?? ''));
                $rightValue = trim((string) ($item['right_value'] ?? ''));

                if ($leftValue === '' && $rightValue === '') {
                    return null;
                }

                if ($leftValue === '' || $rightValue === '') {
                    throw ValidationException::withMessages([
                        'items' => 'Todas as linhas do Size Run precisam ter os dois valores preenchidos.',
                    ]);
                }

                return [
                    'left_value' => $leftValue,
                    'right_value' => $rightValue,
                    'sort_order' => isset($item['sort_order']) && $item['sort_order'] !== ''
                        ? (int) $item['sort_order']
                        : $index,
                ];
            })
            ->filter()
            ->values()
            ->all();

        if (count($items) === 0) {
            throw ValidationException::withMessages([
                'items' => 'Adicione ao menos uma linha valida para o Size Run.',
            ]);
        }

        unset($data['items']);

        $data['sort_order'] = isset($data['sort_order']) ? (int) $data['sort_order'] : 0;
        $data['active'] = $request->boolean('active');

        return [$data, $items];
    }
}
