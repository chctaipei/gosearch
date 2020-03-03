a:3:{i:0;s:845:"<?= $this->tag->getDoctype() ?>
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

";s:4:"body";a:1:{i:0;a:4:{s:4:"type";i:357;s:5:"value";s:1:" ";s:4:"file";s:41:"/data/gosearch/app/View/layouts/base.volt";s:4:"line";i:28;}}i:1;s:201:"

<?= $this->tag->javascriptInclude('static/js/app.js') ?>
<?= $this->assets->outputJs() ?>
<!-- script src="https://unpkg.com/react-jsonschema-form/dist/react-jsonschema-form.js"></script -->
</html>
";}