<?php

namespace GersonSchwinn\Ponto;

use DateTime;
use Exception;
use DateInterval;

class Util
{

    /**
     * Hora Extra Iregularmente Compensada
     */
    private static bool $possuiHEIC = true;
    
    private static bool $estenderHoraNoturna = false; //@phpstan-ignore-line

    public static bool $informarDSR = false;

    private static array $registrosObservacoes = [];

    private static array $jornadas;
    private static array $config = [];

    public static function getMeses(): array
    {
        return [
            1 => 'Janeiro',
            2 => 'Fevereiro',
            3 => 'Março',
            4 => 'Abril',
            5 => 'Maio',
            6 => 'Junho',
            7 => 'Julho',
            8 => 'Agosto',
            9 => 'Setembro',
            10 => 'Outubro',
            11 => 'Novembro',
            12 => 'Dezembro',
        ];
    }

    public static function getAnos(): array
    {
        $range = range(date('Y'), date('Y') + 11);
        return array_combine($range, $range);
    }

    public static function formataDia(int|string $dia): string
    {
        return ($dia < 10) ? str_pad((string)$dia, 2, '0', STR_PAD_LEFT) : $dia;
    }

    public static function formataMes(int|string $mes): string 
    {
        return sprintf('%02d', $mes);
    }

    public static function formatarValor(?float $valor, int $decimais = 2, string $separadorDecimais = ',', string $separadorMilhar = '.'): string
    {
        if (!strlen((string)$valor)) {
            return '';
        }

        return 'R$ ' . self::formatarNumero($valor);
    }

    public static function formatarNumero(float $valor, int $decimais = 2, string $separadorDecimais = ',', string $separadorMilhar = '.'): string
    {
        return number_format($valor, $decimais, $separadorDecimais, $separadorMilhar);
    }

    public static function isFeriado(string $data): bool
    {
        return in_array($data, self::getFeriados());
    }

    public static function isAusencia(string $data): bool
    {
        return in_array($data, self::getAusencias());
    }

    public static function isAtestado(string $data): bool
    {
        return in_array($data, self::getAtestados());
    }

    public static function isFerias(string $data): bool
    {
        return in_array($data, self::getFerias());
    }

    public static function getFeriados(): array
    {
        return self::getRegistrosData(['feriado', 'licenca r.', 'lic. n. r.', 'liberac.r.', 'feriado n.c.']);
    }

    public static function getAusencias(): array
    {
        return self::getRegistrosData(['ausencia', 'falta', 'compens.', 'falta just.', 'falta p.']);
    }

    public static function getAtestados(): array
    {
        return self::getRegistrosData('atestado');
    }

    public static function getFerias(): array
    {
        return self::getRegistrosData('ferias');
    }

    public static function getRegistrosData(string|array $tipos): array
    {
        if (is_string($tipos)) {
            $tipos = [$tipos];
        }

        $strTipos = implode('-', $tipos);

        static $registros = [];
        if (isset($registros[$strTipos])) {
            return $registros[$strTipos];
        }

        $linhas = self::getRegistrosObservacoes();
        $registros[$strTipos] = [];
        foreach ($tipos as $tipo) {
            $tipo = strtolower($tipo);
            foreach ($linhas as $linha) {
                if (strtolower(trim($linha[2])) == $tipo) {
                    $registros[$strTipos][] = self::dataBRToISO(trim($linha[1]));
                }
            }
        }
        return $registros[$strTipos];
    }

    private static function getRegistrosObservacoes(): array
    {
        return self::$registrosObservacoes;
    }

    public static function time_to_sec(?string $time): ?int
    {
        if ($time === null) {
            return null;
        }

        $p = explode(':', $time);
        $seconds = 0;
        $hours = (int)$p[0];
        $minutes = (int)$p[1];
        if (count($p) === 3) {
            $seconds = (int)$p[2];
        }

        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    public static function sec_to_time(?int $seconds, bool $mostrarSegundos = false): ?string
    {
        if ($seconds === null) {
            return null;
        }

        $hours = floor($seconds / 3600);
        $minutes = floor($seconds % 3600 / 60);
        $seconds = $seconds % 60;

        if ($mostrarSegundos) {
            return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }
        return sprintf("%02d:%02d", $hours, $minutes);
    }

    public static function dataBRToISO(string $data): string
    {
        if (!strlen($data)) {
            return '';
        }

        $p = explode(' ', $data);
        $data = $p[0];
        if (self::isDataISO($data)) {
            if (isset($p[1])) {
                return $data . ' ' . $p[1];
            }
            return $data;
        }
        [$dia, $mes, $ano] = explode('/', $data);
        $retorno = sprintf('%d-%02d-%02d', $ano, $mes, $dia);
        if (isset($p[1])) {
            $retorno .= ' ' . $p[1];
        }
        return $retorno;
    }

    public static function isDataBR(string $data): bool
    {
        return preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4}$/', $data);
    }

    public static function isDataISO(string $data): bool
    {
        return preg_match('/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $data);
    }

    public static function formatarDataBR(string $data): string
    {
        return self::dataISOToBR($data);
    }

    public static function dataISOToBR(string $data): string
    {
        if (!strlen($data)) {
            return '';
        }

        $p = explode(' ', $data);
        $data = $p[0];
        [$ano, $mes, $dia] = explode('-', $data);
        $retorno = sprintf('%02d/%02d/%d', $dia, $mes, $ano);
        if (isset($p[1])) {
            $retorno .= ' ' . $p[1];
        }
        return $retorno;
    }

    public static function diferencaDiasDatas(string $inicio, string $fim): int
    {
        $dStart = new DateTime($inicio);
        $dEnd = new DateTime($fim);
        $dDiff = $dStart->diff($dEnd);
        return $dDiff->days;
    }
    public static function getQuantidadeDiasEmComun(string $dataInicio1, string $dataInicio2, string $dataFim1, string $dataFim2): int
    {
        $inicio = self::getMaiorData($dataInicio1, $dataInicio2);
        $fim = self::getMenorData($dataFim1, $dataFim2);

        $t1 = strtotime($inicio);
        $t2 = strtotime($fim);

        if ($t1 > $t2) {
            return 0;
        }

        return self::diferencaDiasDatas($inicio, $fim);
    }

    public static function getMenorData(string $data1, string $data2): string
    {
        if (!strlen($data1) && !strlen($data2)) {
            throw new Exception('Informe uma das duas datas!');
        }

        if (!strlen($data1)) {
            return $data2;
        }

        if (!strlen($data2)) {
            return $data1;
        }

        $t1 = strtotime($data1);
        $t2 = strtotime($data2);

        if ($t1 < $t2) {
            return $data1;
        }

        return $data2;
    }

    public static function getMaiorData(string $data1, string $data2): string
    {
        if (!strlen($data1) && !strlen($data2)) {
            throw new Exception('Informe uma das duas datas!');
        }

        if (!strlen($data1)) {
            return $data2;
        }

        if (!strlen($data2)) {
            return $data1;
        }

        $t1 = strtotime($data1);
        $t2 = strtotime($data2);

        if ($t1 > $t2) {
            return $data1;
        }

        return $data2;
    }

    public static function getDataEssaSegundaFeira(): string
    {
        return date('Y-m-d', strtotime('monday this week'));
    }

    public static function getDataEsseDomingo(): string
    {
        return date('Y-m-d', strtotime('sunday this week'));
    }

    public static function getDataPrimeiroDiaMes(): string
    {
        return date('Y-m-d', mktime(0, 0, 0, (int)date('m'), 1, (int)date('Y')));
    }

    public static function getDataUltimoDiaMes(): string
    {
        return date('Y') . '-' . date('m') . '-' . date('t');
    }

    public static function getDataPrimeiroDiaProximoMes(): string
    {
        return date('Y-m-d', mktime(0, 0, 0, date('m') + 1, 1, (int)date('Y')));
    }

    public static function getDataUltimoDiaProximoMes(): string
    {
        $tInicio = strtotime(self::getDataPrimeiroDiaProximoMes());
        return date('Y', $tInicio) . '-' . date('m', $tInicio) . '-' . date('t', $tInicio);
    }
    public static function formatarPercentual(float|string $valor, int $casasDecimais = 2): string
    {
        if (strlen((string)$valor) === 0) {
            return '';
        }

        return self::formatarNumero($valor) . '%';
    }

    public static function isDiaUtil(string $data, array $feriados): bool
    {
        $diaDaSemana = date('w', strtotime($data));
        $isSabadoOuDomingo = $diaDaSemana == 6 || $diaDaSemana == 0;
        if ($isSabadoOuDomingo) {
            return false;
        }

        $mesDia = substr($data, 5, 5);
        if (in_array($mesDia, $feriados, true)) {
            return false;
        }

        return true;
    }

    public static function getHorasTrabalhadas(Ponto $ponto): ?string
    {
        $segundos = self::getSegundosTrabalhados($ponto);
        if ($segundos <= 0) {
            return null;
        }

        return self::sec_to_time($segundos);
    }

    public static function getSegundosTrabalhadosHoraNoturna(Ponto $ponto): int
    {
        $segundosDiurno = self::getSegundosDiurno($ponto);
        $segundosNoturnos = $ponto->getSegundosNoturno();
        return $segundosDiurno + self::converterSegundosNormalSegundosHoraNoturna($segundosNoturnos);
    }

    public static function getSegundosDiurno(Ponto $ponto): int
    {
        $segundosTrabalhados = self::getSegundosTrabalhados($ponto);
        $segundosNoturnos = $ponto->getSegundosNoturno();
        return $segundosTrabalhados - $segundosNoturnos;
    }

    public static function getSegundosTrabalhados(Ponto $ponto): int
    {
        return $ponto->getSegundosTrabalhados();
    }

    public static function possuiDiferencaMaior5Min(Ponto $ponto): bool
    {
        $diferencas = self::getDiferencasPonto($ponto);

        $resultado = array_filter($diferencas, function ($el) {
            $cincoMinutos = 5 * 60;
            if ($el['entrada'] > $cincoMinutos || $el['saida'] > $cincoMinutos) {
                return true;
            }
            return false;
        });

        return !empty($resultado);
    }

    public static function getDiferencasPonto(Ponto $ponto): array
    {
        $diferencas = [];

        for ($i = 1; $i <= 4; $i++) {

            if (!isset($ponto['entrada' . $i]) || empty($ponto['entrada' . $i])) {
                continue;
            }

            $horaEntrada = $ponto['entrada' . $i];
            $horaSaida = $ponto['saida' . $i];
            $jornadaHora = self::getJornadaPelaHoraEntrada($horaEntrada, $ponto['data']);
            $horaEntradaJornada = $jornadaHora[0];
            $horaSaidaJornada = $jornadaHora[1];

            $diferencas[] = [
                'entrada' => Util::time_to_sec($horaEntradaJornada) - Util::time_to_sec($horaEntrada),
                'saida' => Util::time_to_sec($horaSaida) - Util::time_to_sec($horaSaidaJornada),
            ];

        }

        return $diferencas;
    }

    private static function getJornadaPelaHoraEntrada(string $horaEntrada, string $data): array
    {
        $jornada = self::getJornadaData($data);
        if (empty($jornada)) {
            throw new Exception('Não há jornada para a data ' . $data);
        }

        $k = 0;
        $jornadaHora = null;
        $menorDiferenca = null;
        foreach ($jornada as $periodo) {
            $horaEntradaJornada = $periodo[0];

            if ($k == 0) {
                $menorDiferenca = abs(Util::time_to_sec($horaEntrada) - Util::time_to_sec($horaEntradaJornada));
                $jornadaHora = $periodo;
            }

            $diferenca = abs(Util::time_to_sec($horaEntrada) - Util::time_to_sec($horaEntradaJornada));

            if ($diferenca < $menorDiferenca) {
                $menorDiferenca = $diferenca;
                $jornadaHora = $periodo;
            }
            $k++;
        }

        if (empty($jornadaHora)) {
            throw new Exception('Não foi possível encontrar a joranda para a hora de entrada ' . $horaEntrada . ' na data ' . $data);
        }

        return $jornadaHora;
    }

    private static function getJornada(string $data): array
    {
        $timeData = strtotime($data);
        foreach (self::$jornadas as $jornada) {
            $timeInicioJornada = strtotime($jornada['inicio']);
            $timeFimJornada = strtotime($jornada['fim']);
            $pertenceJornada = $timeData >= $timeInicioJornada && $timeData <= $timeFimJornada;
            if (!$pertenceJornada) {
                continue;
            }

            return $jornada;
        }
        throw new Exception('Não foi encontrada jornada para a data ' . $data);
    }

    private static function getJornadaData(string $data): ?array
    {
        $timeData = strtotime($data);
        $diaSemana = date('w', $timeData);
        $jornada = self::getJornada($data);

        if (isset($jornada['jornada'][$diaSemana])) {
            return $jornada['jornada'][$diaSemana];
        }

        if (isset($jornada['jornada'][$data])) {
            return $jornada['jornada'][$data];
        }

        return null;
    }

    public static function getSegundosNormais(Ponto $ponto, array $options = []): ?int
    {
        $segundos = self::getHorasNormais($ponto, $options);
        if ($segundos === null) {
            return null;
        }
        return (int)($segundos * 60 * 60);
    }

    /**
     * Retorna quantidade de horas normais
     */
    public static function getHorasNormais(Ponto $ponto, array $options = []): ?int
    {

        $options += [
            'ignorarFeriados' => false,
        ];

        if (self::isFeriado($ponto['data']) && !$options['ignorarFeriados']) {
            return null;
        }

        if (self::isFerias($ponto['data'])) {
            return null;
        }

        if (self::isAtestado($ponto['data'])) {
            return null;
        }

        if (self::isDescansoSemanal($ponto)) {
            return null;
        }

        $jornada = self::getJornadaData($ponto['data']);

        if (empty($jornada)) {
            return null;
        }

        $soma = 0;
        foreach ($jornada as $j) {

            $entrada = new DateTime($ponto['data'] . ' ' . $j[0]);
            $saida = new DateTime($ponto['data'] . ' ' . $j[1]);

            //Entrou num dia e saiu no outro
            if ($saida < $entrada) {
                $saida->add(new DateInterval('P1D'));
            }

            $diferenca = $saida->diff($entrada);
            $totalSegundos = self::toSeconds($diferenca);
            $segundosNoturnos = self::getSegundosNoturno($entrada, $saida);
            $segundosDiurnos = $totalSegundos - $segundosNoturnos;
            $segundosNoturnoConvertidos = self::converterSegundosNormalSegundosHoraNoturna($segundosNoturnos);
            $segundosHoraNormalConvertidos = $segundosNoturnoConvertidos + $segundosDiurnos;

            $soma += $segundosHoraNormalConvertidos;
        }


        return intval($soma / 60 / 60);
    }

    public static function getHorasNormalSemana(Ponto $ponto, array $pontos): int 
    {
        $horas = 0;
        foreach ($pontos as $ponto2) {
            if ($ponto['semana'] == $ponto2['semana']) {
                $horas += self::getHorasNormais($ponto2);
            }
        }
        return $horas;
    }

    public static function getSegundosNormalSemana(Ponto $ponto, array $pontos): ?int 
    {
        $horas = self::getHorasNormalSemana($ponto, $pontos);
        
        return $horas * 60 * 60;
    }

    public static function isDescansoSemanal(Ponto $ponto): bool
    {
        if (self::$informarDSR) {
            if ($ponto['obs'] == 'dsr') {
                return true;
            }
            return false;
        }

        return date('w', strtotime($ponto['data'])) == self::getDiaDescansoSemanal($ponto['data']);
    }

    public static function getDiaDescansoSemanal(string $data): int
    {
        $jornada = self::getJornada($data);

        if (isset($jornada['descansoSemanal'])) {
            return $jornada['descansoSemanal'];
        }

        return 0;
    }

    public static function getSegundosTrabalhadosSemana(Ponto $ponto, array $pontos): int
    {
        $segundos = 0;
        foreach ($pontos as $ponto2) {
            if ($ponto['semana'] == $ponto2['semana']) {
                $segundos += self::getSegundosTrabalhados($ponto2);
            }
        }

        return $segundos;
    }

    /**
     * Máximo 48 minutos
     */
    public static function getSegundosIrComp(Ponto $ponto): ?int
    {
        if (!self::$possuiHEIC) {
            return 0;
        }

        $segundosNormais = self::getSegundosNormais($ponto, ['ignorarFeriados' => true]);

        if ($segundosNormais === null) {
            return null;
        }

        if ($ponto->isSabado()) {
            return 0;
        }

        $horaExtra = self::getHorasExtras($ponto);

        if (empty($horaExtra)) {
            return 0;
        }

        if ($horaExtra < (48 * 60)) {
            return $horaExtra;
        }

        return 48 * 60;
    }

    public static function getHorasTrabalhadasIrComp(Ponto $ponto): ?string
    {
        return Util::sec_to_time(self::getSegundosIrComp($ponto));
    }

    public static function getHorasExtrasMenosIrComp(Ponto $ponto, array $pontos): ?float
    {

        $horaExtra = self::getHorasExtras($ponto);
        $diferencaCobrada = $horaExtra - self::getSegundosIrComp($ponto);

        if ($diferencaCobrada <= 0) {
            return null;
        }
        $sNormalSemana = self::getSegundosNormalSemana($ponto, $pontos);
        $sTrabalhadaSemana = self::getSegundosTrabalhadosSemana($ponto, $pontos);

        if ($sTrabalhadaSemana <= $sNormalSemana) {
            return null;
        }

        return $diferencaCobrada;
    }

    /**
     * Quando entrou mais de 5 minutos antes ou saiu mais de 5 minutos depois, conta como hora extra
     * Se no dia deu 10 minutos a mais na soma, vale como hora extra
     *
     */
    public static function getHorasExtras(Ponto $ponto): ?int
    {
        $segundosTrabalhados = self::getSegundosTrabalhadosHoraNoturna($ponto);
        $segundosNormal = self::getSegundosNormais($ponto);

        if ((self::$config['horaExtraSimples'] ?? false) && $segundosTrabalhados < $segundosNormal) {
            return null;
        }

        $jornada = self::getJornadaData($ponto['data']);
        if (empty($jornada)) {
            return $segundosTrabalhados;
        }

        //Feriados, domingos
        if ($segundosNormal === null) {
            return $segundosTrabalhados;
        }

        $diferencaTotal = $segundosTrabalhados - $segundosNormal;
        if ($diferencaTotal >= 10 * 60) {
            return $diferencaTotal;
        }

        $possuiDiferencaMaiorQue5Minutos = self::possuiDiferencaMaior5Min($ponto);
        $diferencas = self::getDiferencasPonto($ponto);

        $somaHoraExtra = 0;
        $somaHoraFalta = 0;
        $somaHoraExtraMaior5Min = 0;
        foreach ($diferencas as $diferenca) {
            $horaExtra = 0;
            if ($diferenca['entrada'] > 0) {
                $horaExtra += $diferenca['entrada'];
                if ($diferenca['entrada'] > 5 * 60) {
                    $somaHoraExtraMaior5Min += $diferenca['entrada'];
                }
            } else {
                $somaHoraFalta += $diferenca['entrada'];
            }

            if ($diferenca['saida'] > 0) {
                $horaExtra += $diferenca['saida'];
                if ($diferenca['saida'] > 5 * 60) {
                    $somaHoraExtraMaior5Min += $diferenca['saida'];
                }
            } else {
                $somaHoraFalta += $diferenca['saida'];
            }

            $somaHoraExtra += $horaExtra;
        }

        if (!$possuiDiferencaMaiorQue5Minutos) {
            return null;
        }

        if (!self::$config['horaExtraConsiderarHoraFalta']) {
            $diferencaTotal = $somaHoraExtraMaior5Min;
        }

        return $diferencaTotal;
    }

    public static function setPossuiHoraExtraIregularmenteCompensada(bool $possui): void
    {
        self::$possuiHEIC = $possui;
    }

    public static function setEstenderHoraNoturna(bool $estenderHoraNoturna): void
    {
        self::$estenderHoraNoturna = $estenderHoraNoturna;
    }

    public static function getObservacoesTratadas(): array
    {
        return ['ferias', 'atestado', 'ausencia', 'feriado', 'falta', 'licenca r.', 'lic. n. r.', 'liberac.r.', 'compens.', 'dsr', 'feriado c.', 'feriado n.c.', 'atestado p.', 'aus. de c. ponto', 'folga', 'aux. doenca', 'falta just.', 'falta p.'];
    }

    public static function setRegistrosObservacoes(array $registros): void
    {
        self::$registrosObservacoes = $registros;
    }

    public static function addJornadaTrabalho(array $jornada, string $dataInicio, string $dataFim, int $descansoSemanal = 0): void
    {
        self::$jornadas[] = ['jornada' => $jornada, 'inicio' => self::dataBRToISO($dataInicio), 'fim' => self::dataBRToISO($dataFim), 'descansoSemanal' => $descansoSemanal];
    }

    public static function getSegundosConvertidosHoraNoturna(Ponto $ponto): ?int
    {
        $segundos = $ponto->getSegundosNoturno();
        if ($segundos === 0) {
            return null;
        }
        return self::converterSegundosNormalSegundosHoraNoturna($segundos);
    }

    public static function converterSegundosNormalSegundosHoraNoturna(int $segundosNormais): int
    {
        $segundosUmaHoraNoturna = 52.5 * 60;

        //Convertendo para segundos, pois o resultado da divisao gera um fator em hora
        return intval(($segundosNormais / $segundosUmaHoraNoturna) * 60 * 60);
    }

    public static function getSegundosNoturno(DateTime $entrada, DateTime $saida, bool $estenderHoraNoturna = false): int
    {

        $tmp = clone $entrada;
        //Quando a hora de entrada é entre meia noite e as 5 horas da manhã, aí atraso um dia
        if ($entrada->format('Hm') < 500) {
            $tmp->sub(new DateInterval('P1D'));
        }
        $dataEntradaHN = DateTime::createFromFormat('Y-m-d H:i', $tmp->format('Y-m-d') . ' 22:00');
        $tmp->add(new DateInterval('P1D'));
        $dataSaidaHN = DateTime::createFromFormat('Y-m-d H:i', $tmp->format('Y-m-d') . ' 05:00');

        //Tem que ter entrado antes das 22 horas
        if ($estenderHoraNoturna && $entrada <= $dataEntradaHN) {
            $dataSaidaHN = max($dataSaidaHN, $saida);
        }

        return self::datesOverlap($entrada, $saida, $dataEntradaHN, $dataSaidaHN);
    }

    private static function datesOverlap(\DateTimeInterface $startOne, \DateTimeInterface $endOne, \DateTimeInterface $startTwo, \DateTimeInterface $endTwo): int
    {

        if ($startOne <= $endTwo && $endOne >= $startTwo) {
            $fim = min($endOne, $endTwo);
            $inicio = max($startTwo, $startOne);
            return self::toSeconds($fim->diff($inicio));
        }

        return 0;
    }

    /**
     * A cada 50 minutos, retorna 10
     */
    public static function getIntDig(Ponto $ponto): int
    {
        $total = 0;
        for ($i = 1; $i <= 4; $i++) {
            if (isset($ponto['entrada' . $i]) && !empty($ponto['entrada' . $i])) {

                $entrada = $ponto['entrada' . $i];
                $saida = $ponto['saida' . $i];

                $diaSaida = $ponto['data'];
                if (Util::time_to_sec($saida) < Util::time_to_sec($entrada)) {
                    $diaSaida = date('Y-m-d', strtotime('+1 day', strtotime($diaSaida)));
                }

                $dEntrada = DateTime::createFromFormat('Y-m-d H:i', $ponto['data'] . ' ' . $entrada);
                $dSaida = DateTime::createFromFormat('Y-m-d H:i', $diaSaida . ' ' . $saida);

                $intervalo = $dEntrada->diff($dSaida);

                $segundos = self::toSeconds($intervalo);

                $qtd = floor($segundos / (50 * 60));

                $total += $qtd;
            }
        }

        return $total * 10 * 60;
    }

    public static function toSeconds(DateInterval $interval): int
    {
        return ($interval->y * 365 * 24 * 60 * 60) +
            ($interval->m * 30 * 24 * 60 * 60) +
            ($interval->d * 24 * 60 * 60) +
            ($interval->h * 60 * 60) +
            ($interval->i * 60) +
            $interval->s;
    }

}

