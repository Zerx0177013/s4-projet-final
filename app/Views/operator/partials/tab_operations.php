<?php
/**
 * Fichier indépendant : ce partiel n'utilise QUE la variable qui lui est
 * explicitement passée via view('operator/partials/tab_operations', [...]) :
 *
 * - array $typeOperations  ex: [
 *       ['id' => 1, 'libelle' => 'Depot', 'idBareme' => null, 'tranches' => []],
 *       ['id' => 2, 'libelle' => 'Retrait', 'idBareme' => 1, 'tranches' => [
 *           ['id' => 1, 'min' => 100, 'max' => 1000, 'montant' => 50, 'idBareme' => 1], ...
 *       ]],
 *       ...
 *   ]
 *   (fournie par TypeOperation::findAll() + Tranche::getSlabsByBareme())
 *
 * Le message d'erreur éventuel ('opError') est lu directement depuis la
 * flashdata de session (posée par TrancheController).
 */
$typeOperations ??= [];
$opError = session()->getFlashdata('opError');

$couleursParLibelle = [
    'Depot'     => '#34D399',
    'Retrait'   => '#F87171',
    'Transfert' => '#60A5FA',
];

if (! function_exists('operationsSlug')) {
    function operationsSlug(string $libelle): string
    {
        return strtolower(preg_replace('/[^A-Za-z0-9]/', '', $libelle)) ?: 'op';
    }
}
?>
            <!-- ════════════════════════════════════════
           TAB : OPÉRATIONS
      ══════════════════════════════════════ -->
            <div id="tab-operations" class="tab-section" style="display:none">
                <div class="page-title">Types d'opérations</div>
                <div class="page-subtitle">Barèmes de frais par tranche de montant (modifiables).</div>

                <?php if ($opError) : ?>
                    <div class="error-msg mb-3"><?= esc($opError) ?></div>
                <?php endif; ?>

                <div class="d-flex flex-column gap-3" id="operations-list">
                    <?php if (empty($typeOperations)) : ?>
                        <div class="nm-card p-4" style="color:var(--nm-muted)">
                            Aucun type d'opération enregistré.
                        </div>
                    <?php endif; ?>

                    <?php foreach ($typeOperations as $typeOperation) : ?>
                        <?php
                            $slug = operationsSlug($typeOperation['libelle']);
                            $couleur = $couleursParLibelle[$typeOperation['libelle']] ?? 'var(--nm-muted)';
                            $hasBareme = $typeOperation['idBareme'] !== null;
                            $tranches = $typeOperation['tranches'] ?? [];
                            $addFormId = 'tranche-add-' . $slug;
                        ?>
                        <div class="op-accordion">
                            <button class="op-header" onclick="toggleOp('<?= $slug ?>')">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="op-dot" style="background:<?= $couleur ?>"></span>
                                    <span style="font-weight:500"><?= esc($typeOperation['libelle']) ?></span>
                                    <?php if (! $hasBareme) : ?>
                                        <span class="op-badge">Sans frais</span>
                                    <?php endif; ?>
                                </div>
                                <svg class="chevron" id="chevron-<?= $slug ?>" style="transform:rotate(180deg)"
                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9" />
                                </svg>
                            </button>
                            <div class="op-body open" id="body-<?= $slug ?>">
                                <?php if (! $hasBareme) : ?>
                                    <div style="padding:1rem 1.25rem;font-size:.875rem;color:var(--nm-muted)">
                                        Aucun barème de frais pour cette opération.
                                    </div>
                                <?php else : ?>
                                    <table class="nm-table">
                                        <thead>
                                            <tr>
                                                <th>Tranche (Ar)</th>
                                                <th class="right">Frais (Ar)</th>
                                                <th class="right">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($tranches as $tranche) : ?>
                                                <?php $formId = 'tranche-form-' . $tranche['id']; ?>
                                                <tr>
                                                    <form id="<?= $formId ?>"
                                                        action="<?= base_url('operator/tranches/modifier/' . $tranche['id']) ?>"
                                                        method="post" style="display:none">
                                                        <?= csrf_field() ?>
                                                    </form>
                                                    <td>
                                                        <div class="fee-group">
                                                            <input form="<?= $formId ?>" type="number" name="min"
                                                                value="<?= (int) $tranche['min'] ?>" min="0"
                                                                step="1" required class="fee-input"
                                                                inputmode="numeric" aria-label="Minimum de la tranche">
                                                            <span class="fee-separator">—</span>
                                                            <input form="<?= $formId ?>" type="number" name="max"
                                                                value="<?= (int) $tranche['max'] ?>" min="0"
                                                                step="1" required class="fee-input"
                                                                inputmode="numeric" aria-label="Maximum de la tranche">
                                                        </div>
                                                    </td>
                                                    <td class="td-right">
                                                        <input form="<?= $formId ?>" type="number" name="montant"
                                                            value="<?= (int) $tranche['montant'] ?>" min="0" step="1"
                                                            required class="fee-input" inputmode="numeric"
                                                            aria-label="Frais de la tranche">
                                                    </td>
                                                    <td class="td-right">
                                                        <div class="fee-action-bar">
                                                        <button form="<?= $formId ?>" type="submit" class="btn-del"
                                                            title="Enregistrer" style="color:var(--nm-primary)">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="20 6 9 17 4 12" />
                                                            </svg>
                                                        </button>
                                                        <a class="btn-del"
                                                            href="<?= base_url('operator/tranches/supprimer/' . $tranche['id']) ?>"
                                                            onclick="return confirm('Supprimer cette tranche ?')"
                                                            title="Supprimer">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round">
                                                                <polyline points="3 6 5 6 21 6" />
                                                                <path d="M19 6l-1 14H6L5 6" />
                                                                <path d="M10 11v6" />
                                                                <path d="M14 11v6" />
                                                                <path d="M9 6V4h6v2" />
                                                            </svg>
                                                        </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr>
                                                <form id="<?= $addFormId ?>" action="<?= base_url('operator/tranches/ajouter') ?>"
                                                    method="post" style="display:none">
                                                    <?= csrf_field() ?>
                                                    <input type="hidden" name="idBareme"
                                                        value="<?= (int) $typeOperation['idBareme'] ?>">
                                                </form>
                                                <td>
                                                    <div class="fee-group">
                                                        <input form="<?= $addFormId ?>" type="number" name="min"
                                                            placeholder="min" min="0" step="1" required
                                                            class="fee-input" inputmode="numeric"
                                                            aria-label="Minimum de la nouvelle tranche">
                                                        <span class="fee-separator">—</span>
                                                        <input form="<?= $addFormId ?>" type="number" name="max"
                                                            placeholder="max" min="0" step="1" required
                                                            class="fee-input" inputmode="numeric"
                                                            aria-label="Maximum de la nouvelle tranche">
                                                    </div>
                                                </td>
                                                <td class="td-right">
                                                    <input form="<?= $addFormId ?>" type="number" name="montant"
                                                        placeholder="frais" min="0" step="1" required
                                                        class="fee-input" inputmode="numeric"
                                                        aria-label="Frais de la nouvelle tranche">
                                                </td>
                                                <td class="td-right">
                                                    <button form="<?= $addFormId ?>" type="submit"
                                                        class="btn-primary btn-square" title="Ajouter une tranche"
                                                        aria-label="Ajouter une tranche">
                                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
                                                            fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round">
                                                            <line x1="12" y1="5" x2="12" y2="19" />
                                                            <line x1="5" y1="12" x2="19" y2="12" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
