
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
                border: 1px solid black;
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

        $f = fopen('ponto.csv', 'r');
        $dados = [];
        $i = 0;
        $mes = 0;
        $semana = 0;
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


            if (trim(strtolower($linha[2])) == 'soma') {
                $dados[$i - 1]['is_fechamento'] = true;
                $mes++;
                $semana++;
                continue;
            }

            $dados[$i] = [
                'data' => Util::dataBRToISO($linha[3]),
                'entrada1' => $linha[5],
                'saida1' => $linha[6],
                'entrada2' => $linha[7],
                'saida2' => $linha[8],
                'mes' => $mes,
                'semana' => $semana
            ];
            
            if(Util::isDomingo($dados[$i])) {
                $semana++;
            }
            $i++;
        }
        
        //echo '<pre>' . print_r($dados, true) . '</pre>';
        fclose($f);
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
            </tr>
                <?php
                $totalHTMes = $totalHNMes = $tSegundosNormais = $tSegundosTrabalhados= 0;
                foreach ($dados as $i=>$dado) {
                    $dia = Util::getDiaDaSemanaCurto($dado['data']);
                    $mostrarHoraSemana = (Util::isSabado($dado) && !isset($dados[$i+1]['is_fechamento'])) || isset($dado['is_fechamento']);
                    
                    $s1 = $s2 = null;
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
                    echo '<tr class="'. $dia .'">';
                    echo '<td>' . $dia . '</td>';
                    echo '<td>' . Util::dataISOToBR($dado['data']) . '</td>';
                    echo '<td>' . Util::getObsData($dado['data']) . '</td>';
                    echo '<td>' . $dado['entrada1'] . '</td>';
                    echo '<td>' . $dado['saida1'] . '</td>';
                    echo '<td>' . $dado['entrada2'] . '</td>';
                    echo '<td>' . $dado['saida2'] . '</td>';
                    echo '<td>' . Util::sec_to_time($segundosNormais) . '</td>';
                    echo '<td>' . Util::sec_to_time($segundosTrabalhados) . '</td>';
                    echo '<td>' . Util::sec_to_time(Util::getHorasExtras($dado)) . '</td>';
                    echo '<td>' . Util::getHorasTrabalhadasIrComp($dado) . '</td>';
                    echo '<td>' . Util::sec_to_time(Util::getHorasExtrasMenosIrComp($dado, $dados)) . '</td>';
                    echo '<td>' . Util::sec_to_time($s1) . '</td>';
                    echo '<td>' . Util::sec_to_time($s2) . '</td>';
                    echo '</tr>';
                    
                    if(isset($dado['is_fechamento'])) {
                        echo '<tr>';
                        echo '<td colspan="2"><strong>Soma</strong></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td>' .  Util::sec_to_time($tSegundosNormais)  . '</td>';
                        echo '<td>' .  Util::sec_to_time($tSegundosTrabalhados)  . '</td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td>'. Util::sec_to_time($totalHNMes) .'</td>';
                        echo '<td>'. Util::sec_to_time($totalHTMes) .'</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td colspan="14"> </td>';
                        echo '</tr>';
                        
                        $tSegundosNormais = $totalHNMes = $totalHTMes = $tSegundosTrabalhados = 0;
                    }
                }
                ?>
        </table>
        </pre>
    </body>
</html>