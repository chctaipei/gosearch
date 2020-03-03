<aside class="main-sidebar">
  <section class="sidebar">
    <ul class="sidebar-menu tree" data-widget="tree">
        <!--  搜尋 -->
          {% if project is defined and scripts is defined and scripts[project] is defined %}
        <li class="treeview {{ echoActive('/search') }}">
          <a href="#">
            <i class='fa fa-search'></i>
            <span>{{ text.sidebar.search }}</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            {% for name,value in scripts[project] %}
            {% set sn = '/search/project/' ~ project ~ "/" ~ name %}
              <li class="{{ echoActive(sn) }}">
                <a href="{{ url(['for':'search-project-script', 'project':project, 'scriptId':name ]) }}"><i class="fa fa-circle-o"></i>{{ name }}</a>
              </li>
            {% endfor %}
          </ul>
          {% else %}
        <li class="treeview">
          <a style="color: #999;">
             <i class='fa fa-search'></i>
             <span>{{ text.sidebar.search }}</span>
          </a>
          {% endif %}
        </li>

        <!--  熱門關鍵字 -->
        <li class="treeview {{ echoActive('/hotwords') }}">
          {% if project is defined %}
            <a href="{{ url(['for':'hotwords-project', 'project':project]) }}">
          {% else %}
            <a style="color: #999;">
          {% endif %}
            <i class='fa fa-camera'></i>
            <span>{{ text.sidebar.hotwords }}</span>
          </a>
        </li>

        <!--  排除關鍵字 -->
        <li class="treeview {{ echoActive('/badword') }}">
          {% if project is defined %}
            <a href="{{ url(['for':'badwords-project', 'project':project]) }}">
          {% else %}
            <a style="color: #999;">
          {% endif %}
             <i class="fa fa-circle-o"></i> 
            <span>{{ text.sidebar.badword }}</span>
          </a>
        </li>

      {% if auth['level'] is defined and auth['level'] == 0 %}
        <!-- 管理 -->
        <li class="treeview {{ echoActive('/admin') }}">
          <a href="#">
            <i class="fa fa-gear"></i>
            <span>{{ text.sidebar.admin.title }}</span>
            <span class="pull-right-container">
              <i class="fa fa-angle-left pull-right"></i>
            </span>
          </a>
          <ul class="treeview-menu">
            <li class="{{ echoActive('/admin/projects') }}"><a href="{{ url(['for':'admin-projects']) }}"><i class="fa fa-pencil-square-o"></i> {{ text.sidebar.admin.project }}</a></li>
            <li class="{{ echoActive('/admin/users') }}"><a href="{{ url(['for':'admin-users']) }}"><i class="fa fa-user-circle-o"></i> {{ text.sidebar.admin.user }}</a></li>
            <li class="{{ echoActive('/admin/service') }}"><a href="{{ url(['for':'admin-service']) }}"><i class="fa fa-wheelchair-alt"></i> 服務管理</a></li>
            <!-- li class="treeview {{ echoActive('/admin/system') }}">
              <a href="#">
                <i class='fa fa-wheelchair-alt'></i>
                <span>{{ text.sidebar.admin.system }}</span>
                <span class="pull-right-container">
                  <i class="fa fa-angle-left pull-right"></i>
                </span>
              </a>
              <ul class="treeview-menu">
                  <li class="{{ echoActive('/admin/cron') }}">
                      <a href="/admin/cron"><i class="fa fa-circle-o"></i> 排程服務</a>
                  </li>
              </ul>
            </li -->
          </ul>
        </li>
      {% endif %}
    </ul>
  </section>
</aside>
