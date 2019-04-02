<?php
namespace zabbix; 

abstract class daemon
{
	protected function daemonRun()
	{
		$child_pid = pcntl_fork();
        if ($child_pid) 
        {
            // Выходим из родительского, привязанного к консоли, процесса
            exit();
        }
        //Делаем основным процессом дочерний.
        posix_setsid();
        $options = $this->getConsoleValues();
        $title = $this->systemData($options);
        $pid = getmypid(); // вы можете использовать это, чтобы увидеть заголовок процесса в ps
        if (!cli_set_process_title($title)) 
        {
            echo "Не удалось установить заголовок процесса для PID $pid...\n";
            exit(1);
        } 
        else 
        {
            echo "Процесс '$title' запущен! PID = $pid\n";
        }
        pcntl_signal(SIGTERM, array($this, "childSignalHandler"));
        $stop = false;
        while(!$stop)
        {
        	$this->work($options);
        }
	}

	//Вывод сообщения в консоль информации такой как в какую очередь отправляются сообщения с каким ключем и т.д.ы
	abstract public function systemData($options);

	//Вывод сообщения в консоль помощи с флагами плагина (как при -help)
	abstract public function helpMessage();

	//необходимые флаги из консоли
	abstract public static function getConsoleValues();

	//функция основной работы демона
	abstract public function work($options);

	public function childSignalHandler($signo, $pid = null, $status = null)
	{
        switch($signo)
        {
            case SIGTERM:
                // При получении сигнала завершения работы устанавливаем флаг
                $this->stop_server = true;
                break;
        }
    }

    public function daemonStop($pid)
    {
    	posix_kill($pid, SIGTERM);
    	pcntl_signal_dispatch();
    }

}