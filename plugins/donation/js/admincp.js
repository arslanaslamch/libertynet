$(document).on('click','.payment-details-donation',function(){
    var o = $(this);
    var c = $("#donationAdmincpModal");
    c.find('#sk').val(o.data('sk'));
    c.find('#pe').val(o.data('pe'));
    c.find('#pk').val(o.data('pk'));
    c.find('#uid').val(o.data('uid'));
    c.modal("show");
    console.log("we get here");
    return false;
});