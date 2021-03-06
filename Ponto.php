<?php

class Ponto extends ArrayObject
{

    private $ponto = [];

    public function __construct($ponto)
    {
        $this->ponto = $ponto;
        $this->setDateTime();
    }

    public function offsetExists($index) : bool
    {
        return isset($this->ponto[$index]);
    }

    public function offsetGet($index)
    {
        return $this->ponto[$index];
    }

    public function offsetSet($index, $newval)
    {
        $this->ponto[$index] = $newval;
    }

    public function offsetUnset($index)
    {
        unset($this->ponto[$index]);
    }

    private function setDateTime()
    {
        for ($i = 1; $i <= 4; $i++) {
            if (!isset($this->ponto['entrada' . $i]) || empty($this->ponto['entrada' . $i])) {
                continue;
            }
            $t1 = DateTime::createFromFormat("Y-m-d H:i", $this->ponto['data'] . ' ' . $this->ponto['entrada' . $i]);
            $t2 = DateTime::createFromFormat("Y-m-d H:i", $this->ponto['data'] . ' ' . $this->ponto['saida' . $i]);

            if($t2 === false) {
                debug($i);
                debug($this->ponto);
            }

            if ($t2 < $t1) {
                $t2->add(DateInterval::createFromDateString('1 day'));
            }

            $this->ponto['dt_entrada' . $i] = $t1;
            $this->ponto['dt_saida' . $i] = $t2;
        }
    }

    public function getSegundosTrabalhados()
    {
        $segundos = 0;
        for ($i = 1; $i <= 4; $i++) {
            if (!isset($this->ponto['entrada' . $i]) || empty($this->ponto['entrada' . $i])) {
                continue;
            }
            $segundos += $this->ponto['dt_saida' . $i]->getTimestamp() - $this->ponto['dt_entrada' . $i]->getTimestamp();
        }

        return $segundos;
    }


    public function isSabado()
    {
        return date('w', strtotime($this->ponto['data'])) == 6;
    }

    public function isDomingo()
    {
        return date('w', strtotime($this->ponto['data'])) == 0;
    }

    /**
     * Regra: Todo sábado e domingo trabalhado, ou quando trabalha após as 23:30
     *
     * @return void
     */
    public function getHoraItinere()
    {
        $horaItinere = '00:40';

        for ($i = 1; $i <= 4; $i++) {
            if (!isset($this->ponto['saida' . $i]) || empty($this->ponto['saida' . $i])) {
                continue;
            }

            if ($this->isSabado()) {
                return $horaItinere;
            }

            if ($this->isDomingo()) {
                return $horaItinere;
            }

            $dSaida = $this->ponto['dt_saida' . $i];

            $dLimite = DateTime::createFromFormat('Y-m-d H:i', $this->ponto['data'] . ' 23:30');
            if ($dSaida > $dLimite) {
                return $horaItinere;
            }
        }

        return null;
    }

    public function getSegundosNoturno($estenderHoraNoturna = false) {
        $segundos = 0;
        for($i = 1; $i <= 4; $i++) {
            
            if(!isset($this->ponto['entrada' . $i])) {
                continue;
            }
            
            $entrada = $this->ponto['entrada' . $i];
            $saida = $this->ponto['saida' . $i];
            
            if(empty($entrada)) {
                continue;
            }
            
            $dEntrada = $this->ponto['dt_entrada' . $i];
            $dSaida = $this->ponto['dt_saida' . $i];
            
            $segundos += Util::getSegundosNoturno($dEntrada, $dSaida, $estenderHoraNoturna);
        }
        
        return $segundos;
    }

}