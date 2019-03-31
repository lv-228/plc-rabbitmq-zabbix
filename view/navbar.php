<?php
  require_once('../class/zabbix_api.php');
  use zabbix\api;
  if(isset($_POST['logout']))
  {
    setcookie("zabbix_auth", "", time() - 3600);
  }
  if(!isset($_COOKIE['rabbits']))
    zabbix::getAllRabbitsInCookie('172.17.0.',4);
  $rabbit =  isset($_GET['rabbit']) ? '?rabbit=' . $_GET['rabbit'] : '';
  //var_dump($_COOKIE);
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
          <input name='login[user]' type="text" class="form-control" id="formGroupExampleInput" placeholder="Login">
        </div>
        <div class="form-group">
          <label for="formGroupExampleInput2">Пароль</label>
          <input name='login[password]' type="text" class="form-control" id="formGroupExampleInput2" placeholder="Password">
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
  <div class="btn-group">
  <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <?= isset($_GET['rabbit']) ? 'RabbitMQ: ' . $_GET['rabbit'] : 'Доступные RabbitMQ' ; ?>
  </button>
  <div class="dropdown-menu">
    <?php for( $i = 0; $i < count($_COOKIE['rabbits']); $i++ ): ?>
      <a class="dropdown-item" href= <?= $_SERVER['PHP_SELF'] . '?' . 'rabbit=' . $_COOKIE['rabbits'][$i] ?> > <?= $_COOKIE['rabbits'][$i] ?></a>
    <?php endfor; ?>
  </div>
<!--   <div class="dropdown-menu">
    <a class="dropdown-item" href="#">Action</a>
    <a class="dropdown-item" href="#">Another action</a>
    <a class="dropdown-item" href="#">Something else here</a>
    <div class="dropdown-divider"></div>
    <a class="dropdown-item" href="#">Ссылка отделенная чертой</a>
  </div> -->
</div>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarText">
    <ul class="navbar-nav mr-auto">
      <li class="nav-item active">
        <a class="nav-link" href=<?= 'add_zabbix_consumer_control.php' . $rabbit ?>>Добавить плагин в zabbix <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item active">
        <a class="nav-link" href="<?= 'start_zabbix_consumer.php' . $rabbit ?>">Запустить zabbix_consumer <span class="sr-only">(current)</span></a>
      </li>
      <li class="nav-item  active" >
        <a class="nav-link" href="<?= 'monitoring_dc.php' . $rabbit ?>">Визуализация</a>
      </li>
      <li class="nav-item  active">
        <a class="nav-link" href="<?= 'monitoring_zabbix_plugins.php' . $rabbit ?>">Мониторинг zabbix_consumer </a>
      </li>
    </ul>
<!--     <button class="btn btn-warning" data-toggle="collapse" data-target="#hide-me">Список RabbitMQ</button>
<div id="hide-me" class="collapse in">
  <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
    <?php for( $i = 0; $i < count($_COOKIE['rabbits']); $i++ ): ?>
      <a <?php echo "onclick=\"ajax('" . $_COOKIE['rabbits'][$i] . "', 'http://localhost:8080/getAllConnectionsToRabbit.php')\"" ?> class="nav-link" id="v-pills-home-tab" data-toggle="pill" href="#v-pills-home" role="tab" aria-controls="v-pills-home" aria-selected="true"><?= $_COOKIE['rabbits'][$i] ?></a>
    <?php endfor; ?>
  </div>
</div> -->
    <span class="navbar-text">
      <form action='/index.php' method="post">
        <input type="hidden" name="logout" value="yeah">
        <button class="btn btn-warning" type="submit"> Выйти </button>
      </form>
    </span>
  </div>
</nav>
  <?php if(!isset($_GET['rabbit'])): ?>
    <h1 class=" p-3 mb-2 bg-warning text-white">RabbitMQ не выбран!</h1>
    <?php exit(0); ?>
  <?php endif; ?>