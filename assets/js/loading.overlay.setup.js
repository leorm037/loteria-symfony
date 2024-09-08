$.LoadingOverlaySetup({
    image: "",
    fontawesome: "fa-solid fa-clover",
    fontawesomeOrder: 2,
    fontawesomeColor: "#006600",
    fontawesomeAnimation: true,
    fontawesomeAutoResize: true,
    fontawesomeResizeFactor: 1
});

$('a[data-loading="true"],button[data-loading="true"]').each(function () {
    $(this).click(function () {
        $.LoadingOverlay('show');

        if ($(this).data('loading')) {
            setTimeout(function () {
                $.LoadingOverlay("hide");
            }, 5000);
        }
    });
});

$('form[data-loading="true"]').each(function () {
    $(this).on('submit', function () {
        $.LoadingOverlay('show');
        
        if ($(this).data('loading')) {
            setTimeout(function () {
                $.LoadingOverlay("hide");
            }, 5000);
        }
    });
});