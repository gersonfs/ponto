
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
                continue;
            }

            $dados[$i] = [
                'data' => Util::dataBRToISO($linha[3]),
                'entrada1' => $linha[5],
                'saida1' => $linha[6],
                'entrada2' => $linha[7],
                'saida2' => $linha[8]
            ];
            $i++;
        }

        //echo '<pre>' . print_r($dados, true) . '</pre>';
        fclose($f);
        ?>
        <pre>
        <table style="width: 80%">
                <?php
                foreach ($dados as $dado) {
                    echo '<tr>';
                    echo '<td>' . Util::getDiaDaSemanaCurto($dado['data']) . '</td>';
                    echo '<td>' . (Util::isFeriado($dado['data']) ? 'Feriado' : '') . '</td>';
                    echo '<td>' . Util::dataISOToBR($dado['data']) . '</td>';
                    echo '<td>' . $dado['entrada1'] . '</td>';
                    echo '<td>' . $dado['saida1'] . '</td>';
                    echo '<td>' . $dado['entrada2'] . '</td>';
                    echo '<td>' . $dado['saida2'] . '</td>';
                    echo '<td>' . Util::getHorasNormais($dado) . '</td>';
                    echo '<td>' . Util::getHorasTrabalhadas($dado) . '</td>';
                    echo '</tr>';
                    
                    if(isset($dado['is_fechamento'])) {
                        echo '<tr>';
                        echo '<td colspan="2">Soma</td>';
                        echo '<td colspan="999"></td>';
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