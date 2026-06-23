<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Color;
use App\Models\CaracteristicaProduct;
use App\Models\Numeracao;
use App\Models\LinksProduct;
use App\Models\Calendario;
use App\Models\Category;
use App\Models\Collection;
use App\Models\FlagProduct;
use App\Models\Segmentacao;
use App\Models\SegmentacaoCliente;
use App\Models\SizeRun;
use App\Models\Subcategory;
use App\Models\MeasureCategory;
use App\Models\ShoeGrid;
use App\Models\TechnologyCategory;
use App\Models\TechnologyItem;
use App\Models\User;
use App\Services\GoogleSheetsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;

class GoogleSheetController extends Controller
{
    protected $sheetService;

    public function __construct(GoogleSheetsService $sheetService)
    {
        $this->sheetService = $sheetService;
    }

    /**
     * Exibe a página de sincronização
     */
    public function index()
    {
        return view('admin.sync.sync-produtos');
    }
    /**
     * Exibe a página de sincronização
     */
    public function indexRepresentantes()
    {
        return view('admin.sync.sync-representantes');
    }

    public function syncSegmentacaoClienteShow()
    {
        return view('admin.sync.sync-segmentos-cliente');
    }

    

    public function syncSegmentacaoCliente()
    {
        $spreadsheetId = '1HZ3QtWU0ZWFlMfmUIn-25lEdHIyxkMTyHd0CuciTpPQ';
        $sheetName = 'MIZUNO';
        $range = "{$sheetName}!A:C";
        $unmatchedSegmentacoes = [];
        $unmatchedSegmentacoesTotal = 0;
        $syncResults = [
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'messages' => [],
        ];

        try {
            $rows = $this->sheetService->readSheet($spreadsheetId, $range);

            if (empty($rows)) {
                throw new \Exception('Nenhum dado encontrado na planilha de segmentacao de cliente.');
            }

            DB::beginTransaction();

            foreach ($rows as $rowIndex => $row) {
                $sheetLine = $rowIndex + 1;
                $segmentoCliente = trim((string) ($row[0] ?? ''));
                $produtosSegmentos = trim((string) ($row[1] ?? ''));
                $linha = trim((string) ($row[2] ?? ''));
                $produtosSegmentosId = $this->resolveSegmentacaoReference($produtosSegmentos);

                $isHeaderRow = $sheetLine === 1
                    && $this->normalizeSheetHeader($segmentoCliente) === 'SEGMENTO_CLIENTE'
                    && $this->normalizeSheetHeader($produtosSegmentos) === 'PRODUTOS_SEGMENTOS'
                    && in_array($this->normalizeSheetHeader($linha), ['', 'LINHA'], true);

                if ($isHeaderRow) {
                    continue;
                }

                if ($segmentoCliente === '' || $segmentoCliente === '-') {
                    $syncResults['skipped']++;
                    $syncResults['messages'][] = "Linha {$sheetLine} ignorada: SEGMENTO_CLIENTE vazio.";
                    continue;
                }

                if ($produtosSegmentos !== '' && $produtosSegmentos !== '-' && $produtosSegmentosId === null) {
                    $syncResults['errors']++;
                    $unmatchedSegmentacoesTotal++;
                    $key = Str::lower(trim($produtosSegmentos));

                    if (!array_key_exists($key, $unmatchedSegmentacoes)) {
                        $unmatchedSegmentacoes[$key] = [
                            'value' => $produtosSegmentos,
                            'lines' => [],
                        ];
                    }

                    $unmatchedSegmentacoes[$key]['lines'][] = $sheetLine;
                    continue;
                }

                $segmentacaoCliente = $this->upsertSegmentacaoCliente(
                    $segmentoCliente,
                    $produtosSegmentosId,
                    'Segmentacao criada automaticamente via sincronizacao da planilha de segmentacao de cliente',
                    $linha
                );

                if ($segmentacaoCliente->wasRecentlyCreated) {
                    $syncResults['created']++;
                    continue;
                }

                if ($segmentacaoCliente->wasChanged(['nome', 'descricao', 'slug', 'active', 'produtos_segmentos', 'linha', 'deleted_at'])) {
                    $syncResults['updated']++;
                } else {
                    $syncResults['skipped']++;
                }
            }

            DB::commit();

            $message = "Sincronizacao de segmentacao de cliente concluida! "
                . "Novos: {$syncResults['created']}, Atualizados: {$syncResults['updated']}, "
                . "Ignorados: {$syncResults['skipped']}, Erros: {$syncResults['errors']}";

            if (!empty($unmatchedSegmentacoes)) {
                $distinct = count($unmatchedSegmentacoes);
                $message .= "\n\nPRODUTOS_SEGMENTOS sem correspondencia em segmentacao: {$distinct} valor(es) ({$unmatchedSegmentacoesTotal} ocorrencia(s)).";

                $lines = [];
                foreach ($unmatchedSegmentacoes as $item) {
                    $uniqueLines = array_values(array_unique($item['lines']));
                    sort($uniqueLines);
                    $previewLines = array_slice($uniqueLines, 0, 10);
                    $lineSuffix = count($uniqueLines) > 10 ? '...' : '';
                    $lines[] = "- {$item['value']} (linhas: " . implode(', ', $previewLines) . $lineSuffix . ')';
                }

                $message .= "\n" . implode("\n", $lines);

                Log::warning('Valores PRODUTOS_SEGMENTOS sem correspondencia em segmentacao', [
                    'spreadsheet_id' => $spreadsheetId,
                    'sheet' => $sheetName,
                    'distinct' => $distinct,
                    'total_occurrences' => $unmatchedSegmentacoesTotal,
                    'values' => array_values(array_map(fn ($item) => [
                        'value' => $item['value'],
                        'lines' => array_values(array_unique($item['lines'])),
                    ], $unmatchedSegmentacoes)),
                ]);
            }

            if (!empty($syncResults['messages'])) {
                $message .= "\n\nDetalhes:\n" . implode("\n", $syncResults['messages']);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao sincronizar segmentacao de cliente', [
                'error' => $e->getMessage(),
                'spreadsheet_id' => $spreadsheetId,
            ]);

            $code = (int) $e->getCode();
            $rawMessage = (string) $e->getMessage();
            $isPermissionDenied = $code === 403
                || str_contains($rawMessage, 'PERMISSION_DENIED')
                || str_contains($rawMessage, 'The caller does not have permission');

            if ($isPermissionDenied) {
                $serviceAccountEmail = null;
                try {
                    $serviceAccountEmail = $this->sheetService->getServiceAccountEmail();
                } catch (\Throwable $t) {
                    $serviceAccountEmail = null;
                }

                $extra = $serviceAccountEmail ? " ({$serviceAccountEmail})" : '';

                return back()->with('error',
                    "Erro na sincronizacao de segmentacao de cliente: PERMISSION_DENIED.\n"
                    . "A planilha precisa ser compartilhada com a Service Account usada pelo sistema{$extra} com permissao de Leitor (ou Editor).\n"
                    . "Se a planilha estiver no Drive de outra conta, abra no Google Sheets > Compartilhar > adicione o e-mail acima e salve."
                );
            }

            return back()->with('error', 'Erro na sincronizacao de segmentacao de cliente: ' . $rawMessage);
        }
    }

    private function resolveValueDomain(Request $request): string
    {
        return match($request->getHost()) {
            '127.0.0.1' => 'TESTE (MZ)',
            'catalogo.olympikus.com.br' => 'OLYMPIKUS',
            'testeolympikus.neooh.com.br' => 'TESTE (OLY)',
            'catalogo.underarmourbr.com.br' => 'UNDER ARMOUR',
            'testeunderarmour.neooh.com.br' => 'TESTE (UA)',
            'catalogo.mizuno.com.br' => 'MIZUNO',
            'testemizuno.neooh.com.br' => 'TESTE (MZ)',
            'mizuno-catalogo.neooh.com.br' => 'TESTE (MZ)',
            default => 'TESTE (MZ)',
        };
    }

    public function sync(Request $request)
    {
        $planilha = $this->resolveValueDomain($request);

        $spreadsheetId = "1skMcMlapMDLis7oZCz2dyRzFPMBfEmDoMLzYqqIInkU";
        $syncResults = [
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
            'messages' => []
        ];

        try {
            DB::beginTransaction();

            // Lê os cabeçalhos das colunas
            $headerRange = $planilha."!A3:AZ";
            
            $headerRows = $this->sheetService->readSheet($spreadsheetId, $headerRange);

            if (empty($headerRows) || empty($headerRows[0])) {
                throw new \Exception('Não foi possível ler os cabeçalhos da planilha.');
            }

            $headers = $headerRows[0];

            // Lê os dados da planilha
            $dataRange = $planilha."!A4:AZ";
            $rows = $this->sheetService->readSheet($spreadsheetId, $dataRange);

            if (empty($rows)) {
                throw new \Exception('Nenhum dado encontrado na planilha.');
            }

            // Agrupa dados por SKU/CÓDIGO para coletar todas as cores
            $groupedProducts = [];

            foreach ($rows as $rowIndex => $row) {
                $productData = [];

                // Mapeia cada valor da linha com o nome da coluna correspondente
                for ($i = 0; $i < count($headers); $i++) {
                    $columnName = $headers[$i] != "" ? $headers[$i] : "coluna_" . ($i + 1);
                    $productData[$columnName] = $row[$i] ?? '';
                }

                // Pula linhas vazias ou sem dados essenciais
                if (empty($productData['NOME']) || empty($productData['CÓDIGO'])) {
                    $syncResults['skipped']++;
                    $syncResults['messages'][] = "Linha " . ($rowIndex + 4) . " ignorada: Nome do produto ou código vazio";
                    continue;
                }

                $sku = $productData['CÓDIGO'];

                // Se é a primeira vez que vemos este SKU, inicializa
                if (!isset($groupedProducts[$sku])) {
                    $groupedProducts[$sku] = [
                        'data' => $productData,
                        'colors' => [],
                        'row_indexes' => []
                    ];
                }
                //dd($productData);
                // Adiciona a cor desta linha se existir
                if (!empty($productData['COR_COD'])) {
                    $groupedProducts[$sku]['colors'][] = [
                        'code' => $productData['COR_COD'],
                        'description' => $productData['COR_DESCRIÇÃO'] ?? $productData['COR_COD'],
                        'genero' => $this->resolveGeneroFromSheet(
                            $productData['GENERO'] ?? null,
                            $syncResults,
                            $productData['CÓDIGO'] ?? null,
                            [$rowIndex + 4],
                            $productData['COR_COD'] ?? null
                        ),
                        'flag' => $productData['COR_CLASSIFICAÇÃO'],
                        'collection' => $this->findOrCreateCollection($productData['COLEÇÃO'] ?? '', $productData['COLEÇÃO_SECUNDÁRIA'] ?? ''),
                        // Tenta obter numeração da cor a partir de possíveis cabeçalhos
                        'numeracao' => $this->extractColorNumeracao($productData),
                        'cliente_segmento' => $productData['CLIENTE_SEGMENTO'] ?? '',
                        'grades' => $productData['GRADES'] ?? '',
                        'periodo_venda' => $productData['PERIODO_VENDA'] ?? '',
                        'data_mkt' => $productData['LANÇAMENTO'] ?? '',
                        'data_trade' => $productData['LANÇAMENTO_TRADE'] ?? '',
                        'data_cliente' => $productData['LANÇAMENTO_CLIENTE'] ?? '',
                        'data_dtc' => $productData['LANÇAMENTO_DTC'] ?? '',
                        'size_run_table' => $productData['TABELA ME'] ?? '',
                        'size_run_article_gen' => $productData['TABELA ME_ARTICLE_GEN'] ?? '',
                        'size_run_article_cod' => $productData['TABELA ME_ARTICLE_COD'] ?? '',
                    ];
                }

                $groupedProducts[$sku]['row_indexes'][] = $rowIndex + 4;
            }

            //dd($groupedProducts['1326413']);
            // Processa cada produto único com todas suas cores, mantendo ordem da planilha
            $orderIndex = 1;
            foreach ($groupedProducts as $sku => $productGroup) {
                try {
                    // Sincroniza o produto com todas as cores coletadas e define ordem sequencial
                    $this->syncProductWithColors($productGroup['data'], $productGroup['colors'], $orderIndex, $syncResults, $productGroup['row_indexes']);
                    $orderIndex++;
                    $syncResults['success']++;
                } catch (\Exception $e) {
                    $syncResults['errors']++;
                    $rowIndexes = implode(', ', $productGroup['row_indexes']);
                    $syncResults['messages'][] = "Erro no produto SKU {$sku} (linhas {$rowIndexes}): " . $e->getMessage();
                    Log::error("Erro ao sincronizar produto SKU {$sku}", [
                        'error' => $e->getMessage(),
                        'data' => $productGroup['data'],
                        'colors' => $productGroup['colors']
                    ]);
                }
            }
            //dd($productData);
            DB::commit();

            $message = "Sincronização concluída! Sucessos: {$syncResults['success']}, Erros: {$syncResults['errors']}, Ignorados: {$syncResults['skipped']}";

            if (!empty($syncResults['messages'])) {
                $message .= "\n\nDetalhes dos erros:\n" . implode("\n", $syncResults['messages']);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Erro geral na sincronização', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erro na sincronização: ' . $e->getMessage());
        }
    }

    public function syncReverse(Request $request)
    {
        $planilha = $this->resolveValueDomain($request);
        //dd($planilha);
        set_time_limit(600);
        ini_set('memory_limit', '512M');

        $spreadsheetId = "1skMcMlapMDLis7oZCz2dyRzFPMBfEmDoMLzYqqIInkU";
        $baseSheetName = $planilha;
        $sheetName = $baseSheetName;

        try {
            $previewRaw = request()->query('preview', request()->input('preview', null));
            $preview = $previewRaw !== null && $previewRaw !== '' && $previewRaw !== false && $previewRaw !== 0 && $previewRaw !== '0';
        } catch (\Exception $e) {
            $preview = false;
        }

        Log::info('syncReverse started', [
            'preview' => $preview,
            'preview_query' => request()->query('preview'),
            'preview_input' => request()->input('preview'),
            'full_url' => request()->fullUrl(),
        ]);

        if ($preview) {
            $suffix = Carbon::now()->format('Ymd_His');
            $sheetName = $baseSheetName . '_PREVIEW_' . $suffix;
            $this->sheetService->duplicateSheet($spreadsheetId, $baseSheetName, $sheetName);
            Log::info('syncReverse preview sheet created', [
                'base_sheet' => $baseSheetName,
                'preview_sheet' => $sheetName,
            ]);
        }

        $headerRange = "{$sheetName}!A3:AZ";
        $dataRange = "{$sheetName}!A4:AZ";

        $syncResults = [
            'updated' => 0,
            'appended' => 0,
            'skipped' => 0,
            'errors' => 0,
            'messages' => []
        ];

        try {
            $headerRows = $this->sheetService->readSheet($spreadsheetId, $headerRange);
            if (empty($headerRows) || empty($headerRows[0])) {
                throw new \Exception('Não foi possível ler os cabeçalhos da planilha.');
            }

            $headers = $headerRows[0];
            $headerIndex = $this->buildHeaderIndex($headers);
            $headerCount = count($headers);
            $endColumn = $this->indexToColumnLetter(max(0, $headerCount - 1));

            $reset = false;
            try {
                $reset = (bool) $request->boolean('reset');
            } catch (\Exception $e) {
                $reset = false;
            }

            if ($reset) {
                $productIds = $this->getProductIdsToReverseSync(null);
                if (empty($productIds)) {
                    return back()->with('success', 'Nenhum produto encontrado no banco para enviar para a planilha.');
                }

                $products = Product::with([
                    'category.segmentacao',
                    'subcategory',
                    'measureCategories',
                    'colors' => function ($q) {
                        $q->orderBy('id')->with([
                            'collection',
                            'flagProduct',
                            'flagProducts',
                            'numeracao',
                            'shoeGrids',
                            'segmentacoesCliente',
                            'sizeRun.sizeRun',
                        ]);
                    },
                    'caracteristicas',
                    'links',
                    'numeracoes',
                    'calendario'
                ])
                    ->whereIn('id', $productIds)
                    ->orderBy('order')
                    ->orderBy('sku')
                    ->get();

                $exportRows = [];
                foreach ($products as $product) {
                    $sku = trim((string) ($product->sku ?? $product->code ?? ''));
                    if ($sku === '') {
                        $syncResults['skipped']++;
                        continue;
                    }

                    $baseValues = $this->buildBaseProductSheetValues($product);
                    $colors = $product->colors ?? collect();
                    if ($colors instanceof \Illuminate\Support\Collection) {
                        $colors = $colors->sortBy('id')->values();
                    }
                    if ($colors->isEmpty()) {
                        $colors = collect([null]);
                    }

                    foreach ($colors as $color) {
                        $rowValues = array_fill(0, $headerCount, '');
                        $this->applyValuesToRow($rowValues, $headerIndex, $baseValues);
                        $this->applyColorValuesToRow($rowValues, $headerIndex, $product, $color);

                        if ($this->isEmptySheetRow($rowValues)) {
                            $syncResults['skipped']++;
                            continue;
                        }

                        $exportRows[] = $rowValues;
                    }
                }

                $this->sheetService->clearSheet($spreadsheetId, "{$sheetName}!A4:{$endColumn}");

                $chunks = array_chunk($exportRows, 500);
                $rowCursor = 4;
                foreach ($chunks as $chunk) {
                    if (empty($chunk)) {
                        continue;
                    }

                    $startRow = $rowCursor;
                    $endRow = $rowCursor + count($chunk) - 1;
                    $this->sheetService->updateSheet(
                        $spreadsheetId,
                        "{$sheetName}!A{$startRow}:{$endColumn}{$endRow}",
                        $chunk,
                        'USER_ENTERED'
                    );

                    $syncResults['updated'] += count($chunk);
                    $rowCursor = $endRow + 1;
                }

                if (!$preview) {
                    Cache::forever('google_sheets_reverse_sync_produtos_last_run', Carbon::now()->toIso8601String());
                }

                $targetText = $preview ? " (aba {$sheetName})" : '';
                $message = "Sincronismo reverso concluído{$targetText}! \n
                Atualizadas: {$syncResults['updated']}, Novas linhas: {$syncResults['appended']}, Ignoradas: {$syncResults['skipped']}, Erros: {$syncResults['errors']}";

                return back()->with('success', $message);
            }

            $rows = $this->sheetService->readSheet($spreadsheetId, $dataRange);
            if ($rows === null) {
                Log::warning('syncReverse readSheet retornou null para dados; tratando como planilha vazia', [
                    'range' => $dataRange,
                    'sheet' => $sheetName,
                ]);
                $rows = [];
            }
            if (!is_array($rows)) {
                throw new \UnexpectedValueException('Falha ao ler os dados da planilha (retorno inválido).');
            }
            $sheetRowMap = [];
            $sheetRowMapBySkuColor = [];
            $sheetRowValuesByNumber = [];

            foreach ($rows as $i => $row) {
                $rowNumber = $i + 4;
                $rowPadded = $this->padRowToCount($row, $headerCount);
                $sheetRowValuesByNumber[$rowNumber] = $rowPadded;

                $codeIndex = $headerIndex['CÓDIGO'] ?? null;
                if ($codeIndex === null) {
                    continue;
                }

                $code = trim((string) ($rowPadded[$codeIndex] ?? ''));
                if ($code === '') {
                    continue;
                }

                $colorCodeIndex = $headerIndex['COR_COD'] ?? null;
                $colorCode = $colorCodeIndex !== null ? trim((string) ($rowPadded[$colorCodeIndex] ?? '')) : '';
                $colorCodeKey = $this->normalizeColorCodeKey($colorCode);

                $collectionIndex = $headerIndex['COLEÇÃO'] ?? null;
                $collectionSecondaryIndex = $headerIndex['COLEÇÃO_SECUNDÁRIA'] ?? null;
                $collectionName = $collectionIndex !== null ? trim((string) ($rowPadded[$collectionIndex] ?? '')) : '';
                $collectionSecondary = $collectionSecondaryIndex !== null ? trim((string) ($rowPadded[$collectionSecondaryIndex] ?? '')) : '';

                $collectionKey = $this->normalizeSheetHeader($collectionName) . '|' . $this->normalizeSheetHeader($collectionSecondary);
                $sheetRowMap[$code][$colorCodeKey][$collectionKey] = $sheetRowMap[$code][$colorCodeKey][$collectionKey] ?? [];
                $sheetRowMap[$code][$colorCodeKey][$collectionKey][] = $rowNumber;

                $sheetRowMapBySkuColor[$code][$colorCodeKey] = $sheetRowMapBySkuColor[$code][$colorCodeKey] ?? [];
                $sheetRowMapBySkuColor[$code][$colorCodeKey][] = $rowNumber;
            }

            $lastRunIso = Cache::get('google_sheets_reverse_sync_produtos_last_run');
            $since = null;
            if (!empty($lastRunIso)) {
                try {
                    $since = Carbon::parse($lastRunIso);
                } catch (\Exception $e) {
                    $since = null;
                }
            }

            $forceFull = $preview;
            try {
                $forceFull = (bool) request()->boolean('force');
            } catch (\Exception $e) {
                $forceFull = $preview;
            }

            if ($forceFull) {
                $since = null;
            }

            $productIds = $this->getProductIdsToReverseSync($since);
            if (empty($productIds)) {
                if ($since !== null) {
                    $productIds = $this->getProductIdsToReverseSync(null);
                }
                if (empty($productIds)) {
                    return back()->with('success', 'Nenhuma alteração encontrada no banco para enviar para a planilha.');
                }
            }

            $products = Product::with([
                'category.segmentacao',
                'subcategory',
                'measureCategories',
                'colors' => function ($q) {
                    $q->orderBy('id')->with([
                        'collection',
                        'flagProduct',
                        'flagProducts',
                        'numeracao',
                        'shoeGrids',
                        'segmentacoesCliente',
                        'sizeRun.sizeRun',
                    ]);
                },
                'caracteristicas',
                'links',
                'numeracoes',
                'calendario'
            ])
                ->whereIn('id', $productIds)
                ->orderBy('order')
                ->orderBy('sku')
                ->get();

            $updates = [];
            $appends = [];

            foreach ($products as $product) {
                $sku = trim((string) ($product->sku ?? $product->code ?? ''));
                if ($sku === '') {
                    $syncResults['skipped']++;
                    continue;
                }

                $baseValues = $this->buildBaseProductSheetValues($product);

                $colors = $product->colors ?? collect();
                if ($colors instanceof \Illuminate\Support\Collection) {
                    $colors = $colors->sortBy('id')->values();
                }
                if ($colors->isEmpty()) {
                    $colors = collect([null]);
                }

                foreach ($colors as $color) {
                    $colorCode = $color ? trim((string) ($color->color_code ?? '')) : '';
                    $colorCodeKey = $this->normalizeColorCodeKey($colorCode);
                    $collectionKey = '';
                    if ($color && $color->collection) {
                        $collectionKey = $this->normalizeSheetHeader((string) ($color->collection->name ?? '')) . '|' .
                            $this->normalizeSheetHeader((string) ($color->collection->description ?? ''));
                    }

                    $rowNumbers = null;
                    if ($collectionKey !== '') {
                        $rowNumbers = $sheetRowMap[$sku][$colorCodeKey][$collectionKey] ?? null;
                    }
                    if (empty($rowNumbers)) {
                        $rowNumbers = $sheetRowMapBySkuColor[$sku][$colorCodeKey] ?? null;
                    }

                    if (!empty($rowNumbers)) {
                        $uniqueRowNumbers = array_values(array_unique(array_map('intval', (array) $rowNumbers)));
                        sort($uniqueRowNumbers);

                        foreach ($uniqueRowNumbers as $rowNumber) {
                            if ($rowNumber <= 0) {
                                continue;
                            }

                            $rowValues = $sheetRowValuesByNumber[$rowNumber] ?? array_fill(0, $headerCount, '');
                            $rowValues = $this->padRowToCount($rowValues, $headerCount);
                            $this->applyValuesToRow($rowValues, $headerIndex, $baseValues);
                            $this->applyColorValuesToRow($rowValues, $headerIndex, $product, $color);

                            $updates[] = [
                                'range' => "{$sheetName}!A{$rowNumber}:{$endColumn}{$rowNumber}",
                                'values' => [$rowValues],
                            ];
                        }
                    } else {
                        $rowValues = array_fill(0, $headerCount, '');
                        $this->applyValuesToRow($rowValues, $headerIndex, $baseValues);
                        $this->applyColorValuesToRow($rowValues, $headerIndex, $product, $color);
                        if ($this->isEmptySheetRow($rowValues)) {
                            $syncResults['skipped']++;
                            Log::warning('syncReverse ignorou append de linha vazia', [
                                'sku' => $sku,
                                'color_code' => $colorCode,
                                'sheet' => $sheetName,
                            ]);
                            continue;
                        }
                        $appends[] = $rowValues;
                    }
                }
            }

            $updateChunks = array_chunk($updates, 200);
            foreach ($updateChunks as $chunk) {
                if (empty($chunk)) {
                    continue;
                }
                $this->sheetService->batchUpdateValues($spreadsheetId, $chunk, 'USER_ENTERED');
                $syncResults['updated'] += count($chunk);
            }

            $appendChunks = array_chunk($appends, 200);
            foreach ($appendChunks as $chunk) {
                if (empty($chunk)) {
                    continue;
                }
                $this->sheetService->appendSheet($spreadsheetId, "{$sheetName}!A4:{$endColumn}", $chunk, 'USER_ENTERED');
                $syncResults['appended'] += count($chunk);
            }

            if (!$preview) {
                Cache::forever('google_sheets_reverse_sync_produtos_last_run', Carbon::now()->toIso8601String());
            }

            $targetText = $preview ? " (aba {$sheetName})" : '';
            $message = "Sincronismo reverso concluído{$targetText}! \n
            Atualizadas: {$syncResults['updated']}, Novas linhas: {$syncResults['appended']}, Ignoradas: {$syncResults['skipped']}, Erros: {$syncResults['errors']}";
            if (!empty($syncResults['messages'])) {
                $message .= "\n\nDetalhes:\n" . implode("\n", $syncResults['messages']);
            }

            return back()->with('success', $message);
        } catch (\Throwable $e) {
            Log::error('Erro no sincronismo reverso (Banco -> Planilha)', [
                'error' => $e->getMessage(),
                'exception' => get_class($e),
            ]);
            return back()->with('error', 'Erro no sincronismo reverso: ' . $e->getMessage());
        }
    }

    private function isEmptySheetRow(array $rowValues): bool
    {
        foreach ($rowValues as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function normalizeColorCodeKey(?string $colorCode): string
    {
        $text = trim((string) $colorCode);
        if ($text === '') {
            return '';
        }

        $text = str_replace('/', '_', $text);
        $ascii = Str::upper(Str::ascii($text));
        $ascii = preg_replace('/[^A-Z0-9]+/u', '_', $ascii);
        $ascii = preg_replace('/_+/', '_', (string) $ascii);
        $ascii = trim((string) $ascii, '_');
        return $ascii;
    }


    /**
     * Sincroniza um produto individual com base nos dados da planilha
     */
    private function syncProduct($data)
    {
        return $this->syncProductWithColors($data, [], null);
    }

    /**
     * Sincroniza um produto com suas cores agrupadas
     */
    private function syncProductWithColors($data, $colors, $desiredOrder = null, &$syncResults = null, $rowIndexes = null)
    {
        //dd($colors);
        $segmentacao = $this->findOrCreateSegmentacao($data['PRODUTOS_SEGMENTO'] ?? '');

        // Busca ou cria categoria
        $category = $this->findOrCreateCategory($data['CATEGORIA'] ?? '', $segmentacao);
        $subcategory = $this->findOrCreateSubcategory($data['SUBCATEGORIA'] ?? '', $category->id ?? null);
        $collection = $this->findOrCreateCollection($data['COLEÇÃO'] ?? '', $data['COLEÇÃO_SECUNDÁRIA'] ?? '');
        $defaultFlagsCell = $data['COR_CLASSIFICAÇÃO'] ?? null;
        $tecnologia = $this->findOrCreateTecnologia($data['TECNOLOGIAS'] ?? '', $syncResults, $data['CÓDIGO'] ?? null, $rowIndexes);

        // Prepara dados do produto
        $productData = [
            'name' => $data['NOME'],
            'description' => $data['DESCRIÇÃO'] ?? '',
            'linha' => ($data['LINHA'] != '-' && $data['LINHA'] !== '') ? $data['LINHA'] : null,
            'code' => $data['CÓDIGO'],
            'sku' => $data['CÓDIGO'], // Usando CÓDIGO como SKU
            'price' => $this->parsePrice($data['PDV'] ?? null),
            'slug' => Str::slug($data['NOME']) . '-' . $data['CÓDIGO'],
            'category_id' => $category->id ?? null,
            'subcategory_id' => $subcategory->id ?? null,
            'active' => true,
            'technologies' => json_encode($tecnologia),
            'flag_calendario' => $this->hasLaunchDatesForAnyColor($colors, $data),
        ];
        //dd($productData);
        // Cria ou atualiza o produto
        $product = Product::updateOrCreate(
            ['sku' => $data['CÓDIGO']],
            $productData
        );

        // Define ordem do produto conforme a posição na planilha, resolvendo conflitos
        if (!is_null($desiredOrder)) {
            $newOrder = (int) $desiredOrder;

            DB::transaction(function () use ($product, $newOrder) {
                $hasConflict = Product::whereNull('deleted_at')
                    ->where('id', '!=', $product->id)
                    ->where('order', $newOrder)
                    ->exists();

                if ($hasConflict) {
                    Product::whereNull('deleted_at')
                        ->where('id', '!=', $product->id)
                        ->where('order', '>=', $newOrder)
                        ->increment('order');
                }

                $product->order = $newOrder;
                $product->save();
            });
        }
        
        // Sincroniza cores agrupadas
        $this->syncColorsGrouped($product, $colors, $collection, $defaultFlagsCell, $data, $syncResults, $rowIndexes);

        // Sincroniza tecnologias
        //$this->syncTecnologias($product, $tecnologia);

        // Sincroniza características
        $this->syncCharacteristics($product, $data);

        // Sincroniza numerações
        $this->syncNumeracoes($product, $data);

        // Sincroniza links
        $this->syncLinks($product, $data);

        // Sincroniza calendário
        $this->syncCalendar($product, $data);

        return $product;
    }

    /**
     * Busca ou cria uma categoria
     */
    private function findOrCreateCategory($categoryName, $segmentacao)
    {
        if (empty($categoryName)) {
            return null;
        }

        return Category::firstOrCreate(
            [
                'name' => $categoryName,
                'segmento_id' => $segmentacao->id ?? null
            ],
            [
                'slug' => Str::slug($categoryName),
                'active' => true
            ]
        );
    }

    /**
     * Busca ou cria uma categoria
     */
    private function findOrCreateSegmentacao($segmentoName)
    {
        if (empty($segmentoName)) {
            return null;
        }

        return Segmentacao::firstOrCreate(
            ['segmento' => $segmentoName],
            [
                'slug' => Str::slug($segmentoName),
                'active' => true
            ]
        );
    }

    /**
     * Busca ou cria uma categoria
     */
    private function findOrCreateTecnologia($tecnologias, &$syncResults = null, $sku = null, $rowIndexes = null)
    {
        if (empty($tecnologias)) {
            return null;
        }
        // Ícone padrão para tecnologias sem imagem
        $defaultIcon = 'images/technology/1759344921.png';

        $tec = $this->splitListValues($tecnologias);

        $array_id_tec = [];
        foreach ($tec as $name) {
            // Busca por tecnologia existente apenas pelo nome, para evitar duplicidade
            // Busca todos os itens com o mesmo nome para tratar duplicidades
            $itemsSameName = TechnologyItem::where('name', $name)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($itemsSameName->isNotEmpty()) {
                // Prioriza manter o mais antigo que possua ícone diferente do padrão
                $nonDefaultItems = $itemsSameName->filter(function ($ti) use ($defaultIcon) {
                    return !empty($ti->icon) && $ti->icon !== $defaultIcon;
                })->values();

                $keeper = $nonDefaultItems->first() ?: $itemsSameName->first();

                // Se o item escolhido não tiver ícone, define o padrão
                if (empty($keeper->icon)) {
                    $keeper->icon = $defaultIcon;
                    $keeper->save();
                }

                // Exclui (soft delete) os demais itens duplicados
                foreach ($itemsSameName as $ti) {
                    if ($ti->id !== $keeper->id) {
                        $ti->delete();
                    }
                }

                $array_id_tec[] = $keeper->id;
            } else {

                // Não existe: cria com categoria padrão, descrição igual ao nome e ícone padrão
                /*$created = TechnologyItem::create([
                    'technology_category_id' => 1,
                    'name' => $name,
                    'description' => $name,
                    'icon' => $defaultIcon,
                    'active' => true,
                ]);
                $array_id_tec[] = $created->id;*/
                if (is_array($syncResults)) {
                    $syncResults['errors']++;
                    $rows = is_array($rowIndexes) ? implode(', ', $rowIndexes) : (string) $rowIndexes;
                    $rowsText = $rows !== '' ? " (linhas {$rows})" : '';
                    $skuText = !empty($sku) ? " SKU {$sku}" : '';
                    $syncResults['messages'][] = "Tecnologia {$name} não existe e precisa ser cadastrada antes de ser vinculada ao produto{$skuText}{$rowsText}.";
                }
            }
        }


        // Evita IDs duplicados caso a lista tenha tecnologias repetidas
        return array_values(array_unique($array_id_tec));
    }

    private function splitListValues($value): array
    {
        $text = trim((string) $value);
        if ($text === '') {
            return [];
        }

        $parts = preg_split('/[;|,]+/', $text) ?: [];
        $parts = array_map(function ($t) {
            return trim((string) $t);
        }, $parts);

        return array_values(array_filter($parts, function ($t) {
            return $t !== '' && $t !== '-';
        }));
    }



    private function findMeasureCategoryIdsFromSheet($medidas, &$syncResults = null, $sku = null, $rowIndexes = null): array
    {
        $names = $this->splitListValues($medidas);
        if (empty($names)) {
            return [];
        }

        $ids = [];
        foreach ($names as $name) {
            $category = MeasureCategory::where('name', $name)->first();
            if (!$category) {
                $slug = Str::slug($name);
                $category = MeasureCategory::where('slug', $slug)->first();
            }

            if ($category) {
                $ids[] = $category->id;
                continue;
            }

            if (is_array($syncResults)) {
                $syncResults['errors']++;
                $rows = is_array($rowIndexes) ? implode(', ', $rowIndexes) : (string) $rowIndexes;
                $rowsText = $rows !== '' ? " (linhas {$rows})" : '';
                $skuText = !empty($sku) ? " SKU {$sku}" : '';
                $syncResults['messages'][] = "Medidas {$name} não existe e precisa ser cadastrada antes de ser vinculada ao produto{$skuText}{$rowsText}.";
            }
        }

        return array_values(array_unique($ids));
    }

    private function findShoeGridIdsFromSheet($grades, &$syncResults = null, $sku = null, $rowIndexes = null, $colorCode = null): array
    {
        $codes = $this->splitListValues($grades);
        if (empty($codes)) {
            return [];
        }

        $ids = [];
        foreach ($codes as $code) {
            $itemsSameCode = ShoeGrid::where('code', $code)
                ->orderBy('created_at', 'asc')
                ->get();

            if ($itemsSameCode->isEmpty()) {
                if (is_array($syncResults)) {
                    $syncResults['errors']++;
                    $rows = is_array($rowIndexes) ? implode(', ', $rowIndexes) : (string) $rowIndexes;
                    $rowsText = $rows !== '' ? " (linhas {$rows})" : '';
                    $skuText = !empty($sku) ? " SKU {$sku}" : '';
                    $colorText = !empty($colorCode) ? " cor {$colorCode}" : '';
                    $syncResults['messages'][] = "Grade {$code} não existe e precisa ser cadastrada antes de ser vinculada ao produto{$skuText}{$colorText}{$rowsText}.";
                }
                continue;
            }

            $keeper = $itemsSameCode->firstWhere('active', true) ?: $itemsSameCode->first();
            if ($keeper) {
                $ids[] = $keeper->id;
            }
        }

        return array_values(array_unique($ids));
    }

    private function parsePeriodoVendas($value): array
    {
        if ($value === null) {
            return [];
        }

        if (is_array($value)) {
            $rawParts = $value;
        } else {
            $text = trim((string) $value);
            if ($text === '' || $text === '-') {
                return [];
            }
            $rawParts = preg_split('/[|,;\\s]+/u', $text) ?: [];
        }

        $map = [
            'JAN' => 1, 'JANEIRO' => 1,
            'FEV' => 2, 'FEVEREIRO' => 2,
            'MAR' => 3, 'MARÇO' => 3, 'MARCO' => 3,
            'ABR' => 4, 'ABRIL' => 4,
            'MAI' => 5, 'MAIO' => 5,
            'JUN' => 6, 'JUNHO' => 6,
            'JUL' => 7, 'JULHO' => 7,
            'AGO' => 8, 'AGOSTO' => 8,
            'SET' => 9, 'SETEMBRO' => 9,
            'OUT' => 10, 'OUTUBRO' => 10,
            'NOV' => 11, 'NOVEMBRO' => 11,
            'DEZ' => 12, 'DEZEMBRO' => 12,
        ];

        $months = [];
        foreach ($rawParts as $part) {
            $p = trim((string) $part);
            if ($p === '' || $p === '-') {
                continue;
            }

            if (is_numeric($p)) {
                $m = (int) $p;
                if ($m >= 1 && $m <= 12) {
                    $months[] = $m;
                }
                continue;
            }

            $key = mb_strtoupper($p, 'UTF-8');
            if (isset($map[$key])) {
                $months[] = $map[$key];
            }
        }

        $months = array_values(array_unique($months));
        sort($months);
        return $months;
    }

    private function normalizeGeneroValue($value): array
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '' || $raw === '-') {
            return [
                'value' => 'Unissex',
                'is_invalid' => false,
                'original' => $raw,
            ];
        }

        $normalized = Str::upper(Str::ascii($raw));
        $normalized = preg_replace('/[^A-Z0-9]+/u', '_', $normalized);
        $normalized = preg_replace('/_+/', '_', (string) $normalized);
        $normalized = trim((string) $normalized, '_');

        $map = [
            'MASCULINO' => 'Masculino',
            'FEMININO' => 'Feminino',
            'UNISSEX' => 'Unissex',
            'UNISEX' => 'Unissex',
            'INFANTIL' => 'Infantil',
            'KID' => 'Infantil',
            'KIDS' => 'Infantil',
            'INFANTIL_KID' => 'Infantil',
            'INFANTIL_KIDS' => 'Infantil',
            'KID_INFANTIL' => 'Infantil',
            'KIDS_INFANTIL' => 'Infantil',
        ];

        if (isset($map[$normalized])) {
            return [
                'value' => $map[$normalized],
                'is_invalid' => false,
                'original' => $raw,
            ];
        }

        return [
            'value' => 'Unissex',
            'is_invalid' => true,
            'original' => $raw,
        ];
    }

    private function resolveGeneroFromSheet($value, &$syncResults = null, $sku = null, $rowIndexes = null, $colorCode = null): string
    {
        $resolved = $this->normalizeGeneroValue($value);

        if ($resolved['is_invalid'] && is_array($syncResults)) {
            $syncResults['errors']++;
            $rows = is_array($rowIndexes) ? implode(', ', $rowIndexes) : (string) $rowIndexes;
            $rowsText = $rows !== '' ? " (linhas {$rows})" : '';
            $skuText = !empty($sku) ? " SKU {$sku}" : '';
            $colorText = !empty($colorCode) ? " cor {$colorCode}" : '';
            $syncResults['messages'][] = "Genero {$resolved['original']} é inválido. Valores aceitos: Masculino, Feminino, Unissex e Infantil/Kids. O sincronismo usou Unissex por padrão{$skuText}{$colorText}{$rowsText}.";
        }

        return $resolved['value'];
    }

    /**
     * Busca ou cria uma subcategoria
     */
    private function findOrCreateSubcategory($subcategoryName, $categoryId)
    {
        if (empty($subcategoryName) || empty($categoryId)) {
            return null;
        }

        return Subcategory::firstOrCreate(
            ['faixa' => $subcategoryName, 'category_id' => $categoryId],
            [
                'slug' => Str::slug($subcategoryName),
                'active' => true
            ]
        );
    }

    /**
     * Busca ou cria uma coleção
     */
    private function findOrCreateCollection($collectionName, $collectionSecondaryName)
    {
        // Normaliza strings
        $collectionName = trim((string) $collectionName);
        $collectionSecondaryName = trim((string) $collectionSecondaryName);

        if ($collectionName === '') {
            return null;
        }

        // Monta o slug considerando nome + secundária quando existir
        if ($collectionSecondaryName === '') {
            // Coleção sem secundária → sempre mesmo slug
            $slug = Str::slug($collectionName);
        } else {
            // Coleção com secundária → par (nome, secundária) diferencia
            $slug = Str::slug($collectionName . '-' . $collectionSecondaryName);
        }

        $codigoColecao = $collectionSecondaryName === ''
            ? $collectionName
            : ($collectionName . '-' . $collectionSecondaryName);

        $collection = Collection::withTrashed()->firstOrNew(['slug' => $slug]);

        if ($collection->exists && $collection->trashed()) {
            $collection->restore();
        }

        $collection->fill([
            'name' => $collectionName,
            'description' => $collectionSecondaryName,
            'codigo_colecao' => $codigoColecao,
            //'active' => false,
        ]);

        $collection->save();

        return $collection;
    }

    /**
     * Busca ou cria uma flag
     */
    private function extractFlagTitles($raw): array
    {
        if ($raw instanceof FlagProduct) {
            $title = trim((string) ($raw->flag_title ?? ''));
            return $title === '' ? [] : [$title];
        }

        if (is_array($raw)) {
            $titles = [];
            foreach ($raw as $item) {
                $titles = array_merge($titles, $this->extractFlagTitles($item));
            }
            return $this->uniqueFlagTitles($titles);
        }

        $text = trim((string) ($raw ?? ''));
        if ($text === '' || $text === '-') {
            return [];
        }

        if (mb_strtolower($text, 'UTF-8') === 'null') {
            return [];
        }

        $parts = preg_split('/[,\n;]+/u', $text) ?: [];
        $titles = [];

        foreach ($parts as $part) {
            $title = trim((string) $part);
            if ($title === '' || $title === '-') {
                continue;
            }
            if (mb_strtolower($title, 'UTF-8') === 'null') {
                continue;
            }
            $titles[] = $title;
        }

        return $this->uniqueFlagTitles($titles);
    }

    private function uniqueFlagTitles(array $titles): array
    {
        $seen = [];
        $unique = [];

        foreach ($titles as $title) {
            $title = trim((string) $title);
            if ($title === '') {
                continue;
            }

            $key = mb_strtolower($title, 'UTF-8');
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $unique[] = $title;
        }

        return $unique;
    }

    private function findOrCreateFlag($flagName, &$syncResults = null, $sku = null, $rowIndexes = null, $colorCode = null)
    {
        $flagName = trim((string) ($flagName ?? ''));
        if ($flagName === '' || $flagName === '-' || mb_strtolower($flagName, 'UTF-8') === 'null') {
            return null;
        }

        // Busca todos os itens com o mesmo título de flag
        $flagsSameTitle = FlagProduct::where('flag_title', $flagName)
            ->orderBy('created_at', 'asc')
            ->get();

        if ($flagsSameTitle->isEmpty()) {
            if (is_array($syncResults)) {
                $syncResults['errors']++;
                $rows = is_array($rowIndexes) ? implode(', ', $rowIndexes) : (string) $rowIndexes;
                $rowsText = $rows !== '' ? " (linhas {$rows})" : '';
                $skuText = !empty($sku) ? " SKU {$sku}" : '';
                $colorText = !empty($colorCode) ? " cor {$colorCode}" : '';
                $syncResults['messages'][] = "Flag {$flagName} não existe e precisa ser cadastrada antes de ser vinculada ao produto{$skuText}{$colorText}{$rowsText}.";
            }

            return null;
        }

        if ($flagsSameTitle->count() === 1) {
            // Apenas um: garante os campos e retorna
            $flag = $flagsSameTitle->first();
            $flag->update([
                'flag_description' => $flagName,
                //'flag_bg' => '#000000',
                //'flag_color_text_bg' => '#ffffff',
                'alinhamento' => 'left',
                'status' => true,
            ]);
            return $flag;
        }

        foreach ($flagsSameTitle as $flag) {
            // Exclui (soft delete) todos os anteriores
            $flag->delete();
        }

        // Mais de um: cria um novo com os valores desejados e exclui os duplicados
        $created = FlagProduct::create([
            'flag_title' => $flagName,
            'flag_description' => $flagName,
            'flag_bg' => '#000000',
            'flag_color_text_bg' => '#ffffff',
            'alinhamento' => 'left',
            'status' => true,
        ]);


        // Usa o item criado
        return $created;
    }

    /**
     * Converte string de preço para float
     */
    private function parsePrice($priceString)
    {
        $text = trim((string) ($priceString ?? ''));
        if ($text === '' || $text === '-') {
            return 0;
        }

        $value = preg_replace('/[^0-9.,]/', '', $text);
        if ($value === null || $value === '') {
            return 0;
        }

        $lastComma = strrpos($value, ',');
        $lastDot = strrpos($value, '.');

        if ($lastComma !== false && $lastDot !== false) {
            if ($lastComma > $lastDot) {
                $value = str_replace('.', '', $value);
                $value = str_replace(',', '.', $value);
            } else {
                $value = str_replace(',', '', $value);
            }
        } elseif ($lastComma !== false) {
            $value = str_replace(',', '.', $value);
        }

        return (float) $value;
    }

    /**
     * Converte string de data para Carbon ou null
     */
    private function parseDate($dateString)
    {
        if ($dateString instanceof Carbon) {
            return $dateString;
        }

        $text = trim((string) ($dateString ?? ''));
        if ($text === '' || $text === '-') {
            return null;
        }

        $normalized = mb_strtolower($text, 'UTF-8');
        if (preg_match('/^(jan|fev|mar|abr|mai|jun|jul|ago|set|out|nov|dez)\s*[\/\-]\s*(\d{2}|\d{4})$/u', $normalized, $matches)) {
            $monthMap = [
                'jan' => 1,
                'fev' => 2,
                'mar' => 3,
                'abr' => 4,
                'mai' => 5,
                'jun' => 6,
                'jul' => 7,
                'ago' => 8,
                'set' => 9,
                'out' => 10,
                'nov' => 11,
                'dez' => 12,
            ];

            $month = $monthMap[$matches[1]] ?? null;
            $year = (int) $matches[2];
            if ($month !== null) {
                if (strlen($matches[2]) === 2) {
                    $year = $year >= 70 ? (1900 + $year) : (2000 + $year);
                }

                return Carbon::create($year, $month, 1, 0, 0, 0);
            }
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $text);
        } catch (\Exception $e) {
            try {
                return Carbon::parse($text);
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    /**
     * Sincroniza cores do produto usando findOrCreate (método legado)
     */
    private function syncColors($product, $data, $collection, $flag)
    {
        // Fallback para compatibilidade - converte dados de uma linha para array
        $colors = [];
        if (!empty($data['COR_COD'])) {
            $colors[] = [
                'code' => $data['COR_COD'],
                'description' => $data['COR_DESCRIÇÃO'] ?? $data['COR_COD'],
                'data_mkt' => $data['LANÇAMENTO'] ?? '',
                'data_trade' => $data['LANÇAMENTO_TRADE'] ?? '',
                'data_cliente' => $data['LANÇAMENTO_CLIENTE'] ?? '',
                'data_dtc' => $data['LANÇAMENTO_DTC'] ?? '',
            ];
        }

        $this->syncColorsGrouped($product, $colors, $collection, $flag, $data);
    }

    /**
     * Sincroniza cores agrupadas do produto
     */
    private function syncColorsGrouped($product, $colors, $collection, $defaultFlagsCell, $data, &$syncResults = null, $rowIndexes = null)
    {
        //dd($colors);
        // Remove cores existentes (hasMany relationship)
        //Color::where('product_id', $product->id)->delete();

        // Se não há cores, não faz nada
        if (empty($colors)) {
            return;
        }

        // Remove duplicatas baseadas no código da cor + coleção
        $uniqueColors = [];
        foreach ($colors as $color) {
            $code = (string) ($color['code'] ?? '');
            if ($code === '') {
                continue;
            }

            $colorCollection = $color['collection'] ?? $collection;
            $collectionId = $colorCollection?->id;
            $uniqueKey = $code . '|' . ($collectionId ?? 'null');

            $color['collection'] = $colorCollection;
            $uniqueColors[$uniqueKey] = $color;
            $uniqueColors[$uniqueKey]['cliente_segmento'] = $this->processClienteSegmento($color['cliente_segmento'] ?? '');
            
            //dd($segmentacoesCliente);
        }
        
        // Processa segmentações de cliente da coluna CLIENTE_SEGMENTO
        
        
        $defaultFlagTitles = $this->extractFlagTitles($defaultFlagsCell);

        // Log para debug das cores sincronizadas
        /*Log::info("Cores sincronizadas para produto {$product->sku}", [
            'product_id' => $product->id,
            'cores_count' => count($uniqueColors),
            'cores' => array_values($uniqueColors),
            'segmentacoes_cliente' => $segmentacoesCliente,
            'flag' => $flag
        ]);*/
        //dd($uniqueColors);
        // Cria as cores encontradas no banco de dados usando findOrCreate
        foreach ($uniqueColors as $cor) {
            $flagTitles = $this->extractFlagTitles($cor['flag'] ?? null);
            if (empty($flagTitles)) {
                $flagTitles = $defaultFlagTitles;
            }

            $flagIds = [];
            foreach ($flagTitles as $flagTitle) {
                $flagModel = $this->findOrCreateFlag(
                    $flagTitle,
                    $syncResults,
                    $product->sku ?? null,
                    $rowIndexes,
                    $cor['code'] ?? null
                );

                if ($flagModel) {
                    $flagIds[] = $flagModel->id;
                }
            }

            $flagIds = array_values(array_unique(array_filter($flagIds)));
            $primaryFlagId = $flagIds[0] ?? null;

            // Mapeia numeração por cor, se fornecida na planilha
            $numeracaoId = null;
            if (!empty($cor['numeracao'])) {
                $numeracaoId = $this->findOrCreateNumeracaoId($cor['numeracao']);
                // Passa o numeracao_id para a criação/atualização da cor
                $cor['numeracao_id'] = $numeracaoId;
            }

            $colorCollection = $cor['collection'] ?? $collection;
            //dd($product, $cor, $colorCollection, $flagModel?->id ?? null);
            $colorModel = $this->findOrCreateColor($product, $cor, $colorCollection, $primaryFlagId);

            if (method_exists($colorModel, 'flagProducts')) {
                $colorModel->flagProducts()->sync($flagIds);
            }
            $shoeGridIds = $this->findShoeGridIdsFromSheet(
                $cor['grades'] ?? '',
                $syncResults,
                $product->sku ?? null,
                $rowIndexes,
                $cor['code'] ?? null
            );
            if (method_exists($colorModel, 'shoeGrids')) {
                $colorModel->shoeGrids()->sync($shoeGridIds);
            }
            $segmentacoesCliente = $cor['cliente_segmento'] ?? [];
            //dd($segmentacoesCliente);
            // Sincroniza segmentações de cliente para esta cor
            if (!empty($segmentacoesCliente)) {
                $colorModel->segmentacoesCliente()->sync($segmentacoesCliente);
                //dd($colorModel->segmentacoesCliente()->sync($segmentacoesCliente));
            } else {
                // Se não há segmentações informadas, remove quaisquer vínculos existentes
                $colorModel->segmentacoesCliente()->detach();
            }

            $this->syncColorSizeRunFromSheet(
                $colorModel,
                $cor,
                $syncResults,
                $product->sku ?? null,
                $rowIndexes,
                $cor['code'] ?? null
            );
            
            Log::info('AQUI - Sincronizada cor', [
                'sku' => $product->sku,
                'color_code' => $cor['code'] ?? null,
                'segmentacoes_cliente' => $segmentacoesCliente,
                'flag_product_id' => $primaryFlagId,
                'flag_product_ids' => $flagIds,
            ]);
                   }
    }

    /**
     * Busca ou cria uma cor para o produto
     */
    private function findOrCreateColor($product, $corData, $collection, $flag)
    {
       
        Log::info("Sincronizando cor para produto {$product->sku}", [
                'color_code' => $corData['code'],
                'product_id' => $product->id,
                'collection_id' => $collection?->id,
                'color_name' => $corData['code'],
                'color_description' => $corData['description'],
                'genero' => !empty($corData['genero']) ? $corData['genero'] : 'Unissex',
                'collection_id' => $collection?->id,
                'flag_product_id' => $flag ?? null,
                'numeracao_id' => $corData['numeracao_id'] ?? null,
                'data_mkt' => $this->parseDate($corData['data_mkt'] ?? null),
                'data_trade' => $this->parseDate($corData['data_trade'] ?? null),
                'data_cliente' => $this->parseDate($corData['data_cliente'] ?? null),
                'data_dtc' => $this->parseDate($corData['data_dtc'] ?? null),
                'active' => true
            ]);
            $return_color = Color::updateOrCreate(
            [
                'color_code' => $corData['code'],
                'product_id' => $product->id,
                'collection_id' => $collection?->id,
                'color_name' => $corData['code'],
                'color_description' => $corData['description'],
                'genero' => !empty($corData['genero']) ? $corData['genero'] : 'Unissex',
            ],[
                //'collection_id' => $collection?->id,
                'flag_product_id' => $flag ?? null,
                'numeracao_id' => $corData['numeracao_id'] ?? null,
                'data_mkt' => $this->parseDate($corData['data_mkt'] ?? null),
                'data_trade' => $this->parseDate($corData['data_trade'] ?? null),
                'data_cliente' => $this->parseDate($corData['data_cliente'] ?? null),
                'data_dtc' => $this->parseDate($corData['data_dtc'] ?? null),
                'active' => true
            ]);
             //dd($return_color);
        return $return_color;
    }

    private function resolveSizeRunIdFromSheet($value): ?int
    {
        $text = trim((string) ($value ?? ''));
        if ($text === '' || $text === '-') {
            return null;
        }

        if (!ctype_digit($text)) {
            return null;
        }

        $sizeRunId = (int) $text;
        if ($sizeRunId <= 0) {
            return null;
        }

        return SizeRun::whereKey($sizeRunId)->exists() ? $sizeRunId : null;
    }

    private function normalizeSizeRunArticleGen($value): array
    {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '' || $raw === '-') {
            return [
                'value' => null,
                'sheet' => null,
                'label' => null,
                'is_invalid' => false,
                'original' => $raw,
            ];
        }

        $normalized = Str::upper(Str::ascii($raw));
        $normalized = preg_replace('/[^A-Z0-9]+/u', '_', $normalized);
        $normalized = preg_replace('/_+/', '_', (string) $normalized);
        $normalized = trim((string) $normalized, '_');

        $map = [
            'M' => ['db' => 'men', 'sheet' => 'M', 'label' => 'Article M'],
            'MEN' => ['db' => 'men', 'sheet' => 'M', 'label' => 'Article M'],
            'MAN' => ['db' => 'men', 'sheet' => 'M', 'label' => 'Article M'],
            'MALE' => ['db' => 'men', 'sheet' => 'M', 'label' => 'Article M'],
            'MASCULINO' => ['db' => 'men', 'sheet' => 'M', 'label' => 'Article M'],
            'W' => ['db' => 'women', 'sheet' => 'W', 'label' => 'Article W'],
            'WOMEN' => ['db' => 'women', 'sheet' => 'W', 'label' => 'Article W'],
            'WOMAN' => ['db' => 'women', 'sheet' => 'W', 'label' => 'Article W'],
            'FEMALE' => ['db' => 'women', 'sheet' => 'W', 'label' => 'Article W'],
            'FEMININO' => ['db' => 'women', 'sheet' => 'W', 'label' => 'Article W'],
        ];

        if (isset($map[$normalized])) {
            return [
                'value' => $map[$normalized]['db'],
                'sheet' => $map[$normalized]['sheet'],
                'label' => $map[$normalized]['label'],
                'is_invalid' => false,
                'original' => $raw,
            ];
        }

        return [
            'value' => null,
            'sheet' => null,
            'label' => null,
            'is_invalid' => true,
            'original' => $raw,
        ];
    }

    private function registerSizeRunSyncError(&$syncResults, string $message): void
    {
        if (!is_array($syncResults)) {
            return;
        }

        $syncResults['errors']++;
        $syncResults['messages'][] = $message;
    }

    private function syncColorSizeRunFromSheet($colorModel, array $corData, &$syncResults = null, $sku = null, $rowIndexes = null, $colorCode = null): void
    {
        $tableRaw = trim((string) ($corData['size_run_table'] ?? ''));
        $articleCode = trim((string) ($corData['size_run_article_cod'] ?? ''));
        $articleGenRaw = $corData['size_run_article_gen'] ?? null;

        if ($tableRaw === '' || $tableRaw === '-') {
            if (method_exists($colorModel, 'sizeRun')) {
                $colorModel->sizeRun()->delete();
            }
            return;
        }

        $rows = is_array($rowIndexes) ? implode(', ', $rowIndexes) : (string) $rowIndexes;
        $rowsText = $rows !== '' ? " (linhas {$rows})" : '';
        $skuText = !empty($sku) ? " SKU {$sku}" : '';
        $colorText = !empty($colorCode) ? " cor {$colorCode}" : '';

        $sizeRunId = $this->resolveSizeRunIdFromSheet($tableRaw);
        if ($sizeRunId === null) {
            $this->registerSizeRunSyncError(
                $syncResults,
                "TABELA ME {$tableRaw} é inválida ou não existe para o produto{$skuText}{$colorText}{$rowsText}."
            );
            return;
        }

        if ($articleCode === '') {
            $this->registerSizeRunSyncError(
                $syncResults,
                "TABELA ME_ARTICLE_COD é obrigatória quando TABELA ME estiver preenchida no produto{$skuText}{$colorText}{$rowsText}."
            );
            return;
        }

        $articleGen = $this->normalizeSizeRunArticleGen($articleGenRaw);
        if ($articleGen['is_invalid'] || empty($articleGen['value'])) {
            $this->registerSizeRunSyncError(
                $syncResults,
                "TABELA ME_ARTICLE_GEN {$articleGen['original']} é inválida. Valores aceitos: M ou W{$skuText}{$colorText}{$rowsText}."
            );
            return;
        }

        $colorModel->sizeRun()->updateOrCreate(
            ['color_id' => $colorModel->id],
            [
                'size_run_id' => $sizeRunId,
                'article_label' => $articleGen['label'],
                'article_value' => $articleCode,
                'me_article_gen' => $articleGen['value'],
                'is_enabled' => true,
            ]
        );
    }

    private function syncTecnologias($tecnologia)
    {
        // Remove tecnologias existentes
        TechnologyItem::where('name', $tecnologia->name)->delete();

        // Adiciona novas tecnologias baseadas nos dados da planilha
        TechnologyItem::create([
            'technology_category_id' => 1,
            'name' => $tecnologia->name ?? null,
            'active' => true
        ]);
    }

    /**
     * Sincroniza características do produto
     */
    private function syncCharacteristics($product, $data)
    {
        // Remove características existentes
        CaracteristicaProduct::where('product_id', $product->id)->delete();

        // Adiciona novas características baseadas nos dados da planilha
        $characteristics = [
            'peso_1' => $data['PESO_1'] ?? '',
            'peso_1_ref' => $data['PESO_1_REF'] ?? '',
            'peso_2' => $data['PESO_2'] ?? '',
            'peso_2_ref' => $data['PESO_2_REF'] ?? '',
            'drop' => $data['DROP'] ?? '',
            'origem' => $data['ORIGEM'] ?? '',
            'indicacao' => $data['INDICAÇÃO'] ?? '',
            'linha' => $data['LINHA'] ?? '',
            'composicao' => $data['COMPOSIÇÃO'] ?? '',
            //'medidas' => $data['MEDIDAS'] ?? '',
            //'tecnologias' => $data['TECNOLOGIAS'] ?? '',
            //'descricao' => $data['DESCRIÇÃO'] ?? '',
            //'informacoes' => $data['INFORMACÕES DO PRODUTO'] ?? '',
        ];

        foreach ($characteristics as $key => $value) {
            if (!empty($value)) {
                CaracteristicaProduct::create([
                    'product_id' => $product->id,
                    'title' => ucfirst(str_replace('_', ' ', $key)),
                    'description' => $value,
                    'destaque' => 0
                ]);
            }
        }
    }

    /**
     * Sincroniza numerações do produto
     */
    private function syncNumeracoes($product, $data)
    {
        // Remove numerações existentes
        $product->numeracoes()->detach();

        // Adiciona novas numerações baseadas nos dados da planilha
        $numeracaoData = $data['NUMERAÇÃO'] ?? '';
        $tamanhosData = $data['TAMANHOS'] ?? '';

        // Combina dados de numeração e tamanhos
        $allSizes = array_merge(
            explode(',', $numeracaoData),
            explode(',', $tamanhosData)
        );

        foreach ($allSizes as $numero) {
            $numero = trim($numero);
            if (!empty($numero) && $numero !== '-') {
                $numeracao = Numeracao::firstOrCreate(
                    ['numero' => $numero],
                    ['active' => true]
                );
                $product->numeracoes()->attach($numeracao->id);
            }
        }
    }

    /**
     * Sincroniza links do produto
     */
    private function syncLinks($product, $data)
    {
        // Remove links existentes
        LinksProduct::where('product_id', $product->id)->delete();

        // Adiciona novos links baseados nos dados da planilha
        $links = [
            [
                'url' => $data['LINK 1'] ?? '',
                'description' => $data['LINK 1_DESCRICAO'] ?? ''
            ],
            [
                'url' => $data['LINK 2'] ?? '',
                'description' => 'Link 2'
            ]
        ];

        foreach ($links as $linkData) {
            if (!empty($linkData['url']) && $linkData['url'] !== '-') {
                LinksProduct::create([
                    'product_id' => $product->id,
                    'link_url' => $linkData['url'],
                    'link_title' => $linkData['description'],
                    'access_levels' => null,
                ]);
            }
        }
    }

    /**
     * Sincroniza entrada no calendário
     */
    private function syncCalendar($product, $data)
    {
        // Remove entrada existente no calendário
        Calendario::where('product_id', $product->id)->delete();

        $launchDates = $this->resolveCalendarLaunchDatesFromColors($product);
        $dataMkt = $launchDates['data_mkt'];
        $dataTrade = $launchDates['data_trade'];
        $dataCliente = $launchDates['data_cliente'];
        $dataDtc = $launchDates['data_dtc'];

        $hasAnyDate = $launchDates['has_dates'];
        if ($hasAnyDate) {
            $baseDate = $launchDates['base_date'];
            $baseCarbon = null;
            try {
                $baseCarbon = $baseDate instanceof Carbon ? $baseDate : Carbon::parse($baseDate);
            } catch (\Exception $e) {
                $baseCarbon = null;
            }

            // Trunca a descrição para evitar erro de dados muito longos
            $info2 = (string) ($product->description ?? '');
            if (mb_strlen($info2, 'UTF-8') > 255) {
                $info2 = mb_substr($info2, 0, 252, 'UTF-8') . '...';
            }
            
            Calendario::create([
                'title' => $product->name,
                'img' => '',
                'ano' => $baseCarbon ? $baseCarbon->year : date('Y'),
                'mes' => $baseCarbon ? $baseCarbon->month : date('n'),
                'info_1' => 'Lançamento',
                'info_2' => $info2,
                'data' => $dataMkt,
                'data_mkt' => $dataMkt,
                'data_trade' => $dataTrade,
                'data_cliente' => $dataCliente,
                'data_dtc' => $dataDtc,
                'product_id' => $product->id
            ]);
        }
    }

    private function resolveCalendarLaunchDatesFromColors($product): array
    {
        $product->loadMissing('colors');

        $dates = [
            'data_mkt' => null,
            'data_trade' => null,
            'data_cliente' => null,
            'data_dtc' => null,
        ];

        foreach ($product->colors as $color) {
            foreach (array_keys($dates) as $field) {
                $value = $color->{$field} ?? null;
                if (!$value) {
                    continue;
                }

                $carbon = $value instanceof Carbon ? $value : Carbon::parse($value);
                if (!$dates[$field] || $carbon->lt($dates[$field])) {
                    $dates[$field] = $carbon->copy();
                }
            }
        }

        $baseDate = $dates['data_mkt'] ?: ($dates['data_trade'] ?: ($dates['data_cliente'] ?: $dates['data_dtc']));

        return array_merge($dates, [
            'base_date' => $baseDate,
            'has_dates' => (bool) $baseDate,
        ]);
    }

    /**
     * Processa a coluna CLIENTE_SEGMENTO e retorna array de IDs das segmentações
     */
    private function processClienteSegmento($clienteSegmentoString)
    {
        if (empty($clienteSegmentoString)) {
            return [];
        }

        // Separa os valores por vírgula
        $segmentos = explode(',', $clienteSegmentoString);
        $segmentacaoIds = [];

        foreach ($segmentos as $segmento) {
            $segmento = trim($segmento);

            if (!empty($segmento) && $segmento !== '-') {
                $segmentacaoCliente = $this->upsertSegmentacaoCliente(
                    $segmento,
                    null,
                    'Segmentacao criada automaticamente via sincronizacao'
                );
                $segmentacaoIds[] = $segmentacaoCliente->id;
            }
        }

        return $segmentacaoIds;
    }

    /**
     * Extrai o valor de numeração por cor a partir de possíveis cabeçalhos da linha da planilha
     */
    private function extractColorNumeracao(array $row): ?string
    {
        // Lista de chaves possíveis que podem representar numeração por cor
        $possibleKeys = [
            'NUMERAÇÃO',
            'TAMANHOS'
        ];

        foreach ($possibleKeys as $key) {
            if (array_key_exists($key, $row)) {
                $value = trim((string)($row[$key] ?? ''));
                if ($value !== '' && $value !== '-') {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Busca ou cria uma numeração e retorna seu ID
     */
    private function findOrCreateNumeracaoId(?string $numeracaoString): ?int
    {
        if ($numeracaoString === null) {
            return null;
        }

        $numero = trim($numeracaoString);
        if ($numero === '' || $numero === '-') {
            return null;
        }

        $numeracao = Numeracao::firstOrCreate(
            ['numero' => $numero],
            ['active' => true]
        );

        return $numeracao->id;
    }

    private function buildHeaderIndex(array $headers): array
    {
        $map = [];
        $normalizedIndexes = [];

        foreach ($headers as $i => $header) {
            $key = trim((string) $header);
            if ($key === '') {
                $key = 'coluna_' . ($i + 1);
            }

            $map[$key] = $i;

            $normalized = $this->normalizeSheetHeader($key);
            $normalizedIndexes[$normalized] = $normalizedIndexes[$normalized] ?? [];
            $normalizedIndexes[$normalized][] = $i;
        }

        $aliases = [
            'CÓDIGO' => ['CODIGO'],
            'COR_COD' => ['COR_COD', 'CORCOD', 'COR_CODIGO', 'CORCODIGO'],
            'COR_DESCRIÇÃO' => ['COR_DESCRICAO', 'COR_DESCRICAO_'],
            'COR_CLASSIFICAÇÃO' => [
                'COR_CLASSIFICACAO',
                'COR_CLASSIFICACAO_',
                'COR_CLASSIFICACAO_FLAG',
                'CLASSIFICACAO_COR',
                'CLASSIFICACAO_DA_COR',
                'FLAG',
            ],
            'CLIENTE_SEGMENTO' => ['CLIENTE_SEGMENTO', 'CLIENTE_SEGMETNO', 'CLIENTE_SEGMENTACAO', 'CLIENTE_SEGMENTAÇÃO'],
            'COLEÇÃO' => ['COLECAO'],
            'COLEÇÃO_SECUNDÁRIA' => ['COLECAO_SECUNDARIA', 'COLECAO_SECUNDARIA_'],
            'NUMERAÇÃO' => ['NUMERACAO', 'NUMERACAO_TAMANHO', 'NUMERACAO_TAMANHOS'],
            'MEDIDAS' => ['MEDIDAS', 'MEDIDA', 'TABELA_MEDIDAS', 'TABELA_DE_MEDIDAS'],
            'GRADES' => ['GRADES', 'GRADE'],
            'PERIODO_VENDA' => ['PERIODO_VENDA', 'PERIODO_DE_VENDA', 'PERIODO_VENDAS'],
        ];

        foreach ($aliases as $expected => $candidates) {
            if (array_key_exists($expected, $map)) {
                continue;
            }

            foreach ($candidates as $candidate) {
                $candidateNorm = $this->normalizeSheetHeader($candidate);
                if (!empty($normalizedIndexes[$candidateNorm])) {
                    $map[$expected] = $normalizedIndexes[$candidateNorm][0];
                    break;
                }
            }
        }

        $classificationNorm = $this->normalizeSheetHeader('COR_CLASSIFICAÇÃO');
        $descriptionNorm = $this->normalizeSheetHeader('COR_DESCRIÇÃO');
        if (!isset($map['COR_CLASSIFICAÇÃO']) && !empty($normalizedIndexes[$descriptionNorm]) && count($normalizedIndexes[$descriptionNorm]) >= 2) {
            $map['COR_CLASSIFICAÇÃO'] = $normalizedIndexes[$descriptionNorm][1];
        }

        return $map;
    }

    private function normalizeSheetHeader(string $header): string
    {
        $header = trim($header);
        if ($header === '') {
            return '';
        }

        $ascii = Str::upper(Str::ascii($header));
        $ascii = preg_replace('/[^A-Z0-9]+/u', '_', $ascii);
        $ascii = preg_replace('/_+/', '_', (string) $ascii);
        $ascii = trim((string) $ascii, '_');
        return $ascii;
    }

    private function padRowToCount(array $row, int $count): array
    {
        $current = count($row);
        if ($current >= $count) {
            return array_slice($row, 0, $count);
        }

        return array_pad($row, $count, '');
    }

    private function indexToColumnLetter(int $index): string
    {
        $index = max(0, $index);
        $letters = '';

        while ($index >= 0) {
            $letters = chr(($index % 26) + 65) . $letters;
            $index = intdiv($index, 26) - 1;
        }

        return $letters;
    }

    private function applyValuesToRow(array &$rowValues, array $headerIndex, array $values): void
    {
        foreach ($values as $column => $value) {
            if (!array_key_exists($column, $headerIndex)) {
                continue;
            }

            $rowValues[$headerIndex[$column]] = $value === null ? '' : (string) $value;
        }
    }

    private function formatSheetDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        try {
            $carbon = $date instanceof Carbon ? $date : Carbon::parse($date);
            return $carbon->format('d/m/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    private function getTecnologiasAsString(Product $product): string
    {
        $raw = $product->technologies ?? null;
        if (empty($raw)) {
            return '';
        }

        $ids = json_decode($raw, true);
        if (!is_array($ids) || empty($ids)) {
            return '';
        }

        $names = TechnologyItem::withTrashed()
            ->whereIn('id', $ids)
            ->pluck('name')
            ->filter()
            ->values()
            ->all();

        $text = implode(', ', $names);
        $text = str_replace(';', ',', $text);
        $text = preg_replace('/\s*,\s*/', ', ', $text);
        return trim((string) $text);
    }

    private function getMeasureCategoriesAsString(Product $product): string
    {
        $categories = $product->relationLoaded('measureCategories') ? $product->measureCategories : collect();
        if (empty($categories) || $categories->isEmpty()) {
            return '';
        }

        return $categories
            ->pluck('name')
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');
    }

    private function buildCaracteristicasSheetValues(Product $product): array
    {
        $map = [
            'peso 1' => 'PESO_1',
            'peso 1 ref' => 'PESO_1_REF',
            'peso 2' => 'PESO_2',
            'peso 2 ref' => 'PESO_2_REF',
            'drop' => 'DROP',
            'origem' => 'ORIGEM',
            'indicação' => 'INDICAÇÃO',
            'indicacao' => 'INDICAÇÃO',
            'linha' => 'LINHA',
            'composição' => 'COMPOSIÇÃO',
            'composicao' => 'COMPOSIÇÃO',
        ];

        $out = [];
        foreach ($product->caracteristicas ?? [] as $car) {
            $title = trim(mb_strtolower((string) ($car->title ?? ''), 'UTF-8'));
            if ($title === '' || !array_key_exists($title, $map)) {
                continue;
            }
            $out[$map[$title]] = (string) ($car->description ?? '');
        }

        return $out;
    }

    private function buildBaseProductSheetValues(Product $product): array
    {
        $links = $product->links ?? collect();
        $links = $links instanceof \Illuminate\Support\Collection ? $links->values() : collect($links)->values();

        $link1 = $links->get(0);
        $link2 = $links->get(1);

        $values = [
            'NOME' => (string) ($product->name ?? ''),
            'DESCRIÇÃO' => (string) ($product->description ?? ''),
            'CÓDIGO' => (string) ($product->sku ?? $product->code ?? ''),
            'LINHA' => (string) ($product->linha ?? ''),
            'PDV' => (string) ($product->price ?? ''),
            'CATEGORIA' => (string) ($product->category?->name ?? ''),
            'SUBCATEGORIA' => (string) ($product->subcategory?->faixa ?? ''),
            'PRODUTOS_SEGMENTO' => (string) ($product->category?->segmentacao?->segmento ?? ''),
            'TECNOLOGIAS' => $this->getTecnologiasAsString($product),
            'MEDIDAS' => $this->getMeasureCategoriesAsString($product),
            'LINK 1' => (string) ($link1->link_url ?? ''),
            'LINK 1_DESCRICAO' => (string) ($link1->link_title ?? ''),
            'LINK 2' => (string) ($link2->link_url ?? ''),
        ];

        return array_merge($values, $this->buildCaracteristicasSheetValues($product));
    }

    private function getColorFlagsAsString($color): string
    {
        if (empty($color)) {
            return '';
        }

        $titles = [];

        if (method_exists($color, 'flagProducts')) {
            $flags = $color->relationLoaded('flagProducts') ? $color->flagProducts : null;
            if ($flags) {
                $titles = $flags
                    ->pluck('flag_title')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();
            }
        }

        if (empty($titles) && $color->flagProduct) {
            $title = trim((string) ($color->flagProduct->flag_title ?? ''));
            if ($title !== '') {
                $titles = [$title];
            }
        }

        return implode(', ', $titles);
    }

    private function getColorShoeGridsAsString($color): string
    {
        if (empty($color) || !method_exists($color, 'shoeGrids')) {
            return '';
        }

        $shoeGrids = $color->relationLoaded('shoeGrids') ? $color->shoeGrids : collect();
        if (empty($shoeGrids) || $shoeGrids->isEmpty()) {
            return '';
        }

        return $shoeGrids
            ->pluck('code')
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');
    }

    private function getColorPeriodoVendaAsString($color): string
    {
        if (empty($color)) {
            return '';
        }

        $periodoVendas = $color->periodo_vendas ?? [];
        if (!is_array($periodoVendas) || empty($periodoVendas)) {
            return '';
        }

        $periodoVendas = array_values(array_unique(array_filter(array_map(function ($mes) {
            $mesInt = (int) $mes;
            return ($mesInt >= 1 && $mesInt <= 12) ? $mesInt : null;
        }, $periodoVendas))));

        sort($periodoVendas);

        $monthLabels = [
            1 => 'Jan',
            2 => 'Fev',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'Mai',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Set',
            10 => 'Out',
            11 => 'Nov',
            12 => 'Dez',
        ];

        $labels = array_values(array_filter(array_map(function ($mes) use ($monthLabels) {
            return $monthLabels[$mes] ?? null;
        }, $periodoVendas)));

        return implode(', ', $labels);
    }

    private function applyColorValuesToRow(array &$rowValues, array $headerIndex, Product $product, $color): void
    {
        $collectionName = $color?->collection?->name ?? '';
        $collectionSecondary = $color?->collection?->description ?? '';

        $segmentacoesCliente = '';
        if ($color && $color->segmentacoesCliente) {
            $segmentacoesCliente = $color->segmentacoesCliente
                ->pluck('nome')
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
        }

        $numeracao = $color?->numeracao?->numero ?? '';
        if ($numeracao === '' && $product->numeracoes) {
            $numeracao = $product->numeracoes
                ->pluck('numero')
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
        }

        $values = [
            'COLEÇÃO' => (string) $collectionName,
            'COLEÇÃO_SECUNDÁRIA' => (string) $collectionSecondary,
            'COR_COD' => (string) ($color?->color_code ?? ''),
            'COR_DESCRIÇÃO' => (string) ($color?->color_description ?? $color?->color_code ?? ''),
            'COR_CLASSIFICAÇÃO' => $this->getColorFlagsAsString($color),
            'GENERO' => (string) ($color?->genero ?? ''),
            'CLIENTE_SEGMENTO' => (string) $segmentacoesCliente,
            'NUMERAÇÃO' => (string) $numeracao,
            'GRADES' => $this->getColorShoeGridsAsString($color),
            'PERIODO_VENDA' => $this->getColorPeriodoVendaAsString($color),
            'LANÇAMENTO' => $this->formatSheetDate($color?->data_mkt ?? null),
            'LANÇAMENTO_TRADE' => $this->formatSheetDate($color?->data_trade ?? null),
            'LANÇAMENTO_CLIENTE' => $this->formatSheetDate($color?->data_cliente ?? null),
            'LANÇAMENTO_DTC' => $this->formatSheetDate($color?->data_dtc ?? null),
            'TABELA ME' => (string) ($color?->sizeRun?->size_run_id ?? ''),
            'TABELA ME_ARTICLE_GEN' => (string) ($this->normalizeSizeRunArticleGen($color?->sizeRun?->me_article_gen ?? null)['sheet'] ?? ''),
            'TABELA ME_ARTICLE_COD' => (string) ($color?->sizeRun?->article_value ?? ''),
        ];

        $this->applyValuesToRow($rowValues, $headerIndex, $values);
    }

    private function hasLaunchDatesForAnyColor(array $colors, array $data): bool
    {
        foreach (['LANÇAMENTO', 'LANÇAMENTO_TRADE', 'LANÇAMENTO_CLIENTE', 'LANÇAMENTO_DTC'] as $field) {
            if (!empty($data[$field])) {
                return true;
            }
        }

        foreach ($colors as $color) {
            foreach (['data_mkt', 'data_trade', 'data_cliente', 'data_dtc'] as $field) {
                if (!empty($color[$field])) {
                    return true;
                }
            }
        }

        return false;
    }

    private function getProductIdsToReverseSync(?Carbon $since): array
    {
        if ($since === null) {
            return Product::whereNull('deleted_at')->pluck('id')->all();
        }

        $ids = [];

        $ids = array_merge($ids, Product::whereNull('deleted_at')->where('updated_at', '>=', $since)->pluck('id')->all());
        $ids = array_merge($ids, Color::whereNull('deleted_at')->where('updated_at', '>=', $since)->pluck('product_id')->all());
        $ids = array_merge($ids, CaracteristicaProduct::whereNull('deleted_at')->where('updated_at', '>=', $since)->pluck('product_id')->all());
        $ids = array_merge($ids, LinksProduct::whereNull('deleted_at')->where('updated_at', '>=', $since)->pluck('product_id')->all());
        $ids = array_merge($ids, Calendario::whereNull('deleted_at')->where('updated_at', '>=', $since)->pluck('product_id')->all());

        try {
            $ids = array_merge($ids, DB::table('product_numeracao')->where('updated_at', '>=', $since)->pluck('product_id')->all());
        } catch (\Exception $e) {
        }

        try {
            $ids = array_merge($ids, DB::table('product_measure_categories')->where('updated_at', '>=', $since)->pluck('product_id')->all());
        } catch (\Exception $e) {
        }

        try {
            $ids = array_merge(
                $ids,
                DB::table('color_segmentacao_cliente')
                    ->join('colors', 'color_segmentacao_cliente.color_id', '=', 'colors.id')
                    ->where('color_segmentacao_cliente.updated_at', '>=', $since)
                    ->whereNull('colors.deleted_at')
                    ->pluck('colors.product_id')
                    ->all()
            );
        } catch (\Exception $e) {
        }

        try {
            $ids = array_merge(
                $ids,
                DB::table('color_shoe_grids')
                    ->join('colors', 'color_shoe_grids.color_id', '=', 'colors.id')
                    ->where('color_shoe_grids.updated_at', '>=', $since)
                    ->whereNull('colors.deleted_at')
                    ->pluck('colors.product_id')
                    ->all()
            );
        } catch (\Exception $e) {
        }

        try {
            $ids = array_merge(
                $ids,
                DB::table('color_flag_product')
                    ->join('colors', 'color_flag_product.color_id', '=', 'colors.id')
                    ->where('color_flag_product.updated_at', '>=', $since)
                    ->whereNull('colors.deleted_at')
                    ->pluck('colors.product_id')
                    ->all()
            );
        } catch (\Exception $e) {
        }

        try {
            $ids = array_merge(
                $ids,
                DB::table('color_size_runs')
                    ->join('colors', 'color_size_runs.color_id', '=', 'colors.id')
                    ->where('color_size_runs.updated_at', '>=', $since)
                    ->whereNull('colors.deleted_at')
                    ->pluck('colors.product_id')
                    ->all()
            );
        } catch (\Exception $e) {
        }

        $ids = array_values(array_unique(array_filter($ids, function ($id) {
            return !empty($id);
        })));

        return $ids;
    }

    /**
     * Sincroniza usuários/representantes da planilha
     */
    public function syncUsers()
    {
        // Aumentar o tempo limite de execução para 10 minutos
        set_time_limit(600);

        // Aumentar o limite de memória
        ini_set('memory_limit', '512M');

        $spreadsheetId = "1skMcMlapMDLis7oZCz2dyRzFPMBfEmDoMLzYqqIInkU";
        $syncResults = [
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
            'messages' => [],
            'created_users' => [] // Para armazenar usuários criados com senhas
        ];

        try {
            // Lê os dados da aba REPRESENTANTES
            $dataRange = "REPRESENTANTES!A2:F";
            $rows = $this->sheetService->readSheet($spreadsheetId, $dataRange);

            if (empty($rows)) {
                throw new \Exception('Nenhum dado encontrado na aba REPRESENTANTES.');
            }

            $batchSize = 100; // Processar em lotes de 100
            $totalRows = count($rows);
            $processedRows = 0;

            // Processar em lotes para evitar timeout
            for ($batch = 0; $batch < $totalRows; $batch += $batchSize) {
                $endBatch = min($batch + $batchSize - 1, $totalRows - 1);

                DB::beginTransaction();

                try {
                    for ($rowIndex = $batch; $rowIndex <= $endBatch; $rowIndex++) {
                        $row = $rows[$rowIndex];

                        // Mapeia os dados conforme especificado
                        $userData = [
                            'representante_nome' => $row[0] ?? '', // Coluna A
                            'lider_ebm_comercial' => $row[1] ?? '', // Coluna B
                            'nome_fantasia_ebm' => $row[2] ?? '', // Coluna C
                            'email' => $row[4] ?? '', // Coluna E
                            'segmentacao_cliente' => $row[5] ?? '' // Coluna F
                        ];

                        // Pula linhas vazias ou sem dados essenciais
                        if (empty($userData['representante_nome']) || empty($userData['email'])) {
                            $syncResults['skipped']++;
                            $syncResults['messages'][] = "Linha " . ($rowIndex + 2) . " ignorada: Nome do representante ou email vazio";
                            continue;
                        }

                        try {
                            $result = $this->syncUserWithSegmentacao($userData);
                            if ($result['created']) {
                                $syncResults['created_users'][] = [
                                    'name' => $result['user']->name,
                                    'email' => $result['user']->email,
                                    'password' => $result['password']
                                ];
                            }
                            $syncResults['success']++;
                            $processedRows++;
                        } catch (\Exception $e) {
                            $syncResults['errors']++;
                            $syncResults['messages'][] = "Erro no usuário {$userData['representante_nome']} (linha " . ($rowIndex + 2) . "): " . $e->getMessage();
                            Log::error("Erro ao sincronizar usuário {$userData['representante_nome']}", [
                                'error' => $e->getMessage(),
                                'data' => $userData
                            ]);
                        }
                    }

                    DB::commit();

                    // Log do progresso
                    Log::info("Lote processado: {$processedRows}/{$totalRows} usuários");
                } catch (\Exception $e) {
                    DB::rollBack();
                    throw $e;
                }
            }

            // Gera arquivo Excel com usuários criados e suas senhas
            if (!empty($syncResults['created_users'])) {
                $this->generateUsersPasswordsFile($syncResults['created_users']);
            }

            $message = "Sincronização de usuários concluída! Sucessos: {$syncResults['success']}, Erros: {$syncResults['errors']}, Ignorados: {$syncResults['skipped']}";

            if (!empty($syncResults['created_users'])) {
                $message .= "\n\nForam criados " . count($syncResults['created_users']) . " novos usuários. Arquivo com senhas gerado em storage/app/users_passwords.xlsx";
            }

            if (!empty($syncResults['messages'])) {
                $message .= "\n\nDetalhes dos erros:\n" . implode("\n", $syncResults['messages']);
            }

            return back()->with('success', $message);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollback();
            }
            Log::error('Erro geral na sincronização de usuários', ['error' => $e->getMessage()]);
            return back()->with('error', 'Erro na sincronização de usuários: ' . $e->getMessage());
        }
    }

    /**
     * Sincroniza um usuário individual com suas segmentações
     */
    private function syncUserWithSegmentacao($data)
    {
        $password = null;
        $created = false;

        // Verifica se o usuário já existe
        $user = User::where('email', $data['email'])->first();

        if (!$user) {
            // Gera senha aleatória para novos usuários
            $password = $this->generateRandomPassword();

            // Cria novo usuário com hash mais rápido
            $user = User::create([
                'name' => $data['representante_nome'],
                'email' => $data['email'],
                'password' => Hash::make($password, ['rounds' => 4]), // Reduz custo do hash
                'type' => 'user', // Tipo padrão para representantes
                'company' => $data['nome_fantasia_ebm'],
                'codigo_lider_comercial' => $data['lider_ebm_comercial']
            ]);

            $created = true;
        } else {
            // Atualiza dados do usuário existente
            $user->update([
                'name' => $data['representante_nome'],
                'company' => $data['nome_fantasia_ebm'],
                'codigo_lider_comercial' => $data['lider_ebm_comercial']
            ]);
        }

        // Processa segmentações de cliente
        $segmentacaoIds = $this->processClienteSegmentoForUser($data['segmentacao_cliente']);

        // Sincroniza segmentações de cliente
        if (!empty($segmentacaoIds)) {
            $user->segmentacoesCliente()->sync($segmentacaoIds);
        }

        return [
            'user' => $user,
            'password' => $password,
            'created' => $created
        ];
    }

    /**
     * Processa segmentações de cliente para usuários
     */
    private function processClienteSegmentoForUser($segmentacaoString)
    {
        if (empty($segmentacaoString)) {
            return [];
        }

        // Separa os valores por vírgula
        $segmentos = explode(',', $segmentacaoString);
        $segmentacaoIds = [];

        foreach ($segmentos as $segmento) {
            $segmento = trim($segmento);

            if (!empty($segmento)) {
                $segmentacaoCliente = $this->upsertSegmentacaoCliente(
                    $segmento,
                    null,
                    'Segmentacao criada automaticamente via sincronizacao de usuarios'
                );
                $segmentacaoIds[] = $segmentacaoCliente->id;
            }
        }

        return $segmentacaoIds;
    }

    private function resolveSegmentacaoReference(?string $reference): ?int
    {
        $reference = trim((string) $reference);

        if ($reference === '' || $reference === '-') {
            return null;
        }

        if (ctype_digit($reference)) {
            $segmentacao = Segmentacao::find((int) $reference);
            if ($segmentacao) {
                return $segmentacao->id;
            }
        }

        $slug = Str::slug($reference);

        $segmentacao = Segmentacao::query()
            ->where('slug', $slug)
            ->orWhereRaw('LOWER(segmento) = ?', [Str::lower($reference)])
            ->first();

        return $segmentacao?->id;
    }

    private function upsertSegmentacaoCliente(string $segmento, ?int $produtosSegmentos = null, string $descricao = 'Segmentacao criada automaticamente via sincronizacao', ?string $linha = null): SegmentacaoCliente
    {
        $segmento = trim($segmento);
        $slug = Str::slug($segmento);

        $attributes = [
            'nome' => $segmento,
            'descricao' => $descricao,
            'active' => true,
            'deleted_at' => null,
        ];

        if ($produtosSegmentos !== null) {
            $attributes['produtos_segmentos'] = $produtosSegmentos;
        }

        if ($linha !== null) {
            $linha = trim($linha);
            $attributes['linha'] = $linha !== '' && $linha !== '-' ? $linha : null;
        }

        $segmentacaoCliente = SegmentacaoCliente::withTrashed()->updateOrCreate(
            ['slug' => $slug],
            $attributes
        );

        if ($segmentacaoCliente->trashed()) {
            $segmentacaoCliente->restore();
        }

        return $segmentacaoCliente;
    }


    /**
     * Gera uma senha aleatória de forma mais eficiente
     */
    private function generateRandomPassword($length = 8)
    {
        // Usar método mais eficiente para gerar senhas
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, $length);
    }

    /**
     * Processa um lote de usuários (usado pelos jobs assíncronos)
     */
    public function processBatchUsers($batchData, $batchNumber)
    {
        $results = [
            'success' => 0,
            'errors' => 0,
            'created_users' => [],
            'batch_number' => $batchNumber
        ];

        DB::beginTransaction();

        try {
            foreach ($batchData as $rowIndex => $userData) {
                try {
                    $result = $this->syncUserWithSegmentacao($userData);

                    if ($result['created']) {
                        $results['created_users'][] = [
                            'name' => $result['user']->name,
                            'email' => $result['user']->email,
                            'password' => $result['password']
                        ];
                    }

                    $results['success']++;
                } catch (\Exception $e) {
                    $results['errors']++;
                    Log::error("Erro ao processar usuário no lote {$batchNumber}", [
                        'error' => $e->getMessage(),
                        'data' => $userData
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    /**
     * Sincronização assíncrona para grandes volumes (via jobs)
     */
    public function syncUsersAsync()
    {
        try {
            $spreadsheetId = "1skMcMlapMDLis7oZCz2dyRzFPMBfEmDoMLzYqqIInkU";
            $dataRange = "REPRESENTANTES!A2:F";
            $rows = $this->sheetService->readSheet($spreadsheetId, $dataRange);

            if (empty($rows)) {
                return redirect()->route('admin.sync')->with('error', 'Nenhum dado encontrado na aba REPRESENTANTES.');
            }

            $batchSize = 50; // Lotes menores para jobs
            $totalRows = count($rows);
            $totalBatches = ceil($totalRows / $batchSize);

            // Preparar dados em lotes
            for ($batch = 0; $batch < $totalRows; $batch += $batchSize) {
                $endBatch = min($batch + $batchSize - 1, $totalRows - 1);
                $batchData = [];

                for ($i = $batch; $i <= $endBatch; $i++) {
                    $row = $rows[$i];

                    if (count($row) < 5) continue;

                    $userData = [
                        'representante_nome' => $row[0] ?? '',
                        'lider_ebm_comercial' => $row[1] ?? '',
                        'nome_fantasia_ebm' => $row[2] ?? '',
                        'email' => $row[4] ?? '',
                        'segmentacao_cliente' => $row[5] ?? ''
                    ];

                    if (!empty($userData['email']) && filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
                        $batchData[] = $userData;
                    }
                }

                if (!empty($batchData)) {
                    $batchNumber = ($batch / $batchSize) + 1;
                    \App\Jobs\SyncUsersJob::dispatch($batchData, $batchNumber, $totalBatches);
                }
            }

            $message = "Sincronização assíncrona iniciada! {$totalBatches} lotes foram enviados para processamento. ";
            $message .= "Verifique os logs para acompanhar o progresso.";

            return redirect()->route('admin.sync')->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Erro na sincronização assíncrona de usuários: ' . $e->getMessage());
            return redirect()->route('admin.sync')->with('error', 'Erro na sincronização assíncrona: ' . $e->getMessage());
        }
    }

    /**
     * Gera arquivo Excel com usuários e senhas
     */
    private function generateUsersPasswordsFile($users)
    {
        $data = [];
        $data[] = ['Nome', 'Email', 'Senha']; // Cabeçalho

        foreach ($users as $user) {
            $data[] = [
                $user['name'],
                $user['email'],
                $user['password']
            ];
        }

        // Cria arquivo CSV simples (compatível com Excel)
        $filename = 'users_passwords_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/' . $filename);

        $file = fopen($filepath, 'w');

        // Adiciona BOM para UTF-8
        fwrite($file, "\xEF\xBB\xBF");

        foreach ($data as $row) {
            fputcsv($file, $row, ';'); // Usa ponto e vírgula como separador
        }

        fclose($file);

        Log::info("Arquivo de senhas gerado: {$filename}");

        return $filename;
    }

    /**
     * Exporta lista de usuários com senhas (para download)
     */
    public function exportUsersWithPasswords()
    {
        // Busca todos os usuários do tipo 'user' (representantes)
        $users = User::where('type', 'user')
            ->with('segmentacoesCliente')
            ->orderBy('name')
            ->get();

        $data = [];
        $data[] = ['Nome', 'Email', 'Empresa', 'Código Líder Comercial', 'Segmentações']; // Cabeçalho

        foreach ($users as $user) {
            $segmentacoes = $user->segmentacoesCliente->pluck('nome')->implode(', ');

            $data[] = [
                $user->name,
                $user->email,
                $user->company,
                $user->codigo_lider_comercial,
                $segmentacoes
            ];
        }

        // Cria arquivo CSV
        $filename = 'representantes_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = storage_path('app/' . $filename);

        $file = fopen($filepath, 'w');

        // Adiciona BOM para UTF-8
        fwrite($file, "\xEF\xBB\xBF");

        foreach ($data as $row) {
            fputcsv($file, $row, ';');
        }

        fclose($file);

        // Retorna o arquivo para download
        return response()->download($filepath)->deleteFileAfterSend(true);
    }

    /**
     * Prepara lotes de sincronização de usuários
     */
    public function prepareBatches()
    {
        try {
            $spreadsheetId = "1skMcMlapMDLis7oZCz2dyRzFPMBfEmDoMLzYqqIInkU";
            $representantesData = $this->sheetService->readSheet($spreadsheetId, 'REPRESENTANTES!A:Z');

            if (empty($representantesData)) {
                return redirect()->route('admin.sync')->with('error', 'Nenhum dado encontrado na aba REPRESENTANTES.');
            }

            // Remove cabeçalho
            array_shift($representantesData);

            // Divide em lotes de 1000
            $batches = array_chunk($representantesData, 1000);
            $totalBatches = count($batches);
            $totalRecords = count($representantesData);

            // Armazena informações dos lotes na sessão
            session([
                'sync_batches' => $batches,
                'total_batches' => $totalBatches,
                'total_records' => $totalRecords,
                'batch_status' => array_fill(0, $totalBatches, 'pending') // pending, processing, completed, error
            ]);

            return redirect()->route('admin.sync-representantes')->with('success', "Preparados {$totalBatches} lotes com {$totalRecords} registros. Use os botões abaixo para executar cada lote.");
        } catch (\Exception $e) {
            Log::error('Erro ao preparar lotes: ' . $e->getMessage());
            return redirect()->route('admin.sync-representantes')->with('error', 'Erro ao preparar lotes: ' . $e->getMessage());
        }
    }

    /**
     * Executa um lote específico
     */
    public function executeBatch($batchIndex)
    {
        try {
            $batches = session('sync_batches');
            $batchStatus = session('batch_status', []);
            $batchResults = session('batch_results', []);

            if (!$batches || !isset($batches[$batchIndex])) {
                return response()->json(['error' => 'Lote não encontrado'], 404);
            }

            // Marca lote como processando
            $batchStatus[$batchIndex] = 'processing';
            session(['batch_status' => $batchStatus]);

            $batchData = $batches[$batchIndex];
            $processedUsers = [];
            $errors = 0;
            $success = 0;

            // Configurações de performance
            ini_set('max_execution_time', 300); // 5 minutos
            ini_set('memory_limit', '512M');

            foreach ($batchData as $row) {
                try {
                    // Mapeia os dados conforme especificado
                    $userData = [
                        'representante_nome' => $row[0] ?? '', // Coluna A
                        'lider_ebm_comercial' => $row[1] ?? '', // Coluna B
                        'nome_fantasia_ebm' => $row[2] ?? '', // Coluna C
                        'email' => $row[4] ?? '', // Coluna E
                        'segmentacao_cliente' => $row[5] ?? '' // Coluna F
                    ];

                    // Pula linhas vazias ou sem dados essenciais
                    if (empty($userData['representante_nome']) || empty($userData['email'])) {
                        continue;
                    }

                    $result = $this->syncUserWithSegmentacao($userData);
                    if ($result) {
                        $processedUsers[] = [
                            'representante_nome' => $userData['representante_nome'],
                            'lider_ebm_comercial' => $userData['lider_ebm_comercial'],
                            'nome_fantasia_ebm' => $userData['nome_fantasia_ebm'],
                            'email' => $result['user']->email,
                            'segmentacao_cliente' => $userData['segmentacao_cliente'],
                            'password' => $result['password']
                        ];
                        $success++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Erro ao processar usuário no lote ' . ($batchIndex + 1) . ': ' . $e->getMessage());
                }
            }

            // Gera arquivo de senhas para este lote
            $filename = null;
            if (!empty($processedUsers)) {
                $filename = $this->generateBatchPasswordsFile($processedUsers, $batchIndex + 1);
            }

            // Marca lote como concluído
            $batchStatus[$batchIndex] = 'completed';
            session(['batch_status' => $batchStatus]);

            // Persiste resultados do lote para exibir após recarregar a página
            $batchResults[$batchIndex] = [
                'success' => $success,
                'errors' => $errors,
                'filename' => $filename,
                'message' => "Lote " . ($batchIndex + 1) . " processado com sucesso!",
            ];
            session(['batch_results' => $batchResults]);

            Log::info("Lote " . ($batchIndex + 1) . " processado com sucesso", [
                'success' => $success,
                'errors' => $errors,
                'filename' => $filename
            ]);

            return response()->json([
                'success' => true,
                'message' => "Lote " . ($batchIndex + 1) . " processado com sucesso!",
                'stats' => [
                    'success' => $success,
                    'errors' => $errors,
                    'filename' => $filename
                ]
            ]);
        } catch (\Exception $e) {
            // Marca lote como erro
            $batchStatus = session('batch_status', []);
            $batchStatus[$batchIndex] = 'error';
            session(['batch_status' => $batchStatus]);

            Log::error('Erro ao executar lote ' . ($batchIndex + 1) . ': ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Erro ao executar lote: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gera arquivo de senhas para um lote específico
     */
    private function generateBatchPasswordsFile($users, $batchNumber)
    {
        $data = [];
        $data[] = ['Representante Nome', 'Líder EBM Comercial', 'Nome Fantasia EBM', 'Email', 'Segmentação Cliente', 'Senha']; // Cabeçalho

        foreach ($users as $user) {
            $data[] = [
                $user['representante_nome'] ?? '',
                $user['lider_ebm_comercial'] ?? '',
                $user['nome_fantasia_ebm'] ?? '',
                $user['email'] ?? '',
                $user['segmentacao_cliente'] ?? '',
                $user['password'] ?? ''
            ];
        }

        $filename = 'lote_' . $batchNumber . '_senhas_' . date('Y-m-d_H-i-s') . '.csv';
        $dir = storage_path('app/public/export-users');
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $filepath = $dir . '/' . $filename;

        $file = fopen($filepath, 'w');

        // Adiciona BOM para UTF-8
        fwrite($file, "\xEF\xBB\xBF");

        foreach ($data as $row) {
            fputcsv($file, $row, ';');
        }

        fclose($file);

        @chmod($filepath, 0644);

        return $filename;
    }

    /**
     * Limpa dados dos lotes da sessão
     */
    public function clearBatches()
    {
        session()->forget(['sync_batches', 'total_batches', 'total_records', 'batch_status', 'batch_results']);
        return redirect()->route('admin.sync-representantes')->with('success', 'Dados dos lotes limpos com sucesso.');
    }

    /**
     * Retorna status dos lotes via AJAX
     */
    public function getBatchStatus()
    {
        return response()->json([
            'total_batches' => session('total_batches', 0),
            'total_records' => session('total_records', 0),
            'batch_status' => session('batch_status', [])
        ]);
    }
}
