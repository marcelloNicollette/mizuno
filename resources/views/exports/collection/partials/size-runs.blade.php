@php
    $sizeRunColors = collect($colors ?? [])
        ->filter(function ($color) {
            $sizeRun = data_get($color, 'pdf_size_run');
            return is_array($sizeRun) && !empty($sizeRun['items']);
        })
        ->values();

    $compact = !empty($compact);
    $wrapperMargin = $compact ? '6px 0 10px 0' : '8px 0 14px 0';
    $blockSpacing = $compact ? '6px' : '8px';
    $titleFontSize = $compact ? '7px' : '8px';
    $articleFontSize = $compact ? '10px' : '11px';
    $tableMarginBottom = $compact ? '2px' : '3px';
    $labelFontSize = $compact ? '7px' : '8px';
    $valueFontSize = $compact ? '7px' : '8px';
    $labelPadding = $compact ? '2px 4px' : '3px 5px';
    $valuePadding = $compact ? '2px 2px' : '3px 4px';
    $noteFontSize = $compact ? '7px' : '8px';
    $labelWidth = $compact ? '42px' : 'auto';
@endphp

@if (!empty($show_size_run_me) && $sizeRunColors->isNotEmpty())
    <div style="margin: {{ $wrapperMargin }};">
        <div style="">
            @foreach ($sizeRunColors as $sizeRunColor)
                @php $sizeRun = $sizeRunColor->pdf_size_run; @endphp
                <div style="{{ !$loop->last ? 'margin-bottom: ' . $blockSpacing . ';' : '' }}">
                    <div style="font-size: {{ $titleFontSize }}; line-height: 1; color: #8b8b8b; margin-bottom: 2px;">
                        {{ $sizeRun['title'] ?? 'Size Run' }}
                    </div>
                    <div
                        style="font-size: {{ $articleFontSize }}; line-height: 1; color: #3f3f3f; font-weight: 700; margin-bottom: 4px;">
                        {{ $sizeRun['article_label'] ?? 'Article' }}:
                        {{ $sizeRun['article_value'] !== '' ? $sizeRun['article_value'] : '-' }}
                    </div>
                    <table cellpadding="0" cellspacing="0"
                        style="border-collapse: collapse; width: 100%; table-layout: fixed; margin-bottom: {{ $tableMarginBottom }};">
                        <tbody>
                            <tr>
                                <td
                                    style="border: 1px solid #aeaeae; background: #f5f5f5; color: #8a8a8a; font-size: {{ $labelFontSize }}; padding: {{ $labelPadding }}; white-space: nowrap; width: {{ $labelWidth }};">
                                    {{ $sizeRun['size_label_left'] ?? 'BR SIZE' }}
                                </td>
                                @foreach ($sizeRun['items'] as $item)
                                    <td
                                        style="border: 1px solid #aeaeae; background: #fff; color: #565656; font-size: {{ $valueFontSize }}; font-weight: 700; text-align: center; padding: {{ $valuePadding }};">
                                        {{ $item['left_value'] }}
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td
                                    style="border: 1px solid #aeaeae; background: #f5f5f5; color: #8a8a8a; font-size: {{ $labelFontSize }}; padding: {{ $labelPadding }}; white-space: nowrap; width: {{ $labelWidth }};">
                                    {{ $sizeRun['size_label_right'] ?? 'US SIZE' }}
                                </td>
                                @foreach ($sizeRun['items'] as $item)
                                    <td
                                        style="border: 1px solid #aeaeae; background: #fff; color: #565656; font-size: {{ $valueFontSize }}; font-weight: 700; text-align: center; padding: {{ $valuePadding }};">
                                        {{ $item['right_value'] }}
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                    <div style="font-size: {{ $noteFontSize }}; color: #8a8a8a; line-height: 1.15;">
                        {{ $sizeRun['note'] ?? '*For the selected color only.' }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
