<?php
namespace zabbix;

require_once('daemon_class.php');
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

class consumer extends daemon
{
    public static $message = "
*****Параметры zabbix_consumer утилиты*****

-z           - ip zabbix сервера
        
-n           - имя узла с типом данных 'траппер' в zabbix
        
-r           - ip rabbitmq сервера
        
-p           - порт rabbitmq
        
--rabbituser - имя пользователя rabbitmq
        
--rabbitpas  - пароль пользователя rabbitmq

--exchenge   - Exchenge RabbitMQ

--queue      - queue RabbitMQ

--bind-key      - Ключь, из очередей которых будут приниматься сообщения

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

    public function getConsoleValues()
    {
        $shortopts = "";
        //ip zabbix сервера принимающего данные
        $shortopts .= "z:";
        //node узел-траппер в zabbix который принимает данные
        $shortopts .= "n:";
        //не объязательный параметр, порт в rabbitmq
        $shortopts .= "p:";
        //rabbitmq сервер
        $shortopts .= "r:";

        $longopts = array
        (
            "rabbituser:",
            "rabbitpas:",
            "exchenge:",
            "queue:",
            "bind-key:"
        );

        return getopt( $shortopts, $longopts );
    }

    public function systemData($options)
    {
        return 'zabbix_consumer_node_= {' . $options['n'] . '} exchenge= {' . $options["exchenge"] . '} queue= {' . $options["queue"] . '} bindKey= {' . $options["bind-key"] . '}';
    }

    public function helpMessage()
    {
        echo $this->$message;
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
        $long_queue = function ($msg) use ($options) {
            $decode_array = json_decode($msg->body, true)[0];
            unset($decode_array[0]);
            if (count($decode_array) > 250) {
                $array_chunk = array_chunk($decode_array, 250);
                for ($i = 0; $i < count($array_chunk); $i++) {
                    for ($j = 0; $j < count($array_chunk[$i]); $j++) {
                        $new_line = $options["n"] . " " . getKeyForMsgBody($array_chunk[$i][$j]) . " " . json_decode($msg->body, true)['timestamp'] . " " . '"' . $array_chunk[$i][$j][7] . '"' . "\n";
                        file_put_contents("boof.txt", $new_line, FILE_APPEND | LOCK_EX);
                    }
                    shell_exec('zabbix_sender -z ' . $options["z"] . ' -T -i ' . __DIR__ . '/boof.txt');
                    file_put_contents("boof.txt", '');
                }
            } else {
                for ($j = 1; $j < count($decode_array); $j++) {
                    $new_line = $options["n"] . " " . getKeyForMsgBody($decode_array[$j]) . " " . json_decode($msg->body, true)['timestamp'] . " " . '"' . $decode_array[$j][7] . '"' . "\n";
                    file_put_contents("boof.txt", $new_line, FILE_APPEND | LOCK_EX);
                }
                shell_exec('zabbix_sender -z ' . $options["z"] . ' -T -i ' . __DIR__ . '/boof.txt');
                file_put_contents("boof.txt", '');
            }
        };
        $channel->basic_consume($queue_name, '', false, true, false, false, $long_queue);
        while (count($channel->callbacks))
        {
            $channel->wait();
        }
        $channel->close();
        $connection->close();
    }
}

    function getKeyForMsgBody(array $body){
        return $key = clearLine($body[0]) . '.' .clearLine($body[1]) . '.' . clearLine($body[2]) . '.' . clearLine($body[3]);
    }

    function clearLine($line){
        return strtolower(str_replace([' ','.','(',')','"'],['_',''], $line));
    }

    function ZabbixConsumerRun(){
        $proc = array();
        $count = 0;
        $matches = array();
        $find = array();
        $f = 0;
        $out = shell_exec("ps -A -f 2>&1");
        $out = explode("\n",$out);
        for($i = 0; $i < count($out); $i++)
        {
            $str = preg_match_all("/\{[A-Za-z0-9\s\.\#\*]{1,}\}/", $out[$i], $matches);
            if(!empty($matches) && count($matches[0]) > 1 )
            {
                $out[$i] = preg_split('/\s+/',$out[$i]);
                $find[$f] = $matches;
                $find[$f]['pid'] = $out[$i][1];
                $f++;
            }
        }
        return $find;
    }
    ?>