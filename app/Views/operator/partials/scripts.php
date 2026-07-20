<?php
/**
 * Fichier indépendant : ce partiel n'utilise QUE les variables qui lui sont
 * explicitement passées via view('operator/partials/scripts', [...]) :
 *
 * - array $comptes
 * - float $gainDepot
 * - float $gainRetrait
 * - float $gainTransfert
 * - float $gainTotal
 */
$comptes ??= [];
$gainDepot ??= 0;
$gainRetrait ??= 0;
$gainTransfert ??= 0;
$gainTotal ??= 0;
$gainCommission ??= 0;
helper('url');
?>
    <script>
        window.operatorAccounts = <?= json_encode($comptes, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
        window.operatorGains = <?= json_encode([
            'depot' => $gainDepot,
            'retrait' => $gainRetrait,
            'transfert' => $gainTransfert,
            'total' => $gainTotal,
            'commission' => $gainCommission,
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    </script>
    <script src="<?= base_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
    <script src="<?= base_url('js/operateur.js') ?>"></script>
