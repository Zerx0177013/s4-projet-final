<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NovaMoney — Mon Compte</title>
  <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('vendor/fonts/inter.css') ?>" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('styles/client-dashboard.css') ?>">
</head>

<body>

  <?= view('client/partials/header') ?>

  <?= view('client/partials/navigation') ?>

  <!-- ── Tab content ─────────────────────────────────────────────── -->
  <div class="tab-content-area">
    <div class="tab-inner">

      <?= view('client/partials/feedback') ?>

      <?= view('client/partials/tab_balance') ?>

      <?= view('client/partials/tab_depot') ?>

      <?= view('client/partials/tab_retrait') ?>

      <?= view('client/partials/tab_transfert') ?>

      <?= view('client/partials/tab_history') ?>

    </div><!-- /tab-inner -->
  </div><!-- /tab-content-area -->

  <?= view('client/partials/scripts') ?>
</body>

</html>
