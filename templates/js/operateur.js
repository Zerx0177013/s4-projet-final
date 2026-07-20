/* ─── Data ─────────────────────────────────────────────────────────── */
let prefixes = ['033', '034', '037', '038'];

const BASE_SLABS = [
    { min: 100, max: 1000, fee: 50 },
    { min: 1001, max: 5000, fee: 50 },
    { min: 5001, max: 10000, fee: 100 },
    { min: 10001, max: 25000, fee: 200 },
    { min: 25001, max: 50000, fee: 400 },
    { min: 50001, max: 100000, fee: 800 },
    { min: 100001, max: 250000, fee: 1500 },
    { min: 250001, max: 500000, fee: 1500 },
    { min: 500001, max: 1000000, fee: 2500 },
    { min: 1000001, max: 2000000, fee: 3000 },
];

const operations = [
    { id: 'depot', name: 'Dépôt', color: '#34D399', hasFees: false, slabs: [] },
    { id: 'retrait', name: 'Retrait', color: '#F87171', hasFees: true, slabs: BASE_SLABS.map(s => ({ ...s })) },
    { id: 'transfert', name: 'Transfert', color: '#60A5FA', hasFees: true, slabs: BASE_SLABS.map(s => ({ ...s, fee: Math.round(s.fee * .7) })) },
];

const clients = [
    {
        phone: '0331234567', name: 'Rakoto Jean', balance: 450000,
        transactions: [
            { type: 'depot', amount: 500000, fee: 0, date: '18/07/2026 09:14' },
            { type: 'retrait', amount: 50000, fee: 800, date: '19/07/2026 14:22' },
        ]
    },
    {
        phone: '0371234567', name: 'Rasoa Marie', balance: 125000,
        transactions: [
            { type: 'depot', amount: 200000, fee: 0, date: '15/07/2026 10:00' },
            { type: 'retrait', amount: 75000, fee: 800, date: '17/07/2026 11:30' },
        ]
    },
    {
        phone: '0332345678', name: 'Rabe Pierre', balance: 78500,
        transactions: [
            { type: 'depot', amount: 100000, fee: 0, date: '16/07/2026 08:45' },
            { type: 'transfert', amount: 20000, fee: 140, date: '18/07/2026 15:10' },
        ]
    },
    {
        phone: '0373456789', name: 'Andry Lala', balance: 320000,
        transactions: [
            { type: 'depot', amount: 500000, fee: 0, date: '14/07/2026 12:00' },
            { type: 'retrait', amount: 180000, fee: 1500, date: '16/07/2026 14:00' },
        ]
    },
    {
        phone: '0334567890', name: 'Mialy Hery', balance: 55000,
        transactions: [
            { type: 'depot', amount: 100000, fee: 0, date: '20/07/2026 08:00' },
            { type: 'transfert', amount: 45000, fee: 315, date: '20/07/2026 09:30' },
        ]
    },
];

/* ─── Helpers ───────────────────────────────────────────────────────── */
function fmtAr(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' Ar' }
function fmtPhone(p) { return p.length === 10 ? `${p.slice(0, 3)} ${p.slice(3, 5)} ${p.slice(5, 8)} ${p.slice(8)}` : p }

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
    let totalR = 0, totalT = 0;
    const rows = clients.map(c => {
        let r = 0, t = 0;
        c.transactions.forEach(tx => {
            if (tx.type === 'retrait') r += tx.fee;
            if (tx.type === 'transfert') t += tx.fee;
        });
        totalR += r; totalT += t;
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
    document.getElementById('gain-retrait').textContent = fmtAr(totalR);
    document.getElementById('gain-transfert').textContent = fmtAr(totalT);
    document.getElementById('gain-total').textContent = fmtAr(totalR + totalT);
}

/* ─── Accounts ──────────────────────────────────────────────────────── */
function renderAccounts() {
    document.getElementById('accounts-subtitle').textContent =
        `${clients.length} compte${clients.length !== 1 ? 's' : ''} enregistré${clients.length !== 1 ? 's' : ''}.`;
    document.getElementById('accounts-tbody').innerHTML = clients.map(c => `
    <tr>
        <td>
            <div class="d-flex align-items-center gap-3">
                <div style="width:36px;height:36px;border-radius:50%;background:rgba(0,214,143,.1);
              color:#00D68F;display:flex;align-items:center;justify-content:center;
              font-size:.7rem;font-weight:700;flex-shrink:0">
                    ${c.name.split(' ').map(w => w[0]).join('').slice(0, 2).toUpperCase()}
                </div>
                <div>
                    <div style="font-weight:500">${c.name}</div>
                    <div class="mono" style="font-size:.7rem;color:var(--nm-muted)">${fmtPhone(c.phone)}</div>
                </div>
            </div>
        </td>
        <td class="td-right mono" style="color:#00D68F;font-weight:600">${fmtAr(c.balance)}</td>
        <td class="td-right d-none d-sm-table-cell" style="color:var(--nm-muted)">${c.transactions.length}</td>
    </tr>`).join('');
}

/* ─── Init ──────────────────────────────────────────────────────────── */
renderPrefixes();
renderOperations();