$("select[name='loteria']").on('change', function () {
    let url = BASE_URL + "/concurso/";
    
    if($(this).val() != "") {
        url = url + "loteria/" + $(this).val() + "/"
    }
    
    redirectUrl(url);
});