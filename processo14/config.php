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
            'inicio' => '12/02/2013', 
            'fim' => '03/11/2015',
            'horarios' => [
                1 => [
                    ['07:00', '11:00'],
                    ['13:00', '17:00'],
                ],
                2 => [
                    ['07:00', '11:00'],
                    ['13:00', '17:00'],
                ],
                3 => [
                    ['07:00', '11:00'],
                    ['13:00', '17:00'],
                ],
                4 => [
                    ['07:00', '11:00'],
                    ['13:00', '17:00'],
                ],
                5 => [
                    ['07:00', '11:00'],
                    ['13:00', '17:00'],
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