<?php
/**
 * Fichier indépendant : ce partiel n'utilise QUE les variables qui lui sont
 * explicitement passées via view('operator/partials/tab_gains', [...]) :
 *
 * - float $gainRetrait
 * - float $gainTransfert
 * - float $gainTotal
 * - array $clientsGains  ex: [ idClient => ['retrait' => float, 'transfert' => float], ... ]
 */
$gainRetrait ??= 0;
$gainTransfert ??= 0;
$gainTotal ??= 0;
$gainCommission ??= 0;
$clientsGains ??= [];
?>
            <!-- ═══════════════════════════════════════
           TAB : GAINS
      ════════════════════════════════════════ -->
            <div id="tab-gains" class="tab-section" style="display:none">
                <div class="page-title">Situation des gains</div>
                <div class="page-subtitle">Revenus générés via les frais d'opérations.</div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-3">
                        <div class="stat-card"
                            style="background:rgba(248,113,113,.05);border:1px solid rgba(248,113,113,.2)">
                            <div class="stat-label">Gains Retraits</div>
                            <div class="stat-value" style="color:#F87171" id="gain-retrait"><?= number_format($gainRetrait, 0, ',', ' ') ?> Ar</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-3">
                        <div class="stat-card"
                            style="background:rgba(96,165,250,.05);border:1px solid rgba(96,165,250,.2)">
                            <div class="stat-label">Gains Transferts</div>
                            <div class="stat-value" style="color:#60A5FA" id="gain-transfert"><?= number_format($gainTransfert, 0, ',', ' ') ?> Ar</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-3">
                        <div class="stat-card"
                            style="background:rgba(0,214,143,.05);border:1px solid rgba(0,214,143,.2)">
                            <div class="stat-label">Total Gains</div>
                            <div class="stat-value" style="color:#00D68F" id="gain-total"><?= number_format($gainTotal, 0, ',', ' ') ?> Ar</div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-3">
                        <div class="stat-card"
                            style="background:rgba(255,193,7,.05);border:1px solid rgba(255,193,7,.2)">
                            <div class="stat-label">Commission</div>
                            <div class="stat-value" style="color:#FFC107" id="gain-commission"><?= number_format($gainCommission, 0, ',', ' ') ?> Ar</div>
                        </div>
                    </div>
                </div>

                <div class="nm-card overflow-hidden">
                    <div class="p-3 px-4"
                        style="border-bottom:1px solid var(--nm-border);font-size:.875rem;font-weight:500">
                        Détail par client
                    </div>
                    <table class="nm-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th class="right">Frais Retraits</th>
                                <th class="right">Frais Transferts</th>
                                <th class="right">Total</th>
                            </tr>
                        </thead>
                        <tbody id="gains-tbody">
                            <?php if (empty($clientsGains)): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;color:var(--nm-muted)">Aucune opération pour le moment.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($clientsGains as $clientId => $g): ?>
                            <?php $totalClient = $g['retrait'] + $g['transfert']; ?>
                            <tr>
                                <td>
                                    <div style="font-weight:500">Compte #<?= (int) $clientId ?></div>
                                </td>
                                <td class="td-right mono" style="color:#F87171"><?= number_format($g['retrait'], 0, ',', ' ') ?> Ar</td>
                                <td class="td-right mono" style="color:#60A5FA"><?= number_format($g['transfert'], 0, ',', ' ') ?> Ar</td>
                                <td class="td-right mono" style="color:#00D68F;font-weight:600"><?= number_format($totalClient, 0, ',', ' ') ?> Ar</td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
