<?php

function getResults($tag = 'saturday', $start_time = "", $end_time = "", $access_token = "") {

    //Limit the maximum number of requests sent to Instagram
    $max_num_req = 50;

    //Limit the number of consecutive unmatched posts before ending the script
    $max_unmatched = 100;

    //Maximum number of posts to fetch
    $max_results = 200;

    if (empty($start_time))
        $start_time = strtotime("-1 days");
    if (empty($end_time))
        $end_time = time();
    if (empty($access_token)) {
        die("Access Token is Required!");
    }

    $url = 'https://api.instagram.com/v1/tags/' . $tag . '/media/recent?access_token=' . $access_token . '&count=20';
    $gotAllResults = false;
    $results = array();
    $unmatched = 0;
    $i = 0;
    while (!$gotAllResults) {
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 2
        ));

        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        foreach ($result['data'] as $item) {
            if ($item['created_time'] >= $start_time && $item['created_time'] <= $end_time) {
                $results[] = $item;
                $unmatched = 0;
            } else {
                if (count($results))
                    $unmatched++;
            }
        }
        if (!isset($result['pagination']['next_url'])) {
            $gotAllResults = true;
        } elseif (count($results) > ($max_results - 1)) {
            $results = array_slice($results, 0, $max_results);
            $gotAllResults = true;
        } elseif ($unmatched > $max_unmatched) {
            $gotAllResults = true;
        } elseif ($i > $max_num_req) {
            //Fail safe in case of unlimited requests. 
            //Scenario where the loop goes above 30 requests
            //echo "Found: " . count($results) . "<br>";
            //echo "Unmatched: " . $unmatched . "<br>";
            //echo "Last Record:" . $item['created_time'] . "<br>";
            //echo "Start Time: " . $start_time . "<br>";
            //echo "End Time: " . $end_time . "<br>";
            //die('force ended');
            $gotAllResults = true;
        } else {
            $url = $result['pagination']['next_url'];
        }
        $i++;
    }
    return $results;
}

//Hit this URL to get access_token as GET parameter
//$access_token_url = "https://instagram.com/oauth/authorize/?client_id=CLIENT-ID&redirect_uri=REDIRECT-URI&response_type=token";



$tag = "yolo";
$start_time = strtotime("-1 days");
$end_time = time();
$access_token = "";
$results = getResults($tag, $start_time, $end_time, $access_token);

foreach ($results as $item) {
    echo "<a href='".$item['link']."'>";
    echo "<img src='".$item['images']['standard_resolution']['url']."' />";
    echo "</a>";
}