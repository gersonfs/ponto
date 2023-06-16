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
    /** @var \GersonSchwinn\Ponto\JornadaSemanal[] */
    private array $jornadasSemanais;
    
    public function __construct(array $jornadasSemanais)
    {
        $this->jornadasSemanais = $jornadasSemanais;
    }

    public function getJornadasSemanais(): array
    {
        return $this->jornadasSemanais;
    }
    
}
