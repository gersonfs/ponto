class EntradaPonto {
    constructor() {
        $('#Gerar').on('click', () => {
            try{
                this.gerarTabela();
            }catch(e) {
                alert(e);
            }
        });
    }

    gerarTabela() {
        let inicio = $('#DataInicio').val();
        let fim = $('#DataFim').val();

        let dInicio = moment(inicio);
        if(!dInicio.isValid()) {
            throw new Error('Data início inválida!');
        }
        let dFim = moment(fim);

        if(!dFim.isValid()) {
            throw new Error('Data fim inválida!');
        }

        if(dInicio > dFim) {
            throw new Error('Data início maior que data fim!');
        }

        let dataAtual = dInicio;
        let html = '';
        while(dataAtual <= dFim) {
            html += '<tr>';
            html += '<td>'+ dataAtual.format('DD/MM/Y') +'</td>';
            html += '<td><input type="text" /></td>'
            html += '<td><input type="text" /></td>'
            html += '<td><input type="text" /></td>'
            html += '<td><input type="text" /></td>'
            html += '<td><input type="text" /></td>'
            html += '<td><input type="text" /></td>'
            html += '</tr>';

            dataAtual.add(1, 'day');
        }

        $('table tbody').html(html);

        $('table tbody input').on('input', function (e) {
            let valor = e.target.value.replace(/\D/g, '');
            e.target.value = VMasker.toPattern(valor, "99:99");
            if(e.target.value.length === 5) {
                let el = $(this).parent().next('td').children();
                if(el.length > 0) {
                    el.focus();
                    return;
                }
                let proximaLinha = $(this).parent().parent().next('tr')
                $('input:first', proximaLinha).focus();
            }
        })
    }
}

$(function () {
    e = new EntradaPonto();
    $('#DataInicio').val('2017-01-01');
    $('#DataFim').val('2017-01-31');
    $('#Gerar').click();
});