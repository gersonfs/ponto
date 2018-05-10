<?php

$dados = [
    'arquivo' => 'ponto.csv',
    'hora100Porcento' => 22,
    'hora130Porcento' => 1000,
    'segundosIntrajornada' => 60 * 60,
    'possuiIntraJornada' => true,
    'possuiHoraExtraIC' => true,
    'estenderHoraNoturna' => false,
    'possuiItinere' => false,
    'jornadas' => [
        [
            'inicio' => '18/08/2010', 
            'fim' => '05/03/2015',
            'horarios' => [
                1 => [
                    ['17:28', '22:15'],
                    ['23:15', '02:02'],
                ],
                2 => [
                    ['17:28', '22:15'],
                    ['23:15', '02:02'],
                ],
                3 => [
                    ['17:28', '22:15'],
                    ['23:15', '02:02'],
                ],
                4 => [
                    ['17:28', '22:15'],
                    ['23:15', '02:02'],
                ],
                5 => [
                    ['17:28', '22:15'],
                    ['23:15', '02:02'],
                ],
                6 => [
                    ['08:00', '12:00']
                ]
            ],
            //'descansoSemanal' => -1
        ],

    ]
];

return $dados;