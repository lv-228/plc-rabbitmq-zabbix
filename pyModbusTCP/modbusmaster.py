from pyModbusTCP.client import ModbusClient
import pika
import json
from datetime import datetime
from time import sleep

# TCP auto connect on first modbus request
def getPlcData(arg):
    while True:
    	regs = connectToPlc(arg.plcip)
    	if regs == None:
    		print "connection successful, but, get date fail, attempt to get data again " + datetime.now().strftime("%Y-%m-%d %H.%M.%S")
    		continue
    	answer = {'inside.temp':regs[0], 'outside.temp':regs[1],'timestamp':datetime.now().strftime('%s')}
	connection = pika.BlockingConnection(pika.ConnectionParameters(host=arg.rabbitmqip))
	channel = connection.channel()
	channel.queue_declare(queue=arg.queue, durable=True)
	channel.basic_publish(exchange=arg.exchange,
                routing_key=arg.pubkey,
                body=json.dumps(answer),
                properties=pika.BasicProperties(
                    delivery_mode = 2, # make message persistent
                  ))
	    #print(" [x] Sent data to RabbitMQ")
	connection.close()
	sleep(arg.sleep)


def connectToPlc(plcip):
    c = ModbusClient(host=plcip, port=502, auto_open=True)
    if not c:
    	sleep(1)
    	print 'Error connection try recconect ' + datetime.now().strftime("%Y-%m-%d %H.%M.%S")
    	connectToPlc(plcip)
    else:
	    r = c.read_holding_registers(0, 2)
    if not r:
    	 sleep(1)
         print 'Error connection to PLC or get data, recconect ' + datetime.now().strftime("%Y-%m-%d %H.%M.%S")
         connectToPlc(plcip)
    else:
        return r