<?php
/**
 * Fichier indépendant : ce partiel n'utilise QUE la variable qui lui est
 * explicitement passée via view('operator/partials/tab_prefixes', [...]) :
 *
 * - array $prefixes  ex: [ ['id' => 1, 'idOperateur' => 2, 'prefix' => '033'], ... ]
 *   (fournie par PrefixOperateur::getPrefixesByOperateur())
 *
 * Le message d'erreur éventuel ('prefixError') est lu directement depuis la
 * flashdata de session (posée par PrefixeController::ajouter()).
 */
$prefixes ??= [];
$prefixError = session()->getFlashdata('prefixError');
?>
            <!-- ════════════════════════════════════════
           TAB : PRÉFIXES
      ══════════════════════════════════════ -->
            <div id="tab-prefixes" class="tab-section">
                <div class="page-title">Préfixes de l'opérateur</div>
                <div class="page-subtitle">Numéros de téléphone valides pour cet opérateur.</div>

                <div class="row g-3 mb-4" id="prefix-grid">
                    <?php if (empty($prefixes)) : ?>
                        <div class="col-12">
                            <div class="nm-card p-4" style="color:var(--nm-muted)">
                                Aucun préfixe enregistré pour cet opérateur.
                            </div>
                        </div>
                    <?php else : ?>
                        <?php foreach ($prefixes as $prefixe) : ?>
                            <div class="col-6 col-sm-4 col-md-3">
                                <div class="prefix-chip">
                                    <span class="prefix-val"><?= esc($prefixe['prefix']) ?></span>
                                    <a class="btn-del" href="<?= base_url('operator/prefixes/supprimer/' . (int) $prefixe['id']) ?>"
                                        onclick="return confirm('Supprimer le préfixe <?= esc($prefixe['prefix'], 'js') ?> ?')">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round">
                                            <polyline points="3 6 5 6 21 6" />
                                            <path d="M19 6l-1 14H6L5 6" />
                                            <path d="M10 11v6" />
                                            <path d="M14 11v6" />
                                            <path d="M9 6V4h6v2" />
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="nm-card p-4" style="max-width:320px">
                    <div style="font-size:.875rem;font-weight:500;margin-bottom:.75rem">Ajouter un préfixe</div>
                    <form action="<?= base_url('operator/prefixes/ajouter') ?>" method="post" onsubmit="return addPrefix()">
                        <?= csrf_field() ?>
                        <div class="d-flex gap-2">
                            <input id="new-prefix-input" name="prefix" type="text" class="nm-input" placeholder="034"
                                maxlength="3" oninput="this.value=this.value.replace(/\D/g,'').slice(0,3)">
                            <button type="submit" class="btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <line x1="12" y1="5" x2="12" y2="19" />
                                    <line x1="5" y1="12" x2="19" y2="12" />
                                </svg>
                            </button>
                        </div>
                    </form>
                    <div id="prefix-error" class="error-msg" style="<?= $prefixError ? '' : 'display:none' ?>">
                        <?= esc($prefixError ?? '') ?>
                    </div>
                </div>
            </div>
