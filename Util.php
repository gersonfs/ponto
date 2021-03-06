<?php

class Util {

    /**
     * Hora Extra Iregularmente Compensada
     * @var boolean
     */
    private static $possuiHEIC = true;
    
    private static $estenderHoraNoturna = false;
    
    public static $informarDSR = false;

    private static $registrosObservacoes = [];
    
    private static $jornadas;
    private static $config;
    
    public static function getMeses() {
        return array(
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
        );
    }

    public static function getAnos() {
        $range = range(date('Y'), date('Y') + 11);
        return array_combine($range, $range);
    }

    public static function formataDia($dia) {
        return ($dia < 10) ? str_pad($dia, 2, 0, STR_PAD_LEFT) : $dia;
    }

    public static function formataMes($mes) {
        return sprintf('%02d', $mes);
    }

    public static function formatarValor($valor, $decimais = 2, $separadorDecimais = ',', $separadorMilhar = '.') {
        if (!strlen($valor)) {
            return $valor;
        }

        return 'R$ ' . self::formatarNumero($valor);
    }

    public static function formatarNumero($valor, $decimais = 2, $separadorDecimais = ',', $separadorMilhar = '.') {
        return number_format($valor, $decimais, $separadorDecimais, $separadorMilhar);
    }

    public static function formatarChaveNfe($chave) {
        $mask = "#### #### #### #### #### #### #### #### #### #### ####";
        return self::mask($chave, $mask);
    }

    public static function getMes($referencia) {
        return self::getMeses()[$referencia];
    }

    public static function getDiaDaSemanaExtenso($data) {
        if (!strlen($data)) {
            return '';
        }
        $dias = ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado"];
        return $dias[date('w', strtotime($data))];
    }

    public static function getDiaDaSemanaCurto($data) {
        if (!strlen($data)) {
            return '';
        }
        
        $dias = ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"];
        
        if(preg_match("/^\d+$/", $data)) {
            return $dias[$data];
        }
        
        return $dias[date('w', strtotime($data))];
    }

    public static function isFeriado($data) {
        return in_array($data, self::getFeriados());
    }

    public static function isAusencia($data) {
        return in_array($data, self::getAusencias());
    }

    public static function isAtestado($data) {
        return in_array($data, self::getAtestados());
    }

    public static function isFerias($data) {
        return in_array($data, self::getFerias());
    }

    public static function getFeriados() {
        return self::getRegistrosData(['feriado', 'licenca r.', 'lic. n. r.', 'liberac.r.', 'feriado n.c.']);
    }

    public static function getAusencias() {
        return self::getRegistrosData(['ausencia', 'falta', 'compens.', 'falta just.', 'falta p.']);
    }

    public static function getAtestados() {
        return self::getRegistrosData('atestado');
    }

    public static function getFerias() {
        return self::getRegistrosData('ferias');
    }

    public static function getRegistrosData($tipos) {
        if(is_string($tipos)) {
            $tipos = [$tipos];
        }

        $strTipos = implode('-', $tipos);

        static $registros = [];
        if(isset($registros[$strTipos])) {
            return $registros[$strTipos];
        }
        
        $linhas = self::getRegistrosObservacoes();
        $registros[$strTipos] = [];
        foreach($tipos as $tipo) {
            $tipo = strtolower($tipo);
            foreach ($linhas as $linha) {
                if (strtolower(trim($linha[2])) == $tipo) {
                    $registros[$strTipos][] = self::dataBRToISO(trim($linha[1]));
                }
            }
        }
        return $registros[$strTipos];
    }

    private static function getRegistrosObservacoes() {
        return self::$registrosObservacoes;
    }

    public static function time_to_sec($time) {
        
        if($time === null) {
            return null;
        }
        
        $p = explode(':', $time);
        $seconds = 0;
        $hours = $p[0];
        $minutes = $p[1];
        if(count($p) === 3) {
            $seconds = $p[2];
        }
        
        return $hours * 3600 + $minutes * 60 + $seconds;
    }

    public static function sec_to_time($seconds, $mostrarSegundos = false) {
        if ($seconds === null) {
            return;
        }

        $hours = floor($seconds / 3600);
        $minutes = floor($seconds % 3600 / 60);
        $seconds = $seconds % 60;

        if ($mostrarSegundos) {
            return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
        }
        return sprintf("%02d:%02d", $hours, $minutes);
    }

    public static function dataBRToISO($data) {
        if (!strlen($data)) {
            return '';
        }
        
        $p = explode(' ', $data);
        $data = $p[0];
        if(self::isDataISO($data)) {
            if(isset($p[1])) {
                return $data . ' ' . $p[1];
            }
            return $data;
        }
        list($dia, $mes, $ano) = explode('/', $data);
        $retorno = sprintf('%d-%02d-%02d', $ano, $mes, $dia);
        if (isset($p[1])) {
            $retorno .= ' ' . $p[1];
        }
        return $retorno;
    }

    public static function isDataBR($data) {
        return preg_match('/^[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{2,4}$/', $data);
    }

    public static function isDataISO($data) {
        return preg_match('/^[0-9]{2,4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $data);
    }

    public static function formatarDataBR($data) {
        return self::dataISOToBR($data);
    }

    public static function dataISOToBR($data) {
        if (!strlen($data)) {
            return '';
        }

        $p = explode(' ', $data);
        $data = $p[0];
        list($ano, $mes, $dia) = explode('-', $data);
        $retorno = sprintf('%02d/%02d/%d', $dia, $mes, $ano);
        if (isset($p[1])) {
            $retorno .= ' ' . $p[1];
        }
        return $retorno;
    }

    public static function dataCakeToIso($data) {
        $str = $data['year'] . '-' . $data['month'] . '-' . $data['day'];
        if (isset($data['hour'])) {
            $str .= ' ' . $data['hour'] . ':' . $data['min'];
        }
        return $str;
    }

    public static function anoMesEstaEntrePeriodo($anoMes, $anoMesInicio, $anoMesFim) {
        $anoMesVerificar = self::converterAnoMesParaInt($anoMes);
        $anoMesInicio = self::converterAnoMesParaInt($anoMesInicio);
        $anoMesFim = self::converterAnoMesParaInt($anoMesFim);

        return $anoMesVerificar >= $anoMesInicio && $anoMesVerificar <= $anoMesFim;
    }

    public static function converterAnoMesParaInt($anoMes) {
        list($ano, $mes) = explode('-', $anoMes);
        return (int) sprintf("%d%02d", $ano, $mes);
    }

    public static function getAnoMesFromData($data) {
        list($ano, $mes, $dia) = explode('-', $data);
        return sprintf("%d-%02d", $ano, $mes);
    }

    public static function getIndiceAnoMesNoPeriodo($anoMes, $anoMesInicio, $anoMesFim) {
        $inicio = self::converterAnoMesParaInt($anoMesInicio);
        $fim = self::converterAnoMesParaInt($anoMesFim);
        $atual = self::converterAnoMesParaInt($anoMes);

        if ($atual < $inicio || $atual > $fim) {
            return 0;
        }

        list($ano, $mes) = explode('-', $anoMesInicio);
        $mesesInicio = ( $ano * 12 ) + $mes;

        list($anoAtual, $mesAtual) = explode('-', $anoMes);
        $mesesAtual = ( $anoAtual * 12 ) + $mesAtual;

        return ( $mesesAtual - $mesesInicio ) + 1;
    }

    public static function diferencaDiasDatas($inicio, $fim) {
        $dStart = new DateTime($inicio);
        $dEnd = new DateTime($fim);
        $dDiff = $dStart->diff($dEnd);
        return $dDiff->days;
    }

    public static function diferencaMesesDatas($anoMesInicio, $anoMesFim) {
        if (strlen($anoMesInicio) != 6) {
            throw new Exception('Mes e ano início ter 6 dígitos!');
        }

        if (strlen($anoMesFim) != 6) {
            throw new Exception('Mes e ano fim ter 6 dígitos!');
        }

        $intAnoMesInicio = (int) $anoMesInicio;
        $intAnoMesFim = (int) $anoMesFim;

        if ($intAnoMesInicio == $intAnoMesFim) {
            return 0;
        }

        $negativar = false;
        if ($intAnoMesInicio > $intAnoMesFim) {
            $negativar = true;
            $f = $anoMesFim;
            $anoMesFim = $anoMesInicio;
            $anoMesInicio = $f;
            $intAnoMesInicio = (int) $anoMesInicio;
            $intAnoMesFim = (int) $anoMesFim;
        }

        $anoInicio = (int) substr($anoMesInicio, 0, 4);
        $mesInicio = (int) substr($anoMesInicio, 4, 2);

        $anoFim = (int) substr($anoMesFim, 0, 4);
        $mesFim = (int) substr($anoMesFim, 4, 2);

        $meses = 0;
        while ($intAnoMesInicio < $intAnoMesFim) {
            $meses++;
            $intAnoMesInicio = (int) date('Ym', mktime(0, 1, 1, $mesInicio + $meses, 1, $anoInicio));
        }

        if ($negativar) {
            $meses *= -1;
        }

        return $meses;
    }

    public static function getQuantidadeDiasEmComun($dataInicio1, $dataInicio2, $dataFim1, $dataFim2) {
        $inicio = self::getMaiorData($dataInicio1, $dataInicio2);
        $fim = self::getMenorData($dataFim1, $dataFim2);

        $t1 = strtotime($inicio);
        $t2 = strtotime($fim);

        if ($t1 > $t2) {
            return 0;
        }

        return self::diferencaDiasDatas($inicio, $fim);
    }

    public static function getMenorData($data1, $data2) {
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

    public static function getMaiorData($data1, $data2) {
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

    public static function tabulacaoStringImpressoras($stringEsquerda, $stringDireita, $separador, $numeroColunas, $breakLine = "\n") {

        $comprimentoTotal = strlen($stringEsquerda) + strlen($stringDireita);
        $numeroLinhas = ceil($comprimentoTotal / $numeroColunas);
        $str = "";
        for ($x = 0; $x < $numeroLinhas; $x++) {
            if ($x == $numeroLinhas - 1) {
                $sobra = $numeroColunas - strlen($stringEsquerda);
                $str .= $stringEsquerda . sprintf("%'" . $separador . $sobra . "s", $stringDireita);
            } else {
                $linha = substr($stringEsquerda, $x, $numeroColunas);
                $sobra = $numeroColunas - strlen($linha);
                $str .= $linha . str_repeat($separador, $sobra) . $breakLine;
                $stringEsquerda = substr($stringEsquerda, ($x + 1) * $numeroColunas);
            }
        }
        return $str;
    }

    public static function centralizarStringImpressora($string, $numeroColunas) {
        if (strlen($string) > $numeroColunas) {
            return $string;
        }

        $sobra = $numeroColunas - strlen($string);
        $espacos = floor($sobra / 2);

        return str_repeat(' ', $espacos) . $string;
    }

    public static function getDiasDaSemanaCurtos() {
        return array(
            0 => 'DOM',
            1 => 'SEG',
            2 => 'TER',
            3 => 'QUA',
            4 => 'QUI',
            5 => 'SEX',
            6 => 'SAB'
        );
    }

    public static function formataCPFCNPJ($cpfCnpj) {
        $cpfCnpj = preg_replace('/[^0-9]/', '', $cpfCnpj);

        if (empty($cpfCnpj)) {
            return '';
        }

        $mask = "##.###.###/####-##";
        if (strlen($cpfCnpj) == 11) {
            $mask = "###.###.###-##";
        }

        return self::mask($cpfCnpj, $mask);
    }

    public static function formataFone($fone) {
        $fone = preg_replace('/[^0-9]/', '', $fone);

        switch (strlen($fone)) {
            case 8:
                $mask = "####-####";
                break;

            //telefone SP sem DDD, eles tem um dígito a mais
            case 9:
                $mask = "#####-####";
                break;

            case 10:
                $mask = "(##) ####-####";
                break;

            //telefone SP com DDD
            case 11:
                $mask = "(##) #####-####";
                break;

            default:
                return $fone;
                break;
        }

        return self::mask($fone, $mask);
    }

    public static function formataCEP($cep, $mascara = '##.###-###') {
        $cep = preg_replace('/[^0-9]/', '', $cep);
        return self::mask($cep, $mascara);
    }

    public static function mask($val, $mask) {
        $maskared = '';
        $k = 0;
        for ($i = 0; $i <= strlen($mask) - 1; $i++) {
            if ($mask[$i] == '#') {
                if (isset($val[$k])) {
                    $maskared .= $val[$k++];
                }
            } else {
                if (isset($mask[$i])) {
                    $maskared .= $mask[$i];
                }
            }
        }
        return $maskared;
    }

    public static function getDataFormatadaContrato($data) {
        list($ano, $mes, $dia) = explode('-', date('Y-m-d', strtotime($data)));
        $meses = self::getMeses();
        return $dia . ' de ' . $meses[(int) $mes] . ' de ' . $ano;
    }

    public static function calcularJurosEMora($valor, $vencimento, $pagamento, $jurosMensais = null, $jurosMora = null, $diasTolerancia = null) {

        $tempo1 = strtotime($vencimento);
        $tempo2 = strtotime($pagamento);

        //se nao estiver atrazado não calcula juros
        if ($tempo2 - $tempo1 <= 0) {
            return 0.00;
        }

        $dias = self::diferencaDiasDatas($vencimento, $pagamento);

        //tolerancia de 10 dias
        if ($dias <= $diasTolerancia) {
            return 0.00;
        }

        $juros = 0.00;
        //juros sobre juros mensais
        while ($dias >= 30) {
            $juros = $juros + ($valor * ( $jurosMensais / 100 ));
            $dias -= 30;
        }

        //juros sobre juros diarios
        while ($dias > 0) {
            $juros = $juros + ($valor * ($jurosMensais / 100 / 30));
            $dias--;
        }

        $juros = $juros + ($valor * ($jurosMora / 100));

        return round($juros, 2);
    }

    public static function getDataEssaSegundaFeira() {
        return date('Y-m-d', strtotime('monday this week'));
    }

    public static function getDataEsseDomingo() {
        return date('Y-m-d', strtotime('sunday this week'));
    }

    public static function getDataPrimeiroDiaMes() {
        return date('Y-m-d', mktime(0, 0, 0, date('m'), 1, date('Y')));
    }

    public static function getDataUltimoDiaMes() {
        return date('Y') . '-' . date('m') . '-' . date('t');
    }

    public static function getDataPrimeiroDiaProximoMes() {
        return date('Y-m-d', mktime(0, 0, 0, date('m') + 1, 1, date('Y')));
    }

    public static function getDataUltimoDiaProximoMes() {
        $tInicio = strtotime(self::getDataPrimeiroDiaProximoMes());
        return date('Y', $tInicio) . '-' . date('m', $tInicio) . '-' . date('t', $tInicio);
    }

    public static function getSqlLojaId($campo, $lojas) {
        if (is_array($lojas)) {
            return $campo . ' IN (' . implode(', ', $lojas) . ')';
        }

        return $campo . ' = ' . $lojas;
    }

    public static function dateDiffDays($data1, $data2) {
        $dStart = new DateTime($data1);
        $dEnd = new DateTime($data2);
        $dDiff = $dStart->diff($dEnd);
        if ($dDiff->days == 0) {
            return $dDiff->days;
        }
        if ($dDiff->format('%R') == '+') {
            return '<span style="color:red">' . $dDiff->format('%R') . $dDiff->days . '</span>';
        }
        return $dDiff->format('%R') . $dDiff->days;
    }

    public static function dateDiff($data1, $data2) {
        $dStart = new DateTime($data1);
        $dEnd = new DateTime($data2);
        $dDiff = $dStart->diff($dEnd);
        return $dDiff->days;
    }

    public static function getColunaExcel($numero) {
        $numeric = $numero % 26;
        $letter = chr(65 + $numeric);
        $num2 = intval($numero / 26);

        if ($num2 > 0) {
            return self::getColunaExcel($num2 - 1) . $letter;
        } else {
            return $letter;
        }
    }

    public static function isAmbienteTesteCielo() {
        return trim(Parametro::v('CIELO_MODO_TESTE')) == '1';
    }

    public static function isWindows() {
        return !self::isLinux();
    }

    public static function isLinux() {
        return strtolower(PHP_OS) == 'linux';
    }

    public static function getSessionPath() {
        $path = session_save_path();
        if (!empty($path)) {
            return $path;
        }

        return sys_get_temp_dir();
    }

    public static function clearAllSessions() {
        $path = self::getSessionPath();
        $files = glob($path . DS . 'sess_*');
        foreach ($files as $file) {
            @unlink($file);
        }
    }

    public static function isEan13($ean) {
        if (!preg_match('/^[0-9]+/', $ean)) {
            return false;
        }

        $digito = $ean[strlen($ean) - 1];
        $valorSemDigito = substr($ean, 0, -1);
        return self::getEan13CheckSum($valorSemDigito) == $digito;
    }

    public static function getEan13CheckSum($ean) {
        $ean = (string) $ean;
        $even = true;
        $esum = 0;
        $osum = 0;
        for ($i = strlen($ean) - 1; $i >= 0; $i--) {
            if ($even) {
                $esum += $ean[$i];
            } else {
                $osum += $ean[$i];
            }
            $even = !$even;
        }
        return (10 - ((3 * $esum + $osum) % 10)) % 10;
    }

    public static function getDiasDiferenca($dataInicial, $dataFinal) {
        $dataInicial = new DateTime($dataInicial);
        $dataFinal = new DateTime($dataFinal);
        $interval = $dataInicial->diff($dataFinal);
        $diasDiferenca = (int) $interval->format("%r%a");
        return $diasDiferenca;
    }

    public static function possuiLojaVirtual() {
        return is_dir(dirname(dirname(APP)) . DS . 'sis_loja');
    }

    public static function formatarPercentual($valor, $casasDecimais = 2) {
        if (strlen($valor) === 0) {
            return '';
        }

        return self::formatarNumero($valor) . '%';
    }

    public static function formataDataArquivoRetorno($data) {
        $dia = substr($data, 0, 2);
        $mes = substr($data, 2, 2);
        $ano = substr($data, 4, 2);
        return $dia . '/' . $mes . '/' . substr(date('Y'), 0, 2) . $ano;
    }

    public static function isFilial($cnpj, $cnpjComparar) {

        if ($cnpj == $cnpjComparar) {
            return false;
        }

        $a = substr($cnpj, 0, 8);
        $b = substr($cnpjComparar, 0, 8);
        return $a == $b;
    }

    public static function isDiaUtil($data, $feriados) {
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

    public static function getArrayFeriados() {
        return explode(" ", Parametro::valor('LISTA_DE_FERIADOS'));
    }

    public static function getHorasTrabalhadas($ponto) {
        $segundos = self::getSegundosTrabalhados($ponto);
        if ($segundos <= 0) {
            return;
        }

        return self::sec_to_time($segundos, false);
    }
    
    public static function getSegundosTrabalhadosHoraNoturna($ponto) {
        $segundosDiurno = self::getSegundosDiurno($ponto);
        $segundosNoturnos = self::getSegundosNoturnoPonto($ponto);
        return $segundosDiurno + self::converterSegundosNormalSegundosHoraNoturna($segundosNoturnos);
    }
    
    public static function getSegundosDiurno($ponto) {
        $segundosTrabalhados = self::getSegundosTrabalhados($ponto);
        $segundosNoturnos = self::getSegundosNoturnoPonto($ponto);
        return $segundosTrabalhados - $segundosNoturnos;
    }

    public static function getSegundosTrabalhados(Ponto $ponto) {
        return $ponto->getSegundosTrabalhados();
    }
    
    public static function possuiDiferencaMaior5Min($ponto) {
        $diferencas = self::getDiferencasPonto($ponto);

        $resultado = array_filter($diferencas, function ($el) 
        {
            $cincoMinutos = 5 * 60;
            if($el['entrada'] > $cincoMinutos || $el['saida'] > $cincoMinutos) {
                return true;
            }
            return false;
        });

        return !empty($resultado);
    }

    public static function getDiferencasPonto($ponto) {
        $diferencas = [];
        
        for($i = 1; $i <= 4; $i++) {
            
            if(!isset($ponto['entrada' . $i]) || empty($ponto['entrada' . $i])) {
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

    private static function getJornadaPelaHoraEntrada($horaEntrada, $data) {
        $jornada = self::getJornadaData($data);
        if(empty($jornada)) {
            throw new Exception('Não há jornada para a data ' . $data);
        }

        $k = 0;
        $jornadaHora = null;
        $menorDiferenca = null;
        foreach($jornada as $periodo) {
            $horaEntradaJornada = $periodo[0];
            
            if($k == 0) {
                $menorDiferenca = abs(Util::time_to_sec($horaEntrada) - Util::time_to_sec($horaEntradaJornada));
                $jornadaHora = $periodo;
            }
            
            $diferenca = abs(Util::time_to_sec($horaEntrada) - Util::time_to_sec($horaEntradaJornada));
            
            if($diferenca < $menorDiferenca) {
                $menorDiferenca = $diferenca;
                $jornadaHora = $periodo;
            }
            $k++;
        }

        if(empty($jornadaHora)) {
            throw new Exception('Não foi possível encontrar a joranda para a hora de entrada ' . $horaEntrada . ' na data ' . $data);
        }

        return $jornadaHora;
    }
    
    private static function getJornada($data) {
        $timeData = strtotime($data);
        foreach(self::$jornadas as $jornada) {
            $timeInicioJornada = strtotime($jornada['inicio']);
            $timeFimJornada = strtotime($jornada['fim']);
            $pertenceJornada = $timeData >= $timeInicioJornada && $timeData <= $timeFimJornada;
            if(!$pertenceJornada) {
                continue;
            }
            
            return $jornada;
        }
        throw new Exception('Não foi encontrada jornada para a data ' . $data);
    }
    
    private static function getJornadaData($data) {
        $timeData = strtotime($data);
        $diaSemana = date('w', $timeData);
        $jornada = self::getJornada($data);

        if(isset($jornada['jornada'][$diaSemana])) {
            return $jornada['jornada'][$diaSemana];
        }
        
        if(isset($jornada['jornada'][$data])) {
            return $jornada['jornada'][$data];
        }
        
        return null;
    }

    public static function getSegundosNormais($ponto, $options = []) {
        $segundos = self::getHorasNormais($ponto, $options);
        if ($segundos === null) {
            return;
        }
        return $segundos * 60 * 60;
    }

    /**
     * Retorna quantidade de horas normais
     *
     * @param array $ponto
     * @param array $options
     * @return float Horas
     */
    public static function getHorasNormais($ponto, $options = []) {
        
        $options += [
            'ignorarFeriados' => false
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
        
        if(empty($jornada)) {
            return null;
        }
        
        $soma = 0;
        foreach($jornada as $j) {
            
            $entrada = new DateTime($ponto['data'] . ' ' . $j[0]);
            $saida = new DateTime($ponto['data'] . ' ' . $j[1]);
            
            //Entrou num dia e saiu no outro
            if($saida < $entrada) {
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


        return $soma / 60 / 60;
    }

    public static function getHorasNormalSemana($ponto, $pontos) {
        $horas = 0;
        foreach ($pontos as $ponto2) {
            if ($ponto['semana'] == $ponto2['semana']) {
                $horas += self::getHorasNormais($ponto2);
            }
        }
        return $horas;
    }

    public static function getSegundosNormalSemana($ponto, $pontos) {
        $horas = self::getHorasNormalSemana($ponto, $pontos);
        if($horas === null) {
            return;
        }
        return $horas * 60 * 60;
    }

    public static function isDescansoSemanal($ponto) {
        if(self::$informarDSR) {
            if($ponto['obs'] == 'dsr') {
                return true;
            }
            return false;
        }
        
        return date('w', strtotime($ponto['data'])) == self::getDiaDescansoSemanal($ponto['data']);
    }
    
    public static function getDiaDescansoSemanal($data) {
        $jornada = self::getJornada($data);
        
        if(isset($jornada['descansoSemanal'])) {
            return $jornada['descansoSemanal'];
        }
        
        return 0;
    }

    public static function getSegundosTrabalhadosSemana($ponto, $pontos) {
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
     * @param type $ponto
     * @return type
     */
    public static function getSegundosIrComp($ponto) {
        if(!self::$possuiHEIC) {
            return 0;
        }

        $segundosNormais = self::getSegundosNormais($ponto, ['ignorarFeriados' => true]);
        
        if($segundosNormais === null) {
            return;
        }
            
        if($ponto->isSabado()) {
            return 0;
        }
        
        $horaExtra = self::getHorasExtras($ponto);
        
        if(empty($horaExtra)) {
            return 0;
        }
        
        if ($horaExtra < (48 * 60)) {
            return $horaExtra;
        }

        return 48 * 60;
    }

    public static function getHorasTrabalhadasIrComp($ponto) {
        return Util::sec_to_time(self::getSegundosIrComp($ponto));
    }

    public static function getHorasExtrasMenosIrComp($ponto, $pontos) {

        $horaExtra = self::getHorasExtras($ponto, $pontos);
        $diferencaCobrada = $horaExtra - self::getSegundosIrComp($ponto);
        
        if ($diferencaCobrada <= 0) {
            return;
        }
        $sNormalSemana = self::getSegundosNormalSemana($ponto, $pontos);
        $sTrabalhadaSemana = self::getSegundosTrabalhadosSemana($ponto, $pontos);

        if ($sTrabalhadaSemana <= $sNormalSemana) {
            return;
        }

        return $diferencaCobrada;
    }
    
    /**
     * Quando entrou mais de 5 minutos antes ou saiu mais de 5 minutos depois, conta como hora extra
     * Se no dia deu 10 minutos a mais na soma, vale como hora extra
     *
     * @param [type] $ponto
     * @return void
     */
    public static function getHorasExtras($ponto) {
        $segundosTrabalhados = self::getSegundosTrabalhadosHoraNoturna($ponto);
        $segundosNormal = self::getSegundosNormais($ponto);

        if($segundosTrabalhados === null) {
            return;
        }
        
        if (self::$config['horaExtraSimples'] && $segundosTrabalhados < $segundosNormal) {
            return;
        }

        $jornada = self::getJornadaData($ponto['data']);
        if(empty($jornada)) {
            return $segundosTrabalhados;
        }

        //Feriados, domingos
        if($segundosNormal === null) {
            return $segundosTrabalhados;
        }

        $diferencaTotal = $segundosTrabalhados - $segundosNormal;
        if($diferencaTotal >= 10 * 60) {
            return $diferencaTotal;
        }

        $possuiDiferencaMaiorQue5Minutos = self::possuiDiferencaMaior5Min($ponto);
        $diferencas = self::getDiferencasPonto($ponto);

        $somaHoraExtra = 0;
        $somaHoraFalta = 0;
        $somaHoraExtraMaior5Min = 0;
        foreach($diferencas as $diferenca) {
            $horaExtra = 0;
            if($diferenca['entrada'] > 0) {
                $horaExtra += $diferenca['entrada'];
                if($diferenca['entrada'] > 5*60) {
                    $somaHoraExtraMaior5Min += $diferenca['entrada'];
                }
            }else{
                $somaHoraFalta += $diferenca['entrada'];
            }

            if($diferenca['saida'] > 0) {
                $horaExtra += $diferenca['saida'];
                if($diferenca['saida'] > 5*60) {
                    $somaHoraExtraMaior5Min += $diferenca['saida'];
                }
            }else {
                $somaHoraFalta += $diferenca['saida'];
            }

            $somaHoraExtra += $horaExtra;
        }

        if(!$possuiDiferencaMaiorQue5Minutos) {
            return null;
        }

        if(!self::$config['horaExtraConsiderarHoraFalta']) {
            $diferencaTotal = $somaHoraExtraMaior5Min;
        }
        
        return $diferencaTotal;
    }

    public static function setPossuiHoraExtraIregularmenteCompensada($possui) {
        self::$possuiHEIC = $possui;
    }
    
    public static function setEstenderHoraNoturna($estenderHoraNoturna) {
        self::$estenderHoraNoturna = $estenderHoraNoturna;
    }

    public static function getObservacoesTratadas() {
        return ['ferias', 'atestado', 'ausencia', 'feriado', 'falta', 'licenca r.', 'lic. n. r.', 'liberac.r.', 'compens.', 'dsr', 'feriado c.', 'feriado n.c.', 'atestado p.', 'aus. de c. ponto', 'folga', 'aux. doenca', 'falta just.', 'falta p.'];
    }

    public static function setRegistrosObservacoes($registros) {
        self::$registrosObservacoes = $registros;
    }
    
    public static function addJornadaTrabalho(array $jornada, $dataInicio, $dataFim, $descansoSemanal = 0) {
        self::$jornadas[] = ['jornada' => $jornada, 'inicio' => self::dataBRToISO($dataInicio), 'fim' => self::dataBRToISO($dataFim), 'descansoSemanal' => $descansoSemanal];
    }
    
    public static function getSegundosConvertidosHoraNoturna($ponto) {
        $segundos = self::getSegundosNoturnoPonto($ponto);
        if($segundos === 0) {
            return null;
        }
        return self::converterSegundosNormalSegundosHoraNoturna($segundos);
    }
    
    public static function converterSegundosNormalSegundosHoraNoturna($segundosNormais) {
        $segundosUmaHoraNoturna = 52.5 * 60;
        
        //Convertendo para segundos, pois o resultado da divisao gera um fator em hora
        return ($segundosNormais / $segundosUmaHoraNoturna) * 60 * 60;
    }
    
    public static function getSegundosNoturnoPonto($ponto) {
        return $ponto->getSegundosNoturno(self::$estenderHoraNoturna);
    }

    public static function getSegundosNoturno(DateTime $entrada, DateTime $saida, bool $estenderHoraNoturna = false) {

        $tmp = clone $entrada;
        //Quando a hora de entrada é entre meia noite e as 5 horas da manhã, aí atraso um dia
        if($entrada->format('Hm') < 500) {
            $tmp->sub(new DateInterval('P1D'));
        }
        $dataEntradaHN = DateTime::createFromFormat('Y-m-d H:i', $tmp->format('Y-m-d') . ' 22:00');
        $tmp->add(new DateInterval('P1D'));
        $dataSaidaHN = DateTime::createFromFormat('Y-m-d H:i', $tmp->format('Y-m-d') . ' 05:00');

        //Tem que ter entrado antes das 22 horas
        if($estenderHoraNoturna && $entrada <= $dataEntradaHN) {
            $dataSaidaHN = max($dataSaidaHN, $saida);
        }
        
        return self::datesOverlap($entrada, $saida, $dataEntradaHN, $dataSaidaHN);
    }

    private static function datesOverlap($startOne, $endOne, $startTwo, $endTwo) {

        if($startOne <= $endTwo && $endOne >= $startTwo) {
            $fim = min($endOne, $endTwo);
            $inicio = max($startTwo, $startOne);
            return self::toSeconds($fim->diff($inicio));
        }
     
        return 0;
     }
    
    public static function getJornadas() {
        return self::$jornadas;
    }
    
    /**
     * A cada 50 minutos, retorna 10
     * @param array $ponto
     * @return int Segundos de Int DIG
     */
    public static function getIntDig($ponto) {
        $total = 0;
        for($i = 1; $i <= 4; $i++) {
            if(isset($ponto['entrada' .$i]) && !empty($ponto['entrada' .$i])) {
                
                $entrada = $ponto['entrada' . $i];
                $saida = $ponto['saida' . $i];
                
                $diaSaida = $ponto['data'];
                if(Util::time_to_sec($saida) < Util::time_to_sec($entrada)) {
                    $diaSaida = date('Y-m-d', strtotime('+1 day', strtotime($diaSaida)));
                }
                
                $dEntrada = DateTime::createFromFormat('Y-m-d H:i', $ponto['data'] . ' ' . $entrada);
                $dSaida = DateTime::createFromFormat('Y-m-d H:i', $diaSaida . ' ' . $saida);
                
                $intervalo = $dEntrada->diff($dSaida);
                
                $segundos = self::toSeconds($intervalo);
                
                $qtd = floor($segundos / (50*60));
                
                $total += $qtd;
            }
        }
        
        return $total * 10 * 60;
    }
    
    public static function toSeconds(DateInterval $interval):int {
        return ($interval->y * 365 * 24 * 60 * 60) +
               ($interval->m * 30 * 24 * 60 * 60) +
               ($interval->d * 24 * 60 * 60) +
               ($interval->h * 60 * 60) +
               ($interval->i * 60) +
               $interval->s; 
    }

    public static function setConfig($config) {
        foreach($config['jornadas'] as $jornada) {
            $descanso = isset($jornada['descansoSemanal']) ? $jornada['descansoSemanal'] : 0;
            self::addJornadaTrabalho($jornada['horarios'], $jornada['inicio'], $jornada['fim'], $descanso);
        }
        self::setPossuiHoraExtraIregularmenteCompensada($config['possuiHoraExtraIC']);
        self::setEstenderHoraNoturna($config['estenderHoraNoturna']);
        if(isset($config['informarDSR']) && $config['informarDSR']) {
            self::$informarDSR = true;
        }
        self::$config = $config;
    }

}

if(!function_exists('debug')) {
    function debug($var) {
        echo '<pre>' . print_r($var, true) . '</pre>';
    }
}