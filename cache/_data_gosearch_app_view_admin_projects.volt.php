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
        專案管理
      </h1>
      <!-- ol class="breadcrumb">
        <li class="active"><i class="fa fa-pencil-square-o"></i> 專案管理</a></li>
      </ol -->


        </section>

        <!-- Main content -->
        <section class="content">

          
        <div class="box-header with-border">
          <h1 class="box-title"></h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-newproject" onclick="$('input[type=text][name=newproject]').val('');">新增專案</button>
          </div>
        </div>

        <div class="">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body table-responsive">
              <table class="table">
               <thead>
                <tr>
                  <th class="col-md-2">專案</th>
                  <th class="col-md-2">索引數量</th>
                  <th class="col-md-2">腳本數量</th>
                  <th class="col-md-2">資料源</th>
                  <th class="col-md-2">動作</th>
                </tr>
               </thead>
               <tbody>
                <?php foreach ($projects as $prj) { ?>
                <tr>
                  <td><a href="<?= $this->url->get(['for' => 'admin-project-name', 'project' => $prj['name']]) ?>"><?= $prj['name'] ?></a></td>
                  <td>
                    <?php if (isset($prj['data']['index'])) { ?>
                    <?= $this->length($prj['data']['index']) ?>
                    <?php } ?>
                  </td>
                  <td>
                    <?php if (isset($prj['scriptCount'])) { ?>
                    <?= $prj['scriptCount'] ?>
                    <?php } ?>
                  </td>
                  <td>
                    <?php if (isset($prj['data']['source'])) { ?>
                    <?= $this->length($prj['data']['source']) ?>
                    <?php } ?>
                  </td>
                  <td>
                      <div>
                         <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-project" name='project' value='<?= $prj['name'] ?>' onclick="showDeleteProject('<?= $prj['name'] ?>')"> 刪除專案</button>
                      </div>
                  </td>
                </tr>
                <?php } ?>
               </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        <div class="modal modal-normal fade" id="modal-newproject">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">建立新專案</h4>
              </div>
              <form id="pform">
              <div class="form-group">
              <div class="modal-body">
                  <label>名稱:
                  <input type="text" name="newproject" value="" form="pform"  placeholder="請輸入小寫英數字"></label><br>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button type="submit" class="btn pull-right bg-blue" form="pform" onclick="addProject();">建立</button>
              </div>
              </div> <!-- //form-group -->
              </form>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
        <div class="modal modal-danger fade" id="modal-project">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 id='the-title' class="modal-title"></h4>
              </div>
              <div class="modal-body text-center">
                <h4 id="projectname"></h4>
                <div id="the-main" style='text-align:center; padding: 10px;' ></div>
                <div class="login-box form-group has-feedback" id='checkpass'>
                  <label>請輸入密碼確認</label>
                  <input type="password" class="form-control" placeholder="" name="password" autocomplete="new-password"/>
                  <span class="glyphicon glyphicon-lock form-control-feedback"></span>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button id='button-delProject' type="submit" class="btn pull-right bg-orange" data-dismiss="modal" form="iform" onclick="delProject();">刪除</button>
                <!-- button id='button-createIndex' type="submit" class="btn pull-right bg-aqua" data-dismiss="modal" form="iform" onclick="createIndex();">建立</button>
                <button id='button-deleteIndex' type="submit" class="btn pull-right bg-red" data-dismiss="modal" form="iform" onclick="deleteIndex();">刪除</button -->
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
<script>

projects = <?= $projectJson ?>;

function initBox(project)
{
    $("#the-main").show();
    $("#the-main").empty();
    $("#modal-project").removeClass("modal-primary");
    $("#modal-project").removeClass("modal-danger");
    $("#modal-project").removeClass("modal-warning");
    $("#checkpass").hide();
    $("#button-delProject").hide();
    $("#projectname").empty().append(project);
}

function showTable(project, status, noneMessage)
{
    var found = false;
    var count = 0;
    var str = "<table class='table table-bordered' style='width:550px'><tr><td width='20'>#</td><td width='150'>索引名稱</td><td>說明</td></tr>";

    jQuery.each(indexcheck[project], function(key, value) {
       found = true;
       var disable = true;
       str += "<tr><td>";
       str += "<input type='checkbox' value='"+ key+ "'";

       if (status.indexOf(value.status) == -1) {
           str += " disabled";
           str += "><td>"+ key +"</td>";
       } else {
           count++;
           str += " id='"+ key +"'";
           str += "><td><b>"+ key +"</b></td>";
       }
       str += "<td>" + value.message + "</td></tr>";
    });
    str += "</table>";

    if (found == false) {
        str = '<p class="text-center">' + noneMessage + '</p>';
    }
    $("#the-main").empty().html(str);

    return count;
}

function showDeleteProject(project)
{
    initBox(project);
    $("#button-delProject").show();
    $("#the-title" ).empty().append("確定要刪除專案 ? (同時刪除所有索引檔)");
    $("#modal-project").addClass("modal-danger");
    $("#the-main").hide();
    $("#checkpass").show();
    $("input[type=password][name=password]").val("");
}

// call [DELETE] /api/project/{project}
function delProject()
{
    var project = $("#projectname" ).text();
    var password = $("input[type=password][name=password]").val();
    var url = '<?= $this->url->get('api/project/') ?>' + project;
    var payload = {
        password: password
    }

    callAjax('DELETE', url, payload, function() {
        alert("成功刪除專案");
        location.reload();
    });
}

// call [POST] /api/project/{project}
function addProject()
{
    var project = $("input[type=text][name=newproject]").val();
    if (project == "") {
        alert("未輸入專案名稱" );
        return;
    }

    var r = confirm("建立專案預計要花 30~60 秒，請確認是否要建立 !!");
    if (r == false) {
        return;
    }

    var url = '<?= $this->url->get('api/project/') ?>' + project;
    callAjax('POST', url, {}, function() {
        alert("建立成功" );
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
             alert("未找到資料");
             return;
         }
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
