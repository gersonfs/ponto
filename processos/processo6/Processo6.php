<?php

namespace processo6;
use type;
use Util;

/**
 * Description of Processo6
 *
 * @author Gerson Felipe Schwinn <gerson@onehost.com.br>
 */
class Processo6
{


    public function getRegistros()
    {
        $linhas = file(__DIR__ . '/tabela_horarios.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $codigosHorarios = [];

        $tmpRegistros = $registros = [];
        $datas = [];
        foreach ($linhas as $linha) {
            [$data, $obs] = explode("\t", $linha);
            if (strpos($data, 'rios:') !== false) {
                [, $strCodigosHorarios] = explode(': ', $data);
                $codigosHorarios = explode('-', $strCodigosHorarios);
                continue;
            }

            $data = Util::dataBRToISO($data);
            if (!in_array($data, $datas)) {
                $datas[] = $data;
            }
            $tmpRegistros[] = [
                'horarios' => $codigosHorarios,
                'data' => $data,
                'periodos' => $this->getPeriodos($data, $codigosHorarios),
                'obs' => trim($obs),
            ];
        }

        sort($datas);

        $inconsistencias = [];
        foreach ($datas as $data) {
            $periodos = $obs = $horarios = [];
            foreach ($tmpRegistros as $tmp) {
                if ($tmp['data'] != $data) {
                    continue;
                }

                foreach ($tmp['horarios'] as $tmp1) {
                    $horarios[] = $tmp1;
                }

                foreach ($tmp['periodos'] as $periodo) {
                    $periodos[] = $periodo;
                }

                $obs[] = $tmp['obs'];

            }

            usort($periodos, function ($a, $b) {
                $t1 = Util::time_to_sec($a['inicio']);
                $t2 = Util::time_to_sec($b['inicio']);
                return $t1 < $t2 ? -1 : 1;
            });

            $obs = array_unique($obs);
            if (count($obs) > 1) {
                //$inconsistencias[] = Util::dataISOToBR($data) . ' - ' . implode(', ',$obs);
                throw new Exception(Util::dataISOToBR($data) . ' - ' . implode(', ', $obs));
                //continue;
            }

            $obs = array_filter($obs);

            $semHoras = ['Falta', 'Feriado', 'Sem. Acadêmica', 'Afast. Justificado'];

            if (!empty($obs) && in_array($obs[0], $semHoras)) {
                $periodos = [];
            }

            $registros[] = [
                'horarios' => $horarios,
                'data' => $data,
                'periodos' => $periodos,
                'obs' => $obs,
            ];
        }

        //echo '<pre>' . print_r($inconsistencias, true) . '</pre>';

        return $registros;
    }

    public function getPeriodos($data, $codigosHorarios)
    {

        $diaSemana = date('w', strtotime($data));

        $registros = [];
        foreach ($codigosHorarios as $codigo) {
            $diaSemanaHorario = $this->getDiaSemanaHorario($codigo);

            if ($diaSemana == $diaSemanaHorario) {

                $horario = $this->getHorario($codigo);

                $registros[] = [
                    'inicio' => $horario['inicio'],
                    'fim' => $horario['fim'],
                ];
            }
        }

        return $registros;
    }

    public function getDiaSemanaHorario($codigoHorario)
    {
        $diasSemana = $this->getHorariosDiaSemana();
        foreach ($diasSemana as $dia => $codigosHorarios) {
            if (in_array($codigoHorario, $codigosHorarios)) {
                return $dia;
            }
        }
        throw new Exception('Horário não encontrado!');
    }

    /**
     * O índice é o dia da semana, 1 => Segunda, 2 => Terça..
     * @return type
     */
    public function getHorariosDiaSemana()
    {
        return [
            1 => range(20, 29),
            2 => range(30, 39),
            3 => range(40, 49),
            4 => range(50, 59),
            5 => range(60, 69),
            6 => range(70, 79),
        ];
    }

    public function getHorario($codigo)
    {
        $tabela = $this->getTabelaHorarios();
        foreach ($tabela as $registro) {
            if (in_array($codigo, $registro['horarios'])) {
                return [
                    'inicio' => $registro['inicio'],
                    'fim' => $registro['fim'],
                ];
            }
        }

        throw new Exception('Horário não encontrado!');
    }

    public function getTabelaHorarios()
    {
        return [
            [
                'horarios' => [20, 30, 40, 50, 60, 70],
                'inicio' => '08:00',
                'fim' => '09:25',
            ],
            [
                'horarios' => [21, 31, 41, 51, 61, 71],
                'inicio' => '09:30',
                'fim' => '10:55',
            ],
            [
                'horarios' => [22, 32, 42, 52, 62, 72],
                'inicio' => '11:00',
                'fim' => '12:25',
            ],
            [
                'horarios' => [24, 34, 44, 54, 64, 74],
                'inicio' => '13:40',
                'fim' => '15:05',
            ],
            [
                'horarios' => [25, 35, 45, 55, 65, 75],
                'inicio' => '15:10',
                'fim' => '16:35',
            ],
            [
                'horarios' => [26, 36, 46, 56, 66, 76],
                'inicio' => '16:40',
                'fim' => '18:05',
            ],
            [
                'horarios' => [27, 37, 47, 57, 67, 77],
                'inicio' => '18:10',
                'fim' => '19:35',
            ],
            [
                'horarios' => [28, 38, 48, 58, 68, 78],
                'inicio' => '19:40',
                'fim' => '21:05',
            ],
            [
                'horarios' => [29, 39, 49, 59, 69, 79],
                'inicio' => '21:10',
                'fim' => '22:35',
            ],
        ];
    }

}
