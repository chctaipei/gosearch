{% extends "layouts/main.volt" %}

{% block content_header %}
      <h1>
        專案列表
      </h1>
{% endblock %}

{% block content %}
                              {% for prj in projects %}
                                <div class="small-box bg-white">
                                    <div class="inner">
                                        <a href="{{ url("/admin/project", ['project': prj['name']]) }}" class="btn btn-default btn-block btn-flat"> {{ prj['name'] }}
                                        </a>
                                    </div>
                                </div>
                              {% endfor %}
{% endblock %}
