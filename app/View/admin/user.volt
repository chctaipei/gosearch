{% extends "layouts/main.volt" %}

{% block content_header %}
      <h1>
        帳號管理
      </h1>
{% endblock %}

{% block content %}
        <div class="box-header with-border">
          <h1 class="box-title"></h3>

          <div class="box-tools pull-right">
            <button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-newuser">新增帳號</button>
          </div>
        </div>

        <div class="">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body  table-responsive">
              <table id='user' class="table table-hover hide">
                <thead>
                <tr>
                  <th class="col-md-3">帳號</th>
                  <th class="col-md-2">姓名</th>
                  <th class="col-md-2 hidden-xs">建立時間</th>
                  <!-- th class="col-md-2">更新時間</th -->
                  <th class="col-md-2">等級</th>
                  <th class="col-md-2">動作</th>
                </tr>
                </thead>
                <tbody>
    {% for user in users %}
                <tr>
                  <td>{{ user['account'] }}</td>
                  <td>{{ user['name'] }}</td>
                  <td class="hidden-xs">{{ user['createTime'] }}</td>
                  <!-- td>{{ user['updateTime'] }}</td -->
                  <td>
                    <form id="syncform" action="javascript:void(0);">
                      <select name="level" form="levelform" onchange="changeLevel(this, '{{ user['account'] }}')">
                        <option value="0"  {% if user['level'] == 0 %}selected{% endif %}>管理者</option>
                        <option value="1"  {% if user['level'] == 1 %}selected{% endif %}>一般</option>
                      </select>
                    </form">
                  </td>
                  <td>
                      <div>
                          <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-deluser" name='deluser' value='{{ user['account'] }}' onclick="showAccount('{{ user['account'] }}')">刪除帳號</button>
                      </div>
                  </td>
                </tr>
    {% endfor %}
                <tbody>
                <tfoot>
                <tr>
                  <td colspan=5></td>
                </tr>
                </tfoot>
              </table>
            </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->
        <div class="modal modal-normal fade" id="modal-newuser">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">建立新帳號 </h4>
              </div>
              <form id="iform">
              <div class="form-group">
              <div class="modal-body">
                  <label id='edit-label'>帳號:</label>
                  <input type="text" name="account" value="" form="iform"><br>
                  <label id='edit-label'>等級:</label>
                  <select id='newlevel' name="level" form="iform">
                      <option value="0">管理者</option>
                      <option value="1" selected>一般</option>
                  </select>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn pull-left bg-gray" data-dismiss="modal">取消</button>
                <button type="submit" class="btn pull-right bg-blue" form="iform" onclick="addAccount();">建立</button>
              </div>
              </div> <!-- //form-group -->
              </form>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
        <div class="modal modal-default fade" id="modal-deluser">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">確定要刪除帳號 ?</h4>
              </div>
              <div class="modal-body text-center">
                <h4 id="del-user"></h4>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn bg-gray pull-left" data-dismiss="modal">取消</button>
                <button type="submit" class="btn pull-right bg-red" onclick="delAccount();">刪除</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
<script>

window.onload = function () {
  var table = $('#user').DataTable({
      "dom": "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-6'l><'col-sm-6'f>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
      'paging'      : true,
      'lengthChange': false,
      'searching'   : true,
      'ordering'    : false,
      'info'        : true,
      'autoWidth'   : false
  });
  $( "#user" ).removeClass('hide');
}

function showAccount(account)
{
    $("#del-user" ).empty().append(account);
}

function delAccount()
{
    var account = $("#del-user" ).text();
    return $.ajax({
      type: "DELETE",
      url: '{{ url("api/user/") }}' + account,
      contentType: 'application/json',
      statusCode: {
        200: function() {
            alert("成功刪除帳號");
            location.reload();
        } 
      },
      error: function(xhr) {
         var error = JSON.parse(xhr.responseText);
         alert(error.message);
         $('#modal-deluser').modal('hide');
      }
    });
}

function changeLevel(obj, account)
{
    var levelObj = { level: obj.value , action: "update"};
    updateAccount(account, levelObj, function() {
        alert( "更新成功" );
    });
    location.reload();
}

function addAccount()
{
    var account = $("input[type=text][name=account]").val();
    var levelObj = { level: $('#newlevel').val(), action: "create" };

    if (account == "") {
        alert("未輸入帳號" );
        return;
    }

    updateAccount(account, levelObj, function() {
        alert("建立成功" );
        // $('#modal-newuser').modal('hide');
        location.reload();
    });
}

function updateAccount(account, obj, callback)
{
    return $.ajax({
      type: "POST",
      url: '{{ url("api/user/") }}' + account,
      contentType: 'application/json',
      data: JSON.stringify(obj),
      statusCode: {
        200: function() {
            callback();
        } 
      },
      error: function(xhr) {
         var error = JSON.parse(xhr.responseText);
         alert(error.message);
      }
    });
}
</script>
{% endblock %}
