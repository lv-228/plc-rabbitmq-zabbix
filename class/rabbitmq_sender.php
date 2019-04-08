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
            ['uri' => $uri, 'type' => 'uint16', 'address' => 0, 'name' => 'temp.inside'],
            ['uri' => $uri, 'type' => 'uint16', 'address' => 1, 'name' => 'temp.outside'],

        ];
        $fc3RequestsFromArray = ReadRegistersBuilder::newReadHoldingRegisters()
            ->allFromArray($registers)
            ->build();
        try
        {
            $responses = (new NonBlockingClient())->sendRequests($fc3RequestsFromArray);
            //$responses->data['timestamp'] = date('U');
            var_dump($responses);die;
            return $responses;
        }
        catch(Exception $e)
        {
            echo 'Connection error: ',  $e->getMessage(), "\n";
            daemonStop(posix_getpid());
        }
    }
}