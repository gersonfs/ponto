<?php

namespace GersonSchwinn\Ponto;
use Util;

/**
 * Description of Processo
 *
 * @author Gerson Felipe Schwinn <gerson@onehost.com.br>
 */
class Processo
{

    public function __construct($config)
    {
        $this->config = $config;
        $this->setJornadas();
    }

    private function setJornadas()
    {
        foreach ($this->config['jornadas'] as $jornada) {
            Util::addJornadaTrabalho($jornada['horarios'], $jornada['inicio'], $jornada['fim']);
        }
    }
}
