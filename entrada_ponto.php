
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Informar Ponto</title>
    <script src="jquery-3.2.1.min.js"></script>
    <script src="moment.js"></script>
    <script src="vanilla-masker.min.js"></script>
    <script src="entrada_ponto.js"></script>
    <style>
    table tbody input{
        width: 100px;
    }
    </style>
</head>
<body>
    <div>
        <label for="DataInicio">Data início</label><input type="date" id="DataInicio" />
        <label for="DataFim">Data fim</label><input type="date" id="DataFim" />
        <button id="Gerar">Gerar</button>
    </div>
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Entrada</th>
                <th>Saída</th>
                <th>Entrada</th>
                <th>Saída</th>
                <th>Entrada</th>
                <th>Saída</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</body>
</html>