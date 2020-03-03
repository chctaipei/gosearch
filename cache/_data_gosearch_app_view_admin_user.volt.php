<?= $this->tag->getDoctype() ?>
<html>

<head>
    <meta charset="UTF-8">
    <?= $this->tag->getTitle() ?>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    <?= $this->tag->stylesheetLink('static/css/all.css') ?>
    <?= $this->tag->stylesheetLink('static/css/bootstrap-toggle.min.css') ?>
    <?= $this->tag->stylesheetLink('static/css/dataTables.bootstrap.min.css') ?>
    <?= $this->assets->outputCss() ?>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
      li.L0, li.L1, li.L2, li.L3,
      li.L5, li.L6, li.L7, li.L8 {
          list-style-type: decimal !important;
      }
    </style
</head>


<body class="skin-blue sidebar-mini">
  <div id="app">
    <div class="wrapper">

      <!-- Main Header -->
<header class="main-header">

    <!-- Logo -->
    <a href="<?= $this->url->get('/') ?>" class="logo">
        <span class="logo-mini"><b><?= $text->logo->mini ?></b></span>
        <span class="logo-lg"><b><?= $text->logo->site ?></b> <?= $text->logo->title ?></span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only"><?= $text->togglenav ?></span>
        </a>

        <div class="navbar-header">

          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
            <i class="fa fa-toggle-down"></i>
            <?php if (isset($project)) { ?>
            <span class="text-right"><?= $project ?></span>
            <?php } ?>
          </button>
        </div>

        <!-- Project Menu -->
        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav">
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-suitcase"></i>
                  <?php if (isset($project)) { ?>
                      <?= $project ?> 
                  <?php } else { ?>
                      請選擇專案
                  <?php } ?>
                  <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                <?php foreach ($projects as $name => $prj) { ?>
                  <li><a href="?project=<?= $name ?>"> <?= $name ?></a></li>
                  <!-- li role="presentation" class="divider"></li -->
                <?php } ?>
                </ul>
              </li>
              <li class="dropdown messages-menu">
                <a href="https://documenter.getpostman.com/view/191215/gosearch/RW87ppAt" target='postman'>
                  <span style="color: #fff"><i class="fa fa-external-link"></i> api doc</span>
                </a>
              </li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
              <li>
              <div class="content-header"> 
                <span style="color: #fff"><i class="fa fa-user"></i> <?= $auth['account'] ?></span>
              </div>
              </li>
              <li>
                <a href="/logout">
                  <i class="fa fa-sign-out"></i> <span>登出&nbsp;&nbsp;</span>
                </a>
              </li>
            </ul>
        </div>
    </nav>
</header>


      <aside class="main-sidebar">
  <section class="sidebar">
    <ul class="sidebar-menu tree" data-widget="tree">
        <!--  搜尋 -->
          <?php if (isset($project) && isset($scripts) && isset($scripts[$project])) { ?>
        <li class="treeview <?= echoActive('/search') ?>">
          <a href="#">
            <i class='fa fa-search'></i>
            <span><?= $text->sidebar->search ?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <?php foreach ($scripts[$project] as $name => $value) { ?>
            <?php $sn = '/search/project/' . $project . '/' . $name; ?>
              <li class="<?= echoActive($sn) ?>">
                <a href="<?= $this->url->get(['for' => 'search-project-script', 'project' => $project, 'scriptId' => $name]) ?>"><i class="fa fa-circle-o"></i><?= $name ?></a>
              </li>
            <?php } ?>
          </ul>
          <?php } else { ?>
        <li class="treeview">
          <a style="color: #999;">
             <i class='fa fa-search'></i>
             <span><?= $text->sidebar->search ?></span>
          </a>
          <?php } ?>
        </li>

        <!--  熱門關鍵字 -->
        <li class="treeview <?= echoActive('/hotwords') ?>">
          <?php if (isset($project)) { ?>
            <a href="<?= $this->url->get(['for' => 'hotwords-project', 'project' => $project]) ?>">
          <?php } else { ?>
            <a style="color: #999;">
          <?php } ?>
            <i class='fa fa-camera'></i>
            <span><?= $text->sidebar->hotwords ?></span>
          </a>
        </li>

        <!--  排除關鍵字 -->
        <li class="treeview <?= echoActive('/badword') ?>">
          <?php if (isset($project)) { ?>
            <a href="<?= $this->url->get(['for' => 'badwords-project', 'project' => $project]) ?>">
          <?php } else { ?>
            <a style="color: #999;">
          <?php } ?>
             <i class="fa fa-circle-o"></i> 
            <span><?= $text->sidebar->badword ?></span>
          </a>
        </li>

      <?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
        <!-- 管理 -->
        <li class="treeview <?= echoActive('/admin') ?>">
          <a href="#">
            <i class="fa fa-gear"></i>
            <span><?= $text->sidebar->admin->title ?></span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="<?= echoActive('/admin/projects') ?>"><a href="<?= $this->url->get(['for' => 'admin-projects']) ?>"><i class="fa fa-pencil-square-o"></i> <?= $text->sidebar->admin->project ?></a></li>
            <li class="<?= echoActive('/admin/users') ?>"><a href="<?= $this->url->get(['for' => 'admin-users']) ?>"><i class="fa fa-user-circle-o"></i> <?= $text->sidebar->admin->user ?></a></li>
            <li class="<?= echoActive('/admin/service') ?>"><a href="<?= $this->url->get(['for' => 'admin-service']) ?>"><i class="fa fa-wheelchair-alt"></i> 服務管理</a></li>
            <!-- li class="treeview <?= echoActive('/admin/system') ?>">
              <a href="#">
                <i class='fa fa-wheelchair-alt'></i>
                <span><?= $text->sidebar->admin->system ?></span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                  <li class="<?= echoActive('/admin/cron') ?>">
                      <a href="/admin/cron"><i class="fa fa-circle-o"></i> 排程服務</a>
                  </li>
              </ul>
            </li -->
          </ul>
        </li>
      <?php } ?>
    </ul>
  </section>
</aside>


      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <div id='alert' class="flat no-border alert alert-warning alert-dismissible hidden" aria-hidden="true"></div>

        <section class="content-header">

          
      <h1>
        帳號管理
      </h1>


        </section>

        <!-- Main content -->
        <section class="content">

          
        <div class="box-header with-border">
          <h1 class="box-title"></h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-newuser">新增帳號</button>
          </div>
        </div>

        <div class="">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body  table-responsive">
              <table id='user' class="table table-hover hide">
                <thead>
                <tr>
                  <th class="col-md-3">帳號</th>
                  <th class="col-md-2">姓名</th>
                  <th class="col-md-2 hidden-xs">建立時間</th>
                  <!-- th class="col-md-2">更新時間</th -->
                  <th class="col-md-2">等級</th>
                  <th class="col-md-2">動作</th>
                </tr>
                </thead>
                <tbody>
    <?php foreach ($users as $user) { ?>
                <tr>
                  <td><?= $user['account'] ?></td>
                  <td><?= $user['name'] ?></td>
                  <td class="hidden-xs"><?= $user['createTime'] ?></td>
                  <!-- td><?= $user['updateTime'] ?></td -->
                  <td>
                    <form id="syncform" action="javascript:void(0);">
                      <select name="level" form="levelform" onchange="changeLevel(this, '<?= $user['account'] ?>')">
                        <option value="0"  <?php if ($user['level'] == 0) { ?>selected<?php } ?>>管理者</option>
                        <option value="1"  <?php if ($user['level'] == 1) { ?>selected<?php } ?>>一般</option>
                      </select>
                    </form">
                  </td>
                  <td>
                      <div>
                          <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-deluser" name='deluser' value='<?= $user['account'] ?>' onclick="showAccount('<?= $user['account'] ?>')">刪除帳號</button>
                      </div>
                  </td>
                </tr>
    <?php } ?>
                <tbody>
                <tfoot>
                <tr>
                  <td colspan=5></td>
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        <div class="modal modal-normal fade" id="modal-newuser">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">建立新帳號 </h4>
              </div>
              <form id="iform">
              <div class="form-group">
              <div class="modal-body">
                  <label id='edit-label'>帳號:</label>
                  <input type="text" name="account" value="" form="iform"><br>
                  <label id='edit-label'>等級:</label>
                  <select id='newlevel' name="level" form="iform">
                      <option value="0">管理者</option>
                      <option value="1" selected>一般</option>
                  </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button type="submit" class="btn pull-right bg-blue" form="iform" onclick="addAccount();">建立</button>
              </div>
              </div> <!-- //form-group -->
              </form>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
        <div class="modal modal-default fade" id="modal-deluser">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">確定要刪除帳號 ?</h4>
              </div>
              <div class="modal-body text-center">
                <h4 id="del-user"></h4>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn bg-gray pull-left" data-dismiss="modal">取消</button>
                <button type="submit" class="btn pull-right bg-red" onclick="delAccount();">刪除</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
<script>

window.onload = function () {
  var table = $('#user').DataTable({
      "dom": "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-6'l><'col-sm-6'f>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      'paging'      : true,
      'lengthChange': false,
      'searching'   : true,
      'ordering'    : false,
      'info'        : true,
      'autoWidth'   : false
  });
  $( "#user" ).removeClass('hide');
}

function showAccount(account)
{
    $("#del-user" ).empty().append(account);
}

function delAccount()
{
    var account = $("#del-user" ).text();
    return $.ajax({
      type: "DELETE",
      url: '<?= $this->url->get('api/user/') ?>' + account,
      contentType: 'application/json',
      statusCode: {
        200: function() {
            alert("成功刪除帳號");
            location.reload();
        } 
      },
      error: function(xhr) {
         var error = JSON.parse(xhr.responseText);
         alert(error.message);
         $('#modal-deluser').modal('hide');
      }
    });
}

function changeLevel(obj, account)
{
    var levelObj = { level: obj.value , action: "update"};
    updateAccount(account, levelObj, function() {
        alert( "更新成功" );
    });
    location.reload();
}

function addAccount()
{
    var account = $("input[type=text][name=account]").val();
    var levelObj = { level: $('#newlevel').val(), action: "create" };

    if (account == "") {
        alert("未輸入帳號" );
        return;
    }

    updateAccount(account, levelObj, function() {
        alert("建立成功" );
        // $('#modal-newuser').modal('hide');
        location.reload();
    });
}

function updateAccount(account, obj, callback)
{
    return $.ajax({
      type: "POST",
      url: '<?= $this->url->get('api/user/') ?>' + account,
      contentType: 'application/json',
      data: JSON.stringify(obj),
      statusCode: {
        200: function() {
            callback();
        } 
      },
      error: function(xhr) {
         var error = JSON.parse(xhr.responseText);
         alert(error.message);
      }
    });
}
</script>


        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      <!-- Main Footer -->
<footer class="main-footer">
    <!-- Default to the left -->
    <strong class='initialism'>串起網路上豐富 美好的人事物</strong>
</footer>


    </div><!-- ./wrapper -->
  </div><!-- ./app -->
</body>

<?= $this->tag->javascriptInclude('static/js/app.js') ?>
<?= $this->tag->javascriptInclude('static/js/bootstrap-toggle.min.js') ?>

<?= $this->tag->javascriptInclude('static/js/jquery.dataTables.min.js') ?>
<?= $this->tag->javascriptInclude('static/js/dataTables.bootstrap.min.js') ?>

<?= $this->assets->outputJs() ?>

<script>
function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function showSuccess(text, reload = true)
{
    showAlert(text, 'success');

    if (reload) {
        setTimeout(function() {
            location.reload();
        }, 1000);
    }
}

function showWarning(text)
{
    showAlert(text, 'warning');
}

function showAlert(text, status = 'warning')
{
    var message;
    if (status == 'success') {
        $("#alert").removeClass('alert-warning');
        $("#alert").addClass('alert-success');
        message = '<h4><i class="icon fa fa-smile-o"></i>成功!</h4>';
    } else {
        $("#alert").removeClass('alert-success');
        $("#alert").addClass('alert-warning');
        message = '<h4><i class="icon fa fa-warning"></i>警告!</h4>';
    }

    html = '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>' +
      message +
      '<p>' + htmlEntities(text) + '</p>';
    $("#alert").html(html);
    $("#alert").removeClass('hidden');
}
</script>


</html>
