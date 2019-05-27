<?php
namespace zabbix;
  require_once('../class/zabbix_class.php');
  require_once('../class/zabbix_consumer.php');
  use zabbix\zabbix, zabbix\consumer;
  $mqConnections = zabbix::curlRequestForRabbitMQAPI($_GET['rabbit'],'api/connections');
  $consumersRun = zabbix::countZabbixConsumerRun();
  if(isset($_POST['consumer']['stop']))
  {
    consumer::term($_POST['consumer']['stop']);
  }
  //var_dump(json_decode($mqConnections, true));
?>
  <?php include('navbar.php'); ?>
<br>
<!--   <ul class="nav nav-tabs" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">Home</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">Profile</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="contact-tab" data-toggle="tab" href="#contact" role="tab" aria-controls="contact" aria-selected="false">Contact</a>
  </li>
</ul> -->
<div class="tab-content" id="v-pills-tabContent">
  <div class="tab-pane fade show active" id="v-pills-home" role="tabpanel" aria-labelledby="v-pills-home-tab">
    <br>
  <?php if(count($consumersRun) == 0): ?>
    <h1 class="p-3 mb-2 bg-danger text-white">Zabbix подписчик не включен!</h1>
  <?php endif; ?>
  <?php if(count($consumersRun) > 0): ?>
  <!-- <h1 class="p-3 mb-2 bg-success text-white">Передача данных в zabbix включена</h1> -->
  <br>
  <table class="table">
    <thead class="thead-light">
      <tr>
        <th scope="col">#</th>
        <th scope="col">Узели сети Zabbix</th>
        <th scope="col">Обменник</th>
        <th scope="col">Очередь</th>
        <th scope="col">Ключь</th>
        <th scope="col">PID</th>
        <th scope="col">Управление</th>
      </tr>
    </thead>
  <tbody>
    <?php for($i = 0; $i < count($consumersRun); $i++): ?>
    <tr class="table-success">
      <th scope="row"><?= $i + 1 ?></th>
      <td><?= $consumersRun[$i][0][1] ?></td>
      <td><?= $consumersRun[$i][0][2] ?></td>
      <td><?= $consumersRun[$i][0][3] ?></td>
      <td><?= $consumersRun[$i][0][4] ?></td>
      <td><?= $consumersRun[$i]['pid'] ?></td>
      <form action="" method="post">
        <input type="hidden" name="consumer[stop]" value='<?= $consumersRun[$i]['pid'] ?>'>
        <td><button type="submit" class="btn btn-danger">Отключить</button></td>
      </form>
    </tr>
    <?php endfor; ?>
  </tbody>
</table>
<?php endif; ?>
  <br>
  <!-- 
  <div class="tab-pane fade" id="v-pills-profile" role="tabpanel" aria-labelledby="v-pills-profile-tab">.2..</div>
  <div class="tab-pane fade" id="v-pills-messages" role="tabpanel" aria-labelledby="v-pills-messages-tab">.3..</div>
  <div class="tab-pane fade" id="v-pills-settings" role="tabpanel" aria-labelledby="v-pills-settings-tab">.4..</div>
</div> -->

  <?php //var_dump(zabbix::countZabbixConsumerRun()); ?>
</body>
</html>