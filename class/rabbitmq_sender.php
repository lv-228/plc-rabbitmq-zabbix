<?php
namespace zabbix;
require_once('daemon_class.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use ModbusTcpClient\Network\NonBlockingClient;
use ModbusTcpClient\Composer\Address;
use ModbusTcpClient\Composer\Read\ReadRegistersBuilder;
use ModbusTcpClient\Composer\Read\ReadRequest;

class sender extends daemon
{

    public static $message = "
*****Параметры zabbix_consumer утилиты*****
        
-r                   - ip rabbitmq сервера
        
-p                   - порт rabbitmq

--plcip              - ip логического контроллера

--mport              - ModbusTCP port

--sbyte              - первый байт для чтения

--cbyte              - количество байт для чтения
        
--rabbituser         - имя пользователя rabbitmq
        
--rabbitpas          - пароль пользователя rabbitmq

--exchenge           - Exchenge RabbitMQ

--queue              - queue RabbitMQ

--bind-key           - Ключь, из очередей которых будут приниматься сообщения

--publish-key        - Ключь, из очередей которых будут приниматься сообщения

--sleep              - Период опроса в секундах

*****Параметры zabbix_consumer утилиты*****

";

    public function __construct()
    {
        parent::daemonRun();
    }

    public static function term($pid)
    {
        parent::daemonStop($pid);
    }

    public function work($options)
    {
        $connection = new AMQPStreamConnection(
            $options["r"],
            $options["p"],
            $options["rabbituser"],
            $options["rabbitpas"],
            '/',
            false,
            'AMQPLAIN',
            null,
            'en_US',
            3.0,
            10.0,
            null,
            false,
            5
        );
        $channel = $connection->channel();
        $channel->exchange_declare($options["exchenge"], 'topic', false, false, false);
        $binding_key = $options["bind-key"];
        list($queue_name, ,) = $channel->queue_declare($options["queue"], false, true, false, false);
        $channel->queue_bind($queue_name, $options["exchenge"], $binding_key);
        $routing_key = $options["publish-key"];
        $msg_body = json_encode($this->getPlcData($options["plcip"], $options["mport"]));
        $msg = new AMQPMessage($msg_body);
        $channel->basic_publish($msg, $options["exchenge"], $routing_key);
        //Очередь для фронтенда, что-бы получить данные через JS библиотеку по mqtt
        //$channel->basic_publish($msg, 'amq.topic',$routing_key);
        $channel->close();
        $connection->close();
        sleep($options['sleep']);
    }

    public static function getConsoleValues()
    {
        $shortopts = "";
        //не объязательный параметр, порт в rabbitmq
        $shortopts .= "p:";
        //rabbitmq сервер
        $shortopts .= "r:";

        $longopts = array
        (
            "publish-key:",
            "plcip:",
            "rabbituser:",
            "rabbitpas:",
            "exchenge:",
            "queue:",
            "bind-key:",
            "mport:",
            "cbyte:",
            "sbyte:",
            "sleep:"
        );
        return getopt( $shortopts, $longopts );
    }

    public function helpMessage()
    {
        echo $this->$message;
    }

    public function systemData($options)
    {
        return 'rabbitmq_send plcip = {' . $options['plcip'] . '} exchenge= {' . $options["exchenge"] . '} queue= {' . $options["queue"] . '} bindKey= {' . $options["bind-key"] . '}';
    }

    private function getPlcData($uri, $port = 502)
    {
        $uri = 'tcp://' . $uri . ':' . $port;

        $registers = 
        [
            ['uri' => $uri, 'type' => 'uint16', 'address' => 1, 'name' => 'dc1.ventilation.valve1.position'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 2, 'name' => 'dc1.ventilation.fan2.status'],
            // will be split into 2 requests as 1 request can return only range of 124 registers max
            ['uri' => $uri, 'type' => 'uint16', 'address' => 3, 'name' => 'dc1.heating.heater2.status'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 4, 'name' => 'dc1.heating.heater3.status'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 5, 'name' => 'dc1.heating.heater4.status'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 6, 'name' => 'dc1.heating.heater5.status'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 7, 'name' => 'dc1.heating.heater6.status'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 8, 'name' => 'dc1.heating.heater7.status'],
            // will be another request as uri is different for subsequent string register
            ['uri' => $uri, 'type' => 'uint16', 'address' => 9, 'name' => 'dc1.ventilation.valve1.status'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 10, 'name' => 'dc1.ventilation.heater1.status'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 11, 'name' => 'dc1.ventilation.fan1.status'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 12, 'name' => 'dc1.it.main.temperature4'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 13, 'name' => 'dc1.it.main.temperature3'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 14, 'name' => 'dc1.engineering.main.temperature2'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 15, 'name' => 'dc1.ventilation.heater1.temperature1'],
        ];
        $fc3RequestsFromArray = ReadRegistersBuilder::newReadHoldingRegisters()
            ->allFromArray($registers)
            ->build();
        try
        {
            $responses = (new NonBlockingClient(['readTimeoutSec' => 1]))->sendRequests($fc3RequestsFromArray);
            $responses->data['timestamp'] = date('U');
            return $responses->data;
        }
        catch(Exception $e)
        {
            echo 'Проблемы с соединением: ',  $e->getMessage(), "\n";
            daemonStop(posix_getpid());
        }
    }
}