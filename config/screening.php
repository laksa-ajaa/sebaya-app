<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Screening Interpretation Ranges
    |--------------------------------------------------------------------------
    |
    | Define interpretation ranges for different screening packages and dimensions.
    | Each dimension has thresholds that determine the severity level.
    |
    | Structure:
    | 'interpretations' => [
    |     'PACKAGE_CODE' => [
    |         'DIMENSION_CODE' => [
    |             'name' => 'Dimension Name',
    |             'ranges' => [
    |                 ['min' => 0, 'max' => 10, 'label' => 'Normal', 'level' => 5],
    |                 ...
    |             ]
    |         ]
    |     ]
    | ]
    |
    | Level: 5=Normal, 4=Ringan, 3=Sedang, 2=Berat, 1=Sangat Berat
    |
    */

    'interpretations' => [
        // DASS-21 Package (Depression, Anxiety, Stress Scale)
        'DASS21' => [
            'D' => [ // Depresi
                'name' => 'Depresi',
                'ranges' => [
                    ['min' => 0, 'max' => 9, 'label' => 'Normal', 'level' => 5],
                    ['min' => 10, 'max' => 13, 'label' => 'Ringan', 'level' => 4],
                    ['min' => 14, 'max' => 20, 'label' => 'Sedang', 'level' => 3],
                    ['min' => 21, 'max' => 27, 'label' => 'Berat', 'level' => 2],
                    ['min' => 28, 'max' => PHP_INT_MAX, 'label' => 'Sangat Berat', 'level' => 1],
                ],
            ],
            'A' => [ // Kecemasan (Anxiety)
                'name' => 'Kecemasan',
                'ranges' => [
                    ['min' => 0, 'max' => 7, 'label' => 'Normal', 'level' => 5],
                    ['min' => 8, 'max' => 9, 'label' => 'Ringan', 'level' => 4],
                    ['min' => 10, 'max' => 14, 'label' => 'Sedang', 'level' => 3],
                    ['min' => 15, 'max' => 19, 'label' => 'Berat', 'level' => 2],
                    ['min' => 20, 'max' => PHP_INT_MAX, 'label' => 'Sangat Berat', 'level' => 1],
                ],
            ],
            'S' => [ // Stres (Stress)
                'name' => 'Stres',
                'ranges' => [
                    ['min' => 0, 'max' => 14, 'label' => 'Normal', 'level' => 5],
                    ['min' => 15, 'max' => 18, 'label' => 'Ringan', 'level' => 4],
                    ['min' => 19, 'max' => 25, 'label' => 'Sedang', 'level' => 3],
                    ['min' => 26, 'max' => 33, 'label' => 'Berat', 'level' => 2],
                    ['min' => 34, 'max' => PHP_INT_MAX, 'label' => 'Sangat Berat', 'level' => 1],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Default Interpretation (Fallback)
        |--------------------------------------------------------------------------
        | Digunakan jika screening package tidak memiliki interpretasi spesifik
        |
        */
        'default' => [
            'general' => [
                'name' => 'General',
                'ranges' => [
                    ['min' => 0, 'max' => 10, 'label' => 'Normal', 'level' => 5],
                    ['min' => 11, 'max' => 20, 'label' => 'Ringan', 'level' => 4],
                    ['min' => 21, 'max' => 30, 'label' => 'Sedang', 'level' => 3],
                    ['min' => 31, 'max' => 40, 'label' => 'Berat', 'level' => 2],
                    ['min' => 41, 'max' => PHP_INT_MAX, 'label' => 'Sangat Berat', 'level' => 1],
                ],
            ],
        ],

        /*
        |--------------------------------------------------------------------------
        | Contoh Package Baru
        |--------------------------------------------------------------------------
        | Uncomment dan sesuaikan untuk menambah screening package baru:
        |
        | 'PHQ9' => [
        |     'depression' => [
        |         'name' => 'Depression',
        |         'ranges' => [
        |             ['min' => 0, 'max' => 4, 'label' => 'Minimal', 'level' => 5],
        |             ['min' => 5, 'max' => 9, 'label' => 'Mild', 'level' => 4],
        |             ['min' => 10, 'max' => 14, 'label' => 'Moderate', 'level' => 3],
        |             ['min' => 15, 'max' => 19, 'label' => 'Moderately Severe', 'level' => 2],
        |             ['min' => 20, 'max' => PHP_INT_MAX, 'label' => 'Severe', 'level' => 1],
        |         ],
        |     ],
        | ],
        |
        */
    ],

    /*
    |--------------------------------------------------------------------------
    | Overall Interpretation
    |--------------------------------------------------------------------------
    |
    | Define overall interpretation based on the most severe dimension level
    |
    */

    'overall' => [
        'DASS21' => [
            'by_severity_level' => [
                5 => ['interpretation' => 'Normal', 'recommendation' => 'Lanjutkan pemantauan rutin dan berikan dukungan positif kepada siswa.'],
                4 => ['interpretation' => 'Ringan', 'recommendation' => 'Berikan konseling ringan, pantau perkembangan siswa, dan dorong partisipasi dalam kegiatan positif.'],
                3 => ['interpretation' => 'Sedang', 'recommendation' => 'Lakukan intervensi dini melalui konseling BK, libatkan orang tua, dan monitor respons siswa.'],
                2 => ['interpretation' => 'Berat', 'recommendation' => 'Rujuk ke profesional kesehatan mental, koordinasi dengan orang tua, dan berikan dukungan akademik sementara.'],
                1 => ['interpretation' => 'Sangat Berat', 'recommendation' => 'Segera rujuk ke spesialis kesehatan mental, libatkan orang tua dan pihak terkait, serta siapkan rencana intervensi darurat.'],
            ],
        ],

        // Default overall interpretation untuk package lain
        'default' => [
            'by_severity_level' => [
                5 => ['interpretation' => 'Normal', 'recommendation' => 'Lanjutkan menjaga kesejahteraan mental Anda.'],
                4 => ['interpretation' => 'Kekhawatiran ringan', 'recommendation' => 'Pertimbangkan berdiskusi dengan konselor.'],
                3 => ['interpretation' => 'Kekhawatiran sedang', 'recommendation' => 'Pertimbangkan mencari dukungan profesional.'],
                2 => ['interpretation' => 'Memerlukan perhatian', 'recommendation' => 'Silakan berkonsultasi dengan profesional kesehatan mental.'],
                1 => ['interpretation' => 'Kondisi berat', 'recommendation' => 'Bantuan profesional segera diperlukan.'],
            ],
        ],
    ],
];
