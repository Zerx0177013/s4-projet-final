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

  <!-- ── Header ──────────────────────────────────────────────────── -->
  <header class="app-header">
    <div class="d-flex align-items-center gap-2">
      <div class="brand-logo">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <rect x="5" y="2" width="14" height="20" rx="2" />
          <line x1="12" y1="18" x2="12.01" y2="18" />
        </svg>
      </div>
      <span class="brand-text">Nova<span class="accent">Money</span></span>
    </div>
    <div class="d-flex align-items-center gap-3">
      <div class="d-none d-sm-block">
        <div class="user-name" id="header-name">—</div>
        <div class="user-phone" id="header-phone">—</div>
      </div>
      <button class="logout-btn" onclick="logout()">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
          stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
          <polyline points="16 17 21 12 16 7" />
          <line x1="21" y1="12" x2="9" y2="12" />
        </svg>
      </button>
    </div>
  </header>

  <!-- ── Balance banner ──────────────────────────────────────────── -->
  <div class="balance-banner">
    <div class="balance-label">Solde disponible</div>
    <div class="balance-value" id="balance-display">0 Ar</div>
  </div>

  <!-- ── Tab nav ─────────────────────────────────────────────────── -->
  <nav class="tab-nav">
    <button class="tab-nav-btn active" data-tab="balance" onclick="switchTab('balance',this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
        <path d="M18 12a2 2 0 0 0 0 4h4v-4z" />
      </svg>
      Solde
    </button>
    <button class="tab-nav-btn" data-tab="depot" onclick="switchTab('depot',this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="19" x2="12" y2="5" />
        <polyline points="5 12 12 19 19 12" />
      </svg>
      Dépôt
    </button>
    <button class="tab-nav-btn" data-tab="retrait" onclick="switchTab('retrait',this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <line x1="12" y1="5" x2="12" y2="19" />
        <polyline points="19 12 12 5 5 12" />
      </svg>
      Retrait
    </button>
    <button class="tab-nav-btn" data-tab="transfert" onclick="switchTab('transfert',this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <line x1="22" y1="2" x2="11" y2="13" />
        <polygon points="22 2 15 22 11 13 2 9 22 2" />
      </svg>
      Transfert
    </button>
    <button class="tab-nav-btn" data-tab="history" onclick="switchTab('history',this)">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
        stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10" />
        <polyline points="12 6 12 12 16 14" />
      </svg>
      Historique
    </button>
  </nav>

  <!-- ── Tab content ─────────────────────────────────────────────── -->
  <div class="tab-content-area">
    <div class="tab-inner">

      <!-- Feedback (partagé) -->
      <div id="feedback" style="display:none" class="feedback-toast">
        <svg id="feedback-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor"
          stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></svg>
        <span id="feedback-msg"></span>
      </div>

      <!-- ══ SOLDE ════════════════════════════════════════════════ -->
      <div id="tab-balance" class="tab-section active">
        <div class="mini-stats" id="mini-stats"></div>
        <div class="section-label">Dernières transactions</div>
        <div id="recent-tx-list"></div>
      </div>

      <!-- ══ DÉPÔT ════════════════════════════════════════════════ -->
      <div id="tab-depot" class="tab-section">
        <div class="op-card">
          <div class="op-card-header">
            <div class="op-card-icon" style="background:rgba(52,211,153,.1)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#34D399" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="19" x2="12" y2="5" />
                <polyline points="5 12 12 19 19 12" />
              </svg>
            </div>
            <div>
              <div class="op-card-title">Dépôt</div>
              <div class="op-card-sub">Créditez votre compte</div>
            </div>
          </div>
          <div class="mb-3">
            <label class="nm-label" for="depot-amount">Montant (Ar)</label>
            <input id="depot-amount" type="number" class="nm-input mono-font" placeholder="ex : 50 000" min="100"
              oninput="clearFeedback()">
          </div>
          <button class="btn-confirm" onclick="doOperation('depot')">Confirmer le dépôt</button>
        </div>
      </div>

      <!-- ══ RETRAIT ══════════════════════════════════════════════ -->
      <div id="tab-retrait" class="tab-section">
        <div class="op-card">
          <div class="op-card-header">
            <div class="op-card-icon" style="background:rgba(248,113,113,.1)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#F87171" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19" />
                <polyline points="19 12 12 5 5 12" />
              </svg>
            </div>
            <div>
              <div class="op-card-title">Retrait</div>
              <div class="op-card-sub">Retirez des fonds</div>
            </div>
          </div>
          <div class="mb-3">
            <label class="nm-label" for="retrait-amount">Montant (Ar)</label>
            <input id="retrait-amount" type="number" class="nm-input mono-font" placeholder="ex : 50 000" min="100"
              oninput="updateFeePreview('retrait');clearFeedback()">
          </div>
          <div id="retrait-preview" style="display:none" class="fee-preview mb-3">
            <div class="fee-row"><span>Montant</span><span class="mono-font" id="retrait-p-amount">—</span></div>
            <div class="fee-row"><span>Frais</span><span class="mono-font" id="retrait-p-fee">—</span></div>
            <div class="fee-row total"><span>Total débité</span><span class="mono-font" id="retrait-p-total">—</span>
            </div>
          </div>
          <button class="btn-confirm" onclick="doOperation('retrait')">Confirmer le retrait</button>
        </div>
      </div>

      <!-- ══ TRANSFERT ════════════════════════════════════════════ -->
      <div id="tab-transfert" class="tab-section">
        <div class="op-card">
          <div class="op-card-header">
            <div class="op-card-icon" style="background:rgba(96,165,250,.1)">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#60A5FA" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <line x1="22" y1="2" x2="11" y2="13" />
                <polygon points="22 2 15 22 11 13 2 9 22 2" />
              </svg>
            </div>
            <div>
              <div class="op-card-title">Transfert</div>
              <div class="op-card-sub">Envoyez de l'argent</div>
            </div>
          </div>

          <!-- Mode selection tabs -->
          <div class="transfert-mode-tabs mb-3">
            <button class="mode-tab active" data-mode="simple" onclick="switchTransfertMode('simple')">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
              </svg>
              Simple
            </button>
            <button class="mode-tab" data-mode="multiple" onclick="switchTransfertMode('multiple')">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              Multiple
            </button>
          </div>

          <!-- Simple mode -->
          <div id="transfert-simple" class="transfert-mode-content">
            <div class="mb-3">
              <label class="nm-label" for="transfert-target">Numéro destinataire</label>
              <input id="transfert-target" type="tel" class="nm-input mono-font" placeholder="0330000000" maxlength="10"
                oninput="this.value=this.value.replace(/\D/g,'').slice(0,10);clearFeedback()">
            </div>
            <div class="mb-3">
              <label class="nm-label" for="transfert-amount">Montant (Ar)</label>
              <input id="transfert-amount" type="number" class="nm-input mono-font" placeholder="ex : 50 000" min="100"
                oninput="updateFeePreview('transfert');clearFeedback()">
            </div>
            <div class="mb-3">
              <label class="nm-checkbox">
                <input type="checkbox" id="transfert-include-fee" onchange="updateFeePreview('transfert')">
                <span>Inclure les frais dans le montant envoyé</span>
                <small>Le destinataire recevra le montant moins les frais</small>
              </label>
            </div>
            <div id="transfert-preview" style="display:none" class="fee-preview mb-3">
              <div class="fee-row"><span>Montant envoyé</span><span class="mono-font" id="transfert-p-amount">—</span></div>
              <div class="fee-row"><span>Frais</span><span class="mono-font" id="transfert-p-fee">—</span></div>
              <div class="fee-row"><span id="transfert-p-received-label">Destinataire reçoit</span><span class="mono-font" id="transfert-p-received">—</span></div>
              <div class="fee-row total"><span>Total débité</span><span class="mono-font" id="transfert-p-total">—</span></div>
            </div>
            <button class="btn-confirm" onclick="doOperation('transfert')">Confirmer le transfert</button>
          </div>

          <!-- Multiple mode -->
          <div id="transfert-multiple" class="transfert-mode-content" style="display:none">
            <div class="mb-3">
              <label class="nm-label">Destinataires</label>
              <div id="recipients-list">
                <div class="recipient-row">
                  <input type="tel" class="nm-input mono-font recipient-phone" placeholder="0330000000" maxlength="10"
                    oninput="this.value=this.value.replace(/\D/g,'').slice(0,10);updateMultipleFeePreview();clearFeedback()">
                  <button class="btn-remove-recipient" onclick="removeRecipient(this)" style="visibility:hidden">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="18" y1="6" x2="6" y2="18"/>
                      <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                  </button>
                </div>
              </div>
              <button class="btn-add-recipient" onclick="addRecipient()">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="12" y1="5" x2="12" y2="19"/>
                  <line x1="5" y1="12" x2="19" y2="12"/>
                </svg>
                Ajouter un destinataire
              </button>
            </div>
            <div class="mb-3">
              <label class="nm-label" for="transfert-multiple-amount">Montant total à répartir (Ar)</label>
              <input id="transfert-multiple-amount" type="number" class="nm-input mono-font" placeholder="ex : 100 000" min="100"
                oninput="updateMultipleFeePreview();clearFeedback()">
            </div>
            <div id="transfert-multiple-preview" style="display:none" class="fee-preview mb-3">
              <div class="fee-row"><span>Montant total</span><span class="mono-font" id="transfert-m-amount">—</span></div>
              <div class="fee-row"><span>Nombre de destinataires</span><span class="mono-font" id="transfert-m-count">—</span></div>
              <div class="fee-row"><span>Par destinataire</span><span class="mono-font" id="transfert-m-per-person">—</span></div>
              <div class="fee-row"><span>Frais par transfert</span><span class="mono-font" id="transfert-m-fee">—</span></div>
              <div class="fee-row"><span>Frais total</span><span class="mono-font" id="transfert-m-total-fee">—</span></div>
              <div class="fee-row total"><span>Total débité</span><span class="mono-font" id="transfert-m-total">—</span></div>
            </div>
            <button class="btn-confirm" onclick="doOperation('transfert-multiple')">Confirmer les transferts</button>
          </div>
        </div>
      </div>

      <!-- ══ HISTORIQUE ═══════════════════════════════════════════ -->
      <div id="tab-history" class="tab-section">
        <div id="history-count" class="section-label"></div>
        <div id="history-list"></div>
      </div>

    </div><!-- /tab-inner -->
  </div><!-- /tab-content-area -->

  <script>
    window.clientLogoutUrl = <?= json_encode(base_url('client/logout'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.clientOperationUrl = <?= json_encode(base_url('client/operation'), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.clientAccount = <?= json_encode([
      'phone'   => $compte['number'],
      'balance' => (float) $compte['solde'],
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.clientTransactions = <?= json_encode($history ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.clientFeeSlabs = <?= json_encode([
      'retrait'   => array_map(fn ($s) => ['min' => (float) $s['min'], 'max' => (float) $s['max'], 'fee' => (float) $s['montant']], $feeSlabs['retrait'] ?? []),
      'transfert' => array_map(fn ($s) => ['min' => (float) $s['min'], 'max' => (float) $s['max'], 'fee' => (float) $s['montant']], $feeSlabs['transfert'] ?? []),
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
    window.clientPrefixes = <?= json_encode($prefixes ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>;
  </script>
  <script src="<?= base_url('vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
  <script src="<?= base_url('js/client-dashboard.js') ?>"></script>
</body>

</html>
