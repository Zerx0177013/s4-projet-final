/* ─── Config ────────────────────────────────────────────────────────── */
const PREFIXES = ['033', '034', '037', '038'];

const SLABS_RETRAIT = [
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
const SLABS_TRANSFERT = SLABS_RETRAIT.map(s => ({ ...s, fee: Math.round(s.fee * .7) }));

/* ─── State ─────────────────────────────────────────────────────────── */
/* En production, charger depuis le serveur via AJAX ou variables PHP */
const DEMO_CLIENTS = {
    '0331234567': {
        name: 'Rakoto Jean', balance: 450000, transactions: [
            { type: 'depot', amount: 500000, fee: 0, date: '18/07/2026 09:14', to: null, balance_after: 500000 },
            { type: 'retrait', amount: 50000, fee: 800, date: '19/07/2026 14:22', to: null, balance_after: 449200 },
        ]
    },
    '0371234567': {
        name: 'Rasoa Marie', balance: 125000, transactions: [
            { type: 'depot', amount: 200000, fee: 0, date: '15/07/2026 10:00', to: null, balance_after: 200000 },
        ]
    },
    '0332345678': {
        name: 'Rabe Pierre', balance: 78500, transactions: [
            { type: 'depot', amount: 100000, fee: 0, date: '16/07/2026 08:45', to: null, balance_after: 100000 },
            { type: 'transfert', amount: 20000, fee: 140, date: '18/07/2026 15:10', to: '0371234567', balance_after: 79860 },
        ]
    },
};

let phone = sessionStorage.getItem('nm_phone') || '0331234567'; /* fallback démo */
let client;

function initClient() {
    if (DEMO_CLIENTS[phone]) {
        client = JSON.parse(JSON.stringify(DEMO_CLIENTS[phone]));
    } else {
        client = { name: 'Client ' + phone, balance: 0, transactions: [] };
    }
}

/* ─── Helpers ───────────────────────────────────────────────────────── */
function fmtAr(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' Ar' }
function fmtPhone(p) { return p.length === 10 ? `${p.slice(0, 3)} ${p.slice(3, 5)} ${p.slice(5, 8)} ${p.slice(8)}` : p }
function nowStr() { return new Date().toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' }) }
function getFee(slabs, amt) {
    const s = slabs.find(s => amt >= s.min && amt <= s.max);
    if (s) return s.fee;
    if (amt > slabs[slabs.length - 1].max) return slabs[slabs.length - 1].fee;
    return 0;
}

const TX_CFG = {
    depot: { label: 'Dépôt', color: '#34D399', iconPath: '<line x1="12" y1="19" x2="12" y2="5"/><polyline points="5 12 12 19 19 12"/>' },
    retrait: { label: 'Retrait', color: '#F87171', iconPath: '<line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 5 5 12"/>' },
    transfert: { label: 'Transfert', color: '#60A5FA', iconPath: '<line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>' },
};
const TX_BG = { depot: 'rgba(52,211,153,.1)', retrait: 'rgba(248,113,113,.1)', transfert: 'rgba(96,165,250,.1)' };

/* ─── UI updates ────────────────────────────────────────────────────── */
function updateHeader() {
    document.getElementById('header-name').textContent = client.name;
    document.getElementById('header-phone').textContent = fmtPhone(phone);
    document.getElementById('balance-display').textContent = fmtAr(client.balance);
}

function renderMiniStats() {
    const totals = { depot: 0, retrait: 0, transfert: 0 };
    client.transactions.forEach(t => totals[t.type] += t.amount);
    document.getElementById('mini-stats').innerHTML =
        Object.entries(totals).map(([type, total]) => `
        <div class="mini-stat">
          <div class="mini-stat-label">${TX_CFG[type].label}s</div>
          <div class="mini-stat-val" style="color:${TX_CFG[type].color}">
            ${new Intl.NumberFormat('fr-FR').format(total)}
          </div>
        </div>`).join('');
}

function txItemHtml(tx, mini = false) {
    const cfg = TX_CFG[tx.type];
    return `<div class="${mini ? 'tx-item' : 'history-item'}">
      ${mini ? `
        <div class="tx-icon" style="background:${TX_BG[tx.type]}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="${cfg.color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${cfg.iconPath}</svg>
        </div>
        <div class="tx-info">
          <div class="tx-type" style="color:${cfg.color}">${cfg.label}</div>
          <div class="tx-date">${tx.date}</div>
        </div>
        <div>
          <div class="tx-amount" style="color:var(--nm-text)">${fmtAr(tx.amount)}</div>
          ${tx.fee > 0 ? `<div class="tx-fee">Frais : ${fmtAr(tx.fee)}</div>` : ''}
        </div>`
            : `
        <div class="hi-top">
          <div class="hi-type" style="color:${cfg.color}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="${cfg.color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px">${cfg.iconPath}</svg>
            ${cfg.label}
          </div>
          <div class="hi-amount" style="color:var(--nm-text)">${fmtAr(tx.amount)}</div>
        </div>
        <div class="hi-bottom">
          <span class="hi-date">${tx.date}</span>
          <span class="hi-balance">Solde : ${fmtAr(tx.balance_after)}</span>
        </div>
        <div class="hi-extra">
          ${tx.fee > 0 ? `Frais : ${fmtAr(tx.fee)}` : ''}
          ${tx.to ? `→ ${fmtPhone(tx.to)}` : ''}
        </div>`
        }
    </div>`;
}

function renderRecentTx() {
    const list = document.getElementById('recent-tx-list');
    if (!client.transactions.length) {
        list.innerHTML = `<div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Aucune transaction pour le moment
      </div>`; return;
    }
    list.innerHTML = client.transactions.slice(0, 6).map(tx => txItemHtml(tx, true)).join('');
}

function renderHistory() {
    const txs = client.transactions;
    document.getElementById('history-count').textContent =
        `${txs.length} transaction${txs.length !== 1 ? 's' : ''}`;
    const list = document.getElementById('history-list');
    if (!txs.length) {
        list.innerHTML = `<div class="empty-state">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Aucune transaction
      </div>`; return;
    }
    list.innerHTML = txs.map(tx => txItemHtml(tx, false)).join('');
}

/* ─── Fee preview ───────────────────────────────────────────────────── */
function updateFeePreview(type) {
    const slabs = type === 'retrait' ? SLABS_RETRAIT : SLABS_TRANSFERT;
    const amt = parseInt(document.getElementById(type + '-amount').value);
    const preview = document.getElementById(type + '-preview');
    if (!amt || amt < 100) { preview.style.display = 'none'; return }
    const fee = getFee(slabs, amt);
    preview.style.display = 'block';
    document.getElementById(type + '-p-amount').textContent = fmtAr(amt);
    document.getElementById(type + '-p-fee').textContent = fmtAr(fee);
    document.getElementById(type + '-p-total').textContent = fmtAr(amt + fee);
}

/* ─── Feedback ──────────────────────────────────────────────────────── */
let fbTimer;
function showFeedback(ok, msg) {
    clearTimeout(fbTimer);
    const el = document.getElementById('feedback');
    const icon = document.getElementById('feedback-icon');
    el.className = 'feedback-toast ' + (ok ? 'success' : 'error');
    document.getElementById('feedback-msg').textContent = msg;
    icon.innerHTML = ok
        ? '<polyline points="20 6 9 17 4 12"/>'
        : '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>';
    el.style.display = 'flex';
    fbTimer = setTimeout(() => el.style.display = 'none', 4500);
}
function clearFeedback() { document.getElementById('feedback').style.display = 'none' }

/* ─── Operations ────────────────────────────────────────────────────── */
function doOperation(type) {
    clearFeedback();
    const amtInput = document.getElementById(type + '-amount');
    const amt = parseInt(amtInput.value);
    if (!amt || amt < 100) { showFeedback(false, 'Montant minimum : 100 Ar.'); return }

    let fee = 0;
    if (type === 'retrait') fee = getFee(SLABS_RETRAIT, amt);
    if (type === 'transfert') fee = getFee(SLABS_TRANSFERT, amt);

    if (type !== 'depot' && client.balance < amt + fee) {
        showFeedback(false, `Solde insuffisant. Disponible : ${fmtAr(client.balance)}.`); return;
    }

    let target = '';
    if (type === 'transfert') {
        target = document.getElementById('transfert-target').value.replace(/\s/g, '');
        if (target.length !== 10) { showFeedback(false, 'Numéro destinataire invalide.'); return }
        if (!PREFIXES.includes(target.slice(0, 3))) { showFeedback(false, `Préfixe ${target.slice(0, 3)} non reconnu.`); return }
        if (target === phone) { showFeedback(false, 'Impossible de vous envoyer à vous-même.'); return }
    }

    /* Mise à jour du solde */
    if (type === 'depot') client.balance += amt;
    else client.balance -= (amt + fee);

    /* Ajout de la transaction */
    client.transactions.unshift({
        type, amount: amt, fee, date: nowStr(),
        to: target || null,
        balance_after: client.balance,
    });

    /* Reset form */
    amtInput.value = '';
    if (type === 'transfert') document.getElementById('transfert-target').value = '';
    document.getElementById(type + '-preview') &&
        (document.getElementById(type + '-preview').style.display = 'none');

    /* Feedback */
    const msgs = {
        depot: `Dépôt de ${fmtAr(amt)} effectué avec succès.`,
        retrait: `Retrait de ${fmtAr(amt)} effectué. Frais : ${fmtAr(fee)}.`,
        transfert: `${fmtAr(amt)} envoyé à ${fmtPhone(target)}. Frais : ${fmtAr(fee)}.`,
    };
    showFeedback(true, msgs[type]);

    /* Refresh UI */
    document.getElementById('balance-display').textContent = fmtAr(client.balance);
    renderMiniStats();
    renderRecentTx();
    renderHistory();
}

/* ─── Tab switching ─────────────────────────────────────────────────── */
function switchTab(id, btn) {
    document.querySelectorAll('.tab-section').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + id).classList.add('active');
    document.querySelectorAll('.tab-nav-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    clearFeedback();
    if (id === 'history') renderHistory();
}

/* ─── Logout ────────────────────────────────────────────────────────── */
function logout() {
    sessionStorage.removeItem('nm_phone');
    window.location.href = 'client-login.html';
}

/* ─── Init ──────────────────────────────────────────────────────────── */
initClient();
updateHeader();
renderMiniStats();
renderRecentTx();