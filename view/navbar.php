<?php
  require_once('../class/zabbix_api.php');
  use zabbix\api;
  $rabbit =  isset($_GET['rabbit']) ? $_GET['rabbit'] : '';
  $zabbix =  isset($_GET['zabbix']) ? $_GET['zabbix'] : '';
?>
<!DOCTYPE html>
<html>
<head>
  <title></title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
</head>
<body>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
  <?php if(!isset($_COOKIE['zabbix_auth'])): ?>
  <div class="container h-100">
    <div class="row h-100 justify-content-center align-items-center">
      <form action="/index.php" method="post" class="col-12">
        <div class="form-group">
          <label for="formGroupExampleInput">Логин</label>
          <input name='login[user]' type="text" class="form-control" id="formGroupExampleInput" placeholder="Login" required="">
        </div>
        <div class="form-group">
          <label for="formGroupExampleInput2">Пароль</label>
          <input name='login[password]' type="text" class="form-control" id="formGroupExampleInput2" placeholder="Password" required="">
        </div>
        <?php if(isset($_GET['auth']) && $_GET['auth'] == 'false'): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          Не верный логин или пароль!
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php endif; ?>
        <button class="btn btn-warning" type="submit"> Войти </button>
      </form>
    </div>
  </div>
  <?php exit(0); ?>
  <?php endif; ?>
    <nav class="navbar navbar-dark bg-primary navbar-expand-lg">
      <?php if($rabbit == '' || $zabbix == ''): ?>
        <form class="form-inline col-4" action="" type='get'>
          <div class="form-group">
            <input name='rabbit' type="text" class="form-control col-4" placeholder="RabbitIP" pattern="[0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}" required=""value="<?= $rabbit ?>">
            &nbsp
            <input name='zabbix' type="text" class="form-control col-4" placeholder="ZabbixIP" pattern="[0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}" required="" value="<?= $zabbix ?>">
          </div>
         <button type="submit" class="btn btn-warning"><span class = "glyphicon glyphicon-search"></span> Подключиться</button>
        </form>
        <h1 class=" p-3 mb-2 bg-warning text-white">Не введены данные: RabbitMQ, Zabbix!</h1>
    </span>
        <?php exit(0); ?>
      <?php else: ?>
        <form class="form-inline col-4" action="" type='get'>
          <input name='rabbit' type="text" class="form-control col-3" placeholder="RabbitIP" pattern="[0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}" required=""value="<?= $rabbit ?>">
              &nbsp
          <input name='zabbix' type="text" class="form-control col-3" placeholder="ZabbixIP" pattern="[0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}[.][0-9]{1,3}" required="" value="<?= $zabbix ?>">
          &nbsp
          <button type="submit" class="btn btn-warning"><span class = "glyphicon glyphicon-search"></span> Изменить</button>
        </form>
      <?php endif; ?>

  <div class="collapse navbar-collapse" id="navbarText">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href=<?= 'add_zabbix_consumer_control.php?rabbit=' . $rabbit . '&zabbix=' . $zabbix?>>Создание шаблона <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item active">
        <a class="nav-link" href="<?= 'start_zabbix_consumer.php?rabbit=' . $rabbit .'&zabbix=' . $zabbix ?>">zabbix_consumer <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item active">
        <a class="nav-link" href="<?= 'start_rabbitmq_sender.php?rabbit=' . $rabbit .'&zabbix=' . $zabbix ?>">rabbitmq_sender <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item  active" >
        <a class="nav-link" href="<?= 'monitoring_dc.php?rabbit=' . $rabbit .'&zabbix=' . $zabbix ?>">Визуализация</a>
      </li>
      <li class="nav-item  active">
        <a class="nav-link" href="<?= 'monitoring_zabbix_plugins.php?rabbit=' . $rabbit .'&zabbix=' . $zabbix ?>">Мониторинг </a>
      </li>
    </ul>
    </div>
    <span class="navbar-text">
      <form action='/index.php' method="post">
        <input type="hidden" name="logout" value="yeah">
        <button class="btn btn-warning" type="submit"> Выйти </button>
      </form>
    </span>
</nav>