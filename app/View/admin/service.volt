{% extends "layouts/main.volt" %}

{% block content_header %}
      <h1>
       服務管理
      </h1>
{% endblock %}

{% block content %}
        <div class="box-header with-border">
          <h1 class="box-title"></h3>

          <div class="box-tools pull-right">
          </div>
        </div>

        <div class="">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body table-responsive">
              <table id='cron' class="table table-hover">
                <thead>
                <tr>
                  <th class="col-md-2">服務</th>
                  <th class="col-md-3">狀態</th>
                  <th class="col-md-2">動作</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                  <td>
                      排程服務 (cron)
                  </td>
                  <td>{{ cronInfo['message'] }}</td>
                  <td>
                      <input type="checkbox" {% if cronInfo['status'] != 0 %} checked {% endif %} data-toggle="toggle" data-size="mini" data-on="啟用" data-off="關閉" onchange="activeCronServer(this)">
                  </td>
                </tr>
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
        </div>
        <!-- /.modal -->
<script>

// call [PUT] /api/service/cron
function activeCronServer(element)
{
    var url = '{{ url("api/service/cron") }}';
    var active = 0;
    if (element.checked) {
        active = 1;
    }

    var payload = {
        'active': active
    }

    callAjax('PUT', url, payload, function(xhr) {
        console.log(xhr);
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
             location.reload();
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
         location.reload();
      }
    });
}

</script>
{% endblock %}
