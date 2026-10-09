<?php

return [
    'grades' => [
        'A' => 'vyborna',
        'B' => 'velmi dobra',
        'C' => 'dobra',
        'D' => 'uspokojiva',
        'E' => 'dostatocna',
        'FX' => 'nedostatocna',
    ],
    'recommendations' => [
        'recommend' => 'odporucam',
        'not_recommend' => 'neodporucam',
    ],
    'originality_statuses' => [
        'vyhovujuca' => 'vyhovujúca',
        'nevyhovujuca' => 'nevyhovujúca',
    ],

    // Numeric value of each letter grade used by the grade calculation.
    'grade_points' => ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'FX' => 6],

    // Adverb form printed on the final "Hodnotenie" page.
    'grade_adverbs' => [
        'A' => 'výborne',
        'B' => 'veľmi dobre',
        'C' => 'dobre',
        'D' => 'uspokojivo',
        'E' => 'dostatočne',
        'FX' => 'nedostatočne',
    ],

    'thesis_types' => [
        'bachelor' => [
            'label' => 'Bakalárska práca',
            'short' => 'BP',
            'genitive' => 'bakalárskej práce',
            'nominative' => 'bakalárska práca',
            'accusative' => 'bakalársku prácu',
            'author_genitive' => 'študenta',
            'author_name_label' => 'Meno študenta',
            'programs' => [
                'B-BEM bezpečnostný a environmentálny manažment',
                'B-MTI materiálové inžinierstvo',
                'B-MCHT mechatronika',
                'B-PPTRS počítačová podpora výrobných technológií a robotizovaných systémov',
                'B-PIAR priemyselná informatika, automatizácia a robotika',
                'B-PM priemyselný manažment',
                'B-SVI smart výrobné inžinierstvo',
            ],
        ],
        'master' => [
            'label' => 'Diplomová práca',
            'short' => 'DP',
            'genitive' => 'diplomovej práce',
            'nominative' => 'diplomová práca',
            'accusative' => 'diplomovú prácu',
            'author_genitive' => 'diplomanta',
            'author_name_label' => 'Meno diplomanta',
            'programs' => [
                'I-BEM bezpečnostný a environmentálny manažment',
                'I-MTI materiálové inžinierstvo',
                'I-METxA materiálové inžinierstvo a technológie',
                'I-PPNV počítačová podpora návrhu a výroby',
                'I-PIAR priemyselná informatika, automatizácia a robotika',
                'I-PIM priemyselné inžinierstvo a manažment',
                'I-SVI smart výrobné inžinierstvo',
            ],
        ],
    ],

    'roles' => [
        'supervisor' => ['label' => 'Vedúci práce', 'genitive' => 'vedúceho', 'title' => 'POSUDOK VEDÚCEHO', 'rating_title' => 'HODNOTENIE VEDÚCEHO'],
        'opponent' => ['label' => 'Oponent práce', 'genitive' => 'oponenta', 'title' => 'POSUDOK OPONENTA', 'rating_title' => 'HODNOTENIE OPONENTA'],
    ],

    // Evaluation blocks. `{author}` is replaced by the thesis-type specific author noun.
    // `critical` marks the criteria whose FX/E grade overrides the weighted result.
    'blocks' => [
        'activity' => [
            'title' => 'Aktivita {author}',
            'comment' => 'activity_comment',
            'criteria' => [
                'activity_independence' => 'Samostatnosť',
                'activity_creativity' => 'Tvorivosť',
            ],
        ],
        'quality' => [
            'title' => 'Kvalita riešenia',
            'comment' => 'quality_comment',
            'criteria' => [
                'quality_overall_concept' => 'Celková koncepcia práce',
                'quality_topic_completeness' => 'Úplnosť spracovania témy',
                'quality_topic_quality' => 'Kvalita spracovania témy',
                // DP vedúci uses both method criteria; DP oponent only the engineering-methods one.
                'quality_methods' => ['bachelor' => 'Použité metódy riešenia', 'master' => 'Aplikácia inžinierskych metód riešenia', 'master:supervisor' => 'Použité metódy riešenia'],
                'quality_complexity' => ['bachelor' => 'Algoritmická náročnosť, prácnosť riešenia', 'master' => 'Algoritmická náročnosť, prácnosť riešenia', 'master:supervisor' => 'Aplikácia inžinierskych metód riešenia'],
                'quality_practicality' => 'Praktická aplikovateľnosť práce',
            ],
            'critical' => ['quality_topic_completeness', 'quality_topic_quality'],
        ],
        'literature' => [
            'title' => 'Práca s literatúrou',
            'comment' => 'literature_comment',
            'criteria' => [
                'literature_sorting' => 'Triedenie a hodnotenie prameňov',
                'literature_usage' => 'Využitie poznatkov z literatúry a praxe',
                'literature_conclusions' => 'Vyvodzovanie vlastných záverov z literárnych prameňov',
            ],
        ],
        'formal' => [
            'title' => 'Formálna úroveň práce',
            'comment' => 'formal_comment',
            'criteria' => [
                'formal_logic' => 'Logika usporiadania práce',
                'formal_style' => 'Štylizácia textu',
                'formal_terminology' => 'Použitá terminológia',
                'formal_graphics' => 'Grafická realizácia',
            ],
        ],
    ],

    // Block display order and weights per role (source: official evaluation spreadsheets).
    // Weights of one role sum to 1.
    'role_blocks' => [
        'supervisor' => ['activity' => 0.10, 'quality' => 0.65, 'literature' => 0.15, 'formal' => 0.10],
        'opponent' => ['quality' => 0.65, 'literature' => 0.20, 'formal' => 0.15],
    ],

    // First month of the academic year (September).
    'academic_year_start_month' => 9,
];
