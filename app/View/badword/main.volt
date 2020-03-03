{% extends "layouts/main.volt" %}

{% block content_header %}
      <h1>
        排除關鍵字
      </h1>
{% endblock %}

{% block content %}

{% if auth['level'] is defined and auth['level'] == 0 %}
    {% set colspan = 2 %}
{% else %}
    {% set colspan = 1 %}
{% endif %}
        <div class="box-header with-border">
          <h1 class="box-title"></h1>

          <div class="box-tools pull-right">
{% if auth['level'] is defined and auth['level'] == 0 %}
<button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-badword" onclick="$('input[type=text][name=newproject]').val('');">新增排除字</button>
{% endif %}
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
                  {% if colspan == 2 %}
                  <th style="width: 60px">動作</th>
                  {% endif %}
                </tr>
                </thead>
                <tbody>
                {% for keyword in badwordList["keyword"] %}
                <tr>
                  <td>{{ keyword }} </td>
                  {% if colspan == 2 %}
                  <td>
                      <div class="btn-group" style="margin:3px">
                         <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-filter" name='project' value='{{ project }}' onclick="showDeleteWord('{{ keyword }}')"> 刪除</button>
                      </div>
                  </td>
                  {% endif %}
                </tr>
                {% endfor %}
                </tbody>
                <tfoot>
                <tr>
                  <td colspan={{ colspan }}></td>
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
                  {% if colspan == 2 %}
                  <th style="width: 60px">動作</th>
                  {% endif %}
                </tr>
                </thead>
                <tbody>
                {% for pattern in badwordList["pattern"] %}
                <tr>
                  <td>{{ pattern }} </td>
                  {% if colspan == 2 %}
                  <td>
                      <div class="btn-group" style="margin:3px">
                         <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-filter" name='project' value='{{ project }}' onclick="showDeleteWord('{{ pattern }}')"> 刪除</button>
                      </div>
                  </td>
                  {% endif %}
                </tr>
                {% endfor %}
                </tbody>
                <tfoot>
                <tr>
                  <td colspan={{ colspan }}></td>
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
  var url = "{{ url(['for':'api-badword-insert', 'project':project]) }}";
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
  var url = "{{ url(['for':'api-badword-delete', 'project':project]) }}";
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
{% endblock %}
