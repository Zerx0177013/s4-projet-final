# TODO V1

Ce fichier détaille les fonctions backend déjà présentes dans le projet, pour compléter `Taches.md` avec un niveau de précision plus fin.

## Général

- [x] `Home::index()` pour afficher la page d'accueil par défaut.

## Contrôleur opérateur

- [x] `OperateurController::choisir()` pour afficher la liste des opérateurs disponibles.
- [x] `OperateurController::selectionner(int $idOperateur)` pour enregistrer l'opérateur actif en session.
- [x] `OperateurController::deconnecter()` pour réinitialiser l'opérateur courant.

## Contrôleur préfixes

- [x] `PrefixeController::ajouter()` pour ajouter un préfixe à l'opérateur connecté.
- [x] `PrefixeController::supprimer(int $id)` pour supprimer un préfixe appartenant à l'opérateur connecté.

## Contrôleur tranches

- [x] `TrancheController::backToOperations()` pour revenir à l'onglet des opérations après une action.
- [x] `TrancheController::overlaps(Tranche $trancheModel, int $idBareme, float $min, float $max, ?int $excludeId = null)` pour vérifier qu'une tranche ne chevauche pas une autre.
- [x] `TrancheController::ajouter()` pour créer une tranche de frais.
- [x] `TrancheController::modifier(int $id)` pour modifier une tranche existante.
- [x] `TrancheController::supprimer(int $id)` pour supprimer une tranche.

## Contrôleur dashboard opérateur

- [x] `DashboardController::afficherGainParOperateur($dateMin = null, $dateMax = null)` pour charger le tableau de bord opérateur avec les gains, les préfixes et les opérations.

## Contrôleur comptes opérateur

- [x] `CompteController::afficherComptes()` pour charger la vue opérateur avec la liste des comptes et leur activité.

## Contrôleur client

- [x] `ClientController::login()` pour afficher la page de connexion client et les préfixes autorisés.
- [x] `ClientController::authenticate()` pour valider le numéro, connecter le client et créer le compte si nécessaire.
- [x] `ClientController::dashboard()` pour afficher le tableau de bord client avec le solde, l'historique et les barèmes de frais.
- [x] `ClientController::operate()` pour exécuter un dépôt, un retrait ou un transfert en AJAX.
- [x] `ClientController::logout()` pour déconnecter le client.
- [x] `ClientController::currentCompte()` pour récupérer le compte courant depuis la session.

## Modèle compte

- [x] `Compte::findByNumber(string $number)` pour retrouver un compte par numéro.
- [x] `Compte::createForNumber(string $number, int $idOperateur)` pour créer un compte client actif avec solde initial à 0.
- [x] `Compte::loginOuCreer(string $number)` pour connecter un client existant ou créer automatiquement son compte.
- [x] `Compte::ajusterSolde(int $id, float $delta)` pour créditer ou débiter un compte.
- [x] `Compte::getComptesAvecTransactions(?int $idOperateur = null)` pour lister les comptes avec leur nombre de transactions.

## Modèle mouvement

- [x] `Mouvement::calculGain(int $idTypeOperation, int $idOperateur, $dateMin = null, $dateMax = null)` pour calculer les frais d'un type d'opération.
- [x] `Mouvement::calculGainParOperateur(int $idOperateur, $dateMin = null, $dateMax = null)` pour regrouper les gains par libellé d'opération.
- [x] `Mouvement::calculGainParTypeOperation(int $idOperateur, int $idTypeOperation, $dateMin = null, $dateMax = null)` pour calculer les gains d'un type précis.
- [x] `Mouvement::getMouvementDetails(int $idOperateur, $dateMin = null, $dateMax = null)` pour récupérer le détail des mouvements de l'opérateur.
- [x] `Mouvement::enregistrerOperation(array $compte, string $type, float $amount, ?string $targetNumber = null)` pour exécuter et enregistrer une opération client.
- [x] `Mouvement::getHistoriqueCompte(int $idCompte, float $soldeActuel)` pour reconstruire l'historique complet d'un compte.

## Modèle préfixes

- [x] `PrefixOperateur::getPrefixesByOperateur(int $idOperateur)` pour lister les préfixes d'un opérateur.
- [x] `PrefixOperateur::findOperateurIdByPrefix(string $prefix)` pour retrouver l'opérateur correspondant à un préfixe.

## Modèle tranches

- [x] `Tranche::getSlabsByBareme(int $idBareme)` pour récupérer les tranches d'un barème.
- [x] `Tranche::findFeeForAmount(int $idBareme, float $amount)` pour déterminer les frais applicables à un montant.

## Modèle types d'opération

- [x] `TypeOperation::findByLibelle(string $libelle)` pour retrouver un type d'opération par son libellé.

## À terminer

- [ ] CRUD pour ajouter des frais à certains types d'opérations, si vous voulez isoler cette logique dans un flux dédié au lieu de passer uniquement par la gestion des tranches.

# TODO V2

Cette partie décrit tous les changements ajoutés après la V1.

## Backend - commission et gains

- [x] Ajout du suivi de `montantCommission` dans la table `Operateur`.
- [x] Initialisation des commissions opérateur dans `base.sql`.
- [x] Récupération du montant de commission via `Operateur::getMontantCommission(int $id)`.
- [x] Intégration de la commission dans `CompteController::afficherComptes()`.
- [x] Transmission de `commission` jusqu'à la vue `operator/operator.php`.
- [x] Affichage d'une carte dédiée à la commission dans `tab_gains.php`.
- [x] Injection de la commission dans `partials/scripts.php` pour l'interface opérateur.

## Backend - transferts et cohérence métier

- [x] Correction de `Mouvement::enregistrerOperation()` pour garder la logique de commission.
- [x] Correction du calcul du coût total pour les transferts avec ou sans frais inclus.
- [x] Correction de la récupération de l'opérateur destinataire pour calculer la commission inter-opérateur.
- [x] Correction du cas où le compte destinataire n'existe pas encore en base.
- [x] Création automatique du compte destinataire via `loginOuCreer()` si le numéro est valide.
- [x] Ajout du champ `amountReceived` dans la transaction retournée pour les transferts.
- [x] Ajout du champ `commission` dans la transaction retournée pour tracer la commission appliquée.
- [x] Protection de l'opération via transaction SQL avec rollback en cas d'erreur.

## Backend - opérateur

- [x] Passage des gains opérateur de `CompteController` vers `operator/operator.php`.
- [x] Mise à jour de `tab_gains.php` pour afficher 4 cartes: retraits, transferts, total et commission.
- [x] Mise à jour de `operator.php` pour propager `gainCommission` au partiel des scripts.
- [x] Nettoyage de `OperateurController.php` et des flux de session opérateur.

## Backend - structure des données

- [x] Ajout de la colonne `montantCommission` dans `Operateur`.
- [x] Ajout de la commission opérateur dans les données de test.
- [x] Conservation du modèle de barèmes et de tranches pour les frais classiques.

## Backend - cohérence du flux client

- [x] Validation du transfert client côté `ClientController::operate()`.
- [x] Transmission de `includeFee` vers `Mouvement::enregistrerOperation()`.
- [x] Gestion du transfert multiple via `enregistrerMultipleTransferts()`.
- [x] Maintien du calcul des frais de tranche côté client et côté serveur.
