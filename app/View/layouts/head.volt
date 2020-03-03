<head>
    <meta charset="UTF-8">
    {{ get_title() }}
    <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
    {{ stylesheet_link("static/css/all.css") }}
    {{ stylesheet_link("static/css/bootstrap-toggle.min.css") }}
    {{ stylesheet_link("static/css/dataTables.bootstrap.min.css") }}
    {{ assets.outputCss() }}
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

    <style>
      li.L0, li.L1, li.L2, li.L3,
      li.L5, li.L6, li.L7, li.L8 {
          list-style-type: decimal !important;
      }
    </style
</head>
