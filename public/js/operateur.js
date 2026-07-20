/* ─── Data ─────────────────────────────────────────────────────────── */

const accountClients = Array.isArray(window.operatorAccounts) ? window.operatorAccounts : [];
const operatorGains = window.operatorGains || { depot: 0, retrait: 0, transfert: 0, total: 0 };

/* ─── Helpers ───────────────────────────────────────────────────────── */
function fmtAr(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' Ar' }
function fmtPhone(p) { return p.length === 10 ? `${p.slice(0, 3)} ${p.slice(3, 5)} ${p.slice(5, 8)} ${p.slice(8)}` : p }
function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, c => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    }[c]));
}

/* ─── Tab switching ─────────────────────────────────────────────────── */
function switchTab(id, btn) {
    document.querySelectorAll('.tab-section').forEach(el => el.style.display = 'none');
    document.getElementById('tab-' + id).style.display = 'block';
    document.querySelectorAll('.nav-btn[data-tab]').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('[data-tab="' + id + '"]').forEach(b => b.classList.add('active'));
    if (id === 'gains') renderGains();
    if (id === 'accounts') renderAccounts();
}

// Si l'URL contient ?tab=xxx (ex: après un ajout/suppression), on active cet onglet.
(function activateTabFromQueryString() {
    const tab = new URLSearchParams(window.location.search).get('tab');
    if (!tab || !document.getElementById('tab-' + tab)) return;
    const btn = document.querySelector('[data-tab="' + tab + '"]');
    switchTab(tab, btn);
})();

/* ─── Prefixes ──────────────────────────────────────────────────────── */
// Les préfixes sont gérés côté serveur (voir operator/partials/tab_prefixes.php
// et PrefixeController). Le formulaire d'ajout se contente de valider la saisie avant envoi.
function addPrefix() {
    const input = document.getElementById('new-prefix-input');
    const errEl = document.getElementById('prefix-error');
    const p = input.value.trim();
    if (!/^\d{3}$/.test(p)) {
        errEl.textContent = 'Préfixe invalide (3 chiffres requis).';
        errEl.style.display = 'block';
        return false;
    }
    errEl.style.display = 'none';
    return true;
}

/* ─── Operations ────────────────────────────────────────────────────── */
// Les types d'opérations et leurs tranches de frais sont gérés côté serveur
// (voir operator/partials/tab_operations.php et TrancheController).
function toggleOp(id) {
    const body = document.getElementById('body-' + id);
    const ch = document.getElementById('chevron-' + id);
    const open = body.classList.toggle('open');
    ch.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}

/* ─── Gains ─────────────────────────────────────────────────────────── */
function renderGains() {
    // Totaux et détail par client rendus côté serveur (voir operator/operator.php)
    // à partir de window.operatorGains / $liste fournis par
    // DashboardController::afficherGainParOperateur(). Rien à recalculer ici.
    document.getElementById('gain-retrait').textContent = fmtAr(operatorGains.retrait || 0);
    document.getElementById('gain-transfert').textContent = fmtAr(operatorGains.transfert || 0);
    document.getElementById('gain-total').textContent = fmtAr(operatorGains.total || 0);
}

/* ─── Accounts ──────────────────────────────────────────────────────── */
function renderAccounts() {
    const accounts = accountClients;

    document.getElementById('accounts-subtitle').textContent =
        `${accounts.length} compte${accounts.length !== 1 ? 's' : ''} enregistré${accounts.length !== 1 ? 's' : ''}.`;

    document.getElementById('accounts-tbody').innerHTML = accounts.map(c => {
        const name = escapeHtml(c.name);
        const phone = escapeHtml(c.phone || c.number || '');
        const initials = name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase();
        const balance = Number(c.balance || c.solde || 0);
        const transactionsCount = Number(c.transactionsCount || 0);

        return `
    <tr>
        <td>
            <div class="d-flex align-items-center gap-3">
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(0,214,143,.1);
              color:#00D68F;display:flex;align-items:center;justify-content:center;
              font-size:.7rem;font-weight:700;flex-shrink:0">
                    ${initials}
                </div>
                <div>
                    <div style="font-weight:500">${name}</div>
                    <div class="mono" style="font-size:.7rem;color:var(--nm-muted)">${fmtPhone(phone)}</div>
                </div>
            </div>
        </td>
        <td class="td-right mono" style="color:#00D68F;font-weight:600">${fmtAr(balance)}</td>
        <td class="td-right d-none d-sm-table-cell" style="color:var(--nm-muted)">${transactionsCount}</td>
    </tr>`;
    }).join('');
}
