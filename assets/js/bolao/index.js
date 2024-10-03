$('#filter_button_limpar').on('click', function(){
    $('#filter_loteria').prop('selectedIndex',0);
    $('#filter_concurso').val(null);
    $('#filter_bolao').val(null);
    $('#filter_apurado').prop('selectedIndex',0);
});