$(function(){
   ss_stats_chart();
});

function ss_stats_chart(){
    if($("#ss-canvas").length){
        let ctx = document.getElementById("ss-canvas").getContext("2d");
        let options= {};
        let label = jQuery.parseJSON($("#ss-label").val());
        let ss_data = jQuery.parseJSON($("#ss-data").val());
        let color = jQuery.parseJSON($("#ss-color").val());
        let  data = {
            datasets: [{
                data: ss_data,
                backgroundColor : color,
                borderColor : color,

            }],

            // These labels appear in the legend and in the tooltips when hovering different arcs
            labels: label
        };
        // And for a doughnut chart
        var myDoughnutChart = new Chart(ctx, {
            type: 'doughnut',
            data: data,
            //options: options
        });
    }
}