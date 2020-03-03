{% extends "layouts/main.volt" %}

{% block content_header %}
<script>
var mappingInfo = {{ mappingInfoJson }};
var project = "{{ project }}";
var type = "{{ type }}";
</script>
      <h1>
        文件管理
      </h1>
      <!-- ol class="breadcrumb">
        <li><a href="{{ url(['for':'admin-projects']) }}"><i class="fa fa-pencil-square-o"></i> 專案管理</a></li>
        <li><a href="{{ url(['for':'admin-project-name', 'project': project]) }}">{{ project }}</a></li>
        <li class="active">{{ type}}</li>
      </ol -->
{% endblock %}

{% block content %}
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
{% if auth['level'] is defined and auth['level'] == 0 %}
            <div class="col-md-9 text-right">
              <div class="input-group input-group-sm">
                <span class="input-group-btn">
                <button type="button" class="btn btn-primary btn-flat" onclick="newDoc()">新增文件</button></span>
              </div>
            </div>
{% endif %}
        </div>

        <div class='hide' id='doc'>
            <div class="row"><br></div>
{% if auth['level'] is defined and auth['level'] == 0 %}
            <div class="input-group input-group-sm"> <span class="input-group-btn"> <button id='updateBtn' type="button" class="btn btn-flat disabled" onclick="saveDoc()">儲存</button> </span></div>
{% endif %}
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

    var url = '{{ url("api/document/") }}' + project + '/' + type + '/' + inputId;
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
{% if auth['level'] is not defined or auth['level'] != 0 %}
                disabled = 'disabled';
{% endif %}

            appendRow(key, value, disabled, placeholder);
        });

        jQuery.each(mappingInfo, function (key, value) {
{% if auth['level'] is not defined or auth['level'] != 0 %}
                var disabled = 'disabled';
{% else %}
                var disabled = '';
{% endif %}
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

    var url = '{{ url("api/document/") }}' + project + '/' + type + '/' + encodeId;
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
{% endblock %}
