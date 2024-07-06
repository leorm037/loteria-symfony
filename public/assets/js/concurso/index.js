$("select[name='loteria']").on('change', function () {
    let url = $(location).prop('href');
    redirectUrl(url, 'loteria', $(this).val(), true);
});