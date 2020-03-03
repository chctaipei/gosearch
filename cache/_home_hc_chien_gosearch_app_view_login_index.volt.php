<?= $this->tag->getDoctype() ?>
<html>

<head>
    <meta charset="UTF-8">
    <?= $this->tag->getTitle() ?>
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>

    <?= $this->tag->stylesheetLink('static/css/all.css') ?>
    

    <?= $this->assets->outputCss() ?>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <?php if (isset($csrfkey)) { ?>
    <script>
        window.Laravel = {"<?= $csrfkey ?>":"<?= $csrftoken ?>"};
    </script>
    <?php } ?>
</head>


<body class="hold-transition login-page">
    <div id="app">
        <div class="login-box">
            <div class="login-logo">
                <?= $text->title ?>
            </div>
        <?php if (isset($error)) { ?>
            <div class="alert alert-danger">
                <strong></strong><?= $text->error->message ?><br><br>
                <ul>
                    <li><?= $error ?></li>
                </ul>
            </div>
        <?php } ?>
                <div class="login-box-body">
                    <p class="login-box-msg"><?= $text->login->message ?></p>
        <form action="/login" method="post">
            <input type="hidden" name="<?= $csrfkey ?>" value="<?= $csrftoken ?>">
            <div class="form-group has-feedback">
                <input class="form-control" placeholder="<?= $text->account ?>" name="username" value=""/>
                <span class="glyphicon glyphicon-user form-control-feedback"></span>
            </div>
            <div class="form-group has-feedback">
                <input type="password" class="form-control" placeholder="<?= $text->password ?>" name="password" autocomplete="off"/>
                <span class="glyphicon glyphicon-lock form-control-feedback"></span>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <button type="submit" class="btn btn-primary btn-block btn-flat"><?= $text->button->login ?></button>
                </div><!-- /.col -->
            </div>
        </form>
    </div><!-- /.login-box-body -->

    </div><!-- /.login-box -->
    </div>
    <!-- Compiled app javascript -->
    <script src="/static/js/app.js"></script>
</body>


<?= $this->tag->javascriptInclude('static/js/app.js') ?>
<?= $this->assets->outputJs() ?>
<!-- script src="https://unpkg.com/react-jsonschema-form/dist/react-jsonschema-form.js"></script -->
</html>
