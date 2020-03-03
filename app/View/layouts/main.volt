{{ get_doctype() }}
<html>

{% include "layouts/head.volt" %}

<body class="skin-blue sidebar-mini">
  <div id="app">
    <div class="wrapper">

      {% include "partials/mainheader.volt" %}

      {% include "partials/sidebar.volt" %}

      <!-- Content Wrapper. Contains page content -->
      <div class="content-wrapper">
        <div id='alert' class="flat no-border alert alert-warning alert-dismissible hidden" aria-hidden="true"></div>

        <section class="content-header">

          {% block content_header %}
          {% endblock %}

        </section>

        <!-- Main content -->
        <section class="content">

          {% block content %}
          {% endblock %}

        </section><!-- /.content -->
      </div><!-- /.content-wrapper -->

      {% include "partials/footer.volt" %}

    </div><!-- ./wrapper -->
  </div><!-- ./app -->
</body>

{% include "layouts/foot.volt" %}

</html>
