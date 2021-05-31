/*// Use Morris.Bar
Morris.Bar({
  element: 'graph',
  data: [
    {x: '2011 Q1', y: 3, z: 2, a: 3},
    {x: '2011 Q2', y: 2, z: null, a: 1},
    {x: '2011 Q3', y: 0, z: 2, a: 4},
    {x: '2011 Q4', y: 2, z: 4, a: 3}
  ],
  xkey: 'x',
  ykeys: ['y', 'z', 'a'],
  labels: ['Y', 'Z', 'A']
}).on('click', function(i, row){
  console.log(i, row);
});*/

function mail_automation_graph(){
    var d = jQuery.parseJSON($("#ma-data").val());
    //console.log(d);
    Morris.Bar({
        element: 'stats-automation',
        data: d,
        xkey: 'x',
        ykeys: ['y', 'z'],
        labels: ['Auto Mail', 'HighLights']
    }).on('click', function(i, row){
        console.log(i, row);
    });
}

$(function(){
    if($("#ma-data").length){
        mail_automation_graph();
    }
});

$(document).on('click','.submit-auto-admin',function(){
    var obj = $(this);
    obj.prop('disabled',true);
    //run ajax update affliates
    var st = $('#mastartDate').val();
    var et = $('#maendDate').val();
    var i = $(".indicator");
    i.fadeIn();
    $("#stats-automation").html('');

    /*if (!maisValidDate(st) || !maisValidDate(et)) {
        alert('Invalid Date');
        return false
    }*/

    $.ajax({
        url : baseUrl + "admincp/mailautomation/ajax",
        method: 'POST',
        data: {action: 'filter-map', from_date: st, end_date: et},
        success: function (data) {
            var json = jQuery.parseJSON(data);
            i.fadeOut();
            obj.prop('disabled',false);
            if(json.status){
                Morris.Bar({
                    element: 'stats-automation',
                    data: json.d,
                    xkey: 'x',
                    ykeys: ['y', 'z'],
                    labels: ['Auto Mail', 'HighLights']
                }).on('click', function(i, row){
                    console.log(i, row);
                });
            }else{
                alert('invalid date range');
            }
        }
    });
    return false;
});

function maisValidDate(dateString) {
    // First check for the pattern
    if (!/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(dateString))
        return false;

    // Parse the date parts to integers
    var parts = dateString.split("/");
    var day = parseInt(parts[1], 10);
    var month = parseInt(parts[0], 10);
    var year = parseInt(parts[2], 10);

    // Check the ranges of month and year
    if (year < 1000 || year > 3000 || month == 0 || month > 12)
        return false;

    var monthLength = [ 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31 ];

    // Adjust for leap years
    if (year % 400 == 0 || (year % 100 != 0 && year % 4 == 0))
        monthLength[1] = 29;

    // Check the range of the day
    return day > 0 && day <= monthLength[month - 1];
};