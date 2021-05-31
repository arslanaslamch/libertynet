function covid_run(){
    if($('.covid-table').length){
        $('.covid-table').DataTable();
    }
}

$(function(){
    covid_run();
});
addPageHook('covid_run');