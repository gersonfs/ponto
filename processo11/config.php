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
            'inicio' => '25/09/2003', 
            'fim' => '29/10/2008',
            'horarios' => [],
            'descansoSemanal' => -1
        ],
    ]
];

$t1 = DateTime::createFromFormat('d/m/Y', '25/09/2003');
$t2 = DateTime::createFromFormat('d/m/Y', '29/10/2008');


while($t1 <= $t2) {
    $dados['jornadas'][0]['horarios'][$t1->format('Y-m-d')] = [['20:00', '08:00']];
    $t1->add(new DateInterval('P2D'));
}

function debug($var) {
    echo '<pre>' . print_r($var, true) . '</pre>';
}

return $dados;