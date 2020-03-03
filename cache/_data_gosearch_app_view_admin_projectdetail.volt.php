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
        <?= $project ?>
      </h1>
      <!-- ol class="breadcrumb">
        <li><a href="<?= $this->url->get(['for' => 'admin-projects']) ?>"><i class="fa fa-pencil-square-o"></i> 專案管理</a></li>
        <li class="active"><?= $project ?></li>
      </ol -->


        </section>

        <!-- Main content -->
        <section class="content">

          
     <div class="box-header with-border">
      <h3 class="box-title">
        索引列表
      </h3>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
      <div class="box-tools pull-right">
          <button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-index" onclick="showEditor('<?= $project ?>', 'index', '+');">新增樣板</button>
      </div>
<?php } ?>
     </div>

        <div class="">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
              <table class="table">
                <thead>
                <tr>
                  <th class='col-md-2 col-xs-3'>索引 (backups)</th>
                  <th class='col-md-1 col-xs-3'>文件數</th>
                  <th class='col-md-2 col-xs-3'>狀態</th>
                  <th class='col-md-2 hidden-xs hidden-sm'>排程</th>
                  <th class='col-md-2 hidden-xs hidden-sm'>資料源</th>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                  <th class='col-md-3 col-xs-3'>動作</th>
<?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($indexInfo as $idx) { ?>
                <tr>
                  <td>
                  <?php if ($idx['status'] == 404 || isset($idx['error']['reason'])) { ?>
                    <?= $idx['type'] ?>
                  <?php } else { ?>
                    <a href="<?= $this->url->get(['for' => 'admin-project-doc', 'project' => $project, 'type' => $idx['type']]) ?>"><?= $idx['type'] ?></a>
                  <?php } ?>
                  <?php if (isset($projects[$project]['data']['backup'][$idx['type']]['count']) && $projects[$project]['data']['backup'][$idx['type']]['count'] > 0) { ?>
                    (<?= $projects[$project]['data']['backup'][$idx['type']]['count'] ?>)

                    <?php if (isset($idx['backups']) && $idx['backups']) { ?>
                      <button type="button" class='btn btn-xs btn-info' data-toggle="collapse" data-target="#coll-<?= $idx['type'] ?>">+</button>
                    <?php } ?>
                  <?php } ?>

                  </td>
                  <td>
                  <?php if (!isset($idx['error']['reason'])) { ?>
                      <?= $idx['count'] ?>
                  <?php } ?>
                  </td>
                  <td>
                  <?php if (isset($idx['error']['reason'])) { ?>
                      <i class='text-red'><?= $idx['error']['reason'] ?></i>
                  <?php } else { ?>
                    <?php foreach ($cronInfo as $id => $info) { ?>
                      <?php if ($info['type'] == $idx['type'] && $info['task'] == 'importData') { ?>
                       <?php if (isset($info['jobid'])) { ?>
                        <div id="job2-<?= $info['jobid'] ?>">
                        <?php if ($info['status'] == 0) { ?>
                         <small>下次執行時間:<br> <?= $info['nextExecTime'] ?></small>
                        <?php } elseif ($info['status'] == 1) { ?>
                         <i class="fa fa-refresh fa-spin text-green"></i><span class='text-green'> 執行中<span>
                        <?php } elseif ($info['status'] == 2) { ?>
                         <i class='text-blue'>等待中</i>
                        <?php } elseif ($info['status'] == 3) { ?>
                         <i>即將執行</i>
                        </div>
                        <?php } ?>
                       <?php } else { ?>
                        <div>尚未設定排程</div>
                       <?php } ?>
                      <?php } ?>
                    <?php } ?>
                  <?php } ?>
                  </td>
                  <td class="hidden-xs hidden-sm">
                  <?php foreach ($cronInfo as $id => $info) { ?>
                    <?php if ($info['type'] == $idx['type'] && $info['task'] == 'importData') { ?>
                      <?= $info['cronstring'] ?>
                    <?php } ?>
                  <?php } ?>
                  </td>
                  <td class="hidden-xs hidden-sm">
                  <?php if (isset($projects[$project]['data']['import'][$idx['type']])) { ?>
                      <?php $source = $projects[$project]['data']['import'][$idx['type']]; ?>
                      <?php if (!isset($sourceInfo[$source])) { ?>
                        <i class='text-red'><?= $source ?> 不存在</i>
                      <?php } else { ?>
                        <?= $source ?>
                      <?php } ?>
                  <?php } ?>
                  </td>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                  <td>
                  <?php if ($idx['status'] == 404 || isset($idx['error']['reason'])) { ?>
                      <?php if (isset($idx['error']['reason']) && $idx['error']['type'] == 'type_missing_exception') { ?>
                      <div class="btn-group" style="margin:3px">
                           <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" name='type' value='<?= $idx['type'] ?>' onclick="showDeleteIndex('<?= $project ?>', '<?= $idx['type'] ?>')">刪除索引</button>
                      </div>
                      <?php } else { ?>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" onclick="showDeleteSchema('<?= $project ?>', 'index', '<?= $idx['type'] ?>');">刪除設定</button>
                      </div>
                      <?php } ?>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-index" onclick="showEditor('<?= $project ?>', 'index', '<?= $idx['type'] ?>');">修改參數</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-normal" name='type' value='<?= $idx['type'] ?>' onclick="createIndex('<?= $project ?>', '<?= $idx['type'] ?>')">建立索引</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-ask" onclick="showBackups('<?= $project ?>', '<?= $idx['type'] ?>');">設定輪替</button>
                      </div>
                  <?php } else { ?>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-import" onclick="showImport('<?= $idx['type'] ?>');">設定來源</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-index" onclick="showEditor('<?= $project ?>', 'index', '<?= $idx['type'] ?>', 0);">查看參數</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" name='type' value='<?= $idx['type'] ?>' onclick="showDeleteIndex('<?= $project ?>', '<?= $idx['type'] ?>')">刪除索引</button>
                      </div>
                  <?php } ?>
                  </td>
<?php } ?>
                </tr>
                <?php if (isset($idx['backups']) && $idx['backups']) { ?>
                <tr id="coll-<?= $idx['type'] ?>" class="collapse">
                  <td colspan=6>
                    <table class="table table-striped" style="border-color: white; background-color: white;">
                      <?php foreach ($idx['backups'] as $key => $value) { ?>
                        <tr>
                          <td class='col-md-2' style="border-top-color:white;">
                    <?php if (isset($idx['alias']) && $idx['alias'] == $key) { ?>
                          <i class='fa fa-angle-right'></i><b>&nbsp;&nbsp;<?= $key ?></b>
                    <?php } else { ?>
                          &nbsp;&nbsp;<?= $key ?>
                    <?php } ?>
                          </td>
                          <td class='col-md-1' style="border-top-color:white;">
                           <?= $value['count'] ?>
                          </td>
                          <td class='col-md-2' style="border-top-color:white;">
                           <?php if (isset($value['importTime'])) { ?>
                               <small>前次上傳時間:<br> <?= $value['importTime'] ?></small>
                           <?php } ?>
                          </td>
                          <td class='col-md-2' style="border-top-color:white;"></td>
                          <td class='col-md-2' style="border-top-color:white;"></td>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                          <td class='col-md-3' style="border-top-color:white;">&nbsp;
                  <?php if (isset($idx['alias'])) { ?>
                    <?php if ($idx['alias'] != $key) { ?>
                          <button type="button" class="btn btn-xs btn-primary" onclick="switchAlias('<?= $key ?>');" <?php if ($value['count'] == 0) { ?> disabled <?php } ?> >切換</button>
                    <?php } ?>
                  <?php } ?>
                          </td>
<?php } ?>
                         </tr>
                      <?php } ?>
                    </table>
                   </td>
                </tr>
                <?php } ?>
                <?php } ?> 
                </tbody>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        </div>
        <!-- /.modal -->
     <div class="box-header with-border">
      <h3 class="box-title">
        搜尋腳本
      </h3>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
      <div class="box-tools pull-right">
          <button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-index" onclick="showEditor('<?= $project ?>', 'search', '+');">新增腳本</button>
      </div>
<?php } ?>
     </div>
        <div class="">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
              <table class="table">
               <thead>
                <tr>
                  <th class='col-md-9 col-xs-9'>腳本</th>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                  <th class='col-md-3 col-xs-3'>動作</th>
<?php } ?>
                </tr>
               </thead>
               <tbody>
               <?php foreach ($scriptInfo as $name => $value) { ?>
                <tr>
                  <td>
                  <?php if (isset($schemaInfo[$name]['type']) && isset($schemaInfo[$name]['json'])) { ?>
                    <a href="<?= $this->url->get(['for' => 'search-project-script', 'project' => $project, 'scriptId' => $name]) ?>"><?= $name ?></a></td>
                  <?php } else { ?>
                    <?= $name ?>
                  <?php } ?>
                  </td>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                  <td>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-index" onclick="showEditor('<?= $project ?>', 'search', '<?= $name ?>');">修改腳本</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" onclick="showDeleteSchema('<?= $project ?>', 'search', '<?= $name ?>');">刪除腳本</button>
                      </div>
                  </td>
<?php } ?>
                </tr>
               <?php } ?> 
               </tbody>
              </table>
            </div>
          </div>
          <!-- /.box -->
        </div>
        <!-- /.modal -->

<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
     <div class="box-header with-border">
      <h3 class="box-title">
        設定資料源
      </h3>
      <div class="box-tools pull-right">
          <button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-source" onclick="showNewSource()">新增來源</button>
      </div>
     </div>

        <div class="">
          <div class="box">
           <div class="box-body">
              <table class="table">
                <thead>
                <tr>
                  <th class='col-md-2 col-xs-9'>名稱</th>
                  <th class='hidden-xs'>參數</th>
                  <th class='col-md-3 col-xs-3'>動作</th>
                </tr>
                </thead>
                <tbody>
               <?php foreach ($sourceInfo as $sourcename => $source) { ?>
                <tr>
                  <td><?= $sourcename ?></td>
                  <td class='hidden-xs'>
                      <div class='box'>
                        <div class='box-title bg-gray-light col-md-12'>dsn</div>
                        <div class='box-body'><?php if (isset($source['dsn'])) { ?><?= $source['dsn'] ?> <?php } ?></div>
                        <div class='box-title bg-gray-light col-md-12'>sql</div>
                        <div class='box-body' style="max-height: 100px; overflow-x: hidden;"><?php if (isset($source['sql'])) { ?><?= $source['sql'] ?> <?php } ?></div>
                        <div class='box-title bg-gray-light col-md-12'>filter</div>
                        <div class='box-body' style="max-height: 100px; overflow-x: hidden;"><?php if (isset($source['filter'])) { ?><?= $source['filter'] ?> <?php } ?></div>
                      </div>
                  </td>
                  <td>
                    <div class="btn-group" style="margin:3px">
                       <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-source" onclick="showEditSource('<?= $sourcename ?>')">修改設定</button>
                    </div>
                    <div class="btn-group" style="margin:3px">
                       <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" onclick="showDeleteSchema('<?= $project ?>', 'source', '<?= $sourcename ?>')">刪除設定</button>
                    </div>
                  </td>
                </tr>
                <?php } ?>
                </tbody>
              </table>
            </div>
          </div>
          <!-- /.box -->
        </div>
<?php } ?>
        <!-- /.modal -->
     <div class="box-header with-border">
      <h3 class="box-title">
        定期排程
      </h3>
      <div class="box-tools pull-right">
          <!-- button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-source" onclick="showNewCron()">新增排程</button -->
      </div>
     </div>

        <div class="">
          <div class="box">
           <div class="box-body">
              <table class="table">
                <thead>
                <tr>
                  <th class='col-md-2 col-xs-3'>名稱</th>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                  <th class='col-md-2 hidden-xs hidden-sm'>參數</th>
<?php } ?>
                  <th class='col-md-2 hidden-xs hidden-sm'>排程 (<a href="https://www.wikiwand.com/zh-hant/Cron" target="_cron"><small>參考說明</small></a>)</th>
                  <th class='col-md-1 col-xs-3'>狀態</th>
                  <th class='col-md-2 hidden-xs hidden-sm'>時間</th>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                  <th class='col-md-3 col-xs-3'>動作</th>
<?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($cronInfo as $id => $info) { ?>
                <tr>
                  <td><?= $info['task'] ?><br><small><?= $info['desc'] ?></small></td>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                  <td class='hidden-xs hidden-sm'>
                      <?php foreach ($info['data']['parameter'] as $name => $value) { ?>
                          <?php if ($value == ':TYPE:') { ?>
                              type = <?= $info['type'] ?><br>
                          <?php } elseif ($name != 'project') { ?>
                              <?= $name ?> = <?= $value ?><br>
                          <?php } ?>
                      <?php } ?>
                  </td>
<?php } ?>
                  <td class='hidden-xs hidden-sm'><?= $info['cronstring'] ?></td>
                  <?php if (isset($info['jobid'])) { ?>
                  <td id="job-<?= $info['jobid'] ?>">
                      <?php if ($info['status'] == 0) { ?>
                         準備中 
                      <?php } elseif ($info['status'] == 1) { ?>
                         <i class="fa fa-refresh fa-spin text-green"></i><span class='text-green'> 執行中<span>
                      <?php } elseif ($info['status'] == 2) { ?>
                         <i class='text-blue'>等待中</i>
                      <?php } elseif ($info['status'] == 3) { ?>
                         <i>即將執行</i>
                      <?php } ?>
                  </td>
                  <?php } else { ?>
                  <td>尚未設定</td>
                  <?php } ?>
                  <td class='hidden-xs hidden-sm'>
                    <?php if ($info['lastExecTime']) { ?>
                      <small class='text-black'>上次執行時間:<br> <?= $info['lastExecTime'] ?></small><br>
                    <?php } ?>
                    <?php if ($info['nextExecTime']) { ?>
                      <small class='text-black'>下次執行時間:<br> <?= $info['nextExecTime'] ?></small>
                    <?php } ?>
                  </td>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
                  <td>
                    <div class="btn-group" style="margin:3px">
                    <input type="checkbox" <?php if ($info['active'] != 0) { ?> checked <?php } ?> data-toggle="toggle" data-size="mini" data-on="啟用" data-off="關閉" onchange="activeCronjob(<?= $id ?>, this)">
                    </div>
                    <div class="btn-group" style="margin:3px">
                       <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-cronjob" onclick="showEditCronjob(<?= $id ?>)"> 修改參數</button>
                    </div>
                    <div class="btn-group" style="margin:3px">
                       <button type="button" class="btn btn-xs btn-default <?php if (!isset($info['jobid'])) { ?> disabled <?php } ?>" onclick="runJob(<?= $id ?>)"> 立即執行</button>
                    </div>
                    <?php if (isset($info['jobid'])) { ?>
                    <div class="btn-group" style="margin:3px">
                    <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal-log" onclick="showLog(<?= $id ?>)"> 查看紀錄</button>
                    </div>
                    <?php } ?>
                  </td>
<?php } ?>
                </tr>
                <?php } ?>
                </tbody>
              </table>
           </div>
          </div>
        </div>
        <!-- /.modal -->

        <div class="modal modal-default fade" id="modal-import">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 "modal-title">設定資料源</h4>
              </div>
              <div class="modal-body text-center">
                     <input type='hidden' name='indextype' class='form-control' value=''>
                     <select id="import" class='form-control'>
                        <option value="">--</option>
                      <?php foreach ($sourceInfo as $key => $value) { ?>
                        <option value="<?= $key ?>"><?= $key ?></option>
                      <?php } ?>
                      </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn bg-gray pull-left" data-dismiss="modal">取消</button>
                <button type="submit" class="btn pull-right bg-blue" onclick="saveImport();">儲存</button>
              </div>
            </div>
          </div>
        </div>
        <!-- /.modal -->

        <div class="modal modal-default fade" id="modal-source">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id='modal-source-title'>設定資料</h4>
              </div>
              <div class="modal-body text-center">
              <table class="table">
                <tr>
                  <td>名稱</td>
                  <td class='col-md-9'><input type='text' name='sourcename' class='form-control' value=''>
                  <input type='hidden' name='oldkey' class='form-control' value=''></td>
                  <td></td>
                </tr>
                <tr>
                  <td>dsn</td>
                  <td><textarea class="form-control" id='dsn' rows='2'></textarea></td>
                </tr>
                <tr>
                  <td>username</td>
                  <td><input type='text' name='username' class='form-control' value=''></td>
                </tr>
                <tr>
                  <td>password</td>
                  <td><input type='text' name='password' class='form-control' autocomplete='new-password' value=''></td>
                </tr>
                <tr>
                  <td>sql</td>
                  <td><textarea class="form-control" id='sql' rows='3'></textarea></td>
                </tr>
                <tr>
                  <td>filter</td>
                  <td>
                     <select id="filter" class='form-control'>
                        <option value="">--</option>
                      <?php foreach ($importFilters as $value) { ?>
                        <option value="<?= $value ?>"><?= $value ?></option>
                      <?php } ?>
                      </select>
                  </td>
                </tr>
                </tr>
              </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn bg-gray pull-left" data-dismiss="modal">取消</button>
                <button type="submit" class="btn pull-right bg-blue" onclick="saveSource();">儲存</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <div class="modal modal-default fade" id="modal-cronjob">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id='modal-cronjob-title'>設定排程</h4>
              </div>
              <div class="modal-body">
              <table id='table-param' class='table'>
                  <th class="col-md-2">名稱</th><th class="col-md-10">內容</th>
              </table>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn bg-gray pull-left" data-dismiss="modal">取消</button>
                <button type="submit" class="btn pull-bluebg-red" onclick="saveCronjob();">儲存</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
        <div class="modal modal-normal fade" id="modal-ask">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header" id='div-header'>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 id='the-title' class="modal-title"></h4>
              </div>
              <div class="modal-body text-center">
                <h4 id="the-body"></h4>
                <div id="div-saveBackups">
                  <div class="input-group form-group">
                      <span class="input-group-addon"><b>輪替數量</b></span>
                      <input type="text" class="form-control" placeholder="0 表示不做輪替" name="backups" value="0" size="1">
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button id='button-deleteIndex' type="submit" class="btn pull-right bg-orange" data-dismiss="modal" onclick="deleteIndex();">刪除</button>
                <button id='button-deleteSchema' type="submit" class="btn pull-right bg-red" data-dismiss="modal" onclick="deleteSchema();">刪除</button>
                <button id='button-saveBackups' type="submit" class="btn pull-right bg-blue" data-dismiss="modal" onclick="saveBackups();">儲存</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
        <div class="modal modal-normal fade" id="modal-index">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="edit-label"></h4><span style="border: 3px solid" class="pull-right bg-red" id='project-warning'></span>
              </div>
              <div class="modal-body">
                  <!-- label class='box-header' id='edit-label'></label -->
                  <input type="hidden" name="project" value="">
                  <input type="hidden" name="type" value="">
                  <div class="input-group form-group" id='tagname'>
                      <span class="input-group-addon"><b>名稱</b></span>
                      <input type="text" class="form-control" placeholder="請輸入英文數字" name="key">
                  </div>
                  <div id="div-index">
                    <div class="form-group">
                      <label>settings: (參見: <a href="https://www.elastic.co/guide/en/elasticsearch/reference/current/index-modules.html#index-modules-settings" target="help">官方文件</a>)</label>
                      <textarea name='value' id='edit-settings' class="form-control" rows="3" placeholder="請輸入內容(JSON)"></textarea>
                    </div>
                    <div class="form-group">
                      <span>mappings:</span>
                      <textarea name='value' id='edit-mappings' class="form-control" rows="8" placeholder="請輸入內容(JSON)"></textarea>
                    </div>
                  </div>
                  <div id="div-search">
                    <div class="form-group">
                      <label>scripts: (參見: <a href="https://www.elastic.co/guide/en/elasticsearch/reference/5.6/search-template.html" target="help">官方文件</a>)</label>
                      <textarea name='value' id='edit-scripts' class="form-control" rows="8" placeholder="請輸入內容(mustache)"></textarea>
                    </div>
                  </div>
                  <div id="div-schema">
                    <div class="input-group form-group">
                      <span class="input-group-addon"><b>使用索引</b></span>
                     <select id="edit-type" class="form-control">
                      <?php foreach ($indexInfo as $idx) { ?>
                        <?php if (!isset($idx['error']['reason'])) { ?>
                        <option value="<?= $idx['type'] ?>" <?php if (isset($source['type']) && $source['type'] == $idx['type']) { ?>selected<?php } ?>><?= $idx['type'] ?></option>
                        <?php } ?>
                      <?php } ?>
                     </select>
                    </div>
                    <div class="form-group">
                      <label>jsonschema:</label>
                      <textarea name='value' id='edit-json' class="form-control" rows="8" placeholder="請輸入內容(JSON)"></textarea>
                    </div>
                    <div class="form-group">
                      <label>ui:</label>
                      <textarea name='value' id='edit-ui' class="form-control" rows="8" placeholder="請輸入內容(CSS)"></textarea>
                    </div>
                  </div>
              </div>
              <div class="modal-footer">
                <button id='button-cancel' type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button id='button-save' type="submit" class="btn pull-right bg-blue" onclick="updateSchema();">儲存</button>
                <button id='button-close' type="button" class="btn pull-right bg-gray" data-dismiss="modal">關閉</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
        <div class="modal modal-normal fade" id="modal-log">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header" id='div-header'>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 id='the-title' class="modal-title"></h4>
              </div>
              <div class="modal-body">
                 <pre class="bg-gray" style="text-align:left; border: 0; height:450px; overflow: auto;" id="log-body"></pre>
              </div>
              <div class="modal-footer">
                <button id='button-close' type="button" class="btn pull-right bg-gray" data-dismiss="modal">關閉</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
<script>
var project = "<?= $project ?>";
var projects = <?= $projectJson ?>;
var sourceInfo = projects[project].data.source;
var indexInfo  = projects[project].data.index;
var scriptInfo = projects[project].data.search;
var schemaInfo = projects[project].data.schema;
var cronInfo = <?= $cronInfoJson ?>;
var cronDefault = <?= $cronDefaultJson ?>;

window.onload = function () {
    updateStatus();
}

var currentLogIndex = null;
var cacheStr = "";
function showLog(index, offset)
{
  // console.log({ index: index, offset: offset} );
  if (index != currentLogIndex) {
      cacheStr = "";
      $( "#log-body" ).empty();
  }

  if (offset) {
    log = cronInfo[index]['log'];
    var str = log.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    if (str != cacheStr) {
        // console.log({ str: str} );
        $( "#log-body" ).empty().append(str);
        cacheStr = str;
        var height = $("#log-body").height() * 200;
        $( "#log-body" ).scrollTop(height);
    }
    return;
  }

  currentLogIndex = index;
  $('#modal-log').on('shown.bs.modal', function () {
    // $( "#log-body" ).empty();
    var log = '';
    if (typeof cronInfo[index]['log'] == "undefined") {
        getJobByIndex(index);
    }
    log = cronInfo[index]['log'];
    var str = log.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

    if (str != cacheStr) {
        // console.log({ index: index, offset: offset} );
        // console.log({ str: str} );
        $( "#log-body" ).empty().append(str);
        cacheStr = str;
        var height = $("#log-body").height() * 200;
        $( "#log-body" ).scrollTop(height);
    }
  });
}

// call [POST] /api/project/{project}/job/{jobId}
function getJobByIndex(index, offset = 0)
{
    var jobid = cronInfo[index]['jobid'];
    var url = '<?= $this->url->get('api/project/') ?>' + project + "/job/" + jobid;
    var payload = {
        'offset': offset
    }

    callAjax('POST', url, payload, function(xhr) {
        cronInfo[index]['status'] = xhr['result']['status'];
        log = xhr['result']['log'];
        if (log != "") {
            if (typeof cronInfo[index]['log'] == "undefined") {
                cronInfo[index]['log'] = "";
            }
            cronInfo[index]['log'] += log;
        }
        cronInfo[index]['offset'] = xhr['result']['offset'];
    });
}

function updateStatusText(jobid, status)
{
    var text = "";
    var text2 = "";
    if (status == 0) {
       text="準備中";
    }
    if (status == 1) {
       text="<i class='fa fa-refresh fa-spin text-green'></i><span class='text-green'> 執行中<span>";
    }
    if (status == 2) {
       text = "<i class='text-blue'>等待中</i>";
    }
    if (status == 3) {
       text = "<i>立即</i>";
    }

    if (status == 0) {
       cronInfo.forEach(function(element, index) {
           if (element.jobid == jobid) {
               text2="<small>下次執行時間:<br>" + element['nextExecTime'] + "</small>";
               return;
           }
       });
    } else {
       text2 = text;
    }

    // console.log({ jobid: jobid, status: status, text: text} );
    $( "#job-"  + jobid ).html(text);
    $( "#job2-"  + jobid ).html(text2);
}

// call [POST] /api/project/{project}/job/{jobId}
function updateStatus()
{
    var flag = false;
    cronInfo.forEach(function(element, index) {
        if (element.status == 1 || element.status == 3) {
            var offset = 0;
            if (typeof element.offset !== "undefined") {
               offset = element.offset;
            }

            // console.log(element);

            getJobByIndex(index, offset);
            if (currentLogIndex == index) {
                showLog(index, offset);
            }

            if (cronInfo[index]['status'] == 1 || element.status == 3) {
                flag = true;
            }
            updateStatusText(cronInfo[index]['jobid'], cronInfo[index]['status']);
        }
    });

    if (flag) {
        setTimeout('updateStatus()', 1000);
    }
}

// call [PUT] /api/project/{project}/mapping/{type}
function createIndex(project, type )
{
    var url = '<?= $this->url->get('api/project/') ?>' + project + "/mapping/" + type;
    callAjax('PUT', url, undefined, function() {
        alert("建立成功" );
        location.reload();
    });
}

function showNewSource()
{
  $( "#modal-source-title" ).empty().append('建立新的資料源');
  $( "input[type=text][name=sourcename]" ).val('');
  $( "input[type=hidden][name=oldkey]" ).val('');
  $( "textarea#dsn" ).val('');
  $( "input[type=text][name=username]" ).val('');
  $( "input[type=text][name=password]" ).val('');
  $( "textarea#sql" ).val('');
  $( "#filter option" ).each(function(i, item) {
    $(item).attr('selected', false);
  });
}

function showEditSource(sourcename)
{
  var info = sourceInfo[sourcename];
  $( "#modal-source-title" ).empty().append('修改現有資料源');
  $( "input[type=text][name=sourcename]" ).val(sourcename);
  $( "input[type=hidden][name=oldkey]" ).val(sourcename);
  $( "textarea#dsn" ).val(info.dsn);
  $( "input[type=text][name=username]" ).val(info.username);
  $( "input[type=text][name=password]" ).val(info.password);
  $( "textarea#sql" ).val(info.sql);
  $( "#filter option").each(function(i, item) {
    if($(item).val() == info.filter) {
        $(item).attr('selected', true);
    }
  });
}

function showImport(type)
{
  var source = projects[project]['data']['import'][type];
  $( "input[type=hidden][name=indextype]" ).val(type);

  $('#import option').each(function(i, item) {
    if($(item).val() == source) {
        $(item).attr('selected', true);
    } else {
        $(item).attr('selected', false);
    }
  });
}

function appendSelectParam(key, value, arr) {
    var select = $("<select class='form-control' id='v-" + key + "'></select>");

    // jQuery.each(projects[project]['data']['index'], function (key2, value) {
    jQuery.each(arr, function (key2, value2) {
        var option;
        if (value == key2) {
            option = $("<option value='" + key2 + "' selected>"+ key2+"</option>");
        } else {
            option = $("<option value='" + key2 + "'>"+ key2 +"</option>");
        }
        select.append(option);
    });
    return select;
}

function appendParam(key, value, placeholder = '') {
    var tr  = $("<tr></tr>"); // _tr_.clone();
    var td1 = $("<td class='col-md-2' id='k-" + key + "'></td>");
    var td2 = $("<td class='col-md-10'></td>");
    var form;
    var text = key;

    if (key == 'type') {
        form = appendSelectParam(key, value, indexInfo);
        text = "索引";
    } else if (key == 'script') {
        form = appendSelectParam(key, value, scriptInfo);
        text = "腳本";
    } else {
        if (key == 'cronstring') {
            text = "排程";
        }
        form = $("<input type='text' class='form-control' id='v-" + key + "' placeholder='" + placeholder +"'>");
        form.val(value);
    }
    td1.text(text);
    td2.append(form);

    tr.append(td1);
    tr.append(td2);
    $( "#table-param" ).append(tr);
}

var cronId;
function showEditCronjob(id)
{
    var info = cronInfo[id];
    var task = info['task'];
    cronId = id;

    // 清除舊資料
    $( "#table-param td" ).remove();

    jQuery.each(info['data']['parameter'], function (key, value) {
        var placeholder='';
        if ($.inArray(key, cronDefault[task]['data']['hidden']) != -1) {
            return;
        }
        if (typeof cronDefault[task]['data']['placeholder'] != "undefined") {
            if (typeof cronDefault[task]['data']['placeholder'][key] == "string") {
                placeholder = cronDefault[task]['data']['placeholder'][key];
            }
        }
        appendParam(key, value, placeholder);
    });
    appendParam('cronstring', info['cronstring']);
}

// call [PUT] /api/project/{project}/cronjob/{task}
function saveCronjob()
{
    var info = cronInfo[cronId];
    var task = info['task'];
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/cronjob/' + info['task'];
    var param = {};

    jQuery.each(info['data']['parameter'], function (key, value) {
        if ($.inArray(key, cronDefault[task]['data']['hidden']) != -1) {
            return;
        }
        param[key] = $( "#v-" + key ).val();
    });

    var payload = {
        'cronstring': $("#v-cronstring").val(),
        'type': info['type'],
        'param': param
    }

    callAjax('PUT', url, payload, function() {
        alert("儲存成功" );
        location.reload();
    });
}

// call [POST] /api/project/{project}/cronjob/{task}/{jobid}
function runJob(id)
{
    var info = cronInfo[id];
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/cronjob/' + info['task'] + "/run";

    var payload = {
        'jobid': info['jobid']
    }

    callAjax('POST', url, payload, function(xhr) {
        alert(xhr.message);
        location.reload();
    });
}

// call [PUT] /api/project/{project}/cronjob/{task}/active
function activeCronjob(id, element)
{
    var info = cronInfo[id];
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/cronjob/' + info['task'] + '/active';
    var active = 0;

    if (element.checked) {
        active = 1;
    }

    var payload = {
        'active': active,
        'type': info['type']
    }

    callAjax('PUT', url, payload, function(xhr) {
        alert(xhr.message);
        // location.reload();
    });
}

// call [PUT] /api/project/{project}/config/source
function saveSource()
{
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/config/source';
    var key = $( "input[type=text][name=sourcename]" ).val();
    var oldkey = $( "input[type=hidden][name=oldkey]" ).val();
    var data = {
        'dsn': $( "textarea#dsn" ).val(),
        'username': $( "input[type=text][name=username]" ).val(),
        'password': $( "input[type=text][name=password]" ).val(),
        'sql':  $( "textarea#sql" ).val(),
        'filter': $( "#filter").find(":selected").val()
        // 'output': $("#output").find(":selected").val()
    };
    var payload = {};
    payload[key] = data;

    if (oldkey == key) {
        oldkey = '';
    }

    callAjax('PUT', url, payload, function() {
        // 新增成功後，刪除舊的
        if (oldkey != '') {
            var payload = {};
            payload[oldkey] = '';
            callAjax('PUT', url, payload, function() {});
        }
        alert("儲存成功" );
        location.reload();
    });
}

// call [PUT] /api/project/{project}/config/import
function saveImport()
{
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/config/import';
    var indextype = $( "input[type=hidden][name=indextype]" ).val();
    var payload = {};
    payload[indextype] = $("#import").find(":selected").val();

    callAjax('PUT', url, payload, function() {
        alert("儲存成功" );
        location.reload();
    });
}

// call [PUT] /api/project/{project}/config/backup
function saveBackups()
{
    var project = $("input[type=hidden][name=project]").val();
    var indextype = $("input[type=text][name=key]").val();
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/config/backup';
    var payload = {};
    payload[indextype] = $( "input[type=text][name=backups]" ).val();

    callAjax('PUT', url, payload, function() {
        alert("儲存成功" );
        location.reload();
    });
}

function getObject(data)
{
    try {
        if (data != '') {
            return JSON.parse(data);
        }
    } catch(err) {
        return false;
    }
    return '';
}

// call [PUT] /api/project/{project}/cronjob/{task}
function renameSyncMatches(oldkey, newkey)
{
    jQuery.each(cronInfo, function (key, value) {
        if (value['task'] == 'syncMatches' && oldkey == value['data']['parameter']['script']) {
            var url = '<?= $this->url->get('api/project/') ?>' + project + '/cronjob/' + value['task'];
            value['data']['parameter']['script'] = newkey;
            var payload = {
                'cronstring': value['cronstring'],
                'type': "",
                'param': value['data']['parameter']
            }
            callAjax('PUT', url, payload, function() {});
        }
    });
}

// call [PUT] /api/project/{project}/config/{type}
// type = ['index'|'search'|'schema']
function updateSchema()
{
    var project = $("input[type=hidden][name=project]").val();
    var type = $("input[type=hidden][name=type]").val();
    var key = $("input[type=text][name=key]").val();
    var oldkey = $("input[type=hidden][name=oldkey]").val();
    // var value = $("#edit-mappings").val();
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/config/' + type;
    var payload = {};

    if (key == "") {
        alert("未輸入名稱");
        return;
    }

    if (typeof projects[project]['data'][type][key] === "object") {
        if (oldkey != key || oldkey == "") {
            alert("名稱重複");
            return;
        }
    }

    if (type == "index") {
        var settings = getObject($("#edit-settings").val());
        if (settings === false) {
            alert("settings: JSON格式錯誤");
            return;
        }

        var mappings = getObject($("#edit-mappings").val());
        if (mappings === false) {
            alert("mappings: JSON格式錯誤");
            return;
        }

        if (mappings === "") {
            alert("mappings: 內容為空");
            return;
        }

        var keys = Object.keys(mappings);
        if (keys.length != 1 || keys[0] != key) {
            alert("mappings: 物件錯誤，物件只能唯一且名稱需要一致" );
            return;
        }

        // payload[key] = { settings: settings, mappings: mappings };
        payload[key] = {};
        if (settings !== "") {
            payload[key]['settings'] = settings;
        }
        payload[key]['mappings'] = mappings;
    } else if (type == "search") {
        var scripts  = $("#edit-scripts").val();
        if (scripts === "") {
            alert("scripts: 內容為空");
            return;
        }
        payload[key] = scripts;
    } else if (type == "schema") {
        var json = getObject($("#edit-json").val());

        if (json === "") {
            alert("jsonschema: 內容為空");
            return;
        }

        if (json === false) {
            alert("jsonschema: JSON格式錯誤");
            return;
        }

        payload[key] = { json: json };

        var ui = getObject($("#edit-ui").val());
        if (ui !== "") {
            if (ui === false) {
                alert("ui schema: JSON格式錯誤");
                return;
            }

            payload[key]['ui'] = ui;
        }

        var type = $("#edit-type").val();
        if (type === "") {
            alert("type: 沒有對應的索引, 請先建立索引");
            return;
        }
        payload[key]['type'] = type;
    }

    callAjax('PUT', url, payload, function() {
        // 新增成功後，刪除舊的
        if (oldkey != key && oldkey != '') {

           // script 更名會影響到 schema
           if (type == 'search') {
               var url2 = '<?= $this->url->get('api/project/') ?>' + project + '/schema/' + oldkey;
               var payload = {
                 'rename': key
               }
               callAjax('PUT', url2, payload, function() {});

               renameSyncMatches(oldkey, key);
           }

            var payload = {};
            payload[oldkey] = '';
            callAjax('PUT', url, payload, function() {});
        }
        //setTimeout(function() {
        alert("更新成功" );
        location.reload();
        //}, 100);
    });
}

// [PUT] /{project}/config/{type}
function deleteSchema()
{
    var project = $("input[type=hidden][name=project]").val();
    var type = $("input[type=hidden][name=type]").val();
    var key = $("input[type=text][name=key]").val();
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/config/' + type;
    var payload = {};
    payload[key] = '';

    callAjax('PUT', url, payload, function() {
        alert("刪除成功" );
        location.reload();
    });
}

// [DELETE] /{project}/mapping/{schema}
function deleteIndex()
{
    var project = $("input[type=hidden][name=project]").val();
    var schema = $("input[type=text][name=key]").val();
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/mapping/' + schema;

    callAjax('DELETE', url, {}, function() {
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

function showSync(project, name, sync)
{

}

function showModal(project, type, name, save)
{
    $( "#project-warning" ).empty();
    if (type == 'index') {
        var warning = $( "#btn-" + project + "-" + name ).prop("title");
        $( "#project-warning" ).append(warning);
    }

    // $( "#project-name" ).empty().append(project);
    $( "input[type=hidden][name=project]" ).val(project);
    $( "input[type=hidden][name=type]" ).val(type);

    var typename = '';
    if (type == 'index') {
        typename = '索引設定';
    } else if (type == 'search') {
        typename = '搜尋腳本設定';
    } else if (type == 'schema') {
        typename = '搜尋套版設定';
    }

    $( "#edit-label" ).empty().append(typename);
    $( "input[type=text][name=type]" ).val(type);
    $( "input[type=text][name=key]" ).val('');
    $( "input[type=hidden][name=oldkey]" ).val('');
    if (name != "+") {
        $( "input[type=text][name=key]" ).val(name);
        $( "input[type=hidden][name=oldkey]" ).val(name);
    }

    $( "#div-index" ).hide();
    $( "#div-search" ).hide();
    $( "#div-schema" ).hide();
    $( "#div-" + type ).show();

    $( "#button-close" ).hide();
    $( "#button-cancel" ).hide();
        $( "#button-save" ).hide();
    if (save == 0) {
        $( "#button-close" ).show();
    } else {
        $( "#button-cancel" ).show();
        $( "#button-save" ).show();
    }

    $( "#edit-settings" ).val('');
    $( "#edit-mappings" ).val('');
    $( "#edit-mappings" ).attr('rows', 10);
    $( "#edit-scripts" ).val('');
    $( "#edit-scripts" ).attr('rows', 10);
    $( "#edit-type" ).val('');
    $( "#edit-json" ).val('');
    $( "#edit-ui" ).val('');
    $( "#tagname" ).show();
    if (type == 'schema') {
        $( "#tagname" ).hide();
    }
}

function countLength(data, minLength = 10, maxLength = 20)
{
    var length = minLength;

    if (typeof data === "string") {
        var lines = data.split("\n");
        length = lines.length;

        if (length > maxLength) {
            return maxLength;
        }
        if (length < minLength) {
            return minLength;
        }
    }

    return length;
}

function showEditor(project, type, name, save=1)
{
    // callAjax('GET', <?= $this->url->get('api/project/') ?>' + project, null, callback);
    var ret = projects[project];
    var obj = ret.data[type];
    var sync = ret.data['sync'];
    var data = '';

    showModal(project, type, name, save);

    if (name == "+") {
        return;
    }

    if (type == 'index') {
        var settings = "";
        var mappings = "";
        if ((typeof obj[name]['settings'] === "object") && ( obj[name]['settings'] !== null)) {
            settings = JSON.stringify(obj[name]['settings'], undefined, 4);
        } else {
            settings = obj[name]['settings'];
        }
        if ((typeof obj[name]['mappings'] === "object") && ( obj[name]['mappings'] !== null)) {
            mappings = JSON.stringify(obj[name]['mappings'], undefined, 4);
        } else {
            mappings = obj[name]['mappings'];
        }
        $( "#edit-settings" ).val(settings);
        $( "#edit-mappings" ).val(mappings);
        $( "#edit-mappings" ).attr('rows', countLength(mappings));
    } else if (type == 'search') {
        var scripts = obj[name];
        $( "#edit-scripts" ).val(scripts);
        $( "#edit-scripts" ).attr('rows', countLength(scripts));
    } else if (type == 'schema') {
        var json = "";
        var ui = "";

        if ((typeof obj[name] === "undefined")) {
            return;
        }

        if ((typeof obj[name]['type'] !== "undefined")) {
            $("#edit-type").val(obj[name]['type']);
        }

        if ((typeof obj[name]['json'] !== "undefined")) {
            if ((typeof obj[name]['json'] === "object") && ( obj[name]['json'] !== null)) {
                json = JSON.stringify(obj[name]['json'], undefined, 4);
            } else {
                json = obj[name]['json'];
            }
            $( "#edit-json" ).val(json);
        }

        if ((typeof obj[name]['ui'] !== "undefined")) {
            if ((typeof obj[name]['ui'] === "object") && ( obj[name]['ui'] !== null)) {
                ui = JSON.stringify(obj[name]['ui'], undefined, 4);
            } else {
                ui = obj[name]['ui'];
            }
            $( "#edit-ui" ).val(ui);
        }
    }
}

function showBackups(project, indextype)
{
    $("#modal-ask").removeClass("modal-danger");
    $("#modal-ask").addClass("modal-normal");
    $("#button-deleteSchema").hide();
    $("#button-deleteIndex").hide();
    $("#button-saveBackups").show();
    $("#div-saveBackups").show();
    $("#the-body" ).hide();

    // $( "#div-header").removeClass("bg-red");
    $( "#div-header").addClass("bg-light-blue");
    $( "#the-title" ).empty().append("<b>設定多份資料輪替：</b><h5><ul><li>每一個備份會額外占用一份空間</li><li>資料匯入時會選擇 1.空的索引 或 2.最舊的索引刪除後新增</li><li>匯入成功後會自動切換到新的索引</li><li>已建立的索引必須刪除後才能設定</li></h5>");
    $( "#the-body" ).empty().append(indextype);
    $( "input[type=hidden][name=project]" ).val(project);
    $( "input[type=text][name=key]" ).val(indextype);

    var backup = 0;
    if (typeof projects[project]['data']['backup'][indextype]['count']  !== "undefined") {
        backup = projects[project]['data']['backup'][indextype]['count'];
    }
    $( "input[type=text][name=backups]" ).val(backup);
}

function showDeleteSchema(project, type, name)
{
    $("#modal-ask").removeClass("modal-danger");
    $("#modal-ask").addClass("modal-normal");
    $("#button-deleteSchema").show();
    $("#button-deleteIndex").hide();
    $("#button-saveBackups").hide();
    $("#div-saveBackups").hide();
    $("#the-body" ).show();

    $( "#div-header").removeClass("bg-light-blue");
    // $( "#div-header").addClass("bg-red");
    $( "#the-title" ).empty().append("<i class='text-red'>確定要刪除？</i>");
    $( "#the-body" ).empty().append(name);
    $( "input[type=hidden][name=project]" ).val(project);
    $( "input[type=hidden][name=type]" ).val(type);
    $( "input[type=text][name=key]" ).val(name);
}

function showDeleteIndex(project, name)
{
    $("#modal-ask").removeClass("modal-normal");
    $("#modal-ask").addClass("modal-danger");
    $("#button-deleteIndex").show();
    $("#button-deleteSchema").hide();
    $("#button-saveBackups").hide();
    $("#div-saveBackups").hide();
    $("#the-body" ).show();

    $( "#the-title" ).empty().append("確定要刪除? 所有資料將會清空");
    $( "#the-body" ).empty().append(name);
    $( "input[type=hidden][name=project]" ).val(project);
    $( "input[type=hidden][name=type]" ).val('index');
    $( "input[type=text][name=key]" ).val(name);
}

// [POST] /api/project/{project}/alias
function switchAlias(alias)
{
    var res = alias.split("_");
    if (res[0] != project) {
        alert("資料錯誤");
        return;
    }

    var url = '<?= $this->url->get('api/project/') ?>' + project + '/alias/';
    var payload = {
        'type': res[1],
        'id': res[2]
    }

    callAjax('POST', url, payload, function() {
        alert("修改成功" );
        location.reload();
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
