<?php

namespace GersonSchwinn\Ponto;

class JornadaSemanal
{
    private array $jornadasDiarias;
    private \DateTimeInterface $dataInicio;
    private \DateTimeInterface $dataFim;
    
    private int $descansoSemanal;

    public function __construct(array $jornadasDiarias, \DateTimeInterface $dataInicio, \DateTimeInterface $dataFim, int $descansoSemanal = 0)
    {
        $this->jornadasDiarias = $jornadasDiarias;
        $this->dataInicio = $dataInicio;
        $this->dataFim = $dataFim;
        $this->descansoSemanal = $descansoSemanal;
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

    public function getDiaDescansoSemanal(): int
    {
        return $this->descansoSemanal;
    }
}