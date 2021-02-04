module rpc_server

go 1.15

replace utils => ../utils

require (
	github.com/streadway/amqp v1.0.0
	github.com/valyala/fastjson v1.6.3
	utils v0.0.0-00010101000000-000000000000
)
