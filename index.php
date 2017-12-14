<?php
ini_set('display_errors', 'On');
?>
<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
        <style>
            table, th, td {
                border: 1px solid #cecece;
                padding: 2px;
            }
            table {
                border-collapse: collapse;
            }
            
            tr.Dom td{
                color: red;
            }
        </style>
    </head>
    <body>
        <?php
        include('Util.php');

        Util::setPossuiHoraExtraIregularmenteCompensada(true);
        Util::setTrabalhaSabado(true);

        $f = fopen('ponto4.csv', 'r');
        $dados = [];
        $i = 0;
        $mes = 0;
        $semana = 0;
        $registrosObservacoes = [];
        while ($linha = fgetcsv($f, 0, ';')) {

            $possuiDadosNaLinha = false;
            foreach ($linha as $i2 => $v) {
                if (strlen(trim($v))) {
                    $possuiDadosNaLinha = true;
                }
            }

            if (!$possuiDadosNaLinha) {
                continue;
            }

            $isFechamento = false;


            if (trim(strtolower($linha[0])) == 'soma') {
                $dados[$i - 1]['is_fechamento'] = true;
                $mes++;
                $semana++;
                continue;
            }

            $obs = strtolower(trim($linha[2]));
            if(!empty($obs)) {
                if(!in_array($obs, Util::getObservacoesTratadas())) {
                    throw new Exception('A observação ' . $obs . ' não está programada!');
                }

                $registrosObservacoes[] = $linha;
            }

            $dados[$i] = [
                'data' => Util::dataBRToISO($linha[1]),
                'obs' => $obs,
                'entrada1' => $linha[3],
                'saida1' => $linha[4],
                'entrada2' => $linha[5],
                'saida2' => $linha[6],
                'mes' => $mes,
                'semana' => $semana
            ];
            
            if(Util::isDomingo($dados[$i])) {
                $semana++;
            }
            $i++;
        }
        fclose($f);

        Util::setRegistrosObservacoes($registrosObservacoes);
        
        //1 é segunda
        /*Util::setJornadaTrabalho([
            1 => [
                ['07:15', '12:00'],
                ['13:00', '17:03'],
            ],
            2 => [
                ['07:15', '12:00'],
                ['13:00', '17:03'],
            ],
            3 => [
                ['07:15', '12:00'],
                ['13:00', '17:03'],
            ],
            4 => [
                ['07:15', '12:00'],
                ['13:00', '17:03'],
            ],
            5 => [
                ['07:15', '12:00'],
                ['13:00', '17:03'],
            ],
        ]);*/
        
        Util::setJornadaTrabalho([
            1 => [
                ['07:15', '12:00'],
                ['13:00', '16:15'],
            ],
            2 => [
                ['07:15', '12:00'],
                ['13:00', '16:15'],
            ],
            3 => [
                ['07:15', '12:00'],
                ['13:00', '16:15'],
            ],
            4 => [
                ['07:15', '12:00'],
                ['13:00', '16:15'],
            ],
            5 => [
                ['07:15', '12:00'],
                ['13:00', '16:15'],
            ],
            6 => [
                ['08:00', '12:00'],
            ],
        ]);
        
        
        //echo '<pre>' . print_r($dados, true) . '</pre>';
        
        ?>
        <pre>
        <table style="width: 80%">
            <tr>
                <td>Dia</td>
                <td>Data</td>
                <td>Obs</td>
                <td>Entrada</td>
                <td>Saida</td>
                <td>Entrada</td>
                <td>Saida</td>
                <td>H.N.</td>
                <td>H.T.</td>
                <td>H.E.</td>
                <td>H.I.C.</td>
                <td>H.E. - H.I.C.</td>
                <td>H. N. S.</td>
                <td>H. T. S.</td>
                <td>H. E. 50%.</td>
                <td>H. E. 100%.</td>
                <td>H. E. 130%.</td>
            </tr>
                <?php
                $totalHEMenosHIC = $totalHTMes = $totalHNMes = $tSegundosNormais = $tSegundosTrabalhados = $sHE = $sHIC = 0;
                $totaisMeses = [];
                foreach ($dados as $i=>$dado) {
                    $dia = Util::getDiaDaSemanaCurto($dado['data']);
                    $mostrarHoraSemana = (Util::isSabado($dado) && isset($dados[$i+1]['is_fechamento'])) || isset($dado['is_fechamento']);
                    
                    $h50 = $h100 = $h130 = $s1 = $s2 = null;
                    if($mostrarHoraSemana) {
                        $s1 = Util::getSegundosNormalSemana($dado, $dados);
                        $s2 = Util::getSegundosTrabalhadosSemana($dado, $dados);
                        $totalHNMes += $s1;
                        $totalHTMes += $s2;
                    }
                    
                    $segundosNormais = Util::getSegundosNormais($dado);
                    $segundosTrabalhados = Util::getSegundosTrabalhados($dado);
                    $tSegundosNormais += $segundosNormais;
                    $tSegundosTrabalhados += $segundosTrabalhados;
                    $he = Util::getHorasExtras($dado);
                    $ic = Util::getSegundosIrComp($dado);
                    $sHE += $he;
                    $sHIC += $ic;
                    $heHic = $he - $ic;
                    if($heHic < 0) {
                        $heHic = 0;
                    }
                    $totalHEMenosHIC += $heHic;
                    echo '<tr class="'. $dia .'">';
                    echo '<td>' . $dia . '</td>';
                    echo '<td>' . Util::dataISOToBR($dado['data']) . '</td>';
                    echo '<td>' . $dado['obs'] . '</td>';
                    echo '<td>' . $dado['entrada1'] . '</td>';
                    echo '<td>' . $dado['saida1'] . '</td>';
                    echo '<td>' . $dado['entrada2'] . '</td>';
                    echo '<td>' . $dado['saida2'] . '</td>';
                    echo '<td>' . Util::sec_to_time($segundosNormais) . '</td>';
                    echo '<td>' . Util::sec_to_time($segundosTrabalhados) . '</td>';
                    echo '<td>' . Util::sec_to_time($he) . '</td>';
                    echo '<td>' . Util::sec_to_time($ic) . '</td>';
                    echo '<td>' . Util::sec_to_time($heHic) . '</td>';
                    echo '<td>' . Util::sec_to_time($s1) . '</td>';
                    echo '<td>' . Util::sec_to_time($s2) . '</td>';
                    echo '<td></td>';
                    echo '<td></td>';
                    echo '<td></td>';
                    echo '</tr>';
                    
                    if(isset($dado['is_fechamento'])) {

                        $h50 = $totalHEMenosHIC;
                        if($h50 > (22 * 60 * 60)) {
                            $h50 = 22 * 60 * 60;
                            $h100 = $totalHEMenosHIC - $h50;
                            if($h100 > (38 * 60 * 60)) {
                                $h100 = 38 * 60 * 60;
                                $h130 = $totalHEMenosHIC - $h50 - $h100;
                            }
                        }

                        $totaisMeses[] = [
                            'normal' => $tSegundosNormais,
                            'trabalhado' => $tSegundosTrabalhados,
                            'he' => $sHE,
                            'hic' => $sHIC,
                            'he-hic' => $totalHEMenosHIC,
                            'periodo' => $dado['data'],
                            'h50' => $h50,
                            'h100' => $h100,
                            'h130' => $h130,
                        ];

                        echo '<tr>';
                        echo '<td colspan="2"><strong>Soma</strong></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td>' . Util::sec_to_time($tSegundosNormais)  . '</td>';
                        echo '<td>' . Util::sec_to_time($tSegundosTrabalhados)  . '</td>';
                        echo '<td>' . Util::sec_to_time($sHE) .'</td>';
                        echo '<td>' . Util::sec_to_time($sHIC) .'</td>';
                        echo '<td>' . Util::sec_to_time($totalHEMenosHIC) .'</td>';
                        echo '<td>' . Util::sec_to_time($totalHNMes) .'</td>';
                        echo '<td>' . Util::sec_to_time($totalHTMes) .'</td>';
                        echo '<td>' . Util::sec_to_time($h50) . '</td>';
                        echo '<td>' . Util::sec_to_time($h100) . '</td>';
                        echo '<td>' . Util::sec_to_time($h130) . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td colspan="14"> </td>';
                        echo '</tr>';
                        
                        $totalHEMenosHIC = $sHIC = $sHE = $tSegundosNormais = $totalHNMes = $totalHTMes = $tSegundosTrabalhados = 0;
                    }
                }
                ?>
        </table>
        <br /><br />

        Totais
        <table>
            <tr>
                <td>Mes</td>
                <td>N</td>
                <td>T</td>
                <td>HE</td>
                <td>HIC</td>
                <td>HE - HIC</td>
                <td>H 50%</td>
                <td>H 100%</td>
                <td>H 130%</td>
            </tr>
            <?php 
            foreach($totaisMeses as $total) {
                echo '<tr>';
                echo '<td>'. date('m/Y', strtotime($total['periodo'])) .'</td>';
                echo '<td>' . number_format($total['normal']/60/60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['trabalhado']/60/60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['he']/60/60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['hic']/60/60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['he-hic']/60/60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['h50']/60/60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['h100']/60/60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['h130']/60/60, 2, ",", "") . '</td>';
                echo '</tr>';
            }
            ?>
        </table>


        </pre>
    </body>
</html>