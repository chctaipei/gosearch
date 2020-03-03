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
        排除關鍵字
      </h1>


        </section>

        <!-- Main content -->
        <section class="content">

          

<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
    <?php $colspan = 2; ?>
<?php } else { ?>
    <?php $colspan = 1; ?>
<?php } ?>
        <div class="box-header with-border">
          <h1 class="box-title"></h1>

          <div class="box-tools pull-right">
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
<button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-badword" onclick="$('input[type=text][name=newproject]').val('');">新增排除字</button>
<?php } ?>
          </div>
        </div>

        <div class="row">
          <div class="col-md-6">
            <div class="box">
            <div class="box-body table-responsive">
            <!-- /.box-header -->
              <table id="badword1" class="table table-hover hide">
                <thead>
                <tr>
                  <th>排除關鍵字</th>
                  <?php if ($colspan == 2) { ?>
                  <th style="width: 60px">動作</th>
                  <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($badwordList['keyword'] as $keyword) { ?>
                <tr>
                  <td><?= $keyword ?> </td>
                  <?php if ($colspan == 2) { ?>
                  <td>
                      <div class="btn-group" style="margin:3px">
                         <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-filter" name='project' value='<?= $project ?>' onclick="showDeleteWord('<?= $keyword ?>')"> 刪除</button>
                      </div>
                  </td>
                  <?php } ?>
                </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                <tr>
                  <td colspan=<?= $colspan ?>></td>
                </tr>
                </tfoot>
              </table>
            </div>
</div>
          </div>
          <div class="col-md-6">
            <div class="box">
            <div class="box-body">
              <table id="badword2" class="table table-hover hide">
                <thead>
                <tr>
                  <th>Regex 排除關鍵字</th>
                  <?php if ($colspan == 2) { ?>
                  <th style="width: 60px">動作</th>
                  <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($badwordList['pattern'] as $pattern) { ?>
                <tr>
                  <td><?= $pattern ?> </td>
                  <?php if ($colspan == 2) { ?>
                  <td>
                      <div class="btn-group" style="margin:3px">
                         <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-filter" name='project' value='<?= $project ?>' onclick="showDeleteWord('<?= $pattern ?>')"> 刪除</button>
                      </div>
                  </td>
                  <?php } ?>
                </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                <tr>
                  <td colspan=<?= $colspan ?>></td>
                </tr>
                </tfoot>
              </table>
            </div>
</div>
            <!-- /.box-body -->
          </div>
        </div>
        <!-- /.modal -->
        <div class="modal modal-normal fade" id="modal-badword">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header" id='div-header'>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 id='the-title' class="modal-title">新增排除字</h4>
              </div>
              <div class="modal-body text-center">
                <h4 id="the-body"></h4>
                <div id="div-count" class="box-body">
                  <div class="form-group">
                    <label class="col-sm-2 control-label"><b>排除字</b></label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" name="query" value="" size="1" placeholder='若為 regex, 請輸入 /..../'>
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button id='button-filter' type="submit" class="btn pull-right bg-red" data-dismiss="modal" onclick="insertBadword();">移除</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
        <div class="modal modal-danger fade" id="modal-filter">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header" id='div-header'>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 id='the-title' class="modal-title">移除黑名單</h4>
              </div>
              <div class="modal-body text-center">
                <h4 id="the-body2"></h4>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button id='button-filter' type="submit" class="btn pull-right bg-red" data-dismiss="modal" onclick="deleteBadword();">移除</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
<script>
window.onload = function () {
  $('#badword1').DataTable({
    "dom": "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-6'l><'col-sm-6'f>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    'paging'      : true,
    'lengthChange': false,
    'searching'   : false,
    'info'        : true,
    'ordering'    : false
  });
  $( "#badword1" ).removeClass('hide');

  $('#badword2').DataTable({
    "dom": "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-6'l><'col-sm-6'f>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    'paging'      : true,
    'lengthChange': false,
    'searching'   : false,
    'info'        : true,
    'ordering'    : false
  });
  $( "#badword2" ).removeClass('hide');
}

function showDeleteWord(query)
{
  $( "input[type=text][name=query]" ).val(query);
  $( "#the-body2" ).text(query);
}

// [POST] /api/badword/{project}
function insertBadword()
{
  var url = "<?= $this->url->get(['for' => 'api-badword-insert', 'project' => $project]) ?>";
  var payload = {
    query:  $( "input[type=text][name=query]" ).val()
  }

  callAjax('POST', url, payload, function() {
      alert("新增成功" );
      location.reload();
  });
}

// [DELETE] /api/badword/{project}
function deleteBadword()
{
  var url = "<?= $this->url->get(['for' => 'api-badword-delete', 'project' => $project]) ?>";
  var payload = {
    query:  $( "input[type=text][name=query]" ).val()
  }

  callAjax('DELETE', url, payload, function() {
      alert("刪除成功" );
      location.reload();
  });
}

function callAjax(method, url, payload, callback)
{
    $.ajax({
      async: false,
      type: method,
      url: url,
      contentType: 'application/json',
      data: JSON.stringify(payload),
      statusCode: {
        200: function(xhr) {
          callback(xhr);
        }
      },
      error: function(xhr) {
         var error = JSON.parse(xhr.responseText);
         if ((typeof error.message === "string")) {
             alert(error.message);
             return;
         }

         if ((error.status == 404)) {
             alert("資料不存在或內容為空");
             return;
         }

         if (typeof error == "object") {
             error = JSON.stringify(error, undefined, 4);
         }
         alert(error);
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
