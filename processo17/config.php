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
            'inicio' => '01/08/2014', 
            'fim' => '31/08/2015',
            'horarios' => [
                1 => [
                    ['07:30', '11:30'],
                    ['13:00', '17:00'],
                ],
                2 => [
                    ['07:30', '11:30'],
                    ['13:00', '17:00'],
                ],
                3 => [
                    ['07:30', '11:30'],
                    ['13:00', '17:00'],
                ],
                4 => [
                    ['07:30', '11:30'],
                    ['13:00', '17:00'],
                ],
                5 => [
                    ['07:30', '11:30'],
                    ['13:00', '17:00'],
                ]
            ],
            //'descansoSemanal' => -1
        ],
    ]
];

return $dados;