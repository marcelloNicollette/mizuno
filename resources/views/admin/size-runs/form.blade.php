@extends('layouts.admin-layout')

@section('page_title', 'Mizuno - Size Run')

@section('content-wrapper')
    @php
        $isEditing = $sizeRun->exists;
        $rows = old('items');

        if ($rows === null) {
            $rows = $isEditing
                ? $sizeRun->items->map(fn ($item) => [
                    'left_value' => $item->left_value,
                    'right_value' => $item->right_value,
                    'sort_order' => $item->sort_order,
                ])->toArray()
                : [
                    ['left_value' => '', 'right_value' => '', 'sort_order' => 1],
                    ['left_value' => '', 'right_value' => '', 'sort_order' => 2],
                ];
        }
    @endphp

    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center space-x-2">
            <svg class="w-8 h-8 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 7h16M4 12h16M4 17h16M8 4v16m8-16v16" />
            </svg>
            <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
                {{ $isEditing ? 'Editar Size Run' : 'Novo Size Run' }}
            </h2>
        </div>

        <a href="{{ route('admin.size-runs.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            Voltar para listagem
        </a>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
            <form method="POST" action="{{ $isEditing ? route('admin.size-runs.update', $sizeRun) : route('admin.size-runs.store') }}">
                @csrf
                @if ($isEditing)
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Nome interno</label>
                        <input type="text" name="name" id="name"
                            value="{{ old('name', $sizeRun->name) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700">Titulo exibido</label>
                        <input type="text" name="title" id="title"
                            value="{{ old('title', $sizeRun->title ?: 'Size Run') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                        @error('title')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="size_label_left" class="block text-sm font-medium text-gray-700">Coluna esquerda</label>
                        <input type="text" name="size_label_left" id="size_label_left"
                            value="{{ old('size_label_left', $sizeRun->size_label_left ?: 'BR SIZE') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                        @error('size_label_left')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="size_label_right" class="block text-sm font-medium text-gray-700">Coluna direita</label>
                        <input type="text" name="size_label_right" id="size_label_right"
                            value="{{ old('size_label_right', $sizeRun->size_label_right ?: 'US SIZE') }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                            required>
                        @error('size_label_right')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="sort_order" class="block text-sm font-medium text-gray-700">Ordem</label>
                        <input type="number" min="0" name="sort_order" id="sort_order"
                            value="{{ old('sort_order', $sizeRun->sort_order ?? 0) }}"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @error('sort_order')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="active" class="block text-sm font-medium text-gray-700">Status</label>
                        <select name="active" id="active"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="1" {{ old('active', $sizeRun->active ?? true) ? 'selected' : '' }}>Ativo</option>
                            <option value="0" {{ !old('active', $sizeRun->active ?? true) ? 'selected' : '' }}>Inativo</option>
                        </select>
                        @error('active')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label for="note" class="block text-sm font-medium text-gray-700">Observacao</label>
                    <input type="text" name="note" id="note"
                        value="{{ old('note', $sizeRun->note) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                        placeholder="Ex.: For the selected color only.">
                    @error('note')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-8 border border-gray-200 rounded-lg overflow-hidden">
                    <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <div>
                            <h3 class="text-sm font-semibold text-gray-800">Linhas da tabela</h3>
                            <p class="text-xs text-gray-500">Cadastre os pares de tamanho que serao exibidos no Size Run.</p>
                        </div>
                        <button type="button" id="add-size-run-row"
                            class="inline-flex items-center px-3 py-2 bg-blue-500 hover:bg-blue-600 text-white text-sm font-medium rounded-md">
                            Adicionar linha
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-white">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor esquerdo</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Valor direito</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordem</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acao</th>
                                </tr>
                            </thead>
                            <tbody id="size-run-rows" class="bg-white divide-y divide-gray-200">
                                @foreach ($rows as $index => $row)
                                    <tr>
                                        <td class="px-4 py-3">
                                            <input type="text" name="items[{{ $index }}][left_value]"
                                                value="{{ $row['left_value'] ?? '' }}"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Ex.: 34">
                                        </td>
                                        <td class="px-4 py-3">
                                            <input type="text" name="items[{{ $index }}][right_value]"
                                                value="{{ $row['right_value'] ?? '' }}"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                                placeholder="Ex.: 5.5">
                                        </td>
                                        <td class="px-4 py-3 w-32">
                                            <input type="number" min="0" name="items[{{ $index }}][sort_order]"
                                                value="{{ $row['sort_order'] ?? $index }}"
                                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                        </td>
                                        <td class="px-4 py-3 w-28">
                                            <button type="button" class="remove-size-run-row text-sm text-red-600 hover:text-red-800">
                                                Remover
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @error('items')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror

                <div class="flex justify-end mt-6">
                    <a href="{{ route('admin.size-runs.index') }}"
                        class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded mr-2">Cancelar</a>
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Salvar</button>
                </div>
            </form>
        </div>
    </div>

    <template id="size-run-row-template">
        <tr>
            <td class="px-4 py-3">
                <input type="text" data-name="left_value"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Ex.: 34">
            </td>
            <td class="px-4 py-3">
                <input type="text" data-name="right_value"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="Ex.: 5.5">
            </td>
            <td class="px-4 py-3 w-32">
                <input type="number" min="0" data-name="sort_order"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </td>
            <td class="px-4 py-3 w-28">
                <button type="button" class="remove-size-run-row text-sm text-red-600 hover:text-red-800">
                    Remover
                </button>
            </td>
        </tr>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const rowsContainer = document.getElementById('size-run-rows');
            const addButton = document.getElementById('add-size-run-row');
            const template = document.getElementById('size-run-row-template');
            let rowIndex = rowsContainer.querySelectorAll('tr').length;

            function bindRemoveButtons(scope = document) {
                scope.querySelectorAll('.remove-size-run-row').forEach((button) => {
                    if (button.dataset.bound === 'true') {
                        return;
                    }

                    button.dataset.bound = 'true';
                    button.addEventListener('click', function() {
                        const rows = rowsContainer.querySelectorAll('tr');

                        if (rows.length === 1) {
                            rows[0].querySelectorAll('input').forEach((input) => input.value = '');
                            return;
                        }

                        button.closest('tr').remove();
                    });
                });
            }

            addButton.addEventListener('click', function() {
                const fragment = template.content.cloneNode(true);

                fragment.querySelectorAll('[data-name]').forEach((input) => {
                    const field = input.dataset.name;
                    input.name = `items[${rowIndex}][${field}]`;

                    if (field === 'sort_order') {
                        input.value = rowIndex + 1;
                    }
                });

                rowsContainer.appendChild(fragment);
                rowIndex += 1;
                bindRemoveButtons(rowsContainer);
            });

            bindRemoveButtons(rowsContainer);
        });
    </script>
@endpush
