<?php
/**
 * Fichier indépendant : ne dépend d'aucune variable transmise par le parent.
 * Le tableau est rempli côté client par js/operateur.js à partir de
 * window.operatorAccounts (injecté par partials/scripts.php).
 */
?>
            <!-- ════════════════════════════════════════
           TAB : COMPTES
      ══════════════════════════════════════ -->
            <div id="tab-accounts" class="tab-section" style="display:none">
                <div class="page-title">Comptes clients</div>
                <div class="page-subtitle" id="accounts-subtitle">— comptes enregistrés.</div>

                <div class="nm-card overflow-hidden">
                    <table class="nm-table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th class="right">Solde</th>
                                <th class="right d-none d-sm-table-cell">Transactions</th>
                            </tr>
                        </thead>
                        <tbody id="accounts-tbody"></tbody>
                    </table>
                </div>
            </div>
