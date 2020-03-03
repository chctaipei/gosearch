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

          
<script>
var mappingInfo = <?= $mappingInfoJson ?>;
var project = "<?= $project ?>";
var type = "<?= $type ?>";
</script>
      <h1>
        文件管理
      </h1>
      <!-- ol class="breadcrumb">
        <li><a href="<?= $this->url->get(['for' => 'admin-projects']) ?>"><i class="fa fa-pencil-square-o"></i> 專案管理</a></li>
        <li><a href="<?= $this->url->get(['for' => 'admin-project-name', 'project' => $project]) ?>"><?= $project ?></a></li>
        <li class="active"><?= $type ?></li>
      </ol -->


        </section>

        <!-- Main content -->
        <section class="content">

          
        <div class="row">
            <div class="col-md-3">
              <div class="input-group input-group-sm">
                <input type="hidden" name="project" value="" form="iform">
                <input type="hidden" name="type" value="" form="iform">
                <input type="text" class="form-control" placeholder="請輸入文件編號" name="docId" form="iform">
                <span class="input-group-btn">
                <button type="button" class="btn btn-info btn-flat" onclick="searchDoc($('input[type=text][name=docId]').val())">搜尋</button></span>
              </div>
            </div>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
            <div class="col-md-9 text-right">
              <div class="input-group input-group-sm">
                <span class="input-group-btn">
                <button type="button" class="btn btn-primary btn-flat" onclick="newDoc()">新增文件</button></span>
              </div>
            </div>
<?php } ?>
        </div>

        <div class='hide' id='doc'>
            <div class="row"><br></div>
<?php if (isset($auth['level']) && $auth['level'] == 0) { ?>
            <div class="input-group input-group-sm"> <span class="input-group-btn"> <button id='updateBtn' type="button" class="btn btn-flat disabled" onclick="saveDoc()">儲存</button> </span></div>
<?php } ?>
            <div class="box">
              <div class="box-body no-padding">
                <table id='table-doc' class='table table-bordered table-hover'>
                  <th class="col-md-1">欄位</th><th class="col-md-11">內容</th>
                </table>
              </div>
            </div>
              <div class="text-right">
                  <i><span class="bg-green">綠底色</span>為陣列，<span class="bg-red">紅底色</span>為物件，<span class="bg-yellow">橘底色</span>為字串，儲存前時請確認型態是否正確</i>
              </div>
        </div>
<script>
window.onload = function () {
  $.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);

    if (results == null) {
        return null;
    }
    return results[1] || null;
  }

  var docId = $.urlParam('docId');
  if (docId) {
      docId = decodeURIComponent(docId);
      $( "input[type=text][name=docId]" ).val(docId);
      searchDoc(docId);
  }
}

function prepareTable() {
    $("tr").remove();
}

function appendNewDoc() {
    var tr = $("<tr><td>文件編號</td><td><span><input type='text' class='form-control' name='newid' placeholder='若輸入已存在的文件編號，將會取代原始文件'></td></tr>");
    $( "#table-doc" ).append(tr);
}

function appendRow(key, value, disabled='', placeholder='') {
    var tr  = $("<tr></tr>"); // _tr_.clone();
    var td1 = $("<td class='col-md-1 hidden-xs' id='k-" + key + "'></td>");
    var td2 = $("<td class='col-md-11'></td>");
    var input = $("<div class='visible-xs-block'>"+key+"</div><input type='text' class='form-control' id='v-" + key + "' "  + disabled + "  placeholder='" + placeholder + "'>");

    td1.text(key);
    input.val(value);
    td2.append(input);
    tr.append(td1);
    tr.append(td2);
    $( "#table-doc" ).append(tr);
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
        200: function(data) {
          callback(data);
        },
        404: function(xhr) {
           alert("未找到資料");
           return;
        }
      },
      error: function(xhr) {
         var error = JSON.parse(xhr.responseText);
         $( '#doc' ).addClass('hide');
         if ((typeof error.message === "string")) {
             alert(error.message);
             return;
         }
      }
    });
}

var docId;
var doc;
function searchDoc(inputId)
{
    $( "#updateBtn" ).addClass('disabled');
    $( "#updateBtn" ).removeClass('btn-warning');
    prepareTable();

    if (inputId == '') {
        return; 
    }

    var url = '<?= $this->url->get('api/document/') ?>' + project + '/' + type + '/' + inputId;
    callAjax('GET', url, null, function(data) {
        var ret = data;
        if (typeof data == "string") {
           ret = JSON.parse(data);
        }

        var source = ret.result._source;
        docId = ret.result._id;;
        doc = {};

        // 列出資料
        jQuery.each(source, function (key, value) {
            var disabled = '';
            var placeholder = '';
            if ((value === null)) {
                value = '';
            }

            if (typeof value === "object") {
                value = JSON.stringify(value);
                // disabled = 'disabled';
            }

            doc[key] = value;

            if (typeof mappingInfo[key] === "object" ) {
                if (typeof mappingInfo[key].format === "string" ) {
                    placeholder = mappingInfo[key].format;
                } else {
                    placeholder = mappingInfo[key].type;
                }
            } else {
                placeholder = "未對應";
                disabled = 'disabled';
            }
<?php if (!isset($auth['level']) || $auth['level'] != 0) { ?>
                disabled = 'disabled';
<?php } ?>

            appendRow(key, value, disabled, placeholder);
        });

        jQuery.each(mappingInfo, function (key, value) {
<?php if (!isset($auth['level']) || $auth['level'] != 0) { ?>
                var disabled = 'disabled';
<?php } else { ?>
                var disabled = '';
<?php } ?>
            if (typeof source[key] == "undefined" ) {
                if (typeof value.format === "string" ) {
                    placeholder = value.format;
                } else {
                    placeholder = value.type;
                }
                doc[key] = '';
                appendRow(key, '', disabled, placeholder);
            }
        });

        $( "#doc" ).removeClass('hide');
        // $('input').on('change', checkModified);
        $('input').on('keyup', checkModified);
    });
}

function newDoc()
{
    $( "#updateBtn" ).addClass('disabled');
    $( "#updateBtn" ).removeClass('btn-warning');
    prepareTable();
    appendNewDoc();

    doc = {};
    jQuery.each(mappingInfo, function (key, value) {
        var placeholder = '';
        if (typeof value.format === "string" ) {
            placeholder = value.format;
        } else {
            placeholder = value.type;
        }

        doc[key] = '';
        appendRow(key, '', '', placeholder);
    });

    $( "#doc" ).removeClass('hide');
    // $('input').on('change',function(){ checkModified(); });
    $('input').on('keyup', checkModified);
}

function getObject(data)
{
    try {
        return JSON.parse(data);
    } catch(err) {
        return false;
    }
}

function isArray(value){  
    return typeof value === 'object' && value.constructor === Array;
}

function checkModified() {
    var flag = false;
    var changed = {};

    jQuery.each(doc, function (key, value) {
        var check = $( "#v-" + key ).text();
        if (check == "") {
            check = $( "#v-" + key ).val();
        }

        if ((typeof value === "object")) {
            value = JSON.stringify(value);
        }

        if ((value === "null")) {
            value = '';
        }

        if (check != value) {
            flag = true;
            data = getObject(check);
            if (data && !jQuery.isNumeric(data)) {
                check = data;

                // object
                if (!isArray(data)) {
                    $( "#v-" + key ).removeClass('bg-yellow');
                    $( "#v-" + key ).removeClass('bg-green');
                    $( "#v-" + key ).addClass('bg-red');
                // array
                } else {
                    $( "#v-" + key ).removeClass('bg-red');
                    $( "#v-" + key ).removeClass('bg-yellow');
                    $( "#v-" + key ).addClass('bg-green');
                }
            } else {
                // string
                $( "#v-" + key ).removeClass('bg-red');
                $( "#v-" + key ).removeClass('bg-green');
                $( "#v-" + key ).addClass('bg-yellow');
            }

            changed[key] = check;
        } else {
            $( "#v-" + key ).removeClass('bg-red');
            $( "#v-" + key ).removeClass('bg-yellow');
            $( "#v-" + key ).removeClass('bg-green');
        }
    });

    if (flag) {
        $( "#updateBtn" ).removeClass('disabled');
        $( "#updateBtn" ).addClass('btn-warning');
        return changed;
    } else {
        $( "#updateBtn" ).addClass('disabled');
        $( "#updateBtn" ).removeClass('btn-warning');
        return false;
    }
}

// [PUT|POST] /api/document/{project}/{type}/{docId}
function saveDoc()
{
    var payload = checkModified();
    if (payload == false) {
        return;
    }

    var method = 'PUT';
    if (docId == 0) {
        var newId = $( "input[type=text][name=newid]" ).val();
        if (newId != "") {
            docId = newId;
        }
        method = 'POST';
    }
    var encodeId = encodeURI(docId);

    var url = '<?= $this->url->get('api/document/') ?>' + project + '/' + type + '/' + encodeId;
    callAjax(method, url, payload, function(data) {
        var ret = data;
        if (typeof data == "string") {
           ret = JSON.parse(data);
        }
        alert(ret.result.result);
        searchDoc(docId);
        $( "input[type=text][name=docId]" ).val(docId);
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
