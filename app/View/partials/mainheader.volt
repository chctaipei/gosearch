<!-- Main Header -->
<header class="main-header">

    <!-- Logo -->
    <a href="{{ url('/') }}" class="logo">
        <span class="logo-mini"><b>{{ text.logo.mini }}</b></span>
        <span class="logo-lg"><b>{{ text.logo.site }}</b> {{ text.logo.title }}</span>
    </a>

    <!-- Header Navbar -->
    <nav class="navbar navbar-static-top" role="navigation">
        <!-- Sidebar toggle button-->
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">{{ text.togglenav }}</span>
        </a>

        <div class="navbar-header">

          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#navbar-collapse">
            <i class="fa fa-toggle-down"></i>
            {% if project is defined %}
            <span class="text-right">{{ project }}</span>
            {% endif %}
          </button>
        </div>

        <!-- Project Menu -->
        <div class="collapse navbar-collapse" id="navbar-collapse">
            <ul class="nav navbar-nav">
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-suitcase"></i>
                  {% if project is defined %}
                      {{ project }} 
                  {% else %}
                      請選擇專案
                  {% endif %}
                  <span class="caret"></span></a>
                <ul class="dropdown-menu" role="menu">
                {% for name,prj in projects %}
                  <li><a href="?project={{ name }}"> {{ name }}</a></li>
                  <!-- li role="presentation" class="divider"></li -->
                {% endfor %}
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
                <span style="color: #fff"><i class="fa fa-user"></i> {{ auth['account'] }}</span>
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
