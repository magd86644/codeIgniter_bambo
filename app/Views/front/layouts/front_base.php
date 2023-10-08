<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title> <?= $this->renderSection('title') ?></title>
  <!-- Favicons -->
  <!-- Custom Google fonts -->
  <link href='<?= base_url() ?>/template_assets/fonts/google_raleway.css' rel='stylesheet' type='text/css'>
  <link href='<?= base_url() ?>/template_assets/fonts/google_crimson_text.css' rel='stylesheet' type='text/css'>
  <link href='<?= base_url() ?>/template_assets/fonts/google_open_sans.css' rel='stylesheet' type='text/css'>

  <!-- Bootstrap CSS Style -->
  <link rel="stylesheet" href="<?= base_url() ?>/public/template_assets/css/bootstrap.min.css">

  <!-- Template CSS Style -->
  <link rel="stylesheet" href="<?= base_url() ?>/public/template_assets/css/style.css">


  <!-- FontAwesome 4.3.0 Icons  -->
  <link rel="stylesheet" href="<?= base_url() ?>/public/template_assets/css/font-awesome.min.css">

  <!-- et line font  -->
  <link rel="stylesheet" href="<?= base_url() ?>/public/template_assets/css/et-line-font/style.css">
</head>


<body>
  <header class="header">

  </header>

  <!-- Wrapper -->
  <div id="wrapper">
    <?= $this->renderSection('content') ?>
    <!-- Footer
  ================================================== -->
    <footer>

    </footer>


  </div> <!-- End wrapper -->

  <!-- Back-to-top
  ================================================== -->
  <div class="back-to-top">
    <i class="fa fa-angle-up fa-3x"></i>
  </div> <!-- end back-to-top -->

  <!-- footer assets -->
  <script src="<?=base_url()?>/public/template_assets/js/jquery-1.11.3.min.js"></script>
  <script src="<?=base_url()?>/public/template_assets/js/bootstrap.min.js"></script>


</body>

</html>