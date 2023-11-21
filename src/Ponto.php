<?php

namespace GersonSchwinn\Ponto;

use GersonSchwinn\Ponto\Util;
use DateTime;
use \DateInterval;

class Ponto extends \ArrayObject
{

    private array $ponto = [];
    private JornadaSemanal $jornadaSemanal;

    public function __construct(array $ponto, JornadaSemanal $jornadaSemanal)
    {
        $this->ponto = $ponto;
        $this->jornadaSemanal = $jornadaSemanal;
        $this->setDateTime();
    }

    public function offsetExists($index): bool
    {
        return isset($this->ponto[$index]);
    }

    public function offsetGet(mixed $index): mixed
    {
        return $this->ponto[$index];
    }

    public function offsetSet(mixed $index, mixed $newval): void
    {
        $this->ponto[$index] = $newval;
    }

    public function offsetUnset(mixed $index): void
    {
        unset($this->ponto[$index]);
    }

    private function setDateTime(): void
    {
        for ($i = 1; $i <= 4; $i++) {
            if (!isset($this->ponto['entrada' . $i]) || empty($this->ponto['entrada' . $i])) {
                continue;
            }
            $t1 = DateTime::createFromFormat("Y-m-d H:i", $this->ponto['data'] . ' ' . $this->ponto['entrada' . $i]);
            $t2 = DateTime::createFromFormat("Y-m-d H:i", $this->ponto['data'] . ' ' . $this->ponto['saida' . $i]);

            if ($t2 === false) {
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

    public function getSegundosTrabalhados(): int
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

    public function getDiaDaSemanaCurto(): string
    {
        if (!strlen($this->ponto['data'])) {
            return '';
        }

        $dias = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];

        if (preg_match("/^\d+$/", $this->ponto['data'])) {
            return $dias[$this->ponto['data']];
        }

        return $dias[date('w', strtotime($this->ponto['data']))];
    }

    public function isSabado(): bool
    {
        return date('w', strtotime($this->ponto['data'])) == 6;
    }

    public function isDomingo(): bool
    {
        return date('w', strtotime($this->ponto['data'])) == 0;
    }

    /**
     * Regra: Todo sábado e domingo trabalhado, ou quando trabalha após as 23:30
     */
    public function getHoraItinere(): ?string
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

    public function getSegundosNoturno(bool $estenderHoraNoturna = false): int
    {
        $segundos = 0;
        for ($i = 1; $i <= 4; $i++) {

            if (!isset($this->ponto['entrada' . $i])) {
                continue;
            }

            $entrada = $this->ponto['entrada' . $i];
            $saida = $this->ponto['saida' . $i];

            if (empty($entrada)) {
                continue;
            }

            $dEntrada = $this->ponto['dt_entrada' . $i];
            $dSaida = $this->ponto['dt_saida' . $i];

            $segundos += Util::getSegundosNoturno($dEntrada, $dSaida, $estenderHoraNoturna);
        }

        return $segundos;
    }

    public function isDescansoSemanal(bool $informarDsr = true): bool
    {
        if ($informarDsr) {
            if ($this->ponto['obs'] == 'dsr') {
                return true;
            }
            return false;
        }

        return date('w', strtotime($this->ponto['data'])) == $this->jornadaSemanal->getDiaDescansoSemanal();
    }

}