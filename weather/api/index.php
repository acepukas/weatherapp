<?php

    $siteListURL = 'http://dd.weatheroffice.ec.gc.ca/citypage_weather/xml/siteList.xml';
    $stationURLTemplate = 'http://dd.weatheroffice.ec.gc.ca/citypage_weather/xml/{province}/{code}_e.xml';
    $defaultStation = '458';
    
    function retrieve_xml($url) {
        return new SimpleXMLElement(file_get_contents($url));
    }

    function retrieve_site_node($siteList,$siteId) {
        $siteNode = null;
        foreach($siteList->site as $site) {
            $requestedCode = 's0000' . $siteId;
            if($site['code'] == $requestedCode) {
                $siteNode = $site; 
                break;
            }
        }
        return $siteNode;
    }

    function get_requested_station($default) {
        $reqStation = $default;
        if(isset($_REQUEST['id'])) {
            $reqStation = $_REQUEST['id'];
        }
        return $reqStation;
    }

    function build_station_url($site,$template) {
        $url = preg_replace('/\{province\}/',$site->provinceCode, $template);
        $url = preg_replace('/\{code\}/',$site['code'], $url);
        return $url;
    }

    function station_json($xml) {
        $loc = $xml->location;
        $curCond = $xml->currentConditions;
        $data = array(
            'name'=>$loc->name,
            'province'=>$loc->province,
            'temperature'=>$curCond->temperature,
            'iconCode'=>$curCond->iconCode,
            'lastUpdated'=>$curCond->dateTime[1]->textSummary,
            'conditions'=>$curCond->condition,
            'humidex'=>$curCond->humidex,
            'visibility'=>$curCond->visibility,
            'windSpeed'=>$curCond->wind->speed,
            'windDir'=>$curCond->wind->direction
        );

        $propStrs = array();
        foreach($data as $propName => $value) {
            array_push($propStrs,'"'.$propName.'":"'.$value.'"');
        }

        return '{'.implode(',',$propStrs).'}';
    }

    $siteList = retrieve_xml($siteListURL);
    $siteNode = retrieve_site_node($siteList,get_requested_station($defaultStation));
    $stationUrl = build_station_url($siteNode,$stationURLTemplate);
    $stationXml = retrieve_xml($stationUrl);

    header("Content-Type: text/html; charset=utf-8");

    echo station_json($stationXml);

?>
