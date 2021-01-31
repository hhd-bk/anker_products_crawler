<?php
declare(strict_types=1);

# local
include "helper.php";


$consumer = new RabbitMQ();

$mysql_client = new DataInserterMySQL();

$trigger = function($msg) {
    // Product information
    global $mysql_client;
    $data = $msg->body;
    $properties = $msg->get_properties();

    // Convert to array
    $data = json_decode($data, true);

    // Insert to db 
    $data = $data["item"];
    echo $data["name"] . "\n";
    $data["url"] = $properties["message_id"];
    $mysql_client->insert_product($data);
    
};

$consumer->start_consuming(
    QUEUE_DOWNLOAD_RESULTS,
    KEY_RESULTS,
    $trigger
);

