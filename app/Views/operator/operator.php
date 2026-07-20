<?php
helper('url');

// Ces valeurs sont fournies par DashboardController::afficherGainParOperateur()
// (extraites de $answer = ['Depot' => .., 'Retrait' => .., 'Transfert' => .., 'total' => .., 'liste' => [...]])
// et transmises explicitement au partiel operator/partials/tab_gains.
$gainRetrait = $Retrait ?? 0;
$gainTransfert = $Transfert ?? 0;
$gainDepot = $Depot ?? 0;
$gainTotal = $total ?? ($gainRetrait + $gainTransfert + $gainDepot);

// Regroupement du détail par client à partir de $liste
// (fournie par Mouvement::getMouvementDetails()), transmis explicitement
// au partiel operator/partials/tab_gains.
$clientsGains = [];
foreach (($liste ?? []) as $mouvement) {
    $clientId = $mouvement['idSender'] ?? $mouvement['idReceiver'] ?? null;
    if ($clientId === null) {
        continue;
    }

    if (!isset($clientsGains[$clientId])) {
        $clientsGains[$clientId] = ['retrait' => 0, 'transfert' => 0];
    }

    $frais = (float) ($mouvement['montantFrais'] ?? 0);
    if (($mouvement['typeLibelle'] ?? '') === 'Retrait') {
        $clientsGains[$clientId]['retrait'] += $frais;
    } elseif (($mouvement['typeLibelle'] ?? '') === 'Transfert') {
        $clientsGains[$clientId]['transfert'] += $frais;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NovaMoney — Opérateur</title>
    <link href="<?= base_url('vendor/bootstrap/bootstrap.min.css') ?>" rel="stylesheet">
    <link href="<?= base_url('vendor/fonts/inter.css') ?>" rel="stylesheet">
    <link href="<?= base_url('styles/operateur.css') ?>" rel="stylesheet">


</head>

<body>

    <?= view('operator/partials/mobile_nav') ?>

    <div class="app-layout">
        <?= view('operator/partials/sidebar') ?>

        <!-- Main -->
        <main class="main-content">

            <?= view('operator/partials/tab_prefixes', [
                'prefixes' => $prefixes ?? [],
            ]) ?>

            <?= view('operator/partials/tab_operations') ?>

            <?= view('operator/partials/tab_gains', [
                'gainRetrait' => $gainRetrait,
                'gainTransfert' => $gainTransfert,
                'gainTotal' => $gainTotal,
                'clientsGains' => $clientsGains,
            ]) ?>

            <?= view('operator/partials/tab_accounts') ?>

        </main>
    </div><!-- /app-layout -->

    <?= view('operator/partials/scripts', [
        'comptes' => $comptes ?? [],
        'gainDepot' => $gainDepot,
        'gainRetrait' => $gainRetrait,
        'gainTransfert' => $gainTransfert,
        'gainTotal' => $gainTotal,
    ]) ?>

</body>

</html>
