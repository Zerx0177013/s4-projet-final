# Informations
Details dans todo.md
- Rohan ETU003918
- Noah ETU004351

# General (Rohan)

- [x] creation du repository public
- [x] creation de l'environnement
- [x] installation de la base SQLite

# Base (Rohan)

- [x] conception de la base
- [x] creation de la base
- [x] creation des tables
- [x] creation des donnees de test

# Backend

## Version 1

### Operateur

- [x] CRUD pour les prefixes (Rohan)
- [x] page de modifications des tarifs pour les frais (Rohan)
- [x] CRUD pour les types d'operations (Rohan)
- [ ] CRUD pour ajouter des frais a certains types d'operations
- [x] fonction calculGainParOperateur($idOperateur, $dateMin, $dateMax) (Rohan)
- [x] fonction choisir() pour lister les operateurs (Rohan)
  - [x] ajouter dans la session l'id de l'operateur actuelle
  - [x] utilser cette id dans tous les fonctions l'utilisant
- [x] fonction ListAllAccounts() avec leur solde (Noah)
  - [x] fonction afficherComptes() pour donner les donnees a comptes -[ ] fonction
  - [x] fonction getComptesAvecTransactions() pour donner les doneees
  - [x] brancher la routes dans Routes.php pour /operator

### Client

- [x] fonction login($numero) (Noah)
  - [x] si le numero existe pas:
    - [x] creation d'un compte avec solde = 0
  - [x] ajax pour voir si le numero est valide ou pas (par rapport au prefix)
- [x] fonction getSolde($id) pour voir les informations du compte (Noah)
- [x] fonction enregistrerOperation(array $compte, string $type, float $amount, ?string $targetNumber = null)
- [x] fonction getHistoriqueCompte($idUser) (Noah)

## Version 2
- [x] Ajouter une colonne pourcentageCommission dans Operateur
- [ ] Ajouter une colonne montantCommission dans mouvement et Operateur
- [x] Configuration des préfixes valable pour les autres opérateurs (ex: 032 et 031, …) Dans la V1 (Rohan)
- [ ] Configuration % en plus de commissions pour les transferts vers les autres opérateurs (Rohan)
  - [ ] modifier les fonctions js psour l'aperçu des prix pour inclure la commission (updateFeePreview)
  - [x] recuperer la somme a envoyer
  - [x] prendre un certain % de cette somme si != operateur (dans la base)
  - [x] la commission va vers l'operateur receiver
  - [x] la somme + frais de retrait va vers le destinataire

- [x] Sur la page “Situation gain via les différents frais” , séparer opérateur et autres opérateurs dans la V1 (Rohan)
- [ ] Situation des montants à envoyer à chaque opérateur dans la V1 (Rohan)
  - [ ] fonction qui somme toutes les commissions d'un operateur


## Frontend

- [x] Création de template avec Figma Make (Noah)
- [x] Création des 4 pages principales : index.html, client-dashboard.html, client-login.html, operator.html
- [x] Création des 4 feuilles de style : index.css, client-dashboard.css, client-login.css, operator.css
- [x] Création des 4 fichiers JS : index.js, client-dashboard.js, client-login.js, operator.js

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
