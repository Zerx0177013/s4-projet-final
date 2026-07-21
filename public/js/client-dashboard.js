/* ─── Config & données (fournies par le serveur, aucune donnée statique) ── */
const PREFIXES = window.clientPrefixes || [];
const PREFIXES_DATA = window.clientPrefixesData || {};
const SLABS_RETRAIT = (window.clientFeeSlabs && window.clientFeeSlabs.retrait) || [];
const SLABS_TRANSFERT = (window.clientFeeSlabs && window.clientFeeSlabs.transfert) || [];

/* ─── State ─────────────────────────────────────────────────────────── */
const phone = (window.clientAccount && window.clientAccount.phone) || '';
const client = {
    balance: (window.clientAccount && window.clientAccount.balance) || 0,
    operateurId: (window.clientAccount && window.clientAccount.operateurId) || 0,
    transactions: Array.isArray(window.clientTransactions) ? window.clientTransactions : [],
};

/* ─── Helpers ───────────────────────────────────────────────────────── */
function fmtAr(n) { return new Intl.NumberFormat('fr-FR').format(n) + ' Ar' }
function fmtPhone(p) { return p && p.length === 10 ? `${p.slice(0, 3)} ${p.slice(3, 5)} ${p.slice(5, 8)} ${p.slice(8)}` : p }
function fmtDate(d) {
    const date = new Date(String(d).replace(' ', 'T'));
    return isNaN(date) ? d : date.toLocaleString('fr-FR', { dateStyle: 'short', timeStyle: 'short' });
}
function getFee(slabs, amt) {
    const s = slabs.find(s => amt >= s.min && amt <= s.max);
    if (s) return s.fee;
    if (slabs.length && amt > slabs[slabs.length - 1].max) return slabs[slabs.length - 1].fee;
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
    document.getElementById('header-name').textContent = 'Client ' + phone;
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
    const counterpart = tx.to || tx.from;
    return `<div class="${mini ? 'tx-item' : 'history-item'}">
      ${mini ? `
        <div class="tx-icon" style="background:${TX_BG[tx.type]}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="${cfg.color}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">${cfg.iconPath}</svg>
        </div>
        <div class="tx-info">
          <div class="tx-type" style="color:${cfg.color}">${cfg.label}</div>
          <div class="tx-date">${fmtDate(tx.date)}</div>
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
          <span class="hi-date">${fmtDate(tx.date)}</span>
          <span class="hi-balance">Solde : ${fmtAr(tx.balance_after)}</span>
        </div>
        <div class="hi-extra">
          ${tx.fee > 0 ? `Frais : ${fmtAr(tx.fee)}` : ''}
          ${counterpart ? `${tx.to ? '→' : '←'} ${fmtPhone(counterpart)}` : ''}
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

    if (type === 'transfert') {
        const targetNumber = document.getElementById('transfert-target').value.replace(/\s/g, '');
        const includeFee = document.getElementById('transfert-include-fee')?.checked || false;

        // Calculer la commission inter-opérateur
        let commission = 0;
        const commissionRow = document.getElementById('transfert-commission-row');

        if (targetNumber.length === 10) {
            const targetPrefix = targetNumber.slice(0, 3);
            const targetPrefixData = PREFIXES_DATA[targetPrefix];

            if (targetPrefixData && targetPrefixData.operateurId !== client.operateurId) {
                // Opérateur différent - calculer la commission
                commission = Math.round(amt * (targetPrefixData.commission || 0) / 100);
                commissionRow.style.display = 'flex';
                document.getElementById('transfert-p-commission').textContent = fmtAr(commission);
            } else {
                commissionRow.style.display = 'none';
            }
        } else {
            commissionRow.style.display = 'none';
        }

        if (includeFee) {
            // Frais inclus : ajouter les frais de retrait pour que le destinataire puisse retirer sans frais
            const retraitFee = getFee(SLABS_RETRAIT, amt);
            const amountReceived = amt + retraitFee; // Le destinataire reçoit plus pour couvrir son retrait
            const totalDebit = amt + fee + retraitFee + commission;

            document.getElementById(type + '-p-amount').textContent = fmtAr(amt);
            document.getElementById(type + '-p-fee').textContent = fmtAr(fee) + ' + ' + fmtAr(retraitFee) + ' (retrait)';
            document.getElementById('transfert-p-received-label').textContent = 'Destinataire reçoit';
            document.getElementById('transfert-p-received').textContent = fmtAr(amountReceived);
            document.getElementById(type + '-p-total').textContent = fmtAr(totalDebit);
        } else {
            // Frais ajoutés au montant
            document.getElementById(type + '-p-amount').textContent = fmtAr(amt);
            document.getElementById(type + '-p-fee').textContent = fmtAr(fee);
            document.getElementById('transfert-p-received-label').textContent = 'Destinataire reçoit';
            document.getElementById('transfert-p-received').textContent = fmtAr(amt);
            document.getElementById(type + '-p-total').textContent = fmtAr(amt + fee + commission);
        }
    } else {
        document.getElementById(type + '-p-amount').textContent = fmtAr(amt);
        document.getElementById(type + '-p-fee').textContent = fmtAr(fee);
        document.getElementById(type + '-p-total').textContent = fmtAr(amt + fee);
    }
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

/* ─── Operations (persistées côté serveur, via ClientController::operate) ── */
async function doOperation(type) {
    clearFeedback();

    // Gestion des transferts multiples
    if (type === 'transfert-multiple') {
        return await doMultipleTransfert();
    }

    const amtInput = document.getElementById(type + '-amount');
    const amt = parseInt(amtInput.value);
    if (!amt || amt < 100) { showFeedback(false, 'Montant minimum : 100 Ar.'); return }

    let target = '';
    let includeFee = false;

    if (type === 'transfert') {
        target = document.getElementById('transfert-target').value.replace(/\s/g, '');
        if (target.length !== 10) { showFeedback(false, 'Numéro destinataire invalide.'); return }
        if (!PREFIXES.includes(target.slice(0, 3))) { showFeedback(false, `Préfixe ${target.slice(0, 3)} non reconnu.`); return }
        if (target === phone) { showFeedback(false, 'Impossible de vous envoyer à vous-même.'); return }
        includeFee = document.getElementById('transfert-include-fee')?.checked || false;
    }

    const payload = { type, amount: amt };
    if (type === 'transfert') {
        payload.target = target;
        payload.includeFee = includeFee;
    }

    let result;
    try {
        const res = await fetch(window.clientOperationUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        result = await res.json();
    } catch (err) {
        showFeedback(false, 'Erreur réseau. Veuillez réessayer.');
        return;
    }

    if (!result.success) {
        showFeedback(false, result.message || 'Opération refusée.');
        return;
    }

    /* Mise à jour de l'état local à partir de la réponse serveur (autorité) */
    client.balance = result.balance;
    client.transactions.unshift(result.transaction);

    /* Reset form */
    amtInput.value = '';
    if (type === 'transfert') {
        document.getElementById('transfert-target').value = '';
        document.getElementById('transfert-include-fee').checked = false;
    }
    document.getElementById(type + '-preview') &&
        (document.getElementById(type + '-preview').style.display = 'none');

    /* Feedback */
    const fee = result.transaction.fee;
    const amountReceived = result.transaction.amountReceived;
    const msgs = {
        depot: `Dépôt de ${fmtAr(amt)} effectué avec succès.`,
        retrait: `Retrait de ${fmtAr(amt)} effectué. Frais : ${fmtAr(fee)}.`,
        transfert: includeFee
            ? `${fmtAr(amt)} envoyé à ${fmtPhone(target)} (frais inclus). Le destinataire reçoit ${fmtAr(amountReceived)}.`
            : `${fmtAr(amt)} envoyé à ${fmtPhone(target)}. Frais : ${fmtAr(fee)}.`,
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

/* ─── Transfert mode switching ────────────────────────────────────── */
function switchTransfertMode(mode) {
    document.querySelectorAll('.mode-tab').forEach(tab => tab.classList.remove('active'));
    document.querySelector(`.mode-tab[data-mode="${mode}"]`).classList.add('active');

    if (mode === 'simple') {
        document.getElementById('transfert-simple').style.display = 'block';
        document.getElementById('transfert-multiple').style.display = 'none';
    } else {
        document.getElementById('transfert-simple').style.display = 'none';
        document.getElementById('transfert-multiple').style.display = 'block';
    }

    clearFeedback();
}

/* ─── Recipients management ───────────────────────────────────────── */
function addRecipient() {
    const list = document.getElementById('recipients-list');
    const row = document.createElement('div');
    row.className = 'recipient-row';
    row.innerHTML = `
        <input type="tel" class="nm-input mono-font recipient-phone" placeholder="0330000000" maxlength="10"
            oninput="this.value=this.value.replace(/\\D/g,'').slice(0,10);updateMultipleFeePreview();clearFeedback()">
        <button class="btn-remove-recipient" onclick="removeRecipient(this)">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    `;
    list.appendChild(row);
    updateRemoveButtonsVisibility();
}

function removeRecipient(btn) {
    btn.parentElement.remove();
    updateRemoveButtonsVisibility();
    updateMultipleFeePreview();
}

function updateRemoveButtonsVisibility() {
    const rows = document.querySelectorAll('.recipient-row');
    rows.forEach((row, idx) => {
        const removeBtn = row.querySelector('.btn-remove-recipient');
        if (rows.length === 1) {
            removeBtn.style.visibility = 'hidden';
        } else {
            removeBtn.style.visibility = 'visible';
        }
    });
}

/* ─── Multiple transfert preview ─────────────────────────────────── */
function updateMultipleFeePreview() {
    const amt = parseInt(document.getElementById('transfert-multiple-amount').value);
    const preview = document.getElementById('transfert-multiple-preview');
    const recipients = document.querySelectorAll('.recipient-phone');
    const validRecipients = Array.from(recipients)
        .map(input => input.value.replace(/\s/g, ''))
        .filter(num => num.length === 10);

    if (!amt || amt < 100 || validRecipients.length === 0) {
        preview.style.display = 'none';
        return;
    }

    const perPerson = Math.floor(amt / validRecipients.length);
    const fee = getFee(SLABS_TRANSFERT, perPerson);
    const totalFee = fee * validRecipients.length;

    // Calculer la commission totale pour tous les destinataires
    let totalCommission = 0;
    validRecipients.forEach(targetNumber => {
        const targetPrefix = targetNumber.slice(0, 3);
        const targetPrefixData = PREFIXES_DATA[targetPrefix];

        if (targetPrefixData && targetPrefixData.operateurId !== client.operateurId) {
            // Opérateur différent - calculer la commission pour ce destinataire
            const commission = Math.round(perPerson * (targetPrefixData.commission || 0) / 100);
            totalCommission += commission;
        }
    });

    // Afficher ou masquer la ligne commission
    const commissionRow = document.getElementById('transfert-m-commission-row');
    if (totalCommission > 0) {
        commissionRow.style.display = 'flex';
        document.getElementById('transfert-m-commission').textContent = fmtAr(totalCommission);
    } else {
        commissionRow.style.display = 'none';
    }

    preview.style.display = 'block';
    document.getElementById('transfert-m-amount').textContent = fmtAr(amt);
    document.getElementById('transfert-m-count').textContent = validRecipients.length;
    document.getElementById('transfert-m-per-person').textContent = fmtAr(perPerson);
    document.getElementById('transfert-m-fee').textContent = fmtAr(fee);
    document.getElementById('transfert-m-total-fee').textContent = fmtAr(totalFee);
    document.getElementById('transfert-m-total').textContent = fmtAr(amt + totalFee + totalCommission);
}

/* ─── Multiple transfert execution ──────────────────────────────── */
async function doMultipleTransfert() {
    const amt = parseInt(document.getElementById('transfert-multiple-amount').value);
    const recipients = Array.from(document.querySelectorAll('.recipient-phone'))
        .map(input => input.value.replace(/\s/g, ''))
        .filter(num => num.length === 10);

    if (!amt || amt < 100) {
        showFeedback(false, 'Montant minimum : 100 Ar.');
        return;
    }

    if (recipients.length === 0) {
        showFeedback(false, 'Ajoutez au moins un destinataire valide.');
        return;
    }

    // Validation des numéros
    for (const target of recipients) {
        if (!PREFIXES.includes(target.slice(0, 3))) {
            showFeedback(false, `Préfixe ${target.slice(0, 3)} non reconnu.`);
            return;
        }
        if (target === phone) {
            showFeedback(false, 'Impossible de vous envoyer à vous-même.');
            return;
        }
    }

    // Vérifier les doublons
    const uniqueRecipients = [...new Set(recipients)];
    if (uniqueRecipients.length !== recipients.length) {
        showFeedback(false, 'Vous avez entré le même numéro plusieurs fois.');
        return;
    }

    const perPerson = Math.floor(amt / recipients.length);
    const fee = getFee(SLABS_TRANSFERT, perPerson);
    const totalCost = (perPerson + fee) * recipients.length;

    if (client.balance < totalCost) {
        showFeedback(false, `Solde insuffisant. Besoin de ${fmtAr(totalCost)}.`);
        return;
    }

    const payload = {
        type: 'transfert-multiple',
        amount: amt,
        targets: recipients
    };

    let result;
    try {
        const res = await fetch(window.clientOperationUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        result = await res.json();
    } catch (err) {
        showFeedback(false, 'Erreur réseau. Veuillez réessayer.');
        return;
    }

    if (!result.success) {
        showFeedback(false, result.message || 'Opération refusée.');
        return;
    }

    // Mise à jour de l'état local
    client.balance = result.balance;
    result.transactions.forEach(tx => client.transactions.unshift(tx));

    // Reset form
    document.getElementById('transfert-multiple-amount').value = '';
    document.querySelectorAll('.recipient-phone').forEach((input, idx) => {
        if (idx === 0) {
            input.value = '';
        } else {
            input.parentElement.remove();
        }
    });
    updateRemoveButtonsVisibility();
    document.getElementById('transfert-multiple-preview').style.display = 'none';

    // Feedback
    showFeedback(true, `${fmtAr(perPerson)} envoyé à ${recipients.length} destinataire(s). Frais total : ${fmtAr(fee * recipients.length)}.`);

    // Refresh UI
    document.getElementById('balance-display').textContent = fmtAr(client.balance);
    renderMiniStats();
    renderRecentTx();
    renderHistory();
}

/* ─── Logout ────────────────────────────────────────────────────────── */
function logout() {
    window.location.href = window.clientLogoutUrl || 'client-login.html';
}

/* ─── Init ──────────────────────────────────────────────────────────── */
updateHeader();
renderMiniStats();
renderRecentTx();
