<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NovaMoney — Accueil</title>
  <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
  <link href="<?= base_url('vendor/fonts/inter.css') ?>" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('styles/index.css') ?>">
</head>

<body>
  <div class="grid-bg"></div>
  <div class="glow"></div>
  <div class="content d-flex flex-column align-items-center justify-content-center min-vh-100 px-3 py-5">

    <div class="text-center mb-5">
      <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
        <div class="brand-logo">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round">
            <rect x="5" y="2" width="14" height="20" rx="2" />
            <line x1="12" y1="18" x2="12.01" y2="18" />
          </svg>
        </div>
        <div class="brand-name">Nova<span class="accent">Money</span></div>
      </div>
      <p class="brand-subtitle">Opérateur Mobile Money · Simulation S4</p>
    </div>

    <div class="row g-3 w-100" style="max-width:560px">
      <div class="col-12 col-md-6">
        <a href="<?= base_url('operator') ?>" class="card-entry green">
          <div class="card-icon green">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            </svg>
          </div>
          <div class="card-title">Espace Opérateur</div>
          <div class="card-desc">Configurez préfixes, barèmes de frais, suivez les gains et les comptes clients.</div>
          <div class="card-cta green">Accéder
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12" />
              <polyline points="12 5 19 12 12 19" />
            </svg>
          </div>
        </a>
      </div>
      <div class="col-12 col-md-6">
        <a href="<?= base_url('client/login') ?>" class="card-entry blue">
          <div class="card-icon blue">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect x="5" y="2" width="14" height="20" rx="2" />
              <line x1="12" y1="18" x2="12.01" y2="18" />
            </svg>
          </div>
          <div class="card-title">Espace Client</div>
          <div class="card-desc">Consultez votre solde, effectuez des dépôts, retraits et transferts en temps réel.</div>
          <div class="card-cta blue">Se connecter
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
              stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12" />
              <polyline points="12 5 19 12 12 19" />
            </svg>
          </div>
        </a>
      </div>
    </div>

  </div>
  <script src="<?= base_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
</body>

</html>
