/* Préfixes acceptés — fournis dynamiquement par le serveur (table
   prefixOperateur), aucune donnée statique côté client. */
const PREFIXES = window.clientPrefixes || [];

/* Render prefix tags */
document.getElementById('prefix-tags').innerHTML =
    PREFIXES.map(p => `<span class="prefix-tag">${p}</span>`).join('');

function showError(msg) {
    document.getElementById('login-error-msg').textContent = msg;
    document.getElementById('login-error').style.display = 'flex';
}
function clearError() {
    document.getElementById('login-error').style.display = 'none';
}

function handleLogin(e) {
    const phone = document.getElementById('phone-input').value.replace(/\s/g, '');
    if (phone.length !== 10 || !/^\d+$/.test(phone)) {
        e.preventDefault();
        showError('Numéro invalide (10 chiffres requis).'); return false;
    }
    if (!PREFIXES.includes(phone.slice(0, 3))) {
        e.preventDefault();
        showError(`Le préfixe ${phone.slice(0, 3)} n'est pas pris en charge par cet opérateur.`); return false;
    }
    /* Numéro valide côté client : le formulaire est soumis normalement au
       serveur, qui crée le compte s'il n'existe pas encore (Compte::loginOuCreer),
       ou connecte directement le client si le numéro est déjà enregistré. */
    return true;
}
