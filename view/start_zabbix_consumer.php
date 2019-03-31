<?php
  require_once('../class/zabbix_api.php');
  require_once('../class/zabbix_consumer.php');
  use zabbix\api, zabbix\consumer;
  $all_hosts = api::getAllHosts( 'localhost' )['result'];
  if( $_POST['consumer'] )
  {
    $system_str = 'php ../test_daemon.php -r ' . $_GET['rabbit'] . ' -z "localhost" -n "' . $_POST['consumer']['node'] . '" -p 5672 --rabbituser "' . $_POST['consumer']['rbLogin'] . '" --rabbitpas "' . $_POST['consumer']['rbPass'] . '" --exchenge "' . $_POST['consumer']['rbExchenge'] . '" --queue "' . $_POST['consumer']['rbQueue'] . '" --bind-key "' . $_POST['consumer']['rbBindKey'] . '" 2>&1';
    system( $system_str );
  }

?>
  <?php include('navbar.php'); ?>
	<form enctype="multipart/form-data" name="newConsumer" action="" method="post">

    <div class="col-auto">

      <br>

<!--       <div class="form-group">
      <label for="inputRabbit">RabbitMQ IP</label>
      <input pattern="([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})" required="" name="consumer[rabbit]" type="text" list="rabbits" class="col-sm-6 custom-select custom-select-md" placeholder="255.255.255.255" >
      <datalist id="rabbits">
        <?php for ( $i = 0; $i < count($all_rabbits); $i++ ): ?>
          <option><?= $all_rabbits[$i] ?></option>
        <?php endfor; ?>
      </datalist>
      <small hidden="" class="alert alert-danger">Ошибка! Формат IP 255.255.255.255</small>
      <small class="form-text text-muted">Выберите RabbitMQ</small>
     </div> -->

      <div class="form-group">
        <label>RabbitMQ логин</label>
        <input required="" name="consumer[rbLogin]" type="text" class="form-control col-sm-6" placeholder="RabbitMQ логин">
      </div>
      <div class="form-group">
        <label>RabbitMQ пароль</label>
        <input required="" name="consumer[rbPass]" type="text" class="form-control col-sm-6 " placeholder="RabbitMQ пароль">
      </div>
      <div class="form-group">
        <label>RabbitMQ exchenge</label>
        <input required="" name="consumer[rbExchenge]" type="text" class="form-control col-sm-6" placeholder="RabbitMQ exchenge">
      </div>
      <div class="form-group">
        <label>RabbitMQ queue</label>
        <input required="" name="consumer[rbQueue]" type="text" class="form-control col-sm-6" placeholder="RabbitMQ queue">
      </div>
      <div class="form-group">
        <label>RabbitMQ bind key</label>
        <input name="consumer[rbBindKey]" type="text" class="form-control col-sm-6" placeholder="RabbitMQ bind key">
      </div>
      <div class="form-group">
        <label for="InputNode">Узел сети</label>
        <input required="" list="hosts" name="consumer[node]" type="text" class="form-control col-sm-6 custom-select custom-select-md" placeholder="Название узла сети">
        <datalist id="hosts">
        <?php for ( $i = 0; $i < count($all_hosts); $i++ ): ?>
          <option><?= $all_hosts[$i]['host'] ?></option>
        <?php endfor; ?>
        </datalist>
        <small class="form-text text-muted">Введите или выберите существующее</small>
      </div>
      <button type="submit" class="btn btn-primary">Подключиться</button>
    </div>
	</form>
</body>
</html>