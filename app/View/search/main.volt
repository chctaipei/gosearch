{% extends "layouts/main.volt" %}

{% block content_header %}
      <h1>
        搜尋文件
      </h1>
      <!-- ol class="breadcrumb">
        <li><a href="{{ url(['for':'admin-projects']) }}"><i class="fa fa-search"></i> 搜尋</a></li>
        <li><a href="{{ url(['for':'admin-project-name', 'project': project]) }}">{{ project }}</a></li>
        <li class="active">{{ scriptId }}</li>
      </ol -->
{% endblock %}

{% block content %}
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
                      {% for idx in indexInfo %}
                        {% if idx['error']['reason'] is not defined %}
                        <option value="{{ idx['type'] }}" {% if type is defined and type == idx['type'] %}selected{% endif %}>{{ idx['type'] }}</option>
                        {% endif %}
                      {% endfor %}
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
{{'
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
'}}
</script>

<script>
var project = "{{ project }}";
var type = "{{ type }}";
var orig_type = type;
var scriptId = "{{ scriptId }}";
{#
這裡沒有使用
var schema = {{ jsonInfoJson }};
var orig_schema = JSON.stringify(schema);
var uiSchema = {{ uiInfoJson }};
var orig_uiSchema = JSON.stringify(uiSchema);
var output = {{ outputInfoJson}};
var orig_output = output;
#}
var script = {{ scriptInfoJson}};
var orig_script = script;
var schemaInfo = {{ schemaInfoJson }};
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
      var link = "{{ url(['for':'admin-project-doc', 'project':project, 'type': type]) }}" + "&docId=" + str;
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
    var url = '{{ url("api/project/") }}' + project + '/config/schema';
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
    var url = '{{ url("api/project/") }}' + project + '/config/search';
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
{% include "partials/form.volt" %}

{% endblock %}
