<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaMoney — Choix de l'opérateur</title>
    <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/fonts/inter.css') ?>" rel="stylesheet">
    <link href="<?= base_url('styles/index.css') ?>" rel="stylesheet">
</head>

<body>
    <div class="grid-bg"></div>
    <div class="glow"></div>
    <div class="content d-flex flex-column align-items-center justify-content-center min-vh-100 px-3 py-5">
        <div class="w-100" style="max-width:480px">

            <a href="<?= base_url('/') ?>" class="nav-btn mb-4"
                style="text-decoration:none;display:inline-flex;align-items:center;gap:0.5rem;width:auto;color:var(--nm-muted);font-size:0.875rem;transition:color 0.2s">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                Retour
            </a>

            <div class="text-center mb-5">
                <div class="d-flex align-items-center justify-content-center gap-3 mb-3">
                    <div class="brand-logo">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="5" y="2" width="14" height="20" rx="2" />
                            <line x1="12" y1="18" x2="12.01" y2="18" />
                        </svg>
                    </div>
                    <div class="brand-name">Nova<span class="accent">Money</span></div>
                </div>
                <p class="brand-subtitle">Espace Opérateur · Sélectionnez l'opérateur à gérer</p>
            </div>

            <?php if (session()->getFlashdata('error')) : ?>
                <div style="background:rgba(248,113,113,0.1);border:1px solid rgba(248,113,113,0.25);color:#f87171;padding:0.875rem;border-radius:12px;font-size:0.875rem;text-align:center;margin-bottom:1.5rem">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="d-flex flex-column gap-3">
                <?php if (empty($operateurs)) : ?>
                    <div style="background:var(--nm-card);border:1px solid var(--nm-border);border-radius:18px;padding:2rem;text-align:center;color:var(--nm-muted)">
                        Aucun opérateur enregistré.
                    </div>
                <?php else : ?>
                    <?php foreach ($operateurs as $operateur) : ?>
                        <a href="<?= base_url('operator/select/' . $operateur['id']) ?>" class="card-entry green">
                            <div class="d-flex align-items-center gap-3">
                                <div class="card-icon green">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="card-title"><?= esc($operateur['nom']) ?></div>
                                    <div class="card-desc">Gérer les préfixes, barèmes et comptes</div>
                                </div>
                            </div>
                            <div class="card-cta green">
                                Accéder au dashboard
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                    <polyline points="12 5 19 12 12 19" />
                                </svg>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
