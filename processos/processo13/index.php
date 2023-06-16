<?php

namespace processo13;
use Util;

ini_set('display_errors', 'On');
include('../Util.php');


$o = new Processo13();

$o->calcular();

class Processo13
{

    private $indices;

    public function __construct()
    {
        $indices = file('indices.csv', FILE_IGNORE_NEW_LINES);
        $planilha = file('planilha.csv', FILE_IGNORE_NEW_LINES);

        unset($planilha[0]);
        unset($indices[0]);
        array_walk($indices, function (&$el) {
            $el = explode(';', $el);
        });


        array_walk($planilha, function (&$el) {
            $el = explode(';', $el);
        });

        $this->indices = $indices;
        $this->planilha = $planilha;
    }

    public function calcular()
    {
        echo '<table>';
        foreach ($this->planilha as &$linha) {
            $d1 = Util::dataBRToISO($linha[3]);
            $d2 = Util::dataBRToISO($linha[4]);

            if ($d1 > $d2) {
                $tmp = $d1;
                $d1 = $d2;
                $d2 = $tmp;
            }

            $linha['fator'] = $this->getFator($d1, $d2);

            echo '<tr>';
            echo '<td>' . $linha[3] . '</td>';
            echo '<td>' . $linha[4] . '</td>';
            echo '<td>' . number_format($linha['fator'], 5, ',', '.') . '</td>';
            echo '</tr>';
        }

        echo '</table>';
    }

    public function getFator($d1, $d2)
    {
        $tmp1 = substr($d1, 0, 7) . '-01';
        $tmp2 = substr($d2, 0, 7) . '-01';

        $t1 = new DateTime($tmp1);
        $t2 = new DateTime($tmp2);
        $indices = [];
        while ($t1 <= $t2) {
            $indices[] = $this->getIndice($t1);
            $t1->add(new DateInterval('P1M'));
        }

        $indice = 1;
        while (!empty($indices)) {
            $valor = array_pop($indices);
            $valorDecimal = str_replace([',', '%'], ['.', ''], $valor) / 100;

            $indice = (($indice * $valorDecimal) + $indice);
        }
        return round($indice, 5);
    }

    private function getIndice($data)
    {
        foreach ($this->indices as $indice) {
            $tmp = DateTime::createFromFormat('d/m/Y', $indice[0]);

            if ($tmp->format('d/m/Y') == $data->format('d/m/Y')) {
                return $indice[1];
            }
        }

        throw new Exception('Não foi encontrado índice para a data ' . $data->format('d/m/Y'));
    }
}

?>