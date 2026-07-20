# Informations

- Rohan ETU003918
- Noah ETU004351

# General

- [x] creation du repository public
- [x] creation de l'environnement
- [x] installation de la base SQLite

# Base

- [x] conception de la base
- [x] creation de la base
- [x] creation des tables
- [x] creation des donnees de test

# Backend

## Version 1

### Operateur

- [ ] CRUD pour les prefixes
- [ ] page de modifications des tarifs pour les frais
- [ ] CRUD pour les types d'operations
- [ ] CRUD pour ajouter des frais a certains types d'operations
- [ ] fonction calculGain($typeOperation, $dateMin, $dateMax)
- [ ] fonction ListAllAccounts() avec leur solde

### Client

- [ ] fonction login($numero)
  - [ ] si le numero existe pas:
    - [ ] creation d'un compte avec solde = 0
  - [ ] ajax pour voir si le numero est valide ou pas (par rapport au prefix)
- [ ] fonction viewPersonalInfo($id) pour voir les informations du compte
- [ ] fonction deposer($idUser, $montant)
- [ ] fonction retirer($idUser, $montant)
- [ ] fonction transferer($idUser,$idReceiver,$montant)
- [ ] fonction getHistorique($idUser)

## Frontend

    [x] Création de template avec Figma Make

    [x] Création des 4 pages principales : index.html, client-dashboard.html, client-login.html, operator.html

    [x] Création des 4 feuilles de style : index.css, client-dashboard.css, client-login.css, operator.css

    [x] Création des 4 fichiers JS : index.js, client-dashboard.js, client-login.js, operator.js

## Frontend (JS) — Fonctions expliquées (Logique statique)

- [x]templates/js/client-login.js

- [x] showError(msg) : Affiche le message d'erreur dans le bloc #login-error et met à jour #login-error-msg.

- [x] clearError() : Masque le bloc d'erreur lors de la saisie utilisateur.
  - [x] handleLogin(e) : Valide le numéro de téléphone (format 10 chiffres + préfixe autorisé), stocke le numéro dans sessionStorage et gère la redirection vers le dashboard.

- [x]templates/js/operateur.js

- [x] fmtAr(n) : Formate un nombre au format monétaire (fr-FR) avec le suffixe "Ar".

- [x] fmtPhone(p) : Applique un masque d'affichage lisible aux numéros de 10 chiffres.

- [x] switchTab(id, btn) : Gère la navigation entre les onglets de l'interface opérateur, met à jour la classe .active et déclenche le rendu des données nécessaires (Gains/Comptes).

- [x] renderPrefixes() : Génère dynamiquement la grille des préfixes autorisés dans #prefix-grid.

- [x] addPrefix() : Vérifie la validité d'un nouveau préfixe et met à jour l'affichage de la grille.

- [x] deletePrefix(p) : Supprime un préfixe du tableau local et rafraîchit la vue.

- [x] renderOperations() : Génère la liste des types d'opérations et leurs barèmes de frais (inputs éditables).

- [x] toggleOp(id) : Gère l'ouverture/fermeture des accordéons d'opérations.

- [x] updateFee(opId, idx, val) : Met à jour la valeur des frais dans le tableau de données en mémoire.

- [x] renderGains() : Calcule les totaux de frais (retrait/transfert) à partir des données clients statiques et affiche le tableau des gains.

- [x] renderAccounts() : Affiche la liste des comptes (nom, solde, transactions) dans le tableau #accounts-tbody.
