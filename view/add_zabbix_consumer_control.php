<?php
  require_once('../class/zabbix_api.php');
  require_once('../class/zabbix_class.php');
  use zabbix\api, zabbix\zabbix;
  
  $all_hosts_groups = api::getAllHostsGroup( 'localhost' )['result'];
  $all_hosts = api::getAllHosts( 'localhost' )['result'];
  //$all_rabbits = zabbix::getAllRabbits( '172.17.0.', 5 );
  $all_template = api::getAllTemplate( 'localhost' )['result'];

  if( $_POST['consumer'] && count($_FILES) != 0 )
  {
    $template = $_POST['consumer']['template'] != '' ? $_POST['consumer']['template'] : false;
    $answer = zabbix::createZabbixGroupNodeTemplate('localhost',$_POST['consumer']['name'],$_POST['consumer']['node'], $_GET['rabbit'], $template , $_FILES);
  }
    
?>
  <?php include('navbar.php'); ?>
	<form enctype="multipart/form-data" name="newConsumer" action="" method="post">

    <div class="col-auto">

      <br>

  	 <div class="form-group">
      <label for="InputNode">Имя группы</label>
      <input required="" name="consumer[name]" type="text" list="hostsGroups" class="col-sm-6 custom-select custom-select-md" placeholder="Имя группы" >
      <datalist id="hostsGroups">
        <?php for ( $i = 0; $i < count($all_hosts_groups); $i++ ): ?>
          <option><?= $all_hosts_groups[$i]['name'] ?></option>
        <?php endfor; ?>
      </datalist>
    	<small class="form-text text-muted">Введите или выберите существующее</small>
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
      <div class="form-group">
        <label for="inputTemplate">Имя шаблона</label>
        <input list="templates" name="consumer[template]" type="text" class="form-control col-sm-6 custom-select custom-select-md" placeholder="Имя шаблона">
        <datalist id="templates">
        <?php for ( $i = 0; $i < count($all_template); $i++ ): ?>
          <option><?= $all_template[$i]['host'] ?></option>
        <?php endfor; ?>
        </datalist>
        <small class="form-text p-1 mb-2 bg-warning text-dark">Введите или выберите существующее (если шаблон не выбран то элементы данных будут записаны в узел сети!)</small>
      </div>
      <label for="FormControlXlsxFile">Файл с датчиками</label>
      <input accept=".xlsx" required enctype="multipart/form-data" name="consumer[file]" type="file" class="form-control-file col-sm-6" id="exampleFormControlFile1" ><br>
      <button type="submit" class="btn btn-primary">Создать</button>
    </div>
	</form>
<?php if(isset($answer)): ?>
  <!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#exampleModalCenter">
  Загруженные элементы
</button>

<!-- Modal -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Загрузка шаблона</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
          <table class="table">
            <thead class="thead-light">
              <tr>
                <th scope="col">#</th>
                <th scope="col">Результат</th>
              </tr>
            </thead>
            <tbody>
              <?php for($i = 0; $i < count($answer['succsess']); $i++): ?>
              <tr class="table-success">
                <th scope="row"><?= $i + 1 ?></th>
                  <td><?= $answer['succsess'][$i] ?></td>
              </tr>
              <?php endfor; ?>
              <?php for($i = 0; $i < count($answer['error']); $i++): ?>
              <tr class="table-danger">
                <th scope="row"><?= $i + 1 ?></th>
                  <td><?= $answer['error'][$i] ?></td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
        <button hidden="" type="button" class="btn btn-primary">Save changes</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
  $(document).ready(function(){
    $('#exampleModalCenter').modal('show');
  });
</script>
<?php endif; ?>
</body>
</html>