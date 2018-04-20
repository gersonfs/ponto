<?php

$dados = [
    'arquivo' => 'ponto.csv',
    'hora100Porcento' => 190.67,
    'hora130Porcento' => 1000,
    'segundosIntrajornada' => 60 * 60,
    'possuiIntraJornada' => true,
    'possuiHoraExtraIC' => false,
    'estenderHoraNoturna' => false,
    'possuiItinere' => false,
    'jornadas' => [
        [
            'inicio' => '13/03/2013', 
            'fim' => '05/07/2014',
            'horarios' => [
                1 => [
                    ['09:00', '16:00'],
                ],
                2 => [
                    ['09:00', '16:00'],
                ],
                3 => [
                    ['09:00', '16:00'],
                ],
                4 => [
                    ['09:00', '16:00'],
                ],
                5 => [
                    ['09:00', '16:00'],
                ],
            ],
            'descansoSemanal' => -1
        ],
    ]
];


function debug($var) {
    echo '<pre>' . print_r($var, true) . '</pre>';
}

return $dados;