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
            'inicio' => '01/10/2013', 
            'fim' => '20/09/2014 ',
            'horarios' => [
                1 => [
                    ['17:36', '22:15'],
                    ['23:15', '02:02'],
                ],
                2 => [
                    ['17:36', '22:15'],
                    ['23:15', '02:02'],
                ],
                3 => [
                    ['17:36', '22:15'],
                    ['23:15', '02:02'],
                ],
                4 => [
                    ['17:36', '22:15'],
                    ['23:15', '02:02'],
                ],
                5 => [
                    ['17:36', '22:15'],
                    ['23:15', '02:02'],
                ],
                6 => [
                    ['08:00', '12:00']
                ]
            ],
            //'descansoSemanal' => -1
        ],
        [
            'inicio' => '21/09/2014', 
            'fim' => '11/11/2014',
            'horarios' => [
                1 => [
                    ['18:48', '22:00'],
                    ['23:00', '03:13'],
                ],
                2 => [
                    ['18:48', '22:00'],
                    ['23:00', '03:13'],
                ],
                3 => [
                    ['18:48', '22:00'],
                    ['23:00', '03:13'],
                ],
                4 => [
                    ['18:48', '22:00'],
                    ['23:00', '03:13'],
                ],
                5 => [
                    ['18:48', '22:00'],
                    ['23:00', '03:13'],
                ],
                6 => [
                    ['08:00', '12:00']
                ]
            ],
            //'descansoSemanal' => -1
        ],
        [
            'inicio' => '12/11/2014', 
            'fim' => '03/11/2015',
            'horarios' => [
                1 => [
                    ['07:48', '12:00'],
                    ['13:00', '16:48'],
                ],
                2 => [
                    ['07:48', '12:00'],
                    ['13:00', '16:48'],
                ],
                3 => [
                    ['07:48', '12:00'],
                    ['13:00', '16:48'],
                ],
                4 => [
                    ['07:48', '12:00'],
                    ['13:00', '16:48'],
                ],
                5 => [
                    ['07:48', '12:00'],
                    ['13:00', '16:48'],
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