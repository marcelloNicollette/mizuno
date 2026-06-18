<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            $table->date('data_mkt')->nullable()->after('periodo_vendas');
            $table->date('data_trade')->nullable()->after('data_mkt');
            $table->date('data_cliente')->nullable()->after('data_trade');
            $table->date('data_dtc')->nullable()->after('data_cliente');
        });

        DB::table('products')
            ->select('id', 'data_mkt', 'data_trade', 'data_cliente', 'data_dtc')
            ->orderBy('id')
            ->chunkById(100, function ($products) {
                foreach ($products as $product) {
                    $payload = [
                        'data_mkt' => $this->normalizeDate($product->data_mkt ?? null),
                        'data_trade' => $this->normalizeDate($product->data_trade ?? null),
                        'data_cliente' => $this->normalizeDate($product->data_cliente ?? null),
                        'data_dtc' => $this->normalizeDate($product->data_dtc ?? null),
                    ];

                    if (!array_filter($payload)) {
                        continue;
                    }

                    DB::table('colors')
                        ->where('product_id', $product->id)
                        ->update($payload);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('colors', function (Blueprint $table) {
            $table->dropColumn(['data_mkt', 'data_trade', 'data_cliente', 'data_dtc']);
        });
    }

    private function normalizeDate($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '' || $text === '-') {
            return null;
        }

        try {
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
};
