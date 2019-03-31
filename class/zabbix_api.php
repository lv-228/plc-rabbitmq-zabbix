<?php
namespace zabbix;
require_once(__DIR__ . '/../trait/trait_work_file.php');
use zabbix\work_with_file;
abstract class api
{
	public static $data = 
	[
    	'jsonrpc' => 2.0,
    	'method'  => 'apiinfo.version',
    	'id'      => 1,
    	'auth'    => null,
    	'params'  => array()
	];

  //hostid это либо id хоста либо id шаблона!
	public static $add_test_item = 
	[
    'method'  		 => 'item.create',
    'params' 		   => 
    [
      'name' 		   => 'Это тестовое поле!',
      'key_' 		   => 'test.item',
      'hostid' 	   => '',
      'type' 		   => 2,//zabbix traper
      'value_type' => 3,
      //'interfaceid' => 4,
      'units' 	   => 'A',
      'delay' 	   => '30s'
    ]
	];

  //groups должен быть массив в массиве, странность zabbix API
  public static $add_test_host = 
  [
    'method'         => 'host.create',
    'params'         => 
    [
      'host'         => 'Test node',
      'interfaces'   =>
      [
        'type'       => 1,
        'main'       => 1,
        'useip'      => 1,
        'ip'         => '255.255.255.0',
        'port'       => '10050',
        'dns'        => '',
      ],
      'groups'       => 
      [
      [
        'groupid'    => 5,
      ]
      ],
    ]
  ];

  public static $add_test_host_group = 
  [
    'method'         => 'hostgroup.create',
    'params'         => 
    [
      'name'         => 'DC_Test',
    ]
  ];

  public static $add_test_template = 
  [
    'method'        => 'template.create',
    'params'        => 
    [
      'host'        => 'Test template',
      'groups'      => 
      [
        [
          'groupid' => 5
        ]
      ],
    ]
  ];

	public static $auth = 
	[
    'method' 	 	  => 'user.login',
    'params' 	 	  => 
    [
      'user' 	 	  => '',
      'password'  => ''
    ],
    'id' 		 	    => 1,
    'auth' 		 	  => null
	];

	public static $all_host_get = 
	[
  	'method' 			         => 'host.get',
  	'params' 				       => 
  	[
      'output' 		 	       => 
        [
          'hostid',
          'host'
      	],
      'selectInterfaces' 	 => 
      	[
       		'interfaceid',
       		'ip'
      	]
  	],
	];

  public static $all_host_group_get = 
  [
    'method'               => 'hostgroup.get',
    'params'               => 
    [
      'output'             => 
        [
          'name',
          'extend'
        ]
    ]
  ];

  public static $all_template_get = 
  [
    'method'   => 'template.get',
    'params'   => 
    [
      'output' =>
      [
        'host',
        'templateid'
      ]
    ]
  ];

  public static $find_host_group_by_name =
  [
    'method'   => 'hostgroup.get',
    'params'   =>
    [
      'output' => 'hostgroupid',
      'filter' =>
      [
        'name' => 
        [
          ''
        ]
      ]
    ]
  ];

  public static $find_host_by_name =
  [
    'method'   => 'host.get',
    'params'   => 
    [
      'filter' =>
      [
        'host' =>
        [
          ''
        ]
      ]
    ]
  ];

  public static $find_template_by_name =
  [
    'method'   => 'template.get',
    'params'   => 
    [
      'output' => 'templateid',
      'filter' =>
      [
        'host' =>
        [
          ''
        ]
      ]
    ]
  ];

	public static $zabbix_auth_key;

  public function zabbixLogin( $domain, $login, $password)
  {
    $authData = self::$auth;
    $authData['params']['user'] = $login;
    $authData['params']['password'] = $password;
    return self::sendCurlToZabbixApiAndGetAnswer( $domain, $authData);
  }

  public function createItem($domain, $node)
  {
    $api_test_item_array = self::$add_test_item;
    $api_test_item_array['params']['hostid'] = $node;
    return self::sendCurlToZabbixApiAndGetAnswer( $domain, $api_test_item_array );
  }

  public function createHostGroup( $domain, $name = false, $return_id = false )
  {
    $host_group['method'] = self::$add_test_host_group['method'];
    $host_group['params']['name'] = $name ? $name : self::$add_test_host_group['params']['name'];
    $answer = self::sendCurlToZabbixApiAndGetAnswer( $domain, $host_group );
    $answer = self::apiCatchAlreadyExist($answer) == false ? self::getHostGroupIdByName( $domain, $name ) : $answer;
    return $return_id ? self::getHostGroupIdFromAnswer($answer) : $answer;
  }

  public function createHost($domain, $name = false, $ip = false, $group_id = false, $return_id = false)
  {
    $host                                   = self::$add_test_host;
    $host['params']['host']                 = $name ? $name : self::$add_test_host['params']['host'];
    $host['params']['interfaces']['ip']     = $ip ? $ip : self::$add_test_host['params']['interfaces']['ip'];
    $host['params']['groups'][0]['groupid'] = $group_id ? $group_id : self::$add_test_host['params']['groups'][0]['groupid'];
    $answer = self::sendCurlToZabbixApiAndGetAnswer( $domain, $host );
    $answer = self::apiCatchAlreadyExist( $answer ) == false ? self::getHostIdByName( $domain, $name ) : $answer;
    return $return_id ? self::getHostIdFromAnswer($answer) : $answer;
  }

  public function createTemplate( $domain , $name = false, $group_id = false , $host = false , $return_id = false)
  {
    $template = self::$add_test_template;
    $template['params']['host']                 = $name ? $name : self::$add_test_template['params']['host'];
    $template['params']['groups'][0]['groupid'] = $group_id ? $group_id : self::$add_test_template['params']['groups'][0]['groupid'];
    if($host != false)
      $template['params']['hosts'][0]['hostid'] = $host;
    else
      unset($template['params']['hosts']);
    $answer = self::sendCurlToZabbixApiAndGetAnswer( $domain, $template );
    $answer = self::apiCatchAlreadyExist( $answer ) == false ? self::getTemplateIdByName( $domain, $name ) : $answer;
    return $return_id ? self::getTemplateIdFromAnswer( $answer ) : $answer;
  }

  /**
    *
    * Создает массив и записывает данные полученных их xlsx в zabbix шаблон
    * units - единица измерения
    * входные параметры:
    * array $xmlOut = массив полученный из xlsx файла,
    * int hostId = идентификатор созданного шаблона
    * string auth_key = строка идентификатор для авторизации запроса, получается из отправки запроса на API user.login
    */
  public static function createArrayFromHost( $hostId, $domain )
  {
    if(!file_exists('../xlsx.d/xl')){
      throw new \Exception("Нет xlsx данных! Файл не загружен на сервер!");
      die;
    }
    $k = 0; 
    $j = 0;
    $xmlOut = work_with_file::getFromXlsx()[0];
    $return_array['method'] = self::$add_test_item['method'];
    for($i = 0; $i < count($xmlOut); $i++)
    {
      $this_xml_node              = $xmlOut[$i];
      $return_array['params'] = 
      [
        'name'       => $this_xml_node[4],
        'key_'       => self::clearLine($this_xml_node[0]) . '.' . self::clearLine($this_xml_node[1]) . '.' . self::clearLine($this_xml_node[2]) . '.' . self::clearLine($this_xml_node[3]),
        'hostid'     => $hostId,
        'type'       => 2,
        'value_type' => 3,
        'units'      => isset($this_xml_node[6]) ? $this_xml_node[6] : null,
        'delay'      => '30s'
      ];
      $answer = self::sendCurlToZabbixApiAndGetAnswer( $domain , $return_array );
      if(self::apiCatchAlreadyExist( $answer ))
      {
        $answer_array['succsess'][$j] = 'Элемент с ключем ' . $return_array['params']['key_'] . ' добавлен!';
        $j++;
        //var_dump(self::apiCatchAlreadyExist( $answer )); echo $return_array['params']['key_']; echo ' <br> '; 
      }
      else
      {
        $answer_array['error'][$k] = 'Элемент с ключем ' . $return_array['params']['key_'] . ' уже существует в шаблоне!';
        $k++;
        //var_dump(self::apiCatchAlreadyExist( $answer )); echo $return_array['params']['key_']; echo ' <br> ';
      }
    }
    return $answer_array;
  }

  public function getAuthKeyAndSetCookie($domain)
  {
    self::$zabbix_auth_key = self::sendCurlToZabbixApiAndGetAnswer( $domain, self::$auth )["result"];
    setcookie( 'zabbix_auth' , self::$zabbix_auth_key );
  }

  public function getAllHosts($domain)
  {
    return self::sendCurlToZabbixApiAndGetAnswer( $domain, self::$all_host_get );
  }

  public function getAllTemplate($domain)
  {
    return self::sendCurlToZabbixApiAndGetAnswer( $domain, self::$all_template_get );
  }

  public function getAllHostsGroup($domain)
  {
    return self::sendCurlToZabbixApiAndGetAnswer( $domain, self::$all_host_group_get );
  }

  public function getHostGroupIdByName( $domain, $name )
  {
    $findGroup = self::$find_host_group_by_name;
    $findGroup['params']['filter']['name'] = $name;
    return self::sendCurlToZabbixApiAndGetAnswer( $domain, $findGroup );
  }

  public function getHostIdByName( $domain, $name )
  {
    $findHost = self::$find_host_by_name;
    $findHost['params']['filter']['host'] = $name;
    return self::sendCurlToZabbixApiAndGetAnswer( $domain, $findHost );
  }

  public function getTemplateIdByName( $domain, $name )
  {
    $findTemplate = self::$find_template_by_name;
    $findTemplate['params']['filter']['host'] = $name;
    return self::sendCurlToZabbixApiAndGetAnswer( $domain, $findTemplate );
  }

  private function getHostGroupIdFromAnswer( $answer )
  {
    return isset($answer['result']['groupids'][0]) ? $answer['result']['groupids'][0] : $answer['result'][0]['groupid'];
  }

  private function getHostIdFromAnswer( $answer )
  {
    return isset($answer['result']['hostids'][0]) ? $answer['result']['hostids'][0] : $answer['result'][0]['hostid'];
  }

  private function getTemplateIdFromAnswer( $answer )
  {
    return isset($answer['result']['templateids'][0]) ? $answer['result']['templateids'][0] : $answer['result'][0]['templateid'];
  }

  private function sendCurlToZabbixApiAndGetAnswer($domain,$zabbix_api_array)
  {
    $send_data = self::$data;
    $send_data['method'] = $zabbix_api_array['method'];
    $send_data['params'] = $zabbix_api_array['params'];
    $send_data['auth']   = isset($_COOKIE['zabbix_auth']) ? $_COOKIE['zabbix_auth'] : null;
    $ch = curl_init();
    curl_setopt_array( $ch, array
      (
        CURLOPT_HEADER         => 1,
        CURLOPT_URL            => $domain . '/api_jsonrpc.php',
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => json_encode( $send_data ),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => array
        (
          'Content-Type: application/json-rpc'
        )
        )
    );
    return self::getArrayFromCurlJson( curl_exec( $ch ) );
  }

  private function getArrayFromCurlJson( $curl_exec )
  {
    preg_match( '/{(.*?)"id":[0-9]}/', $curl_exec, $matches );
    $arr = json_encode( $matches[0] );
    return json_decode( json_decode( $arr ) , true );
  }

	/**
 	* Очищает строку от не нужных символов
 	*
 	*/
	private function clearLine( $line )
	{
    return strtolower(str_replace([' ','.','(',')','"'],['_',''], $line));
	}

  private function apiCatchAlreadyExist( $answer )
  {
    return (isset($answer['error']) || (isset($answer['error']) && preg_match('^already exists.', $answer['error']['code']))) ? false : true;
  }
}