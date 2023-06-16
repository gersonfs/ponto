<?php

namespace GersonSchwinn\Ponto;

class JornadaDiaria
{
    private int $diaSemana;
    private array $entradasSaidas;

    public function __construct(int $diaSemana, array $entradasSaidas)
    {
        $this->diaSemana = $diaSemana;
        $this->entradasSaidas = $entradasSaidas;
    }

    public function getDiaSemana(): int
    {
        return $this->diaSemana;
    }

    public function getEntradasSaidas(): array
    {
        return $this->entradasSaidas;
    }

    public function getDiaDaSemanaCurto(): string
    {
        $dias = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];

        return $dias[$this->diaSemana];
    }
}