{% extends "layouts/main.volt" %}

{% block content_header %}
      <h1>
        熱門關鍵字
      </h1>
{% endblock %}

{% block content %}
{% if auth['level'] is defined and auth['level'] == 0 %}
    {% set colspan = 7 %}
{% else %}
    {% set colspan = 6 %}
{% endif %}
        <div class="box-header with-border">
          <h1 class="box-title"></h3>
          <div class="box-tools pull-right">
          </div>
        </div>

        <!-- div class="" -->
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body table-responsive">
              <table id="hotquery" class="table table-hover hide" style="width:100%">
                <thead>
                <tr>
                  <th class="col-md-1">排序</th>
                  <th class="col-md-3">熱門詞</th>
                  <th class="col-md-1">分數</th>
                  <th class="col-md-1">數量</th>
                  <th class="col-md-2 hidden-xs">建立時間</th>
                  <th class="col-md-2 hidden-xs">更新時間</th>
                  {% if colspan == 7 %}
                  <th class="col-md-2">動作</th>
                  {% endif %}
                </tr>
                </thead>
                <tbody>
                {% for id,hot in hotwordsList["hotwords"] %}
                <tr>
                  <td>{{ id+1 }}</td>
                  <td>
                      {% if type and scriptId %} 
                      <a href="{{ url(['for':"search-project-script", 'project':project, 'type':type, 'scriptId':scriptId])}}?query={{hot['query']|e}}"> {{ hot['query'] }}</a>
                      {% else %}
                      {{ hot['query'] }}
                      {% endif %}
                  </td>
                  <td>{{ hot['count'] }}</td>
                  <td>{{ hot['matches'] }}</td>
                  <td class="hidden-xs">{{ hot['createTime'] }}</td>
                  <td class="hidden-xs">{{ hot['updateTime'] }}</td>
                  {% if colspan == 7 %}
                  <td>
                      <div class="btn-group" style="margin:3px">
                         <button type="button" class="btn btn-xs btn-info" data-toggle="modal" data-target="#modal-count" name='project' value='{{ prj['name'] }}' onclick="showEditCount('{{ hot['query'] }}', {{ hot['count'] }})"> 修改</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                         <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-filter" name='project' value='{{ prj['name'] }}' onclick="showFilterWord('{{ hot['query'] }}')"> 濾除</button>
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
            <!-- /.box-body -->
          </div>
        <!-- /div -->

        <!-- /.modal -->
        <div class="modal modal-normal fade" id="modal-count">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header" id='div-header'>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 id='the-title' class="modal-title">修改分數</h4>
              </div>
              <div class="modal-body text-center">
                <h4 id="the-body"></h4>
                <div id="div-count" class="box-body">
                  <input type="hidden" class="form-control" name="query" value="">
                  <div class="form-group">
                    <label class="col-sm-2 control-label"><b>分數</b></label>
                    <div class="col-sm-10">
                      <input type="text" class="form-control" name="count" value="" size="1">
                    </div>
                  </div>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button id='button-updateCount' type="submit" class="btn pull-right bg-blue" data-dismiss="modal" onclick="updateCount();">儲存</button>
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
                <h4 id='the-title' class="modal-title">濾除關鍵字</h4>
              </div>
              <div class="modal-body text-center">
                <h4 id="the-body2"></h4>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button id='button-filter' type="submit" class="btn pull-right bg-red" data-dismiss="modal" onclick="filterBadword();">移除並加入黑名單</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
<script>
window.onload = function () {
  var table = $('#hotquery').DataTable({
    "dom": "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-6'l><'col-sm-6'f>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
    'paging'      : true,
    'lengthChange': false,
    'searching'   : true,
    'search'      : { 'regex': true, 'smart': true },
    'info'        : true,
    'ordering'    : false
  });
  $( "#hotquery" ).removeClass('hide');
/*
    myValue="i.*";
    columnNo=1;
    regExSearch = '^\\s' + myValue +'\\s*$';
    table.column(columnNo).search(regExSearch, true, false).draw();
*/
}

function showEditCount(query, count)
{
  $( "input[type=text][name=count]" ).val(count);
  $( "input[type=hidden][name=query]" ).val(query);
  $( "#the-body" ).text(query);
}

function showFilterWord(query)
{
  $( "input[type=hidden][name=query]" ).val(query);
  $( "#the-body2" ).text(query);
}

// [PUT] /api/hotwords/{project}
function updateCount()
{
  var url = "{{ url(['for':'api-hotwords-update', 'project':project]) }}";
  var payload = {
    query:  $( "input[type=hidden][name=query]" ).val(),
    count: $( "input[type=text][name=count]" ).val()
  }

  callAjax('PUT', url, payload, function() {
      alert("更新成功" );
      location.reload();
  });
}

// [POSTT] /api/hotwords/{project}/filter
function filterBadword()
{
  var url = "{{ url(['for':'api-hotwords-filter', 'project':project]) }}";
  var payload = {
    query:  $( "input[type=hidden][name=query]" ).val()
  }

  callAjax('POSTT', url, payload, function() {
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
