<?php
namespace zabbix;
require_once('zabbix_api.php');
require_once('../trait/trait_work_file.php');
require_once('../vendor/autoload.php');
use zabbix\api, zabbix\work_with_file;

class zabbix
{
	private function getKeyForMsgBody(array $body)
	{
        return $key = clearLine($body[0]) . '.' .clearLine($body[1]) . '.' . clearLine($body[2]) . '.' . clearLine($body[3]);
    }

    // private function clearLine($line)
    // {
    //     return strtolower(str_replace([' ','.','(',')','"'],['_',''], $line));
    // }

    public function countZabbixConsumerRun($showProc = false)
    {
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
                $find[$f]['pid'] = $out[$i][0];
                $f++;
            }
        }
        return $find;
    }

    public function zabbixAgentTestRuning()
    {
        $out = shell_exec('service --status-all 2>&1');
        $out = explode("\n",$out);
        for($i = 0; $i < count($out); $i++)
        {
            $out[$i] = preg_split("/\s+/", $out[$i]);
            if(isset($out[$i][4]))
            {
                if(preg_match("^zabbix-agent^", $out[$i][4]))
                {
                    if($out[$i][2] == "-")
                    {
                        echo 0;
                    }
                    else
                    {
                        echo 1; 
                    }
                }
            }
        }
    }

    public function zabbixAgentTestPort()
    {
        $out = shell_exec("nc -zv localhost 10050 2>&1");
        if(preg_match("^open^", $out)){
            if(preg_match("^zabbix-agent^", $out))
                echo 2;
            else
                echo 1;
        }
        elseif(preg_match("^refused^", $out))
            echo 0;
    }

    //Очень долго работает, примерно 25-30 сек
    public function getAllRabbitsInCookie($ip_three_num,$area) 
    {
        $count = 0;
        for( $i = 2; $i <= $area; $i++ )
        {
            $out = shell_exec( "nc -zv $ip_three_num$i 4369 2>&1" );
            if( preg_match( "^open^", $out ) )
            {
                $rabbitmq_server = $ip_three_num . $i;
                setcookie("rabbits[$count]", $rabbitmq_server);
                $count++;
            }
        }
        return (array)$rabbitmq_server;
  }

  public function createZabbixGroupNodeTemplate( $domain , $host_group_name = false, $host_name = false, $ip = false, $template_name = false, $global_files )
  {
    $groupHostId  = api::createHostGroup( $domain , $host_group_name, true);
    $hostId       = api::createHost( $domain , $host_name , $ip , $groupHostId, true );
    $templateId   = $template_name != false ? api::createTemplate( $domain , $template_name, $groupHostId , $hostId, true ) : null;
    work_with_file::uploadAndGetXlDirForXlsx($global_files);
    $answer_array = $templateId == null ? api::createArrayFromHost( $hostId , $domain ) : api::createArrayFromHost( $templateId , $domain );
    work_with_file::delTree('../xlsx.d/xl');
    return $answer_array;
  }

  public function curlRequestForRabbitMQAPI($ip,$method)
  {
    $ch = curl_init();
    $options = 
    [
        CURLOPT_URL            => "http://$ip:15672/" . $method,
        CURLOPT_USERPWD        => "guest:guest",
        CURLOPT_CUSTOMREQUEST  => "GET",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => 
        [
            'Content-Type: application/json'
        ]

    ];
    curl_setopt_array($ch, $options);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }
}