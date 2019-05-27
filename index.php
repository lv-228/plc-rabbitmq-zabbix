<?php
namespace zabbix;
	if( !file_exists( 'class/zabbix_api.php' ) )
       	throw new \Exception( '\n Файл с zabbix api не найден! \n' );
    require_once( 'class/zabbix_api.php' );

    if(isset($_POST['logout']))
  	{
    	setcookie("zabbix_auth", "", time() - 3600);
    	header('location: ' . $_SERVER['HTPP_REFERER']);
  	}
  	if(isset($_POST['login']))
  	{
    	$answerLogin = api::zabbixLogin( 'localhost', $_POST['login']['user'], $_POST['login']['password'] );
    	if(isset($answerLogin['error']['data']) &&  $answerLogin['error']['data'] == 'Login name or password is incorrect.'){
    		header('location: view/monitoring_zabbix_plugins.php?auth=false');
        die;
      }
    	if(isset($answerLogin['result']))
    	{
      		setcookie('zabbix_auth', $answerLogin['result']);
      		header('location: view/monitoring_zabbix_plugins.php');
    	}
  	}
	if( isset( $_COOKIE[ 'zabbix_auth' ] ) )
	{
		header( "location: " . 'view/add_zabbix_consumer_control.php');
	}
	else
	{
		if( !isset( $_COOKIE[ 'zabbix_auth' ] ) )
		{
			//zabbix_api::getAuthKeyAndSetCookie( 'localhost' );
		}
		//header( "location: " . 'add_zabbix_consumer_control.php');	
	}
	header('location: view/monitoring_zabbix_plugins.php');
?>