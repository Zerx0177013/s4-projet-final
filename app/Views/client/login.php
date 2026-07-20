<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NovaMoney — Connexion Client</title>
  <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('vendor/fonts/inter.css') ?>" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('styles/clients-login.css') ?>">
</head>

<body>
  <div class="grid-bg"></div>

  <div class="content d-flex flex-column align-items-center justify-content-center min-vh-100 px-3 py-5">
    <div class="w-100" style="max-width:380px">

      <a href="<?= base_url('/') ?>" class="back-link">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <line x1="19" y1="12" x2="5" y2="12" />
          <polyline points="12 19 5 12 12 5" />
        </svg>
        Retour
      </a>

      <div class="login-card">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="login-icon">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path
                d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12 19.79 19.79 0 0 1 1.61 3.18 2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.1a16 16 0 0 0 8.01 8.01l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
            </svg>
          </div>
          <div>
            <div class="login-title">Connexion</div>
            <div class="login-sub">Entrez votre numéro de téléphone</div>
          </div>
        </div>

        <form onsubmit="handleLogin(event)">
          <div class="mb-3">
            <label class="nm-label" for="phone-input">Numéro de téléphone</label>
            <input id="phone-input" type="tel" class="nm-input" placeholder="0330000000" maxlength="10"
              autocomplete="tel" oninput="this.value=this.value.replace(/\D/g,'').slice(0,10);clearError()">
          </div>

          <div id="login-error" class="alert-nm mb-3" style="display:none">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10" />
              <line x1="15" y1="9" x2="9" y2="15" />
              <line x1="9" y1="9" x2="15" y2="15" />
            </svg>
            <span id="login-error-msg"></span>
          </div>

          <button type="submit" class="btn-submit">
            Se connecter
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12" />
              <polyline points="12 5 19 12 12 19" />
            </svg>
          </button>
        </form>

        <p class="login-hint">
          Aucune inscription requise.<br>
          Les nouveaux numéros sont créés automatiquement.
        </p>
      </div>

      <div class="prefix-hint">Préfixes acceptés :</div>
      <div class="prefix-tags" id="prefix-tags"></div>
    </div>
  </div>

  <script>
    window.clientDashboardUrl = <?= json_encode(base_url('client/dashboard'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  </script>
  <script src="<?= base_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= base_url('js/client-login.js') ?>"></script>
</body>

</html>
