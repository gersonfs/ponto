<?php
include '../config/bootstrap.php';

use GersonSchwinn\Ponto\Ponto;
use GersonSchwinn\Ponto\Util;

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

            tr.Dom td {
                color: red;
            }
        </style>
    </head>
    <body>
        <?php
        $dataInicio = DateTime::createFromFormat('d/m/Y', '08/10/2012');
        $dataFim = DateTime::createFromFormat('d/m/Y', '19/05/2016');
        $jornada = [
            1 => [
                ['16:15', '21:30'],
                ['22:30', '01:30'],
            ],
            2 => [
                ['16:15', '21:30'],
                ['22:30', '01:30'],
            ],
            3 => [
                ['16:15', '21:30'],
                ['22:30', '01:30'],
            ],
            4 => [
                ['16:15', '21:30'],
                ['22:30', '01:30'],
            ],
            5 => [
                ['16:15', '21:30'],
                ['22:30', '01:30'],
            ],
        ];

        $jornadas = [
            new \GersonSchwinn\Ponto\JornadaSemanal($jornada, $dataInicio, $dataFim),
        ];
        $processo = new \GersonSchwinn\Ponto\Processo($jornadas);
        
        Util::addJornadaTrabalho($jornada, $dataInicio->format('d/m/Y'), $dataFim->format('d/m/Y'));

        Util::setPossuiHoraExtraIregularmenteCompensada(false);
        //$hora100Porcento = 22;
        //$hora130Porcento = 38;

        $hora100Porcento = 1000;
        $hora130Porcento = 1000;
        $segundosIntrajornada = 60 * 60;
        $possuiIntraJornada = true;

        $f = fopen('../processos/processo21.csv', 'r');
        /**
         * @var \GersonSchwinn\Ponto\Ponto[] $dados
         */
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
            if (!empty($obs)) {
                if (!in_array($obs, Util::getObservacoesTratadas())) {
                    throw new Exception('A observação ' . $obs . ' não está programada!');
                }

                $registrosObservacoes[] = $linha;
            }

            $horaIntrajornada = null;
            if (!empty($linha[3]) && $possuiIntraJornada) { //@phpstan-ignore-line
                $horaIntrajornada = $segundosIntrajornada; //1 hora
            }
            $dados[$i] = new Ponto([
                'data' => Util::dataBRToISO($linha[1]),
                'obs' => $obs,
                'entrada1' => $linha[3],
                'saida1' => $linha[4],
                'entrada2' => $linha[5],
                'saida2' => $linha[6],
                'entrada3' => $linha[7],
                'saida3' => $linha[8],
                'entrada4' => $linha[9] ?? null,
                'saida4' => $linha[10] ?? null,
                'hora_intrajornada' => $horaIntrajornada,
                'mes' => $mes,
                'semana' => $semana,
            ], $jornadas[0]);

            if ($dados[$i]->isDescansoSemanal()) {
                $semana++;
            }
            $i++;
        }
        fclose($f);

        Util::setRegistrosObservacoes($registrosObservacoes);
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
                <td>Entrada</td>
                <td>Saida</td>
                <td>Entrada</td>
                <td>Saida</td>
                <td title="Hora Normal">H.N.</td>
                <td title="Hora Trabalhada">H.T.</td>
                <td title="Hora Extra">H.E.</td>
                <td title="Hora irregularmente compensada">H.I.C.</td>
                <td>H.E. - H.I.C.</td>
                <td>H. N. S.</td>
                <td>H. T. S.</td>
                <td>H. E. 50%.</td>
                <td>H. E. 100%.</td>
                <td>H. E. 130%.</td>
                <td title="Hora noturna">H.N.</td>
                <td title="Hora intrajornada">H. I.</td>
            </tr>
                <?php
                $totalHEMenosHIC = $totalHTMes = $totalHNMes = $tSegundosNormais = $tSegundosTrabalhados = $sHE = $sHIC = $sH100 = $sHN = $sHi = 0;
                $totaisMeses = [];
                foreach ($dados as $i => $dado) {
                    $mostrarHoraSemana = ($dado->isSabado() && isset($dados[$i + 1]['is_fechamento'])) || isset($dado['is_fechamento']);

                    $h50 = $h100 = $h130 = $s1 = $s2 = null;
                    if ($mostrarHoraSemana) {
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
                    if ($heHic < 0) {
                        $heHic = 0;
                    }

                    $he100 = null;
                    if ((Util::isDescansoSemanal($dado) || Util::isFeriado($dado['data'])) && $heHic > 0) {
                        $he100 = $heHic;
                        $sH100 += $he100;
                    }

                    $hn = Util::getSegundosConvertidosHoraNoturna($dado);
                    if (!empty($hn)) {
                        $sHN += $hn;
                    }

                    if (!empty($dado['hora_intrajornada'])) {
                        $sHi += $dado['hora_intrajornada'];
                    }

                    $totalHEMenosHIC += $heHic;
                    echo '<tr class="' . $dado->getDiaDaSemanaCurto() . '">';
                    echo '<td>' . $dado->getDiaDaSemanaCurto() . '</td>';
                    echo '<td>' . Util::dataISOToBR($dado['data']) . '</td>';
                    echo '<td>' . $dado['obs'] . '</td>';
                    echo '<td>' . $dado['entrada1'] . '</td>';
                    echo '<td>' . $dado['saida1'] . '</td>';
                    echo '<td>' . $dado['entrada2'] . '</td>';
                    echo '<td>' . $dado['saida2'] . '</td>';
                    echo '<td>' . $dado['entrada3'] . '</td>';
                    echo '<td>' . $dado['saida3'] . '</td>';
                    echo '<td>' . $dado['entrada4'] . '</td>';
                    echo '<td>' . $dado['saida4'] . '</td>';
                    echo '<td title="Hora Normal">' . Util::sec_to_time($segundosNormais) . '</td>';
                    echo '<td title="Hora Trabalhada">' . Util::sec_to_time($segundosTrabalhados) . '</td>';
                    echo '<td title="Hora Extra">' . Util::sec_to_time($he) . '</td>';
                    echo '<td title="Hora Irregularmente Compensada">' . Util::sec_to_time($ic) . '</td>';
                    echo '<td title="Hora Extra - Hora Irregularmente Compensada">' . Util::sec_to_time($heHic) . '</td>';
                    echo '<td>' . Util::sec_to_time($s1) . '</td>';
                    echo '<td>' . Util::sec_to_time($s2) . '</td>';
                    echo '<td></td>';
                    echo '<td>' . Util::sec_to_time($he100) . '</td>';
                    echo '<td></td>';
                    echo '<td>' . Util::sec_to_time($hn) . '</td>';
                    echo '<td>' . Util::sec_to_time($dado['hora_intrajornada']) . '</td>';
                    echo '</tr>';

                    if (isset($dado['is_fechamento'])) {

                        $h50 = $totalHEMenosHIC - $sH100;
                        if ($h50 < 0) {
                            $h50 = 0;
                        }

                        $h100 = $sH100;

                        if ($h50 > ($hora100Porcento * 60 * 60)) {
                            $h50 = $hora100Porcento * 60 * 60;
                            $h100 = $sH100 + ($totalHEMenosHIC - $h50);
                            if ($h100 > ($hora130Porcento * 60 * 60)) {
                                $h100 = $hora130Porcento * 60 * 60;
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
                            'hn' => $sHN,
                            'hi' => $sHi,
                        ];

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
                        echo '<td>' . Util::sec_to_time($tSegundosNormais) . '</td>';
                        echo '<td>' . Util::sec_to_time($tSegundosTrabalhados) . '</td>';
                        echo '<td>' . Util::sec_to_time($sHE) . '</td>';
                        echo '<td>' . Util::sec_to_time($sHIC) . '</td>';
                        echo '<td>' . Util::sec_to_time($totalHEMenosHIC) . '</td>';
                        echo '<td>' . Util::sec_to_time($totalHNMes) . '</td>';
                        echo '<td>' . Util::sec_to_time($totalHTMes) . '</td>';
                        echo '<td>' . Util::sec_to_time($h50) . '</td>';
                        echo '<td>' . Util::sec_to_time($h100) . '</td>';
                        echo '<td>' . Util::sec_to_time($h130) . '</td>';
                        echo '<td>' . Util::sec_to_time($sHN) . '</td>';
                        echo '<td>' . Util::sec_to_time($sHi) . '</td>';
                        echo '</tr>';
                        echo '<tr>';
                        echo '<td colspan="14"> </td>';
                        echo '</tr>';

                        $totalHEMenosHIC = $sH100 = $sHIC = $sHE = $tSegundosNormais = $totalHNMes = $totalHTMes = $tSegundosTrabalhados = $sHN = $sHi = 0;
                    }
                }
                ?>
        </table>
        <br/><br/>

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
                <td>H N</td>
                <td>H IN</td>
            </tr>
            <?php
            $somaNormal = $somaTrabalhada = $somaHe = $somaHic = $somaHeHic = $somaH50 = $somaH100 = $somaH130 = $somaHN = $somaHi = 0;
            foreach ($totaisMeses as $total) {
                $somaNormal += $total['normal'];
                $somaTrabalhada += $total['trabalhado'];
                $somaHe += $total['he'];
                $somaHic += $total['hic'];
                $somaHeHic += $total['he-hic'];
                $somaH50 += $total['h50'];
                $somaH100 += $total['h100'];
                $somaH130 += $total['h130'];
                $somaHN += $total['hn'];
                $somaHi += $total['hi'];

                echo '<tr>';
                echo '<td>' . date('m/Y', strtotime($total['periodo'])) . '</td>';
                echo '<td>' . number_format($total['normal'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['trabalhado'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['he'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['hic'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['he-hic'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['h50'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['h100'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['h130'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['hn'] / 60 / 60, 2, ",", "") . '</td>';
                echo '<td>' . number_format($total['hi'] / 60 / 60, 2, ",", "") . '</td>';
                echo '</tr>';
            }
            ?>
            <tfoot>
                <tr>
                    <td>Soma</td>
                    <?php
                    echo '<td>' . number_format($somaNormal / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaTrabalhada / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaHe / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaHic / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaHeHic / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaH50 / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaH100 / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaH130 / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaHN / 60 / 60, 2, ",", "") . '</td>';
                    echo '<td>' . number_format($somaHi / 60 / 60, 2, ",", "") . '</td>';
                    ?>
                </tr>
            </tfoot>
        </table>
        
        <p>Jornada:</p>
        <?php

        foreach ($processo->getJornadasSemanais() as $jornada) {
            echo 'Início: ' . $jornada->getDataInicio()->format('d/m/Y');
            echo ' Fim: ' . $jornada->getDataFim()->format('d/m/Y');
            echo '<br />';
            foreach ($jornada->getJornadasDiarias() as $jornadaDiaria) {
                echo '<p>';
                echo $jornadaDiaria->getDiaDaSemanaCurto();
                echo ' ';
                $str = [];
                foreach ($jornadaDiaria->getEntradasSaidas() as $ponto) {
                    $str [] = $ponto[0] . ' - ' . $ponto[1];
                }
                echo implode(', ', $str);
                echo '</p>';
            }
        }
        ?>

        </pre>
    </body>
</html>