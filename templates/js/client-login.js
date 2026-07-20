/* Prefixes accepted — doit correspondre à la config opérateur */
const PREFIXES = ['033', '034', '037', '038'];

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
    e.preventDefault();
    const phone = document.getElementById('phone-input').value.replace(/\s/g, '');
    if (phone.length !== 10 || !/^\d+$/.test(phone)) {
        showError('Numéro invalide (10 chiffres requis).'); return;
    }
    if (!PREFIXES.includes(phone.slice(0, 3))) {
        showError(`Le préfixe ${phone.slice(0, 3)} n'est pas pris en charge par cet opérateur.`); return;
    }
    /* Stocker le numéro en session et rediriger */
    sessionStorage.setItem('nm_phone', phone);
    window.location.href = 'client-dashboard.html';
}