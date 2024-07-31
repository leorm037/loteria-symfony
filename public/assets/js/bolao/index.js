$("button[data-delete]").each(function () {
    let uuid = $(this).data('delete');
    
    $(this).on('click', function(){
        $('#deleteUuid').val(uuid);        
        $('#deleteModal').modal('show');
    });
});