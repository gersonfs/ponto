<?php

namespace GersonSchwinn\Ponto;

class JornadaSemanal
{
    private array $jornadasDiarias;
    private \DateTimeInterface $dataInicio;
    private \DateTimeInterface $dataFim;

    public function __construct(array $jornadasDiarias, \DateTimeInterface $dataInicio, \DateTimeInterface $dataFim)
    {
        $this->jornadasDiarias = $jornadasDiarias;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
    }

    public function getDataInicio(): \DateTimeInterface
    {
        return $this->dataInicio;
    }

    public function getDataFim(): \DateTimeInterface
    {
        return $this->dataFim;
    }

    /**
     * @return \GersonSchwinn\Ponto\JornadaDiaria[]
     */
    public function getJornadasDiarias(): array
    {
        $jornadas = [];
        foreach ($this->jornadasDiarias as $diaSemana => $entradasSaidas) {
            $jornadas[] = new JornadaDiaria($diaSemana, $entradasSaidas);
        }
        return $jornadas;
    }
}