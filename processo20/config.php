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
            'inicio' => '04/07/2012', 
            'fim' => '13/06/2017',
            'horarios' => [
                1 => [
                    ['07:30', '12:00'],
                    ['12:45', '16:15'],
                ],
                2 => [
                    ['07:30', '12:00'],
                    ['12:45', '16:15'],
                ],
                3 => [
                    ['07:30', '12:00'],
                    ['12:45', '16:15'],
                ],
                4 => [
                    ['07:30', '12:00'],
                    ['12:45', '16:15'],
                ],
                5 => [
                    ['07:30', '12:00'],
                    ['12:45', '16:15'],
                ],
                6 => [
                    ['07:30', '11:30']
                ]
            ],
            //'descansoSemanal' => -1
        ],
    ]
];

return $dados;