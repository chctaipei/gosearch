{% extends "layouts/main.volt" %}

{% block content_header %}
      <h1>
        {{ project }}
      </h1>
      <!-- ol class="breadcrumb">
        <li><a href="{{ url(['for':'admin-projects']) }}"><i class="fa fa-pencil-square-o"></i> 專案管理</a></li>
        <li class="active">{{ project }}</li>
      </ol -->
{% endblock %}

{% block content %}
     <div class="box-header with-border">
      <h3 class="box-title">
        索引列表
      </h3>
{% if auth['level'] is defined and auth['level'] == 0 %}
      <div class="box-tools pull-right">
          <button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-index" onclick="showEditor('{{ project }}', 'index', '+');">新增樣板</button>
      </div>
{% endif %}
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
{% if auth['level'] is defined and auth['level'] == 0 %}
                  <th class='col-md-3 col-xs-3'>動作</th>
{% endif %}
                </tr>
                </thead>
                <tbody>
                {% for idx in indexInfo %}
                <tr>
                  <td>
                  {% if idx['status'] == 404 or idx['error']['reason'] is defined %}
                    {{ idx['type'] }}
                  {% else %}
                    <a href="{{ url(['for':'admin-project-doc', 'project':project, 'type': idx['type']]) }}">{{ idx['type'] }}</a>
                  {% endif %}
                  {% if projects[project]['data']['backup'][idx['type']]['count'] is defined and projects[project]['data']['backup'][idx['type']]['count'] > 0 %}
                    ({{ projects[project]['data']['backup'][idx['type']]['count'] }})

                    {% if idx['backups'] is defined and idx['backups'] %}
                      <button type="button" class='btn btn-xs btn-info' data-toggle="collapse" data-target="#coll-{{ idx['type'] }}">+</button>
                    {% endif %}
                  {% endif %}

                  </td>
                  <td>
                  {% if idx['error']['reason'] is not defined %}
                      {{ idx['count'] }}
                  {% endif %}
                  </td>
                  <td>
                  {% if idx['error']['reason'] is defined %}
                      <i class='text-red'>{{ idx['error']['reason'] }}</i>
                  {% else %}
                    {% for id,info in cronInfo %}
                      {% if info['type'] == idx['type'] and info['task'] == 'importData' %}
                       {% if info['jobid'] is defined %}
                        <div id="job2-{{info['jobid']}}">
                        {% if info['status'] == 0%}
                         <small>下次執行時間:<br> {{ info['nextExecTime'] }}</small>
                        {% elseif info['status'] == 1 %}
                         <i class="fa fa-refresh fa-spin text-green"></i><span class='text-green'> 執行中<span>
                        {% elseif info['status'] == 2 %}
                         <i class='text-blue'>等待中</i>
                        {% elseif info['status'] == 3 %}
                         <i>即將執行</i>
                        </div>
                        {% endif %}
                       {% else %}
                        <div>尚未設定排程</div>
                       {% endif %}
                      {% endif %}
                    {% endfor %}
                  {% endif %}
                  </td>
                  <td class="hidden-xs hidden-sm">
                  {% for id,info in cronInfo %}
                    {% if info['type'] == idx['type'] and info['task'] == 'importData' %}
                      {{  info['cronstring'] }}
                    {% endif %}
                  {% endfor %}
                  </td>
                  <td class="hidden-xs hidden-sm">
                  {% if projects[project]['data']['import'][idx['type']] is defined %}
                      {% set source = projects[project]['data']['import'][idx['type']] %}
                      {% if sourceInfo[source] is not defined %}
                        <i class='text-red'>{{ source }} 不存在</i>
                      {% else %}
                        {{ source }}
                      {% endif %}
                  {% endif %}
                  </td>
{% if auth['level'] is defined and auth['level'] == 0 %}
                  <td>
                  {% if idx['status'] == 404 or idx['error']['reason'] is defined %}
                      {% if idx['error']['reason'] is defined and idx['error']['type'] == "type_missing_exception" %}
                      <div class="btn-group" style="margin:3px">
                           <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" name='type' value='{{ idx['type'] }}' onclick="showDeleteIndex('{{ project }}', '{{ idx['type'] }}')">刪除索引</button>
                      </div>
                      {% else %}
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" onclick="showDeleteSchema('{{ project }}', 'index', '{{ idx['type'] }}');">刪除設定</button>
                      </div>
                      {% endif %}
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-index" onclick="showEditor('{{ project }}', 'index', '{{ idx['type'] }}');">修改參數</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-normal" name='type' value='{{ idx['type'] }}' onclick="createIndex('{{ project }}', '{{ idx['type'] }}')">建立索引</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-ask" onclick="showBackups('{{ project }}', '{{ idx['type'] }}');">設定輪替</button>
                      </div>
                  {% else %}
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-import" onclick="showImport('{{ idx['type'] }}');">設定來源</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-index" onclick="showEditor('{{ project }}', 'index', '{{ idx['type'] }}', 0);">查看參數</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" name='type' value='{{ idx['type'] }}' onclick="showDeleteIndex('{{ project }}', '{{ idx['type'] }}')">刪除索引</button>
                      </div>
                  {% endif %}
                  </td>
{% endif %}
                </tr>
                {% if idx['backups'] is defined and idx['backups'] %}
                <tr id="coll-{{ idx['type'] }}" class="collapse">
                  <td colspan=6>
                    <table class="table table-striped" style="border-color: white; background-color: white;">
                      {% for key,value in idx['backups'] %}
                        <tr>
                          <td class='col-md-2' style="border-top-color:white;">
                    {% if idx['alias'] is defined and idx['alias'] == key %}
                          <i class='fa fa-angle-right'></i><b>&nbsp;&nbsp;{{ key }}</b>
                    {% else %}
                          &nbsp;&nbsp;{{ key }}
                    {% endif %}
                          </td>
                          <td class='col-md-1' style="border-top-color:white;">
                           {{ value['count'] }}
                          </td>
                          <td class='col-md-2' style="border-top-color:white;">
                           {% if value['importTime'] is defined %}
                               <small>前次上傳時間:<br> {{ value['importTime'] }}</small>
                           {% endif %}
                          </td>
                          <td class='col-md-2' style="border-top-color:white;"></td>
                          <td class='col-md-2' style="border-top-color:white;"></td>
{% if auth['level'] is defined and auth['level'] == 0 %}
                          <td class='col-md-3' style="border-top-color:white;">&nbsp;
                  {% if idx['alias'] is defined  %}
                    {% if  idx['alias'] != key %}
                          <button type="button" class="btn btn-xs btn-primary" onclick="switchAlias('{{ key }}');" {% if value['count'] == 0 %} disabled {% endif %} >切換</button>
                    {% endif %}
                  {% endif %}
                          </td>
{% endif %}
                         </tr>
                      {% endfor %}
                    </table>
                   </td>
                </tr>
                {% endif %}
                {% endfor %} 
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
{% if auth['level'] is defined and auth['level'] == 0 %}
      <div class="box-tools pull-right">
          <button type="button" class="btn btn-xs bg-purple" data-toggle="modal" data-target="#modal-index" onclick="showEditor('{{ project }}', 'search', '+');">新增腳本</button>
      </div>
{% endif %}
     </div>
        <div class="">
          <div class="box">
            <!-- /.box-header -->
            <div class="box-body">
              <table class="table">
               <thead>
                <tr>
                  <th class='col-md-9 col-xs-9'>腳本</th>
{% if auth['level'] is defined and auth['level'] == 0 %}
                  <th class='col-md-3 col-xs-3'>動作</th>
{% endif %}
                </tr>
               </thead>
               <tbody>
               {% for name,value in scriptInfo %}
                <tr>
                  <td>
                  {% if schemaInfo[name]['type'] is defined and schemaInfo[name]['json'] is defined%}
                    <a href="{{ url(['for':'search-project-script', 'project':project, 'scriptId':name]) }}">{{ name }}</a></td>
                  {% else %}
                    {{ name }}
                  {% endif %}
                  </td>
{% if auth['level'] is defined and auth['level'] == 0 %}
                  <td>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-index" onclick="showEditor('{{ project }}', 'search', '{{ name }}');">修改腳本</button>
                      </div>
                      <div class="btn-group" style="margin:3px">
                          <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" onclick="showDeleteSchema('{{ project }}', 'search', '{{ name }}');">刪除腳本</button>
                      </div>
                  </td>
{% endif %}
                </tr>
               {% endfor %} 
               </tbody>
              </table>
            </div>
          </div>
          <!-- /.box -->
        </div>
        <!-- /.modal -->

{% if auth['level'] is defined and auth['level'] == 0 %}
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
               {% for sourcename,source in sourceInfo %}
                <tr>
                  <td>{{ sourcename }}</td>
                  <td class='hidden-xs'>
                      <div class='box'>
                        <div class='box-title bg-gray-light col-md-12'>dsn</div>
                        <div class='box-body'>{% if source['dsn'] is defined %}{{ source['dsn'] }} {% endif %}</div>
                        <div class='box-title bg-gray-light col-md-12'>sql</div>
                        <div class='box-body' style="max-height: 100px; overflow-x: hidden;">{% if source['sql'] is defined %}{{ source['sql'] }} {% endif %}</div>
                        <div class='box-title bg-gray-light col-md-12'>filter</div>
                        <div class='box-body' style="max-height: 100px; overflow-x: hidden;">{% if source['filter'] is defined %}{{ source['filter'] }} {% endif %}</div>
                      </div>
                  </td>
                  <td>
                    <div class="btn-group" style="margin:3px">
                       <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-source" onclick="showEditSource('{{ sourcename }}')">修改設定</button>
                    </div>
                    <div class="btn-group" style="margin:3px">
                       <button type="button" class="btn btn-xs btn-danger" data-toggle="modal" data-target="#modal-ask" onclick="showDeleteSchema('{{ project }}', 'source', '{{ sourcename }}')">刪除設定</button>
                    </div>
                  </td>
                </tr>
                {% endfor %}
                </tbody>
              </table>
            </div>
          </div>
          <!-- /.box -->
        </div>
{% endif %}
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
{% if auth['level'] is defined and auth['level'] == 0 %}
                  <th class='col-md-2 hidden-xs hidden-sm'>參數</th>
{% endif %}
                  <th class='col-md-2 hidden-xs hidden-sm'>排程 (<a href="https://www.wikiwand.com/zh-hant/Cron" target="_cron"><small>參考說明</small></a>)</th>
                  <th class='col-md-1 col-xs-3'>狀態</th>
                  <th class='col-md-2 hidden-xs hidden-sm'>時間</th>
{% if auth['level'] is defined and auth['level'] == 0 %}
                  <th class='col-md-3 col-xs-3'>動作</th>
{% endif %}
                </tr>
                </thead>
                <tbody>
                {% for id,info in cronInfo %}
                <tr>
                  <td>{{ info['task'] }}<br><small>{{ info['desc'] }}</small></td>
{% if auth['level'] is defined and auth['level'] == 0 %}
                  <td class='hidden-xs hidden-sm'>
                      {% for name,value in info['data']['parameter'] %}
                          {% if value == ":TYPE:" %}
                              type = {{ info['type'] }}<br>
                          {% elseif name != "project" %}
                              {{ name }} = {{ value }}<br>
                          {% endif %}
                      {% endfor %}
                  </td>
{% endif %}
                  <td class='hidden-xs hidden-sm'>{{ info['cronstring'] }}</td>
                  {% if info['jobid'] is defined %}
                  <td id="job-{{ info['jobid'] }}">
                      {% if info['status'] == 0%}
                         準備中 
                      {% elseif info['status'] == 1 %}
                         <i class="fa fa-refresh fa-spin text-green"></i><span class='text-green'> 執行中<span>
                      {% elseif info['status'] == 2 %}
                         <i class='text-blue'>等待中</i>
                      {% elseif info['status'] == 3 %}
                         <i>即將執行</i>
                      {% endif %}
                  </td>
                  {% else %}
                  <td>尚未設定</td>
                  {% endif %}
                  <td class='hidden-xs hidden-sm'>
                    {% if info['lastExecTime'] %}
                      <small class='text-black'>上次執行時間:<br> {{  info['lastExecTime'] }}</small><br>
                    {% endif %}
                    {% if info['nextExecTime'] %}
                      <small class='text-black'>下次執行時間:<br> {{  info['nextExecTime'] }}</small>
                    {% endif %}
                  </td>
{% if auth['level'] is defined and auth['level'] == 0 %}
                  <td>
                    <div class="btn-group" style="margin:3px">
                    <input type="checkbox" {% if info['active'] != 0 %} checked {% endif %} data-toggle="toggle" data-size="mini" data-on="啟用" data-off="關閉" onchange="activeCronjob({{ id }}, this)">
                    </div>
                    <div class="btn-group" style="margin:3px">
                       <button type="button" class="btn btn-xs btn-primary" data-toggle="modal" data-target="#modal-cronjob" onclick="showEditCronjob({{ id }})"> 修改參數</button>
                    </div>
                    <div class="btn-group" style="margin:3px">
                       <button type="button" class="btn btn-xs btn-default {% if info['jobid'] is not defined %} disabled {% endif %}" onclick="runJob({{ id }})"> 立即執行</button>
                    </div>
                    {% if info['jobid'] is defined %}
                    <div class="btn-group" style="margin:3px">
                    <button type="button" class="btn btn-xs btn-default" data-toggle="modal" data-target="#modal-log" onclick="showLog({{ id }})"> 查看紀錄</button>
                    </div>
                    {% endif %}
                  </td>
{% endif %}
                </tr>
                {% endfor %}
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
                      {% for key,value in sourceInfo %}
                        <option value="{{ key }}">{{ key }}</option>
                      {% endfor %}
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
                      {% for value in importFilters %}
                        <option value="{{ value }}">{{ value }}</option>
                      {% endfor %}
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
                      {% for idx in indexInfo %}
                        {% if idx['error']['reason'] is not defined %}
                        <option value="{{ idx['type'] }}" {% if source['type'] is defined and source['type'] == idx['type'] %}selected{% endif %}>{{ idx['type'] }}</option>
                        {% endif %}
                      {% endfor %}
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
var project = "{{ project }}";
var projects = {{ projectJson }};
var sourceInfo = projects[project].data.source;
var indexInfo  = projects[project].data.index;
var scriptInfo = projects[project].data.search;
var schemaInfo = projects[project].data.schema;
var cronInfo = {{ cronInfoJson }};
var cronDefault = {{ cronDefaultJson }};

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
    var url = '{{ url("api/project/") }}' + project + "/job/" + jobid;
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
    var url = '{{ url("api/project/") }}' + project + "/mapping/" + type;
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
    var url = '{{ url("api/project/") }}' + project + '/cronjob/' + info['task'];
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
    var url = '{{ url("api/project/") }}' + project + '/cronjob/' + info['task'] + "/run";

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
    var url = '{{ url("api/project/") }}' + project + '/cronjob/' + info['task'] + '/active';
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
    var url = '{{ url("api/project/") }}' + project + '/config/source';
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
    var url = '{{ url("api/project/") }}' + project + '/config/import';
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
    var url = '{{ url("api/project/") }}' + project + '/config/backup';
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
            var url = '{{ url("api/project/") }}' + project + '/cronjob/' + value['task'];
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
    var url = '{{ url("api/project/") }}' + project + '/config/' + type;
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
               var url2 = '{{ url("api/project/") }}' + project + '/schema/' + oldkey;
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
    var url = '{{ url("api/project/") }}' + project + '/config/' + type;
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
    var url = '{{ url("api/project/") }}' + project + '/mapping/' + schema;

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
    // callAjax('GET', {{ url("api/project/") }}' + project, null, callback);
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

    var url = '{{ url("api/project/") }}' + project + '/alias/';
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
{% endblock %}
