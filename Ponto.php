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

}