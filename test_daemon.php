<?php
namespace zabbix;

require_once('class/zabbix_consumer.php');
require_once('class/rabbitmq_sender.php');

if(isset(consumer::getConsoleValues()['z']))
	$consumer = new consumer();
if(isset(sender::getConsoleValues()['plcip']))
	$sender = new sender();

//consumer::term(1876);