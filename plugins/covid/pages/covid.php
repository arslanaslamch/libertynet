<?php
function dashboard_pager($app) {
    $app->setTitle(lang('covid::covid-statistics'));
    $results = covid_results();
    //$results = array('world','bank');
    $continent = input('filter','all');
    if($continent != 'all' && $results){
        //print_r($results);die();die();
        $newArray = array(); //you will hold the new countries
        include_once path('plugins/covid/lib/CountriesArray.php');
        $continent_raw = ucwords(str_replace('-',' ', $continent));
        $countries_codes = array_keys(CountriesArray::getFromContinent( 'alpha2', 'name', $continent_raw ));
        //print_r($countries_codes);die();
        if(!$countries_codes) redirect(url_to_pager("covid-page"));
        //print_r($countries_codes);die();
        //echo '<pre>', print_r($countries_codes),'</pre>';die();
        foreach ($results['Countries'] as $result){
            //$continent_codes = covid_countries($continent);
            //print_r($result);die();
            if(in_array($result['CountryCode'],$countries_codes)){
                $newArray['Countries'][] = $result;
            }
        }
        //echo '<pre>',print_r($newArray),'</pre>';die();
        $newArray['Global']['NewConfirmed'] = array_sum(array_column($newArray['Countries'], 'NewConfirmed'));
        $newArray['Global']['TotalConfirmed'] = array_sum(array_column($newArray['Countries'], 'TotalConfirmed'));
        $newArray['Global']['NewDeaths'] = array_sum(array_column($newArray['Countries'], 'NewDeaths'));
        $newArray['Global']['TotalDeaths'] = array_sum(array_column($newArray['Countries'], 'TotalDeaths'));
        $newArray['Global']['NewRecovered'] = array_sum(array_column($newArray['Countries'], 'NewRecovered'));
        $newArray['Global']['TotalRecovered'] = array_sum(array_column($newArray['Countries'], 'TotalRecovered'));

        $results = $newArray;
        //echo '<pre>', print_r($newArray),'</pre>';die();
    }
    $title = ($continent == 'all') ? lang('covid::world').' '.lang('covid::statistics') : ucwords(str_replace('-',' ', $continent)) .' '.lang('covid::statistics');
    return $app->render(view('covid::index', array('results' => $results,'title'=>$title)));
}