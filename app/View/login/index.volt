{% extends "layouts/base.volt" %}
{% block body %}
<body class="hold-transition login-page">
    <div id="app">
        <div class="login-box">
            <div class="login-logo">
                {{ text.title }}
            </div>
        {% if error is defined %}
            <div class="alert alert-danger">
                <strong></strong>{{ text.error.message }}<br><br>
                <ul>
                    <li>{{ error }}</li>
                </ul>
            </div>
        {% endif %}
                <div class="login-box-body">
                    <p class="login-box-msg">{{ text.login.message }}</p>
        <form action="/login" method="post">
            <input type="hidden" name="{{ csrfkey }}" value="{{ csrftoken }}">
            <div class="form-group has-feedback">
                <input class="form-control" placeholder="{{ text.account }}" name="username" value=""/>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="{{ text.password }}" name="password" autocomplete="off"/>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary btn-block btn-flat">{{ text.button.login }}</button>
                </div><!-- /.col -->
            </div>
        </form>
    </div><!-- /.login-box-body -->

    </div><!-- /.login-box -->
    </div>
    <!-- Compiled app javascript -->
    <script src="/static/js/app.js"></script>
</body>
{% endblock %}
