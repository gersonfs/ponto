<?php

return [
    'arquivo' => 'ponto.csv',
    'hora100Porcento' => 40,
    'hora130Porcento' => 1000,
    'segundosIntrajornada' => 60 * 60,
    'possuiIntraJornada' => true,
    'possuiHoraExtraIC' => true,
    'estenderHoraNoturna' => true,
    'possuiItinere' => true,
    'jornadas' => [
        [
            'inicio' => '28/10/2013', 
            'fim' => '27/7/2017',
            'horarios' => [
                1 => [
                    ['07:30', '12:00'],
                    ['13:00', '16:30'],
                ],
                2 => [
                    ['07:30', '12:00'],
                    ['13:00', '16:30'],
                ],
                3 => [
                    ['07:30', '12:00'],
                    ['13:00', '16:30'],
                ],
                4 => [
                    ['07:30', '12:00'],
                    ['13:00', '16:30'],
                ],
                5 => [
                    ['07:30', '12:00'],
                    ['13:00', '16:30'],
                ],
                6 => [
                    ['08:00', '12:00'],
                ],
            ]
        ],
    ]
];


function debug($var) {
    echo '<pre>' . print_r($var, true) . '</pre>';
}