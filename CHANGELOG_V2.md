# Changelog - Version 2.0

## Nouvelles Fonctionnalités Côté Client

### 1. Option "Inclure les frais dans le montant envoyé" (Transfert)

**Fonctionnalité :**
- Lors d'un transfert, le client peut choisir d'inclure les frais dans le montant saisi
- Si activé : le destinataire reçoit `montant - frais`, et l'émetteur est débité du `montant` seulement
- Si désactivé (comportement par défaut) : le destinataire reçoit le `montant` complet, et l'émetteur est débité de `montant + frais`

**Exemple :**
- Transfert de 10 000 Ar avec frais de 500 Ar
- **Frais inclus** : Destinataire reçoit 9 500 Ar, émetteur débité de 10 000 Ar
- **Frais non inclus** : Destinataire reçoit 10 000 Ar, émetteur débité de 10 500 Ar

**Interface :**
- Case à cocher "Inclure les frais dans le montant envoyé"
- Prévisualisation mise à jour en temps réel montrant :
  - Montant envoyé
  - Frais
  - Montant que le destinataire recevra
  - Total débité du compte

---

### 2. Transferts Multiples (Envoi vers plusieurs destinataires)

**Fonctionnalité :**
- Permet d'envoyer de l'argent à plusieurs personnes en une seule opération
- Le montant total est divisé équitablement entre tous les destinataires
- Chaque transfert génère ses propres frais
- **Note :** Les frais sont toujours ajoutés au montant (pas d'option "frais inclus" pour ce mode)

**Caractéristiques :**
- Ajout/suppression dynamique de destinataires
- Validation de tous les numéros avant l'envoi
- Détection des doublons
- Calcul automatique du montant par personne
- Prévisualisation détaillée des coûts

**Exemple :**
- Montant total : 30 000 Ar
- 3 destinataires : chacun reçoit 10 000 Ar
- Frais par transfert : 500 Ar
- Total frais : 1 500 Ar
- Total débité : 31 500 Ar

**Interface :**
- Deux modes : "Simple" et "Multiple" (onglets)
- Bouton "+ Ajouter un destinataire" pour ajouter des champs
- Bouton de suppression pour chaque destinataire (sauf le premier si unique)
- Prévisualisation complète avec :
  - Montant total
  - Nombre de destinataires
  - Montant par personne
  - Frais par transfert
  - Frais total
  - Total à débiter

---

## Fichiers Modifiés

### 1. Interface (Frontend)

#### `/app/Views/client/dashboard.php`
**Changements :**
- Ajout de la structure HTML pour les deux modes de transfert (Simple/Multiple)
- Ajout de la case à cocher "Inclure les frais"
- Nouvelle section pour la liste de destinataires avec boutons d'ajout/suppression
- Mise à jour de la prévisualisation des frais pour le mode simple
- Nouvelle prévisualisation pour le mode multiple

#### `/public/styles/client-dashboard.css`
**Ajouts :**
- Styles pour les onglets de mode transfert (`.transfert-mode-tabs`, `.mode-tab`)
- Styles pour la case à cocher personnalisée (`.nm-checkbox`)
- Styles pour la liste de destinataires (`.recipient-row`)
- Styles pour les boutons d'ajout/suppression (`.btn-add-recipient`, `.btn-remove-recipient`)
- Animations et transitions pour une meilleure UX

#### `/public/js/client-dashboard.js`
**Nouvelles fonctions :**
- `switchTransfertMode(mode)` : Bascule entre mode simple et multiple
- `addRecipient()` : Ajoute un nouveau champ destinataire
- `removeRecipient(btn)` : Supprime un destinataire
- `updateRemoveButtonsVisibility()` : Gère la visibilité des boutons de suppression
- `updateMultipleFeePreview()` : Calcule et affiche la prévisualisation pour transferts multiples
- `doMultipleTransfert()` : Exécute les transferts multiples

**Fonctions modifiées :**
- `updateFeePreview(type)` : Prend en compte l'option "inclure les frais"
- `doOperation(type)` : Gère le paramètre `includeFee` et redirige vers `doMultipleTransfert()` si nécessaire

---

### 2. Backend (Logique métier)

#### `/app/Controllers/ClientController.php`
**Méthode modifiée :** `operate()`
**Changements :**
- Lecture du paramètre `includeFee` depuis la requête
- Gestion du type `transfert-multiple` avec validation des destinataires
- Appel à la nouvelle méthode `enregistrerMultipleTransferts()` pour les transferts multiples
- Transmission du paramètre `includeFee` à `enregistrerOperation()`

#### `/app/Models/Mouvement.php`
**Méthode modifiée :** `enregistrerOperation()`
**Nouveau paramètre :** `bool $includeFee = false`
**Changements :**
- Calcul du montant réel à envoyer au destinataire si `$includeFee` est actif
- Validation que les frais ne dépassent pas le montant si frais inclus
- Ajustement du calcul du coût total pour l'émetteur
- Enregistrement du montant correct dans la base de données

**Nouvelle méthode :** `enregistrerMultipleTransferts()`
**Paramètres :**
- `array $compte` : Le compte émetteur
- `float $totalAmount` : Le montant total à répartir
- `array $targetNumbers` : Les numéros des destinataires

**Logique :**
1. Validation du montant total et des destinataires
2. Détection des doublons
3. Calcul du montant par personne (division équitable)
4. Validation du montant minimum par personne (100 Ar)
5. Calcul des frais par transfert
6. Vérification du solde suffisant
7. Validation de tous les comptes destinataires
8. Transaction atomique pour tous les transferts
9. Mise à jour des soldes de tous les comptes
10. Retour de toutes les transactions créées

---

## Validations Implémentées

### Transfert Simple avec Frais Inclus
- ✅ Vérification que les frais ne dépassent pas le montant total
- ✅ Montant minimum après déduction des frais respecté
- ✅ Calcul correct du solde émetteur et destinataire

### Transferts Multiples
- ✅ Au moins un destinataire requis
- ✅ Tous les numéros doivent être valides (10 chiffres)
- ✅ Tous les préfixes doivent être reconnus
- ✅ Pas de transfert vers soi-même
- ✅ Pas de doublons dans la liste
- ✅ Montant par personne ≥ 100 Ar
- ✅ Solde suffisant pour tous les transferts + frais
- ✅ Tous les comptes destinataires doivent exister et être actifs
- ✅ Transaction atomique (tout ou rien)

---

## Expérience Utilisateur

### Prévisualisation en Temps Réel
- Les frais sont calculés dès la saisie du montant
- L'impact de l'option "frais inclus" est visible immédiatement
- Pour les transferts multiples, le montant par personne et les frais totaux sont affichés

### Feedback Utilisateur
- Messages de succès détaillés après chaque opération
- Messages d'erreur spécifiques pour chaque type de problème
- Mise à jour instantanée du solde et de l'historique

### Design Adaptatif
- Interface responsive qui s'adapte aux petits écrans
- Icônes intuitives pour chaque action
- Animations douces pour les transitions

---

## Sécurité et Intégrité

### Transactions Atomiques
- Tous les transferts multiples s'exécutent dans une seule transaction
- En cas d'erreur, tout est annulé (rollback automatique)
- Aucun transfert partiel n'est possible

### Validation Côté Serveur
- Toutes les validations sont dupliquées côté serveur
- Impossible de contourner les vérifications via l'API
- Protection contre les injections et manipulations

### Cohérence des Données
- Les soldes sont toujours cohérents
- L'historique reflète toutes les opérations
- Les frais sont correctement enregistrés pour l'opérateur

---

## Tests Suggérés

### Transfert avec Frais Inclus
1. ☐ Transfert normal sans frais inclus
2. ☐ Transfert avec frais inclus
3. ☐ Montant trop faible pour couvrir les frais
4. ☐ Vérifier que le destinataire reçoit le bon montant

### Transferts Multiples
1. ☐ Envoi vers 2 destinataires
2. ☐ Envoi vers 5 destinataires
3. ☐ Tentative avec doublons
4. ☐ Tentative avec un numéro invalide
5. ☐ Solde insuffisant
6. ☐ Montant trop faible pour être divisé
7. ☐ Ajout/suppression de destinataires dynamique

---

## Migration

Aucune migration de base de données n'est nécessaire. Les nouvelles fonctionnalités utilisent la structure existante.

**Compatibilité :** Les anciennes transactions restent valides et s'affichent normalement dans l'historique.

---

## Notes Techniques

### Calcul des Frais
Les frais sont calculés sur le montant du transfert (avant ou après déduction selon l'option), pas sur le montant total pour les transferts multiples.

### Performance
Les transferts multiples sont optimisés avec une seule transaction SQL, minimisant les allers-retours avec la base de données.

### Extensibilité
Le code est structuré pour faciliter l'ajout de nouvelles fonctionnalités :
- Transfert programmé
- Transfert récurrent
- Groupes de destinataires favoris
- Historique de transferts multiples

---

**Date de déploiement :** À définir
**Version :** 2.0.0
**Auteur :** Équipe de développement NovaMoney
