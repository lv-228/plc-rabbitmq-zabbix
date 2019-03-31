<?php
namespace zabbix;

require_once('class/zabbix_consumer.php');
require_once('class/rabbitmq_sender.php');

//var_dump(modbus_logo8_get_holding_r_by_type("192.168.0.116",2,0,10,"VW",1));

$consumer = new consumer();

//consumer::term(12430);