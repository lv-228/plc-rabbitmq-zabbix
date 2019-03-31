<?php
namespace zabbix;
require_once('daemon_class.php');
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

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

*****Параметры zabbix_consumer утилиты*****

";

    public function __construct()
    {
        if(!function_exists('modbus_logo8_get_holding_r_by_type'))
        {
            throw new Exception('Error! Отсутсвует функция modbus_logo8_get_holding_r_by_type, подключите к php библиотеку cppphpmidbus.so', 1);
        }
        parent::daemonRun();
    }

    public static function term($pid)
    {
        parent::daemonStop($pid);
    }

    public function work($options)
    {
        $plcdata = array();
        $plcdata = modbus_logo8_get_holding_r_by_type((string)$options["plcip"],(int)$options["mport"],(int)$options["sbyte"],(int)$options["cbyte"], "VW", 1);
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
        $msg_body = json_encode($plcdata);
        $msg = new AMQPMessage($msg_body);
        $channel->basic_publish($msg, $options["exchenge"], $routing_key);
        //Очередь для фронтенда, что-бы получить данные через JS библиотеку по mqtt
        //$channel->basic_publish($msg, 'amq.topic',$routing_key);
        $channel->close();
        $connection->close();
    }

    public function getConsoleValues()
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
}