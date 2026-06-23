<?php

namespace Database\Seeders;

use App\Models\SizeRun;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeRunSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'ME Masculino 34-44',
                'title' => 'Size Run',
                'size_label_left' => 'BR SIZE',
                'size_label_right' => 'US SIZE',
                'note' => 'For the selected color only.',
                'sort_order' => 1,
                'active' => true,
                'items' => [
                    ['left_value' => '34', 'right_value' => '3.5', 'sort_order' => 1],
                    ['left_value' => '35', 'right_value' => '4.5', 'sort_order' => 2],
                    ['left_value' => '36', 'right_value' => '5.5', 'sort_order' => 3],
                    ['left_value' => '37', 'right_value' => '6', 'sort_order' => 4],
                    ['left_value' => '38', 'right_value' => '7', 'sort_order' => 5],
                    ['left_value' => '39', 'right_value' => '7.5', 'sort_order' => 6],
                    ['left_value' => '40', 'right_value' => '8.5', 'sort_order' => 7],
                    ['left_value' => '41', 'right_value' => '9.5', 'sort_order' => 8],
                    ['left_value' => '42', 'right_value' => '10', 'sort_order' => 9],
                    ['left_value' => '43', 'right_value' => '11', 'sort_order' => 10],
                    ['left_value' => '44', 'right_value' => '12', 'sort_order' => 11],
                ],
            ],
            [
                'name' => 'ME Feminino 34-41',
                'title' => 'Size Run',
                'size_label_left' => 'BR SIZE',
                'size_label_right' => 'US SIZE W',
                'note' => 'For the selected color only.',
                'sort_order' => 2,
                'active' => true,
                'items' => [
                    ['left_value' => '34', 'right_value' => '5.5', 'sort_order' => 1],
                    ['left_value' => '35', 'right_value' => '6', 'sort_order' => 2],
                    ['left_value' => '36', 'right_value' => '7', 'sort_order' => 3],
                    ['left_value' => '37', 'right_value' => '7.5', 'sort_order' => 4],
                    ['left_value' => '38', 'right_value' => '8.5', 'sort_order' => 5],
                    ['left_value' => '39', 'right_value' => '9', 'sort_order' => 6],
                    ['left_value' => '40', 'right_value' => '10', 'sort_order' => 7],
                    ['left_value' => '41', 'right_value' => '11', 'sort_order' => 8],
                ],
            ],
        ];

        DB::transaction(function () use ($templates) {
            foreach ($templates as $template) {
                $items = $template['items'];
                unset($template['items']);

                $sizeRun = SizeRun::updateOrCreate(
                    ['name' => $template['name']],
                    $template
                );

                $sizeRun->items()->delete();
                $sizeRun->items()->createMany($items);
            }
        });
    }
}
