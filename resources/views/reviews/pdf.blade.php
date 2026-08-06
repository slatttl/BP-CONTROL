@php
    $sections = [
        'Aktivita študenta' => [
            'activity_independence' => 'Samostatnosť',
            'activity_creativity' => 'Tvorivosť',
        ],
        'Kvalita riešenia' => [
            'quality_overall_concept' => 'Celková koncepcia práce',
            'quality_topic_completeness' => 'Úplnosť spracovania témy',
            'quality_topic_quality' => 'Kvalita spracovania témy',
            'quality_methods' => 'Použité metódy riešenia',
            'quality_complexity' => 'Algoritmická náročnosť, prácnosť riešenia',
            'quality_practicality' => 'Praktická aplikovateľnosť práce',
        ],
        'Práca s literatúrou' => [
            'literature_sorting' => 'Triedenie a hodnotenie prameňov',
            'literature_usage' => 'Využitie poznatkov z literatúry a praxe',
            'literature_conclusions' => 'Vyvodzovanie vlastných záverov z literárnych prameňov',
        ],
        'Formálna úroveň práce' => [
            'formal_logic' => 'Logika usporiadania práce',
            'formal_style' => 'Štylizácia textu',
            'formal_terminology' => 'Použitá terminológia',
            'formal_graphics' => 'Grafická realizácia',
        ],
    ];

    $sectionComments = [
        'Aktivita študenta' => 'activity_comment',
        'Kvalita riešenia' => 'quality_comment',
        'Práca s literatúrou' => 'literature_comment',
        'Formálna úroveň práce' => 'formal_comment',
    ];

    $gradeLabels = [
        'A' => 'výborná',
        'B' => 'veľmi dobrá',
        'C' => 'dobrá',
        'D' => 'uspokojivá',
        'E' => 'dostatočná',
        'FX' => 'nedostatočná',
    ];
@endphp
<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="utf-8">
    <style>
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
        <div class="title">POSUDOK VEDÚCEHO BAKALÁRSKEJ PRÁCE</div>
        <div class="year">Akademický rok: {{ $review->academic_year }}</div>

        <div class="top-fields">
            <div class="label-row">
                <div class="half field-label">Meno študenta:</div>
                <div class="half right field-label">Študijný program:</div>
            </div>
            <div class="value-row">
                <div class="half field-box">{{ $review->student_name }}</div>
                <div class="half right field-box">{{ $review->study_program }}</div>
            </div>

            <div class="label-row">
                <div class="full field-label">Názov práce:</div>
            </div>
            <div class="value-row">
                <div class="full field-box title-box">{{ $review->thesis_title }}</div>
            </div>

            <div class="label-row">
                <div class="half field-label">Meno vedúceho:</div>
            </div>
            <div class="value-row">
                <div class="half field-box">{{ $review->supervisor_name }}</div>
            </div>
            <div class="clearfix"></div>
        </div>

        @foreach (array_slice($sections, 0, 2, true) as $sectionTitle => $fields)
            <div class="section">
                <div class="section-title">{{ $sectionTitle }}</div>
                <table class="matrix">
                    <thead>
                        <tr>
                            <th class="crit-col"></th>
                            @foreach ($grades as $code => $label)
                                <th class="grade-col"><span class="grade-code">{{ $code }}</span><span class="grade-name">{{ $gradeLabels[$code] }}</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fields as $field => $label)
                            <tr>
                                <td class="crit-col">{{ $label }}:</td>
                                @foreach ($grades as $code => $gradeLabel)
                                    <td class="grade-col"><span class="tick">{{ $review->{$field} === $code ? 'X' : '' }}</span></td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="comment-block @if ($sectionTitle === 'Kvalita riesenia') large @else small @endif"><span class="comment-label">Komentár:</span>
{{ $review->{$sectionComments[$sectionTitle]} }}</div>
            </div>
        @endforeach
    </div>

    <div class="page">
        @foreach (array_slice($sections, 2, 2, true) as $sectionTitle => $fields)
            <div class="section">
                <div class="section-title">{{ $sectionTitle }}</div>
                <table class="matrix">
                    <thead>
                        <tr>
                            <th class="crit-col"></th>
                            @foreach ($grades as $code => $label)
                                <th class="grade-col"><span class="grade-code">{{ $code }}</span><span class="grade-name">{{ $gradeLabels[$code] }}</span></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fields as $field => $label)
                            <tr>
                                <td class="crit-col">{{ $label }}:</td>
                                @foreach ($grades as $code => $gradeLabel)
                                    <td class="grade-col"><span class="tick">{{ $review->{$field} === $code ? 'X' : '' }}</span></td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="comment-block small"><span class="comment-label">Komentár:</span>
{{ $review->{$sectionComments[$sectionTitle]} }}</div>
            </div>
        @endforeach

        <div class="summary-title">Celkové zhodnotenie práce</div>
        <div class="summary-box">
            Predložená bakalárska práca spĺňa stanovené kritériá kvality z hľadiska obsahu, formy spracovania, prínosu a stanoveného cieľa. Prácu
            <span class="recommend">{{ $recommendations[$review->final_recommendation] ?? $review->final_recommendation }}</span>
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
                    <td>Miera originality bakalárskej práce:</td>
                    <td class="value">{{ $originalityStatuses[$review->originality_status] ?? $review->originality_status }}</td>
                </tr>
                <tr>
                    <td colspan="2">Komentár:<br>{{ $review->originality_comment }}</td>
                </tr>
                <tr>
                    <td colspan="2">Vyjadrenie autora:<br>{{ $review->author_statement }}</td>
                </tr>
            </tbody>
        </table>

        <div class="footer-row">
            <div class="footer-left">Miesto a dátum: {{ $review->place }}, {{ $review->review_date?->format('j.n.Y') }}</div>
            <div class="footer-right"><div class="signature-name">{{ $review->supervisor_name }}</div></div>
            <div class="clearfix"></div>
        </div>
    </div>

    <div>
        <div class="final-title">HODNOTENIE VEDÚCEHO BAKALÁRSKEJ PRÁCE</div>
        <div class="year">Akademický rok: {{ $review->academic_year }}</div>

        <div class="top-fields">
            <div class="label-row">
                <div class="half field-label">Meno študenta:</div>
                <div class="half right field-label">Študijný program:</div>
            </div>
            <div class="value-row">
                <div class="half field-box">{{ $review->student_name }}</div>
                <div class="half right field-box">{{ $review->study_program }}</div>
            </div>

            <div class="label-row">
                <div class="full field-label">Názov práce:</div>
            </div>
            <div class="value-row">
                <div class="full field-box title-box">{{ $review->thesis_title }}</div>
            </div>

            <div class="label-row">
                <div class="half field-label">Meno vedúceho:</div>
            </div>
            <div class="value-row">
                <div class="half field-box">{{ $review->supervisor_name }}</div>
            </div>
            <div class="clearfix"></div>
        </div>

        <div class="grade-summary">
            Bakalársku prácu hodnotím známkou
            <strong>- {{ $grades[$review->final_grade] ?? '' }} [{{ $review->final_grade }}] -</strong>
        </div>

        <div class="footer-row">
            <div class="footer-left">Miesto a dátum: {{ $review->place }}, {{ $review->review_date?->format('j.n.Y') }}</div>
            <div class="footer-right"><div class="signature-name">{{ $review->supervisor_name }}</div></div>
            <div class="clearfix"></div>
        </div>
    </div>
</body>
</html>
