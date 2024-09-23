$(document).ready(function() {
    $('#toggle-apostadores').on('click', function(e) {
        e.preventDefault();

        $('input[type="checkbox"]').each(function() {
            $(this).prop('checked', !$(this).prop('checked'));
        });
    });
});