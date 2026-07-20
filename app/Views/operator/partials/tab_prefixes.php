<?php
/**
 * Fichier indépendant : ne dépend d'aucune variable transmise par le parent.
 * Les préfixes sont entièrement gérés côté client par js/operateur.js.
 */
?>
            <!-- ════════════════════════════════════════
           TAB : PRÉFIXES
      ══════════════════════════════════════ -->
            <div id="tab-prefixes" class="tab-section">
                <div class="page-title">Préfixes de l'opérateur</div>
                <div class="page-subtitle">Numéros de téléphone valides pour cet opérateur.</div>

                <div class="row g-3 mb-4" id="prefix-grid">
                    <!-- généré par JS -->
                </div>

                <div class="nm-card p-4" style="max-width:320px">
                    <div style="font-size:.875rem;font-weight:500;margin-bottom:.75rem">Ajouter un préfixe</div>
                    <div class="d-flex gap-2">
                        <input id="new-prefix-input" type="text" class="nm-input" placeholder="034" maxlength="3"
                            oninput="this.value=this.value.replace(/\D/g,'').slice(0,3)"
                            onkeydown="if(event.key==='Enter')addPrefix()">
                        <button class="btn-primary" onclick="addPrefix()">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19" />
                                <line x1="5" y1="12" x2="19" y2="12" />
                            </svg>
                        </button>
                    </div>
                    <div id="prefix-error" class="error-msg" style="display:none"></div>
                </div>
            </div>
