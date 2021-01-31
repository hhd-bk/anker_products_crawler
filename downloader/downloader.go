package main

import (
	"io/ioutil"
	"log"
	"net/http"
	"sync"

	"github.com/streadway/amqp"
)

// Test function
func main() {
	// Open a connection
	conn, err := amqp.Dial("amqp://guest:guest@localhost:5672/")
	failOnError(err, "Failed to connect to RabbitMQ")
	defer conn.Close()

	// Init a channel
	ch, err := conn.Channel()
	failOnError(err, "Failed to open a channel")
	defer ch.Close()

	// Configuration for consuming
	msgs, err := ch.Consume(
		"download_jobs",
		"jobs",
		true,
		false,
		false,
		false,
		nil,
	)
	failOnError(err, "Failed to register a consumer")

	// Limiting number of goroutines
	var wg sync.WaitGroup

	// Start 10 goroutines waiting to fetch API
	maxGoroutines := 10
	wg.Add(maxGoroutines)

	// Start consuming
	forever := make(chan bool)
	for i := 0; i < maxGoroutines; i++ {
		go func() {
			for {
				d, ok := <-msgs
				if !ok {
					wg.Done()
					return
				}
				// Get data from API
				data := fetchAPI(string(d.Body))

				// Sent data to rabbitmq
				ch.Publish(
					"anker",
					"results",
					false,
					false,
					amqp.Publishing{
						ContentType: "text/plain",
						Body:        []byte(data),
						MessageId:   string(d.Body),
					})
			}
		}()
	}
	<-forever
}

// Fetch the public api
func fetchAPI(url string) []byte {
	log.Println(url)
	// Generate HTTP Request
	req, err := http.NewRequest(http.MethodGet, url, nil)
	failOnError(err, "Failed to create a request")

	// Spoofing
	req.Header.Add("User-Agent", "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4324.96 Safari/537.36")

	// Send the HTTP Request
	httpClient := &http.Client{}
	resp, err := httpClient.Do(req)
	failOnError(err, "Failed to send HTTP request")

	// Convert to byte slice
	data, err := ioutil.ReadAll(resp.Body)
	failOnError(err, "Failed to parse HTTP response")

	return data
}
