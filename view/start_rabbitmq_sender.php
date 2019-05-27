<?php
  require_once('../class/zabbix_api.php');
  require_once('../class/zabbix_consumer.php');
  use zabbix\api, zabbix\consumer;
  if( $_POST['sender'] )
  {
    $out = shell_exec('python ../pyModbusTCP/daemon_service.py start -r "' . $_GET['rabbit'] . '" --plcip "' . $_POST['sender']['plcip'] . '" --exchange "' . $_POST['sender']['rbExchenge'] . '" --queue "' . $_POST['sender']['rbQueue'] .  '" --pubkey "' . $_POST['sender']['pkey'] . '" --sleep "' . $_POST['sender']['sleep'] . '"');
    var_dump('python ../pyModbusTCP/daemon_service.py start -r "' . $_GET['rabbit'] . '" --plcip "' . $_POST['sender']['plcip'] . '" --exchange "' . $_POST['sender']['rbExchenge'] . '" --queue "' . $_POST['sender']['rbQueue'] .  '" --pubkey "' . $_POST['sender']['pkey'] . '" --sleep "' . $_POST['sender']['sleep'] . '"');die;
    echo "$out";
  }

?>
  <?php include('navbar.php'); ?>
	<form enctype="multipart/form-data" name="newSender" action="" method="post">

    <div class="col-auto">

      <br>
      <div class="form-group">
        <label>PLC ip</label>
        <input name="sender[plcip]" type="text" class="form-control col-sm-6" placeholder="PLC ip">
      </div>
      <div class="form-group">
        <label>RabbitMQ exchenge</label>
        <input required="" name="sender[rbExchenge]" type="text" class="form-control col-sm-6" placeholder="RabbitMQ exchenge">
      </div>
      <div class="form-group">
        <label>RabbitMQ queue</label>
        <input required="" name="sender[rbQueue]" type="text" class="form-control col-sm-6" placeholder="RabbitMQ queue">
      </div>
      <div class="form-group">
        <label>Publish key</label>
        <input name="sender[pkey]" type="text" class="form-control col-sm-6" placeholder="Publish key">
      </div>
      <div class="form-group">
        <label>Период опроса</label>
        <input name="sender[sleep]" type="text" class="form-control col-sm-6" placeholder="секунды (поддерживает float значения)">
      </div>
      <button type="submit" class="btn btn-primary">Подключиться</button>
    </div>
	</form>
</body>
</html>