<?php

include "helper.php";

$rabbitmq_client = new RabbitMQ();

// Create queues
$rabbitmq_client->create_queue(QUEUE_DOWNLOAD_JOBS);
$rabbitmq_client->create_queue(QUEUE_DOWNLOAD_RESULTS);

// Create exchange
$rabbitmq_client->create_exchange(EXCHANGE_NAME);

// Binding queue with routing keys
$rabbitmq_client->binding_queue(QUEUE_DOWNLOAD_JOBS, EXCHANGE_NAME, KEY_JOBS);
$rabbitmq_client->binding_queue(QUEUE_DOWNLOAD_RESULTS, EXCHANGE_NAME, KEY_RESULTS);

// Create product_details in 'anker' database
$mysql_client = new DataInserterMySQL();
$mysql_client->create_table();