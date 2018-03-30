<?php

return [
    'arquivo' => 'ponto.csv',
    'hora100Porcento' => 40,
    'hora130Porcento' => 1000,
    'segundosIntrajornada' => 60 * 60,
    'possuiIntraJornada' => true,
    'possuiHoraExtraIC' => false,
    'estenderHoraNoturna' => true,
    'possuiItinere' => true,
    'jornadas' => [
        [
            'inicio' => '11/04/2011', 
            'fim' => '23/10/2014',
            'horarios' => [
                1 => [
                    ['08:10', '11:30'],
                    ['16:30', '20:30'],
                ],
                2 => [
                    ['08:10', '11:30'],
                    ['16:30', '20:30'],
                ],
                3 => [
                    ['08:10', '11:30'],
                    ['16:30', '20:30'],
                ],
                4 => [
                    ['08:10', '11:30'],
                    ['16:30', '20:30'],
                ],
                5 => [
                    ['08:10', '11:30'],
                    ['16:30', '20:30'],
                ],
                6 => [
                    ['08:10', '11:30'],
                    ['16:30', '20:30'],
                ],
            ]
        ],
    ]
];


function debug($var) {
    echo '<pre>' . print_r($var, true) . '</pre>';
}