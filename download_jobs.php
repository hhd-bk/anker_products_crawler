<?php

# local import 
include "helper.php";

function send_request($url) {
    $curl = curl_init($url); 

    // Spoofing
    $user_agent = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36";
    curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    
    // Execution
    $data = curl_exec($curl);
    usleep(250000);
    curl_close($curl);

    // Error message from curl execution
    // $error = curl_error($curl);
    // echo $error;

    // Parse to json
    $data = utf8_decode($data);
    $data = json_decode($data, true);
    return $data;
};

$producer = new RabbitMQ();

// Pagination
$pag = 0;
    while (true){
    // Generate API 
    $public_api = "https://shopee.vn/api/v2/search_items/?by=pop&limit=30&match_id=16461019&newest={$pag}&order=desc&page_type=shop&version=2";
    
    // Send request to api
    $data = send_request($public_api);
    $items = $data["items"];
    
    // Check if have next page or not
    if (empty($items)) {
        echo "End\n";
        break;
    } else {
        echo $public_api . "\n";
        $pag += 30;
    }

    // Create job and send to downloader
    foreach ($items as $item) {
        // Get item id 
        $item_id = $item["itemid"];

        // Generate url for this item
        $url = preg_replace("/\[(.*?)\]/", "", $item["name"]);
        $url = str_replace(" ", "-", $url);
        $url = "https://shopee.vn/api/v2/item/get?itemid={$item_id}&shopid=16461019";

        // Send to jobs queue
        $producer->produce($url, EXCHANGE_NAME, KEY_JOBS);
    }
}