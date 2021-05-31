function store_type_change(d,url){
    let v = $(d).val();
    window.location.href = url+'?t='+v;
}

$(document).on('click','.btn-assign-store',function(){
    let obj = $(this);
    let name = obj.data('name');
    let sid = obj.data('sid');

    $("#sid-value").val(sid);
    $('#assignStoreModal').modal('show');
    $('.store-name').html(name);
    return false;
});

$(document).on('click','#assign-saf-store',function(){
    let obj = $(this);
    let sid = $("#sid-value").val();
    let uid = $("#mail-selected-members .wrapper-of-saf-assign span input").val();
    obj.html("Processing...");
    obj.prop('disabled',true);

    $.ajax({
        url : baseUrl + 'store/admincp/ajax/saf',
        method : 'POST',
        data : { sid : sid, uid : uid, action : 'assign'},
        success : function(data){
            location.reload();
        }
    });
   return false;
});