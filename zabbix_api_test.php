<?php
	require_once( 'zabbix_api.php' );
	var_dump(zabbix_api::createArrayFromTemplate( 10263 , 'localhost' ));