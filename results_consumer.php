<?php
declare(strict_types=1);

// local import 
include "helper.php";

require_once __DIR__ . '/vendor/autoload.php';
use Elasticsearch\ClientBuilder;

// RabbitMQ Client 
$consumer = new RabbitMQ();

// Mysql 
$mysql_client = new DataInserterMySQL();

// Elastic Search
$eslastic_client = ClientBuilder::create()->build();

$trigger = function($msg) {
    // Product information
    global $mysql_client, $eslastic_client;
    $data = $msg->body;
    $properties = $msg->get_properties();
    var_dump($data);
    // Convert to array
    $data = json_decode($data, true);

    // Insert data to mysql 
    $data = $data["item"];
    // $data["url"] = $properties["message_id"];
    // $mysql_client->insert_product($data);
    
    // Insert data to elastic search 
    // $es_data = [
    //     "index" => "anker_products",
    //     "type" => "_doc",
    //     "body" => $data,
    // ];

    // $response = $eslastic_client->index($es_data);
};

$consumer->start_consuming(
    QUEUE_DOWNLOAD_RESULTS,
    KEY_RESULTS,
    $trigger
);

