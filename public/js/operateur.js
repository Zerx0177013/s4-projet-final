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

/* ─── Prefixes ──────────────────────────────────────────────────────── */
function renderPrefixes() {
    const grid = document.getElementById('prefix-grid');
    grid.innerHTML = prefixes.map(p => `
    <div class="col-6 col-sm-4 col-md-3">
        <div class="prefix-chip">
            <span class="prefix-val">${p}</span>
            <button class="btn-del" onclick="deletePrefix('${p}')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6" /><path d="M19 6l-1 14H6L5 6" /><path d="M10 11v6" /><path d="M14 11v6" /><path d="M9 6V4h6v2" /></svg>
            </button>
        </div>
    </div>`).join('');
}

function addPrefix() {
    const input = document.getElementById('new-prefix-input');
    const errEl = document.getElementById('prefix-error');
    const p = input.value.trim();
    if (!/^\d{3}$/.test(p)) { errEl.textContent = 'Préfixe invalide (3 chiffres requis).'; errEl.style.display = 'block'; return }
    if (prefixes.includes(p)) { errEl.textContent = 'Ce préfixe existe déjà.'; errEl.style.display = 'block'; return }
    prefixes.push(p);
    input.value = '';
    errEl.style.display = 'none';
    renderPrefixes();
}

function deletePrefix(p) {
    prefixes = prefixes.filter(x => x !== p);
    renderPrefixes();
}

/* ─── Operations ────────────────────────────────────────────────────── */
function renderOperations() {
    const list = document.getElementById('operations-list');
    list.innerHTML = operations.map(op => `
    <div class="op-accordion">
        <button class="op-header" onclick="toggleOp('${op.id}')">
            <div class="d-flex align-items-center gap-3">
                <span class="op-dot" style="background:${op.color}"></span>
                <span style="font-weight:500">${op.name}</span>
                ${!op.hasFees ? '<span class="op-badge">Sans frais</span>' : ''}
            </div>
            <svg class="chevron" id="chevron-${op.id}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9" /></svg>
        </button>
        <div class="op-body ${op.id === 'retrait' ? 'open' : ''}" id="body-${op.id}">
            ${op.hasFees
            ? `<table class="nm-table">
                <thead><tr>
                  <th>Tranche (Ar)</th>
                  <th class="right">Frais (Ar)</th>
                </tr></thead>
                <tbody>${op.slabs.map((s, i) => `
                  <tr>
                    <td class="mono" style="font-size:.75rem;color:var(--nm-muted)">
                      ${new Intl.NumberFormat('fr-FR').format(s.min)} – ${new Intl.NumberFormat('fr-FR').format(s.max)}
                    </td>
                    <td class="td-right">
                      <input type="number" class="fee-input" value="${s.fee}"
                        onchange="updateFee('${op.id}',${i},this.value)">
                    </td>
                  </tr>`).join('')}
                </tbody></table>`
            : `<div style="padding:1rem 1.25rem;font-size:.875rem;color:var(--nm-muted)">Aucun barème de frais pour cette opération.</div>`
        }
        </div>
    </div>`).join('');
    // Open retrait chevron by default
    const ch = document.getElementById('chevron-retrait');
    if (ch) ch.style.transform = 'rotate(180deg)';
}

function toggleOp(id) {
    const body = document.getElementById('body-' + id);
    const ch = document.getElementById('chevron-' + id);
    const open = body.classList.toggle('open');
    ch.style.transform = open ? 'rotate(180deg)' : 'rotate(0deg)';
}

function updateFee(opId, idx, val) {
    const op = operations.find(o => o.id === opId);
    if (op) op.slabs[idx].fee = parseInt(val) || 0;
}

/* ─── Gains ─────────────────────────────────────────────────────────── */
function renderGains() {
    // Totaux réels, fournis par DashboardController::afficherGainParOperateur() via window.operatorGains
    document.getElementById('gain-retrait').textContent = fmtAr(operatorGains.retrait || 0);
    document.getElementById('gain-transfert').textContent = fmtAr(operatorGains.transfert || 0);
    document.getElementById('gain-total').textContent = fmtAr(operatorGains.total || 0);

    // Détail par client : pas fourni par afficherGainParOperateur (agrégat par opérateur uniquement),
    // on continue d'afficher la répartition à partir des données de démonstration.
    const rows = clients.map(c => {
        let r = 0, t = 0;
        c.transactions.forEach(tx => {
            if (tx.type === 'retrait') r += tx.fee;
            if (tx.type === 'transfert') t += tx.fee;
        });
        return `<tr>
        <td>
            <div style="font-weight:500">${c.name}</div>
            <div class="mono" style="font-size:.7rem;color:var(--nm-muted)">${fmtPhone(c.phone)}</div>
        </td>
        <td class="td-right mono" style="color:#F87171">${fmtAr(r)}</td>
        <td class="td-right mono" style="color:#60A5FA">${fmtAr(t)}</td>
        <td class="td-right mono" style="color:#00D68F;font-weight:600">${fmtAr(r + t)}</td>
    </tr>`;
    }).join('');
    document.getElementById('gains-tbody').innerHTML = rows;
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

/* ─── Init ──────────────────────────────────────────────────────────── */
renderPrefixes();
renderOperations();
