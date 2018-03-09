<?php
include('Util.php');



if(!empty($_POST)) {
    $periodosInformados = explode(',', $_POST['periodos']);
    
    $dataInicio = Util::dataBRToISO($_POST['inicio']);
    $dataFim = Util::dataBRToISO($_POST['fim']);
}

?>
<html>
    <body>
        <form method="post">
            Inicio: 
            <input type="text" name="inicio" />
            <br />
            Fim: 
            <input type="text" name="fim" />
            <br />
            Períodos separados por , exemplo: 0,1
            0 é domingo, 1 é segunda, 6 é sábado
            <input type="text" name="periodos" />
            
            <input type="submit" name="" value="Gerar" />
        </form>
        
        
        
            <?php 
            if(isset($dataInicio)) {
                $dataAtual = strtotime($dataInicio);
                $tF = strtotime($dataFim);
                $i = 0;
                while($dataAtual <= $tF) {
                    $i++;
                    if(!in_array(date('w', $dataAtual), $periodosInformados)) {
                        $dataAtual = strtotime('+ ' . $i . ' days', strtotime($dataInicio));
                        continue;
                    }
                    echo date("d/m/Y", $dataAtual) . '<br />';

                    $dataAtual = strtotime('+ ' . $i . ' days', strtotime($dataInicio));
                }
            }
            ?>
    </body>
</html>