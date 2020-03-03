{% extends "layouts/main.volt" %}

{% block content_header %}
      <h1>
        專案管理
      </h1>
      <!-- ol class="breadcrumb">
        <li class="active"><i class="fa fa-pencil-square-o"></i> 專案管理</a></li>
      </ol -->
{% endblock %}

{% block content %}
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
                {% for prj in projects %}
                <tr>
                  <td><a href="{{ url(['for':'admin-project-name', 'project':prj['name']]) }}">{{ prj['name'] }}</a></td>
                  <td>
                    {% if  prj['data']['index'] is defined %}
                    {{ prj['data']['index']|length }}
                    {% endif %}
                  </td>
                  <td>
                    {% if  prj['scriptCount'] is defined %}
                    {{ prj['scriptCount'] }}
                    {% endif %}
                  </td>
                  <td>
                    {% if  prj['data']['source'] is defined %}
                    {{ prj['data']['source']|length }}
                    {% endif %}
                  </td>
                  <td>
                      <div>
                         <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-project" name='project' value='{{ prj['name'] }}' onclick="showDeleteProject('{{ prj['name'] }}')"> 刪除專案</button>
                      </div>
                  </td>
                </tr>
                {% endfor %}
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

projects = {{ projectJson }};

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
    var url = '{{ url("api/project/") }}' + project;
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

    var url = '{{ url("api/project/") }}' + project;
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
{% endblock %}
