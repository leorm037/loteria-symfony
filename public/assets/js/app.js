function urlParamUpdate(url, param, value, clearParams = false) {
    let newUrl = new URL(url);

    if (clearParams) {
        newUrl.searchParams.forEach((value, key, object) => object.delete(key));
    }

    if (!!value) {
        newUrl.searchParams.set(param, value);
    } else {
        newUrl.searchParams.delete(param);
    }

    return newUrl.href;
}

function redirectUrl(url, param = null, value = null, clearParams = false) {
    let newUrl = url;

    $.LoadingOverlay('show');

    if (!!param) {
        newUrl = urlParamUpdate(url, param, value, clearParams);
    }

    $(location).attr('href', newUrl);
    $(window).attr('location', newUrl);
    $(location).prop('href', newUrl);

    $.LoadingOverlay('hide');
}

function redirectParamsUrl(url, params, clearParams = false) {
    let newUrl = url;

    $.LoadingOverlay('show');

    if (params.length > 0) {
        params.forEach(function (param, index) {
            newUrl = urlParamUpdate(newUrl, param[0], param[1], (0 == index && clearParams) ? true : false);
        });
    } else {
        newUrl = urlParamUpdate(newUrl, null, null, true);
    }

    $(location).attr('href', newUrl);
    $(window).attr('location', newUrl);
    $(location).prop('href', newUrl);

    $.LoadingOverlay('hide');
}

$('i.bi-door-closed').parent().on('mouseover', function(){
    $(this).children('i').removeClass('bi bi-door-closed');
    $(this).children('i').addClass('bi bi-door-open');
}).on('mouseout', function(){
    $(this).children('i').removeClass('bi bi-door-open');
    $(this).children('i').addClass('bi bi-door-closed');
});

$('i.bi-door-open-fill').parent().on('mouseover', function(){
    $(this).children('i').removeClass('bi bi-door-open-fill');
    $(this).children('i').addClass('bi bi-door-closed-fill');
}).on('mouseout', function(){
    $(this).children('i').removeClass('bi bi-door-closed-fill');
    $(this).children('i').addClass('bi bi-door-open-fill');
});

$("button[data-delete]").each(function () {
    let uuid = $(this).data('delete');
    
    $(this).on('click', function(){
        $('#deleteUuid').val(uuid);        
        $('#deleteModal').modal('show');
        $('#modalDeleteButton').focus();
    });
});