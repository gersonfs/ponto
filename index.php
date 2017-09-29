
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
                <td>H.I.C.</td>
                <td>H.E.</td>
            </tr>
                <?php
                foreach ($dados as $dado) {
                    echo '<tr>';
                    echo '<td>' . Util::getDiaDaSemanaCurto($dado['data']) . '</td>';
                    echo '<td>' . Util::dataISOToBR($dado['data']) . '</td>';
                    echo '<td>' . (Util::isFeriado($dado['data']) ? 'Feriado' : '') . '</td>';
                    echo '<td>' . $dado['entrada1'] . '</td>';
                    echo '<td>' . $dado['saida1'] . '</td>';
                    echo '<td>' . $dado['entrada2'] . '</td>';
                    echo '<td>' . $dado['saida2'] . '</td>';
                    echo '<td>'; 
                    if(!Util::isDomingo($dado) && !Util::isFeriado($dado['data'])) {
                        echo Util::sec_to_time(Util::getHorasNormais($dado) * 60 * 60);
                    }
                    echo '</td>';
                    echo '<td>' . Util::getHorasTrabalhadas($dado) . '</td>';
                    echo '<td>' . Util::getHorasTrabalhadasIrComp($dado) . '</td>';
                    echo '<td>' . Util::getHorasExtras($dado, $dados) . '</td>';
                    echo '</tr>';
                    
                    if(isset($dado['is_fechamento'])) {
                        echo '<tr>';
                        echo '<td colspan="2"><strong>Soma</strong></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '<td></td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td colspan="999"> </td>';
                        echo '</tr>';
                    }
                }
                ?>
            <tr>
                <td></td>
            </tr>
        </table>
        </pre>
    </body>
</html>