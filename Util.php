<?php

class Util {

    /**
     * Hora Extra Iregularmente Compensada
     * @var boolean
     */
    private static $possuiHEIC = true;

    private static $trabalhaSabado = true;

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
        return self::getRegistrosData('feriado');
    }

    public static function getAusencias() {
        return self::getRegistrosData('ausencia');
    }

    public static function getAtestados() {
        return self::getRegistrosData('atestado');
    }

    public static function getFerias() {
        return self::getRegistrosData('ferias');
    }

    public static function getRegistrosData($tipo) {
        static $registros = [];
        if(isset($registros[$tipo])) {
            return $registros[$tipo];
        }
        
        $linhas = self::getRegistros();
        $registros[$tipo] = [];
        $tipo = strtolower($tipo);
        foreach ($linhas as $linha) {
            $p = explode("\t", $linha);
            if (strtolower(trim($p[1])) == $tipo) {
                $registros[$tipo][] = self::dataBRToISO(trim($p[0]));
            }
        }
        return $registros[$tipo];
    }

    public static function getObsData($data) {
        $linhas = self::getRegistros();
        $data = Util::dataISOToBR($data);
        foreach ($linhas as $linha) {
            $p = explode("\t", $linha);
            if ($p[0] == $data) {
                return trim($p[1]);
            }
        }
        return '';
    }

    private static function getRegistros() {
        static $linhas = null;
        if($linhas != null) {
            return $linhas;
        }
        $linhas = file('feriados.csv', FILE_IGNORE_NEW_LINES);
        return $linhas;
    }

    public static function time_to_sec($time) {
        $hours = substr($time, 0, -6);
        $minutes = substr($time, -5, 2);
        $seconds = substr($time, -2);

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

    public static function getSegundosTrabalhados($ponto) {
        if (empty($ponto['entrada1'])) {
            return null;
        }
        
        $t1 = DateTime::createFromFormat("Y-m-d H:i", $ponto['data'] . ' ' . $ponto['entrada1']);
        $t2 = DateTime::createFromFormat("Y-m-d H:i", $ponto['data'] . ' ' . $ponto['saida1']);

        $segundos = $t2->getTimestamp() - $t1->getTimestamp();

        if (strlen($ponto['entrada2'])) {
            $t1 = DateTime::createFromFormat("Y-m-d H:i", $ponto['data'] . ' ' . $ponto['entrada2']);
            $t2 = DateTime::createFromFormat("Y-m-d H:i", $ponto['data'] . ' ' . $ponto['saida2']);

            $segundos += $t2->getTimestamp() - $t1->getTimestamp();
        }

        return $segundos;
    }

    public static function getSegundosNormais($ponto) {
        $segundos = self::getHorasNormais($ponto);
        if ($segundos === null) {
            return;
        }
        return $segundos * 60 * 60;
    }

    public static function getHorasNormais($ponto) {
        $diaDaSemana = date('w', strtotime($ponto['data']));

        //Domingo
        if ($diaDaSemana == 0) {
            return null;
        }

        if (self::isFeriado($ponto['data'])) {
            return null;
        }

        if (self::isFerias($ponto['data'])) {
            return null;
        }

        if (self::isAtestado($ponto['data'])) {
            return null;
        }

        //Sabado
        if ($diaDaSemana == 6) {
            if(self::$trabalhaSabado) {
                return 4;
            }
            return null;
        }

        if(!self::$trabalhaSabado) {
            return 8.8;
        }

        return 8;
    }

    public static function getHorasNormalSemana($ponto, $pontos) {
        $horas = 0;
        foreach ($pontos as $ponto2) {
            if ($ponto['semana'] == $ponto2['semana']) {
                $horas += (int) self::getHorasNormais($ponto2);
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

    public static function isDomingo($ponto) {
        return date('w', strtotime($ponto['data'])) == 0;
    }

    public static function isSabado($ponto) {
        return date('w', strtotime($ponto['data'])) == 6;
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

        $segundosNormais = self::getSegundosNormais($ponto);
        
        if($segundosNormais === null) {
            return;
        }
            
        $segundosTrabalhados = self::getSegundosTrabalhados($ponto);

        if ($segundosTrabalhados <= $segundosNormais) {
            return 0;
        }

        if(self::isSabado($ponto)) {
            return 0;
        }

        $diferenca = $segundosTrabalhados - $segundosNormais;

        if ($diferenca < (48 * 60)) {
            return $diferenca;
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
    
    public static function getHorasExtras($ponto) {
        $segundosTrabalhados = self::getSegundosTrabalhados($ponto);
        $segundosNormal = self::getSegundosNormais($ponto);

        if($segundosTrabalhados === null) {
            return;
        }
        
        if ($segundosTrabalhados < $segundosNormal) {
            return;
        }

        return $segundosTrabalhados - $segundosNormal;

    }

    public static function setPossuiHoraExtraIregularmenteCompensada($possui) {
        self::$possuiHEIC = $possui;
    }

    public static function setTrabalhaSabado($trabalhaSabado) { 
        self::$trabalhaSabado = $trabalhaSabado;
    }

}
