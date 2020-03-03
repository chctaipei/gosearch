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
        搜尋文件
      </h1>
      <!-- ol class="breadcrumb">
        <li><a href="<?= $this->url->get(['for' => 'admin-projects']) ?>"><i class="fa fa-search"></i> 搜尋</a></li>
        <li><a href="<?= $this->url->get(['for' => 'admin-project-name', 'project' => $project]) ?>"><?= $project ?></a></li>
        <li class="active"><?= $scriptId ?></li>
      </ol -->


        </section>

        <!-- Main content -->
        <section class="content">

          
        <div class="box-header with-border">
          <h1 class="box-title"></h1>
          <div class="box-tools pull-right">
          </div>
        </div>

<nav class="navbar navbar-default navbar-fixed-bottom visible-xs-block">
    <div class="navbar-header bg-primary">
      <a type="button" class="navbar-toggle btn btn-xs" data-toggle="collapse" data-target="#search-collapse" href="#app">
        <span class=" glyphicon glyphicon-search"></span>
      </a>
    </div>
</nav>

      <div class="row">
        <div class="col-md-3 col-sm-4">
         <div class="collapse navbar-collapse" id="search-collapse" style='padding-left:0px; padding-right:0px'>
          <div class="box box-solid col-md-12 no-padding">
              <div class="box-body table-responsive" style="overflow-x: hidden;"> 
                  <div id="search-form"></div>
              </div>
          </div>
         </div>
        </div>

        <!-- div class="col-md-9" style="width: calc(100% - 250px);" -->
        <div class="col-md-9 col-sm-8"">
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs pull-right">
              <li class="active"><a href="#tab-html" data-toggle="tab">套版</a></li>
              <li class=""><a href="#tab-body" data-toggle="tab">回應</a></li>
              <li class="pull-left dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">設定 <span class="caret"></span></a>
                <ul class="dropdown-menu">
                  <li><a href="#tab-type" data-toggle="tab">Index Type</a></li>
                  <li role="presentation" class="divider"></li>
                  <li><a href="#tab-jsonschema" data-toggle="tab">JSON Schema</a></li>
                  <li><a href="#tab-uischema" data-toggle="tab">UI Schema</a></li>
                  <li role="presentation" class="divider"></li>
                  <li><a href="#tab-script" data-toggle="tab">Search Script</a></li>
                  <li role="presentation" class="divider"></li>
                  <li><a href="#tab-output" data-toggle="tab">Output Script</a></li>
                </ul>
              </li>
              <li class="pull-left"><a href="#tab-data" data-toggle="tab">請求</a></li>
            </ul>
            <div class="tab-content no-padding">
              <div class="tab-pane active" id="tab-html" style="position: relative;">
              <pre style="border: 0; background-color:transparent;"></pre>
              </div>
              <div class="tab-pane" id="tab-body" style="position: relative;">
                <pre style="border: 0; background-color:transparent;" id="search-body"></pre>
              </div>
              <div contenteditable="true" class="tab-pane " id="tab-data">
                <pre class="bg-gray disabled hide" style="text-align:right; border: 0; background-color:white;" id="search-url"></pre>
                <pre style="border: 0; background-color:transparent;" id="search-data"></pre>
              </div>

              <!-- 修改 output script -->
              <div class="tab-pane" id="tab-type" style="position: relative;">
     <!-- div class="box-header with-border">
      <div class="box-title">
        名稱
      </div>
      <div class="box-tools pull-right">
          <button id='outputBtn' type="button" class="btn btn-xs disabled" onclick="saveSchema()">儲存</button>
      </div>
     </div>
     <div class="box-header with-border">
                  <div class="input-group form-group" id='tagname'>
                      <input type="hidden" class="form-control" name="oldKey">
                      <input type="text" class="form-control" placeholder="請輸入英文數字" name="key">
                  </div>
                  <div class="">
                  <i></i>
                  </div>
     </div -->

     <div class="box-header with-border">
      <div class="box-title">
        使用索引
      </div>
      <div class="box-tools pull-right">
          <button id='typeBtn' type="button" class="btn btn-xs disabled" onclick="saveSchema()">儲存</button>
      </div>
     </div>
     <div class="box-header with-border">
                <div class="input-group form-group">
                  <select id="edit-type" class="form-control">
                      <?php foreach ($indexInfo as $idx) { ?>
                        <?php if (!isset($idx['error']['reason'])) { ?>
                        <option value="<?= $idx['type'] ?>" <?php if (isset($type) && $type == $idx['type']) { ?>selected<?php } ?>><?= $idx['type'] ?></option>
                        <?php } ?>
                      <?php } ?>
                  </select>
                </div>
                </div>
     </div>

              <!-- 修改 output script -->
              <div class="tab-pane" id="tab-output" style="position: relative;">
     <div class="box-header with-border">
      <div class="box-title">
        Output Script <small>(參考: <a href="http://handlebarsjs.com/expressions.html" target="_help">Handlebars Expressions</a>)</small>
      </div>
      <div class="box-tools pull-right">
          <button id='outputBtn' type="button" class="btn btn-xs disabled" onclick="saveSchema()">儲存</button>
      </div>
     </div>
              <div contenteditable="true" id='edit-output'>
                <pre style="border: 0; background-color:Azure;" id="output"></pre>
              </div>
              </div>

              <!-- 修改 json schema -->
              <div class="tab-pane" id="tab-jsonschema" style="position: relative;">
     <div class="box-header with-border">
      <div class="box-title">
        JSON Schema <small>(參考: <a href="https://mozilla-services.github.io/react-jsonschema-form/" target="_help">react-jsonschema-form</a>}</small>
      </div>
      <div class="box-tools pull-right">
          <button id='jsonBtn' type="button" class="btn btn-xs disabled" onclick="saveSchema()">儲存</button>
      </div>
     </div>
              <div contenteditable="true" id='edit-jsonschema'>
                <pre style="border: 0; background-color:white;" id="json-schema"></pre>
              </div>
              </div>

              <!-- 修改 ui schema -->
              <div class="tab-pane" id="tab-uischema" style="position: relative;">
     <div class="box-header with-border">
      <div class="box-title">
        UI Schema <small>(參考: <a href="https://mozilla-services.github.io/react-jsonschema-form/" target="_help">react-jsonschema-form</a>)</small>
      </div>
      <div class="box-tools pull-right">
          <button id='uiBtn' type="button" class="btn btn-xs disabled" onclick="saveSchema()">儲存</button>
      </div>
     </div>
              <div contenteditable="true" id='edit-uischema'>
                <pre style="border: 0; background-color:white;" id="ui-schema"></pre>
              </div>
              </div>

              <!-- 修改 search script -->
              <div class="tab-pane" id="tab-script" style="position: relative;">
     <div class="box-header with-border">
      <div class="box-title">
        Search Script <small>(參考: <a href="https://www.elastic.co/guide/en/elasticsearch/reference/5.6/search-template.html" target="_help">search-template</a>) <i>注意: 修改後必須儲存才會生效</i></small>
      </div>
      <div class="box-tools pull-right">
          <button id='scriptBtn' type="button" class="btn btn-xs disabled" onclick="saveScript()">儲存</button>
      </div>
     </div>
              <div contenteditable="true" id='edit-script'>
                <pre style="border: 0; background-color:Azure;" id="script"></pre>
              </div>
              </div>
            </div>
          </div>
        </div>
      </div>
<!-- 預設套版 -->
<script id="entry-template" type="text/x-handlebars-template">
<?= '
&nbsp;
{{#unless hits.total}}
<div class="box-header">
  <span class="box-title">沒有符合的資料</span>
</div>
{{/unless}}
{{#if hits.total}}
<div class="box-header">
  <span class="box-title">符合筆數：{{hits.total}}</span>  {{pagination}}
</div>
<div class="box-body table-responsive">
<table class="table table-hover">
    <thead>
           __TH__ 
    </thead>
    <tbody>
        {{#hits.hits}}
        <tr>
           __TD__ 
        </tr>
        {{/hits.hits}}
    </tbody>
</table>
</div>
{{/if}}
' ?>
</script>

<script>
var project = "<?= $project ?>";
var type = "<?= $type ?>";
var orig_type = type;
var scriptId = "<?= $scriptId ?>";

var script = <?= $scriptInfoJson ?>;
var orig_script = script;
var schemaInfo = <?= $schemaInfoJson ?>;
var result = '';
var formData = {
  "from": 0,
  "size": 10
};

// $(function () {
window.onload = function () {
    preProcess();
    showCode(JSON.stringify(schema, undefined,4), "#json-schema");
    showCode(JSON.stringify(uiSchema, undefined,4), "#ui-schema");
    $("#script").empty().append(htmlEntities(script));
    $("#output").empty().append(htmlEntities(output));
    // showCode(script, "#script");
    // showCode(output, "#output");

    // showCode(JSON.stringify(formData, undefined,4), "#search-data");
    // renderForm();

    $('#tab-data').on("input", function (event) { 
        var text = $('#search-data').text();
        var data = JSON.parse(text);

        type = $("#edit-type").val();
        var url = "/api/search/" + project + "/" + type + "/_search/" + scriptId;

        if ((typeof data !== "undefined")) {
          callAjax('POST', url, data, function(output) {
            result = output;
            showCode(output, "#search-body");
            showResult();
          });
         }
    });

    $('#edit-jsonschema').on("input", function (event) { 
        var text = $('#json-schema').text();
        // console.log(text);
        schema = JSON.parse(text);
        if (JSON.stringify(schema) != orig_schema) {
          $( "#jsonBtn" ).removeClass('disabled');
          $( "#jsonBtn" ).addClass('btn-warning');
        } else {
          $( "#jsonBtn" ).addClass('disabled');
          $( "#jsonBtn" ).removeClass('btn-warning');
        }
        renderForm();
    });

    $('#edit-uischema').on("input", function (event) { 
        var text = $('#ui-schema').text();
        // console.log(text);
        uiSchema = JSON.parse(text);
        if (JSON.stringify(uiSchema) != orig_uiSchema) {
          $( "#uiBtn" ).removeClass('disabled');
          $( "#uiBtn" ).addClass('btn-warning');
        } else {
          $( "#uiBtn" ).addClass('disabled');
          $( "#uiBtn" ).removeClass('btn-warning');
        }
        renderForm();
    });

    $('#edit-script').on("input", function (event) { 
        script = $('#script').text();
        if (script != orig_script) {
          $( "#scriptBtn" ).removeClass('disabled');
          $( "#scriptBtn" ).addClass('btn-warning');
        } else {
          $( "#scriptBtn" ).addClass('disabled');
          $( "#scriptBtn" ).removeClass('btn-warning');
        }
    });

    $('#edit-output').on("input", function (event) { 
        output = $('#output').text();
        if (output != orig_output) {
          $( "#outputBtn" ).removeClass('disabled');
          $( "#outputBtn" ).addClass('btn-warning');
        } else {
          $( "#outputBtn" ).addClass('disabled');
          $( "#outputBtn" ).removeClass('btn-warning');
        }
        compileTemplate();
        showResult();
    });

    $('#edit-type').change(function (event) { 
        type = $("#edit-type").val();
        if (type != orig_type) {
          $( "#typeBtn" ).removeClass('disabled');
          $( "#typeBtn" ).addClass('btn-warning');
        } else {
          $( "#typeBtn" ).addClass('disabled');
          $( "#typeBtn" ).removeClass('btn-warning');
        }
    });

    // 給 gohappy 專用的 SMALL_IMAGE
    Handlebars.registerHelper('gohappyimg', function(str) {
      var link = str;
      if (str.substring(0, 4) != 'http') {
          var id = str.match(/^\d+/);
          var path = Math.floor(id/30000);
          link = "http://img.shopping.friday.tw/images/product/" + path + "/" + id + "/" + str;
      }

      return new Handlebars.SafeString(link);
    });

    // 帶出修改本文的連結
    Handlebars.registerHelper('docLink', function(str) {
      var link = "<?= $this->url->get(['for' => 'admin-project-doc', 'project' => $project, 'type' => $type]) ?>" + "&docId=" + str;
      return new Handlebars.SafeString(link);
    });

    // cleanText
    Handlebars.registerHelper('cleanText', function(str) {
        var div = document.createElement("div");
        div.innerHTML = str;
        var text = div.textContent || div.innerText || "";
        var arr = text.split("\n").map(function(item) {
            return item.trim();
        });
        text = arr.join("\n");
        return text.replace(/^\s+|\s+$/gm,'');
    });

    // 可參考 https://www.sitepoint.com/jquery-infinite-scrolling-demos/
    Handlebars.registerHelper('pagination', function() {
        var record = result;
        if (typeof result == "string") {
           record = JSON.parse(result);
        }
        if (typeof record['hits']['total'] == 'undefined') {
            return;
        }

        var total = record["hits"]["total"];
        var total_pages = Math.ceil(total/formData["size"]);
        var current_page = Math.ceil((formData["from"]+1)/formData["size"]);
        var div = $("<div class='box-footer clearfix'></div>");
        var ul = $("<ul class='pagination pagination-sm no-margin pull-right'></ul>");

        var from = Math.ceil(current_page/10) * 10 - 9;
        var to = Math.ceil(current_page/10) * 10;
        if (to > total_pages) {
            to = total_pages;
        }

        // <<
        if (from > 1) {
            var li = $("<li></li>");
            var previous = from - 1;
            li.append("<a href='javascript:searchByPage("+previous+");'>&laquo;</a>");
        } else {
            var li = $("<li class='disabled'></li>");
            li.append("<a href='#'>&laquo;</a>");
        }
        ul.append(li);

        // <
        if (current_page > 1) {
            var previous_page = current_page - 1;
            var li = $("<li></li>");
            li.append("<a href='javascript:searchByPage(" + previous_page +");'>&lsaquo;</a>");
        } else {
            var li = $("<li class='disabled'></li>");
            li.append("<a href='#'>&lsaquo;</a>");
        }
        ul.append(li);

        // 1...9
        for (var i = from; i<= to; i++) {
            var li;
            if (i != current_page) {
                li = $("<li></li>");
                li.append("<a href='javascript:searchByPage("+i+");'>" + i + "</a>");
            } else {
                li = $("<li class='active'></li>");
                li.append("<a href='#'>" + i + "</a>");
            }
            ul.append(li);
        }

        // >
        if (current_page < total_pages) {
            var next_page = current_page + 1;
            var li = $("<li></li>");
            li.append("<a href='javascript:searchByPage(" + next_page +");'>&rsaquo;</a>");
        } else {
            var li = $("<li class='disabled'></li>");
            li.append("<a href='#'>&rsaquo;</a>");
        }
        ul.append(li);

        // >>
        if (to < total_pages) {
            var li = $("<li></li>");
            var next = to + 1;
            li.append("<a href='javascript:searchByPage("+next+");'>&raquo;</a>");
        } else {
            var li = $("<li class='disabled'></li>");
            li.append("<a href='#'>&raquo;</a>");
        }
        ul.append(li);

        div.append(ul);
        return new Handlebars.SafeString(div.html());
        // p.append(div);
        // console.log(p.html());
        // return new Handlebars.SafeString(p.html());
    });
    // compileTemplate();

    // 可以 url 後面 => ?query=apple&size=10
    var search = false;
    var urlParams = new URLSearchParams(window.location.search);
    var entries = urlParams.entries();
    for (var pair of entries) { 
       formData[pair[0]] = pair[1];
       search = true;
    }

    // console.log(formData);

    showCode(JSON.stringify(formData, undefined,4), "#search-data");
    renderForm();

    // 更新 form 裡面的資料
    /* 應該要可以只透過修改 formData 完成
    entries = urlParams.entries();
    for (var pair of entries) { 
       $("#root_" + pair[0]).val(pair[1]);
       console.log(pair[0], pair[1]); 
    }
    */

    if (search) {
        searchBySchema(formData);
    }
}

function preProcess()
{
    schema = {
      "type": "object",
      "properties": {
        "from": {
            "title": "從第幾筆開始",
            "type": "integer",
            "minimum": 0,
            "default": 0
        },
        "size": {
            "title": "數量",
            "type": "integer",
            "minimum": 0,
            "maximum": 50,
            "default": 10
        },
        "_source": {
            "title": "輸出欄位",
            "type": "array",
            "items": {
                "type": "string"
            }
        }
      }
    };

    uiSchema = {};
    output = '';

    if ((typeof schemaInfo['json'] !== "undefined")) {
        schema = schemaInfo['json'];
    }
    orig_schema = JSON.stringify(schema);

    if ((typeof schemaInfo['json'] !== "undefined")) {
        uiSchema = schemaInfo['ui'];
    }
    orig_uiSchema = JSON.stringify(uiSchema);

    if ((typeof schemaInfo['output'] !== "undefined")) {
        output = schemaInfo['output'].replace(/&#123;/g, '{');
    }
    orig_output = output;
}

function saveSchema()
{
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/config/schema';
    var payload = {};
    var output2 = output.replace(/{/g, '&#123;');

    payload[scriptId] = { json: schema, ui: uiSchema, type: type, output: output2 };
    // console.log(output2);

    callAjax('PUT', url, payload, function() {
      alert("Schema 儲存成功");
      orig_schema = JSON.stringify(schema);
      orig_uiSchema = JSON.stringify(uiSchema);
      orig_output = output2;
      $( "#typeBtn" ).addClass('disabled');
      $( "#typeBtn" ).removeClass('btn-warning');
      $( "#uiBtn" ).addClass('disabled');
      $( "#uiBtn" ).removeClass('btn-warning');
      $( "#jsonBtn" ).addClass('disabled');
      $( "#jsonBtn" ).removeClass('btn-warning');
      $( "#outputBtn" ).addClass('disabled');
      $( "#outputBtn" ).removeClass('btn-warning');
    });
}

function saveScript()
{
    var url = '<?= $this->url->get('api/project/') ?>' + project + '/config/search';
    var payload = {};
    payload[scriptId] = script;

    callAjax('PUT', url, payload, function() {
      alert("Script 儲存成功");
      orig_script = script
      $( "#scriptBtn" ).addClass('disabled');
      $( "#scriptBtn" ).removeClass('btn-warning');
    });
}

var source = "";
function compileTemplate()
{
    if (output != "") {
        if (output == source) {
            return;
        }
        source = output;
    } else if (result !== "") {
        var record = result;
        if (typeof result == "string") {
           record = JSON.parse(result);
        }

        if (typeof record['hits']['hits'][0] != 'undefined') {
            source = document.getElementById("entry-template").innerHTML;
            var tmp = record['hits']['hits'][0]['_source'];
            var th = "";
            var td = "";
            $.each(tmp, function (key, value) {
                th += "<th>"+key+"</th>";
                td += "<td>{\{_source." + key + "}}</td>";
            });
            source = source.replace("__TH__", th).replace("__TD__", td);
        }
    }

    template = Handlebars.compile(source);
}

function showResult()
{
    compileTemplate();

    if (result == '') {
        return;
    }

    var data = result;
    if (typeof result == "string") {
        data = JSON.parse(result);
    }

    var html = template(data);
    $( "#tab-html" ).empty().append(html);
}

function showCode(data, div, lineNo = true)
{
    if (typeof data == "object") {
        data = JSON.stringify(data, undefined,4)
    }
    var str = data.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    var pretty = PR.prettyPrintOne(str, 'json', lineNo);
    $( div ).empty().append(pretty);
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
         alert(xhr.responseText);
         console.log(error);
      }
    });
}

function searchBySchema(formInput)
{
    formData = formInput;
    showCode(JSON.stringify(formData, undefined,4), "#search-data");

    type = $("#edit-type").val();

    var url = "/api/search/" + project + "/" + type + "/_search/" + scriptId;
    showCode("      url = " + url, "#search-url", false);
    $('#search-url').removeClass('hide');
    callAjax('POST', url, formData, function(data) {
        result = data;
        showCode(data, "#search-body");
        showResult();
    });
}

function searchByPage(page)
{
    formData["from"] = (page -1)* formData["size"];
    searchBySchema(formData);
}

</script>
<?= $this->tag->javascriptInclude('static/js/react.min.js') ?>
<?= $this->tag->javascriptInclude('static/js/react-dom.min.js') ?>
<?= $this->tag->javascriptInclude('static/js/browser.min.js') ?>
<?= $this->tag->javascriptInclude('static/js/run_prettify.js') ?>
<?= $this->tag->javascriptInclude('static/js/react-jsonschema-form.js') ?>
<?= $this->tag->javascriptInclude('static/js/handlebars-v4.0.11.js') ?>
<script type="text/babel">
const Form = JSONSchemaForm.default;

const CustomTitleField = ({id, title, required}) => {
  const legend = required ? title + '*' : title;
  return (<div className="box-header bg-info"><h5 className='box-title'>{legend}</h5></div>);
};

const fields = { TitleField: CustomTitleField };

function FTpl(props) {
  const {id, classNames, label, help, required, description, errors, children, displayLabel} = props;
  return (
    //<div className={classNames}>
    <div className={classNames || "form-group form-group-sm"}>
      {displayLabel && 
         <label className="control-label" htmlFor={id}>
           {label}
           {required && <span className="required">*</span>}
         </label>
      }
      {displayLabel && description ? description : null}
      {children}
      {errors}
      {help}
    </div>
  );
}

function transformErrors(errors)
{
  return errors.map(error => {
    if (error.name === "pattern") {
      error.message = "Only digits are allowed"
    } else if (error.name === "type") {
      error.message = "型態錯誤";
    } else if (error.name === "enum") {
      error.message = "不允許使用";
    } else {
      error.message = error.name;
    }
    return error;
  });
}

const log = (type) => console.log.bind(console, type);

window.renderForm = function() {
  ReactDOM.render((
   <Form schema={schema}
        fields={fields}
        action="/users/list"
        FieldTemplate={FTpl}
        transformErrors={transformErrors}
        uiSchema={uiSchema}
        onChange = {(data) => searchBySchema(data.formData)}
        onSubmit = {(data) => searchBySchema(data.formData)}
        onError={log("errors")} />
    ), document.getElementById("search-form"));
};

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
