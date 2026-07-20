<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaMoney — Choix de l'opérateur</title>
    <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/fonts/inter.css') ?>" rel="stylesheet">
    <link href="<?= base_url('styles/operateur.css') ?>" rel="stylesheet">
</head>

<body>
    <div class="d-flex flex-column align-items-center justify-content-center min-vh-100 px-3 py-5">
        <div class="w-100" style="max-width:480px">

            <a href="<?= base_url('/') ?>" class="nav-btn mb-4"
                style="text-decoration:none;display:inline-flex;width:auto">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="12 19 5 12 12 5" />
                </svg>
                Retour
            </a>

            <div class="d-flex align-items-center gap-2 mb-2 justify-content-center">
                <div class="brand-logo">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="5" y="2" width="14" height="20" rx="2" />
                        <line x1="12" y1="18" x2="12.01" y2="18" />
                    </svg>
                </div>
                <span class="brand-text">Nova<span class="accent">Money</span></span>
            </div>

            <div class="page-title" style="text-align:center">Espace Opérateur</div>
            <div class="page-subtitle" style="text-align:center">Sélectionnez l'opérateur à gérer.</div>

            <?php if (session()->getFlashdata('error')) : ?>
                <div class="error-msg mb-3" style="text-align:center">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <div class="d-flex flex-column gap-3">
                <?php if (empty($operateurs)) : ?>
                    <div class="nm-card p-4" style="text-align:center;color:var(--nm-muted)">
                        Aucun opérateur enregistré.
                    </div>
                <?php else : ?>
                    <?php foreach ($operateurs as $operateur) : ?>
                        <a href="<?= base_url('operator/select/' . $operateur['id']) ?>" class="nm-card p-4 d-flex align-items-center justify-content-between"
                            style="text-decoration:none;color:var(--nm-text)">
                            <div class="d-flex align-items-center gap-3">
                                <div class="brand-logo">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                                    </svg>
                                </div>
                                <div style="font-weight:500"><?= esc($operateur['nom']) ?></div>
                            </div>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                style="width:18px;height:18px;color:var(--nm-muted);flex-shrink:0">
                                <line x1="5" y1="12" x2="19" y2="12" />
                                <polyline points="12 5 19 12 12 19" />
                            </svg>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
