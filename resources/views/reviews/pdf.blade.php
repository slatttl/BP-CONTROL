@php
    $typeConfig = $review->thesisConfig();
    $isOpponent = $review->isOpponent();
    $type = $review->thesis_type ?: 'bachelor';
    $role = $review->review_role ?: 'supervisor';
    $signer = $isOpponent ? $review->opponent_name : $review->supervisor_name;
    $recommended = $review->final_recommendation === 'recommend';
    $gradeLabels = ['A' => 'výborná', 'B' => 'veľmi dobrá', 'C' => 'dobrá', 'D' => 'uspokojivá', 'E' => 'dostatočná', 'FX' => 'nedostatočná'];
    $activeBlocks = collect(array_keys(config("review.role_blocks.{$role}")))
        ->mapWithKeys(fn ($name) => [$name => config("review.blocks.{$name}")]);
    $firstPageBlocks = $isOpponent ? 1 : 2;
    $roleConfig = $review->roleConfig();
    $title = $roleConfig['title'].' '.mb_strtoupper($typeConfig['genitive']);
    $ratingTitle = $roleConfig['rating_title'].' '.mb_strtoupper($typeConfig['genitive']);
@endphp
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">    <style>
        @page { size: A4; margin: 5.5mm 6mm 6.5mm 6mm; }
        * { box-sizing: border-box; }
        html, body { margin: 0; padding: 0; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.7pt; line-height: 1.08; color: #000; }
        p, table, h1, h2, h3 { margin: 0; }

        .page { page-break-after: always; }
        .page:last-child { page-break-after: auto; }

        .title {
            text-align: center;
            font-size: 13.6pt;
            font-weight: 700;
            margin: 0 0 6mm;
        }
        .year {
            text-align: center;
            font-size: 8.8pt;
            font-weight: 700;
            margin-bottom: 6mm;
        }

        .top-fields {
            width: 100%;
            margin-bottom: 5mm;
        }
        .label-row {
            width: 100%;
            margin-bottom: 1mm;
            clear: both;
        }
        .value-row {
            width: 100%;
            margin-bottom: 4.3mm;
            clear: both;
        }
        .half {
            float: left;
            width: 48.5%;
        }
        .half.right {
            float: right;
        }
        .full {
            width: 100%;
            clear: both;
        }
        .field-label {
            font-size: 8.5pt;
            font-weight: 700;
        }
        .field-box {
            border: 0.2mm solid #000;
            min-height: 9.8mm;
            padding: 1.4mm 1.6mm;
            font-size: 8.8pt;
            line-height: 1.1;
        }
        .field-box.title-box {
            min-height: 11mm;
        }

        .section {
            margin-bottom: 5mm;
            clear: both;
        }
        .section-title {
            text-align: center;
            font-size: 10.7pt;
            font-weight: 700;
            font-style: italic;
            margin-bottom: 0.7mm;
        }
        .matrix {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .matrix th,
        .matrix td {
            border: 0.2mm solid #000;
            padding: 0.45mm 0.55mm;
            vertical-align: middle;
        }
        .matrix .crit-col {
            width: 35.5%;
            font-size: 8.35pt;
            vertical-align: top;
        }
        .matrix .grade-col {
            width: 10.75%;
            text-align: center;
        }
        .grade-code {
            display: block;
            font-size: 7.6pt;
            font-weight: 700;
            font-style: italic;
            line-height: 1;
        }
        .grade-name {
            display: block;
            font-size: 6.2pt;
            font-weight: 700;
            font-style: italic;
            line-height: 1.05;
        }
        .tick {
            display: inline-block;
            width: 3mm;
            height: 3mm;
            border: 0.18mm solid #000;
            line-height: 2.6mm;
            text-align: center;
            font-size: 7.6pt;
            font-weight: 700;
        }
        .comment-block {
            border-left: 0.2mm solid #000;
            border-right: 0.2mm solid #000;
            border-bottom: 0.2mm solid #000;
            min-height: 24mm;
            padding: 0.9mm 1.2mm 1.2mm;
            white-space: pre-line;
            font-size: 8.5pt;
            line-height: 1.12;
        }
        .comment-block.large {
            min-height: 49mm;
        }
        .comment-block.small {
            min-height: 18mm;
        }
        .comment-label {
            font-weight: 400;
        }

        .summary-title {
            text-align: center;
            font-size: 10.7pt;
            font-weight: 700;
            font-style: italic;
            margin-bottom: 0.7mm;
        }
        .summary-box {
            border: 0.2mm solid #000;
            padding: 2.1mm 2mm;
            text-align: center;
            margin-bottom: 5mm;
        }
        .recommend {
            display: block;
            margin: 1.6mm 0 1.2mm;
            font-size: 17pt;
            font-weight: 700;
            line-height: 1;
            text-transform: lowercase;
        }

        .questions-title,
        .protocol-title {
            text-align: center;
            font-size: 10.4pt;
            font-weight: 700;
            font-style: italic;
            margin-bottom: 0.7mm;
        }
        .questions-box {
            border: 0.2mm solid #000;
            min-height: 60mm;
            padding: 1.2mm 1.4mm;
            white-space: pre-line;
            background: #d8f6f7;
            margin-bottom: 5mm;
        }

        .protocol-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-bottom: 8mm;
        }
        .protocol-table td {
            border: 0.2mm solid #000;
            padding: 0.9mm 1.2mm;
            vertical-align: top;
        }
        .protocol-table .value {
            width: 24%;
            text-align: right;
            font-weight: 700;
        }

        .footer-row {
            width: 100%;
            clear: both;
            margin-top: 6mm;
        }
        .footer-left {
            float: left;
            width: 50%;
        }
        .footer-right {
            float: right;
            width: 35%;
            text-align: center;
        }
        .signature-name {
            border-top: 0.2mm solid #000;
            padding-top: 1.3mm;
        }

        .final-title {
            text-align: center;
            font-size: 13.2pt;
            font-weight: 700;
            margin: 0 0 6mm;
        }
        .grade-summary {
            margin-top: 10mm;
            text-align: center;
            font-size: 10pt;
        }
        .grade-summary strong {
            display: block;
            margin-top: 2mm;
            font-size: 14.5pt;
        }

        .clearfix { clear: both; }
    </style>
</head>
<body>
    <div class="page">
        <div class="title">{{ $title }}</div>
        <div class="year">Akademický rok: {{ $review->academic_year }}</div>
        @include('reviews.partials.pdf-header')

        @foreach ($activeBlocks->take($firstPageBlocks) as $blockName => $block)
            @include('reviews.partials.pdf-block')
        @endforeach
    </div>

    <div class="page">
        @foreach ($activeBlocks->slice($firstPageBlocks) as $blockName => $block)
            @include('reviews.partials.pdf-block')
        @endforeach

        <div class="summary-title">Celkové zhodnotenie práce</div>
        <div class="summary-box">
            Predložená {{ $typeConfig['nominative'] }} {{ $recommended ? 'spĺňa' : 'nespĺňa' }} stanovené kritériá kvality z hľadiska obsahu, formy spracovania, prínosu a stanoveného cieľa. Prácu
            <span class="recommend">{{ $recommended ? 'odporúčam' : 'neodporúčam' }}</span>
            k obhajobe pred Štátnou skúšobnou komisiou.
        </div>

        <div class="questions-title">Otázky a pripomienky k práci</div>
        <div class="questions-box">{{ $review->questions }}</div>

        <div class="protocol-title">Vyjadrenie k protokolu o kontrole originality práce</div>
        <table class="protocol-table">
            <tbody>
                <tr>
                    <td>Výsledok overenia miery originality z CRZP:</td>
                    <td class="value">{{ number_format((float) $review->originality_percentage, 2, ',', ' ') }} %</td>
                </tr>
                <tr>
                    <td>Miera originality {{ $typeConfig['genitive'] }}:</td>
                    <td class="value">{{ $originalityStatuses[$review->originality_status] ?? $review->originality_status }}</td>
                </tr>
                <tr>
                    <td colspan="2">Komentár:<br>{{ $review->originality_comment }}</td>
                </tr>
            </tbody>
        </table>

        @include('reviews.partials.pdf-footer')
    </div>

    <div>
        <div class="final-title">{{ $ratingTitle }}</div>
        <div class="year">Akademický rok: {{ $review->academic_year }}</div>
        @include('reviews.partials.pdf-header')

        <div class="grade-summary">
            {{ ucfirst($typeConfig['accusative']) }} hodnotím známkou
            <strong>- {{ $gradeAdverbs[$review->final_grade] ?? '' }} [{{ $review->final_grade }}] -</strong>
        </div>

        @include('reviews.partials.pdf-footer')
    </div>
</body>
</html>