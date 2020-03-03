{{ get_doctype() }}
<html>

<head>
    <meta charset="UTF-8">
    {{ get_title() }}
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    {{ stylesheet_link("static/css/all.css") }}
    {#  stylesheet_link("static/css/bootstrapSwitch.css")  #}

    {{ assets.outputCss() }}

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    {% if csrfkey is defined %}
    <script>
        window.Laravel = {"{{ csrfkey }}":"{{ csrftoken }}"};
    </script>
    {% endif %}
</head>

{% block body %} {% endblock %}

{{ javascript_include("static/js/app.js") }}
{{ assets.outputJs() }}
<!-- script src="https://unpkg.com/react-jsonschema-form/dist/react-jsonschema-form.js"></script -->
</html>
