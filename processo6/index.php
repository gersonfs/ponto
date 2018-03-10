<html>
    <body>
    <table>
    <tr>
        <td>Dia</td>
        <td>Data</td>
        <td>Obs</td>
        <td>Início</td>
        <td>Fim</td>
        <td>Início</td>
        <td>Fim</td>
        <td>Início</td>
        <td>Fim</td>
        <td>Início</td>
        <td>Fim</td>
        <td>Soma</td>
    </tr>
<?php
include '../Util.php';
include 'Processo6.php';

$ponto = new Processo6();
$registros = $ponto->getRegistros();
$dias = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
$somaMes = 0;
$totais = [];
foreach($registros as $i => $registro) {
    echo '<tr>';
    echo '<td>'. $dias[date('w', strtotime($registro['data']))] .'</td>';
    echo '<td>'. Util::dataISOToBR($registro['data']) .'</td>';
    echo '<td>'. implode(', ', $registro['obs']) .'</td>';
    $soma = 0;
    foreach($registro['periodos'] as $periodo) {
        echo '<td>'. $periodo['inicio'] .'</td>';
        echo '<td>'. $periodo['fim'] .'</td>';
        
        $soma += Util::time_to_sec($periodo['fim']) - Util::time_to_sec($periodo['inicio']);
    }
    
    $somaMes += $soma;
    
    $diferenca = 4 - count($registro['periodos']);
    for($i2 = 0; $i2 < $diferenca; $i2++) {
        echo '<td></td><td></td>';
    }
    
    echo '<td>'. ($soma > 0 ? Util::sec_to_time($soma) : '' ) .'</td>';
    
    $proximaData = isset($registros[$i + 1]) ? $registros[$i + 1]['data'] : null;
    $virouMes = empty($proximaData) || date('m', strtotime($registro['data'])) != date('m', strtotime($proximaData));
    
    if($virouMes) {
        echo '<tr>';
        echo '<td>Soma</td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td></td>';
        echo '<td>'. ($somaMes > 0 ? Util::sec_to_time($somaMes) : '') .'</td>';
        echo '</tr>';
        
        $somaMes = 0;
    }
    echo '</tr>';
}
?>
</table>
</body>
</html>