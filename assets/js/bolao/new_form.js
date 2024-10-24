$("select[name='bolao[loteria]']").on('change', function () {
    let uuid = $(this).val();
    let url = BASE_URL + '/concurso/' + uuid + '/ultimo';

    $.get(url).done(function (data, textStatus, jqXHR) {
        let concurso = parseInt(data.concurso);
        let concursoProximo = concurso++;
        $("input[name='bolao[concursoNumero]']").val(concursoProximo);
    }).fail(function (jqXHR, textStatus, errorThrown) {
        $("input[name='bolao[concursoNumero]']").val(null);
    });
});