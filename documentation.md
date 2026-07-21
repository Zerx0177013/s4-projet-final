# Documentation NovaMoney - Guide Complet du Code

## 📋 Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture du Projet](#architecture-du-projet)
3. [Côté Opérateur](#côté-opérateur)
4. [Côté Client](#côté-client)
5. [Modèles et Base de Données](#modèles-et-base-de-données)
6. [Flow des Opérations](#flow-des-opérations)
7. [Exemples de Modifications](#exemples-de-modifications)

---

## 🎯 Vue d'ensemble

NovaMoney est une application de simulation mobile money avec deux interfaces principales :
- **Interface Opérateur** : Gestion des configurations, tarifs, et statistiques
- **Interface Client** : Opérations bancaires (dépôt, retrait, transfert)

### Technologies utilisées
- **Backend** : CodeIgniter 4 (PHP)
- **Frontend** : HTML, CSS, JavaScript (Vanilla)
- **Base de données** : MySQL/PostgreSQL

---

## 🏗️ Architecture du Projet

```
app/
├── Controllers/           # Logique métier et routing
│   ├── ClientController.php
│   ├── OperateurController.php
│   ├── CompteController.php
│   ├── PrefixeController.php
│   └── TrancheController.php
├── Models/               # Accès aux données
│   ├── Compte.php
│   ├── Mouvement.php
│   ├── Operateur.php
│   ├── PrefixOperateur.php
│   ├── Bareme.php
│   └── Tranche.php
├── Views/                # Interface utilisateur
│   ├── client/
│   │   ├── dashboard.php
│   │   ├── login.php
│   │   └── partials/      # Composants réutilisables
│   └── operator/
│       ├── operator.php
│       └── partials/
└── Config/
    └── Routes.php         # Configuration des routes
```

---

## 🏢 Côté Opérateur

### 📂 Structure des fichiers

#### 1. **OperateurController.php**
Contrôleur principal pour l'interface opérateur.

**Méthodes principales :**
- `index()` : Affiche le dashboard opérateur avec toutes les données
- `login()` / `logout()` : Gestion de l'authentification

**Données chargées :**
```php
$operateur = $operateurModel->find($idOperateur);
$prefixes = $prefixOperateurModel->getPrefixesByOperateur($idOperateur);
$comptes = $compteModel->getComptesByOperateur($idOperateur);
$feeSlabs = [
    'depot' => $trancheModel->getTranchesByBareme($baremeDepot),
    'retrait' => $trancheModel->getTranchesByBareme($baremeRetrait),
    'transfert' => $trancheModel->getTranchesByBareme($baremeTransfert)
];
$stats = [
    'totalAccounts' => count($comptes),
    'activeAccounts' => count(array_filter($comptes, fn($c) => $c['idStatus'] == 1)),
    'totalBalance' => array_sum(array_column($comptes, 'solde')),
    'totalFees' => (float)$mouvementModel->calculGain(null, $idOperateur)
];
```

#### 2. **PrefixeController.php**
Gestion des préfixes téléphoniques (032, 033, 034, etc.).

**Actions :**
- `add()` : Ajouter un nouveau préfixe
- `update()` : Modifier un préfixe existant
- `delete()` : Supprimer un préfixe

**Exemple d'ajout :**
```php
public function add() {
    $data = $this->request->getJSON(true);
    $prefixOperateurModel->insert([
        'prefixe' => $data['prefix'],
        'idOperateur' => $idOperateur
    ]);
}
```

#### 3. **TrancheController.php**
Gestion des tranches de frais (barèmes).

**Structure d'une tranche :**
```php
[
    'min' => 100,      // Montant minimum
    'max' => 1000,     // Montant maximum
    'montant' => 50,   // Frais appliqués
    'idBareme' => 1    // Type (dépôt/retrait/transfert)
]
```

**Actions :**
- `add()` : Ajouter une nouvelle tranche
- `update()` : Modifier une tranche
- `delete()` : Supprimer une tranche


#### 4. **CompteController.php**
Gestion des comptes clients.

**Actions :**
- `updateStatus()` : Activer/bloquer un compte
- `getDetails()` : Récupérer les détails et l'historique d'un compte

**Exemple de blocage de compte :**
```php
public function updateStatus() {
    $data = $this->request->getJSON(true);
    $compteModel->update($data['id'], [
        'idStatus' => $data['status'] // 1 = actif, 2 = bloqué
    ]);
}
```

### 🖥️ Interface Opérateur (Views)

#### Structure des vues
```
operator/
├── operator.php          # Vue principale
└── partials/
    ├── header.php        # En-tête
    ├── sidebar.php       # Menu latéral
    ├── dashboard.php     # Statistiques générales
    ├── accounts.php      # Gestion des comptes
    ├── prefixes.php      # Gestion des préfixes
    ├── fees.php          # Gestion des tranches
    ├── gains.php         # Statistiques de gains
    └── scripts.php       # JavaScript
```


#### JavaScript Opérateur (`operator-dashboard.js`)

**Fonctions principales :**

1. **Gestion des préfixes**
```javascript
async function addPrefix(prefix) {
    const res = await fetch('/operator/prefixes/add', {
        method: 'POST',
        body: JSON.stringify({ prefix })
    });
    // Recharge les données
}

async function deletePrefix(id) {
    await fetch(`/operator/prefixes/delete/${id}`, { method: 'POST' });
}
```

2. **Gestion des tranches**
```javascript
async function addFeeSlab(type, min, max, amount) {
    await fetch('/operator/fees/add', {
        method: 'POST',
        body: JSON.stringify({ type, min, max, amount })
    });
}
```

3. **Gestion des comptes**
```javascript
async function toggleAccountStatus(accountId, currentStatus) {
    const newStatus = currentStatus === 1 ? 2 : 1;
    await fetch('/operator/accounts/update-status', {
        method: 'POST',
        body: JSON.stringify({ id: accountId, status: newStatus })
    });
}
```


---

## 👤 Côté Client

### 📂 Structure des fichiers

#### 1. **ClientController.php**
Contrôleur principal pour l'interface client.

**Méthodes principales :**

##### `login()`
Connexion ou création automatique de compte.
```php
public function login() {
    $number = $this->request->getPost('number');
    $compte = $compteModel->loginOuCreer($number); // Crée le compte si n'existe pas
    session()->set('client_id', $compte['id']);
    return redirect()->to('client/dashboard');
}
```

##### `dashboard()`
Affiche le tableau de bord client avec toutes les données nécessaires.
```php
public function dashboard() {
    $compte = $compteModel->find(session('client_id'));
    $history = $mouvementModel->getHistoriqueCompte($compte['id'], $compte['solde']);
    $feeSlabs = [...]; // Tranches de frais
    $prefixes = [...]; // Préfixes valides
    $prefixesData = [...]; // Données opérateurs (commission %)
    
    return view('client/dashboard', [
        'compte' => $compte,
        'history' => $history,
        'feeSlabs' => $feeSlabs,
        'prefixes' => $prefixes,
        'prefixesData' => $prefixesData
    ]);
}
```


##### `operation()`
Traite toutes les opérations client (dépôt, retrait, transfert simple, transfert multiple).

**Structure de la méthode :**
```php
public function operation() {
    $data = $this->request->getJSON(true);
    $type = $data['type']; // 'depot', 'retrait', 'transfert', 'transfert-multiple'
    
    try {
        if ($type === 'transfert-multiple') {
            $result = $mouvementModel->enregistrerMultipleTransferts(
                $compte, 
                $data['amount'], 
                $data['targets']
            );
        } else {
            $result = $mouvementModel->enregistrerOperation(
                $compte, 
                $type, 
                $data['amount'], 
                $data['target'] ?? null,
                $data['includeFee'] ?? false
            );
        }
        
        return $this->response->setJSON([
            'success' => true,
            'balance' => $result['balance'],
            'transaction' => $result['transaction']
        ]);
    } catch (\Exception $e) {
        return $this->response->setJSON([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}
```


### 🖥️ Interface Client (Views)

#### Structure des vues
```
client/
├── dashboard.php         # Vue principale
├── login.php            # Page de connexion
└── partials/
    ├── header.php       # En-tête + Balance
    ├── navigation.php   # Navigation par onglets
    ├── feedback.php     # Toast de notification
    ├── tab_balance.php  # Onglet Solde
    ├── tab_depot.php    # Onglet Dépôt
    ├── tab_retrait.php  # Onglet Retrait
    ├── tab_transfert.php # Onglet Transfert (Simple + Multiple)
    ├── tab_history.php  # Onglet Historique
    └── scripts.php      # Données + Scripts JS
```

#### Données transmises à la vue (`scripts.php`)
```php
window.clientAccount = {
    phone: '0330000000',
    balance: 10000,
    operateurId: 1
};

window.clientFeeSlabs = {
    retrait: [{min: 100, max: 1000, fee: 50}, ...],
    transfert: [{min: 100, max: 1000, fee: 50}, ...]
};

window.clientPrefixes = ['032', '033', '034', ...];

window.clientPrefixesData = {
    '032': {operateurId: 1, commission: 2},  // 2% de commission
    '033': {operateurId: 2, commission: 3},  // 3% de commission
    ...
};
```


### 📱 JavaScript Client (`client-dashboard.js`)

#### État global
```javascript
const client = {
    balance: 10000,           // Solde actuel
    operateurId: 1,          // ID de l'opérateur
    transactions: []          // Historique des transactions
};
```

#### Fonctions principales

##### 1. **Calcul des frais (Simple)**
```javascript
function updateFeePreview(type) {
    const amt = parseInt(document.getElementById(type + '-amount').value);
    const fee = getFee(SLABS_TRANSFERT, amt);
    
    // Pour transfert : calcul de la commission inter-opérateur
    if (type === 'transfert') {
        const targetNumber = document.getElementById('transfert-target').value;
        const targetPrefix = targetNumber.slice(0, 3);
        const targetData = PREFIXES_DATA[targetPrefix];
        
        let commission = 0;
        if (targetData && targetData.operateurId !== client.operateurId) {
            commission = Math.round(amt * (targetData.commission || 0) / 100);
        }
        
        // Affichage
        const totalCost = amt + fee + commission;
    }
}
```


##### 2. **Calcul des frais (Multiple)**
```javascript
function updateMultipleFeePreview() {
    const amt = parseInt(document.getElementById('transfert-multiple-amount').value);
    const validRecipients = Array.from(document.querySelectorAll('.recipient-phone'))
        .map(input => input.value.replace(/\s/g, ''))
        .filter(num => num.length === 10);
    
    const perPerson = Math.floor(amt / validRecipients.length);
    const fee = getFee(SLABS_TRANSFERT, perPerson);
    const totalFee = fee * validRecipients.length;
    
    // Calcul de la commission totale
    let totalCommission = 0;
    validRecipients.forEach(targetNumber => {
        const targetPrefix = targetNumber.slice(0, 3);
        const targetData = PREFIXES_DATA[targetPrefix];
        
        if (targetData && targetData.operateurId !== client.operateurId) {
            totalCommission += Math.round(perPerson * (targetData.commission || 0) / 100);
        }
    });
    
    const totalCost = amt + totalFee + totalCommission;
}
```


##### 3. **Exécution d'une opération**
```javascript
async function doOperation(type) {
    const payload = {
        type: type,
        amount: parseInt(document.getElementById(type + '-amount').value)
    };
    
    if (type === 'transfert') {
        payload.target = document.getElementById('transfert-target').value;
        payload.includeFee = document.getElementById('transfert-include-fee').checked;
    }
    
    const res = await fetch(window.clientOperationUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    
    const result = await res.json();
    
    if (result.success) {
        // Mise à jour locale
        client.balance = result.balance;
        client.transactions.unshift(result.transaction);
        
        // Refresh UI
        updateHeader();
        renderRecentTx();
        renderHistory();
    }
}
```


##### 4. **Gestion des destinataires multiples**
```javascript
function addRecipient() {
    const list = document.getElementById('recipients-list');
    const row = document.createElement('div');
    row.className = 'recipient-row';
    row.innerHTML = `
        <input type="tel" class="nm-input mono-font recipient-phone" 
               placeholder="0330000000" maxlength="10"
               oninput="this.value=this.value.replace(/\\D/g,'').slice(0,10);
                        updateMultipleFeePreview();">
        <button class="btn-remove-recipient" onclick="removeRecipient(this)">×</button>
    `;
    list.appendChild(row);
}

function removeRecipient(btn) {
    btn.parentElement.remove();
    updateMultipleFeePreview();
}
```

---

## 💾 Modèles et Base de Données

### 🗂️ Structure des tables principales

#### Table `Operateur`
```sql
id              INT PRIMARY KEY
nom             VARCHAR(255)
pourcentageCommission DECIMAL(5,2)    -- Commission inter-opérateur (%)
montantCommission     DECIMAL(15,2)   -- Total des commissions reçues
```


#### Table `PrefixOperateur`
```sql
id              INT PRIMARY KEY
prefixe         VARCHAR(3)          -- Ex: '032', '033'
idOperateur     INT FOREIGN KEY     -- Référence Operateur
```

#### Table `Compte`
```sql
id              INT PRIMARY KEY
number          VARCHAR(10) UNIQUE  -- Numéro de téléphone
solde           DECIMAL(15,2)
idOperateur     INT FOREIGN KEY
idStatus        INT                 -- 1=actif, 2=bloqué
```

#### Table `Bareme`
```sql
id              INT PRIMARY KEY
libelle         VARCHAR(50)         -- 'Depot', 'Retrait', 'Transfert'
idOperateur     INT FOREIGN KEY
```

#### Table `Tranche`
```sql
id              INT PRIMARY KEY
min             DECIMAL(15,2)       -- Montant minimum
max             DECIMAL(15,2)       -- Montant maximum
montant         DECIMAL(15,2)       -- Frais
idBareme        INT FOREIGN KEY
```

#### Table `TypeOperation`
```sql
id              INT PRIMARY KEY
libelle         VARCHAR(50)         -- 'Depot', 'Retrait', 'Transfert'
idBareme        INT FOREIGN KEY
```


#### Table `Mouvement`
```sql
id              INT PRIMARY KEY
somme           DECIMAL(15,2)       -- Montant principal
montantFrais    DECIMAL(15,2)       -- Frais de transaction
montantCommission DECIMAL(15,2)     -- Commission inter-opérateur
idTypeOperation INT FOREIGN KEY
idSender        INT FOREIGN KEY     -- Compte émetteur (NULL si dépôt)
idReceiver      INT FOREIGN KEY     -- Compte destinataire (NULL si retrait)
idOperateur     INT FOREIGN KEY     -- Opérateur de l'émetteur
dateMouvement   DATETIME
```

### 📊 Modèle `Mouvement.php`

#### Méthode principale : `enregistrerOperation()`

**Signature :**
```php
public function enregistrerOperation(
    array $compte, 
    string $type,           // 'depot', 'retrait', 'transfert'
    float $amount, 
    ?string $targetNumber = null, 
    bool $includeFee = false
): array
```

**Logique détaillée :**


**1. Calcul des frais**
```php
// Récupérer le barème selon le type d'opération
$typeOperation = $typeOperationModel->findByLibelle(ucfirst($type));
$fee = $trancheModel->findFeeForAmount($typeOperation['idBareme'], $amount);
```

**2. Gestion du paramètre `includeFee` (transfert uniquement)**
```php
if ($includeFee) {
    // Ajouter les frais de retrait pour le destinataire
    $typeRetrait = $typeOperationModel->findByLibelle('Retrait');
    $retraitFee = $trancheModel->findFeeForAmount($typeRetrait['idBareme'], $amount);
    
    // Le destinataire reçoit : montant + frais de retrait
    $amountReceived = $amount + $retraitFee;
    
    // On ajoute les frais de retrait au coût total de l'émetteur
    $fee += $retraitFee;
}
```

**3. Calcul de la commission inter-opérateur**
```php
if ($type === 'transfert') {
    $targetOperatorId = (int) $target['idOperateur'];
    $senderOperatorId = (int) $compte['idOperateur'];
    
    if ($senderOperatorId !== $targetOperatorId) {
        // Commission = montant × pourcentage
        $pourcentage = (float) $operateurModel->getPourcentageCommission($targetOperatorId);
        $commissionFee = $amount * $pourcentage / 100;
    }
}
```


**4. Calcul du coût total**
```php
$totalCost = match ($type) {
    'depot' => 0.0,                                    // Gratuit
    'retrait' => $amount + $fee,                       // Montant + frais
    'transfert' => $amount + $fee + $commissionFee,    // Montant + frais + commission
};
```

**5. Transaction en base de données**
```php
$db->transStart();

// Enregistrer le mouvement
$this->insert([
    'somme' => $amount,
    'montantFrais' => $fee,
    'montantCommission' => $commissionFee,
    'idTypeOperation' => $typeOperation['id'],
    'idSender' => $idSender,
    'idReceiver' => $idReceiver,
    'idOperateur' => $senderOperatorId
]);

// Mettre à jour les soldes
$newBalance = match ($type) {
    'depot' => $compteModel->ajusterSolde($compte['id'], $amount),
    'retrait' => $compteModel->ajusterSolde($compte['id'], -($amount + $fee)),
    'transfert' => $compteModel->ajusterSolde($compte['id'], -$totalCost)
};

if ($type === 'transfert') {
    $compteModel->ajusterSolde($target['id'], $amountReceived);
    
    // Créditer la commission à l'opérateur destinataire
    if ($commissionFee > 0) {
        $operateurModel->AddToMontantCommission($targetOperatorId, $commissionFee);
    }
}

$db->transComplete();
```


#### Méthode : `enregistrerMultipleTransferts()`

**Logique :**
1. Divise le montant total entre les destinataires : `perPerson = floor(totalAmount / count)`
2. Valide les doublons et l'auto-envoi
3. Appelle `enregistrerOperation()` pour chaque destinataire (réutilise toute la logique)

```php
public function enregistrerMultipleTransferts(array $compte, float $totalAmount, array $targetNumbers): array
{
    $perPerson = floor($totalAmount / count($targetNumbers));
    
    $transactions = [];
    foreach ($targetNumbers as $targetNumber) {
        // Recharger le compte pour avoir le solde à jour
        $compteActuel = $compteModel->find($compte['id']);
        
        // Appeler enregistrerOperation pour chaque transfert
        $result = $this->enregistrerOperation(
            $compteActuel, 
            'transfert', 
            $perPerson, 
            $targetNumber, 
            false
        );
        
        $compte['solde'] = $result['balance'];
        $transactions[] = $result['transaction'];
    }
    
    return [
        'balance' => $compte['solde'],
        'transactions' => $transactions
    ];
}
```


---

## 🔄 Flow des Opérations

### 📥 Flow Dépôt

```
Client clique "Confirmer le dépôt"
    ↓
client-dashboard.js : doOperation('depot')
    ↓
POST /client/operation
    ↓
ClientController::operation()
    ↓
Mouvement::enregistrerOperation($compte, 'depot', $amount)
    ↓
1. Validation : montant >= 100 Ar
2. Calcul frais : 0 Ar (dépôt gratuit)
3. Transaction DB :
   - INSERT INTO Mouvement (idReceiver = compte)
   - UPDATE Compte SET solde = solde + amount
    ↓
Retour JSON : { success: true, balance: nouveauSolde, transaction: {...} }
    ↓
Mise à jour UI : balance, historique, statistiques
```

### 📤 Flow Retrait

```
Client entre montant → updateFeePreview('retrait')
    ↓
Calcul et affichage des frais
    ↓
Client clique "Confirmer le retrait"
    ↓
doOperation('retrait')
    ↓
POST /client/operation
    ↓
Mouvement::enregistrerOperation($compte, 'retrait', $amount)
    ↓
1. Calcul frais selon tranche
2. Vérification solde >= (montant + frais)
3. Transaction DB :
   - INSERT INTO Mouvement (idSender = compte)
   - UPDATE Compte SET solde = solde - (amount + fee)
    ↓
Retour et mise à jour UI
```


### 💸 Flow Transfert Simple (Sans commission)

**Cas : Même opérateur (032 → 032)**

```
Client entre numéro + montant → updateFeePreview('transfert')
    ↓
1. Calcul frais de transfert
2. Vérification opérateur destinataire
3. Commission = 0 (même opérateur)
4. Affichage : montant + frais
    ↓
Client clique "Confirmer"
    ↓
POST /client/operation { type: 'transfert', amount: 5000, target: '0320000000' }
    ↓
Mouvement::enregistrerOperation($compte, 'transfert', 5000, '0320000000', false)
    ↓
1. Calcul frais : 50 Ar
2. Commission : 0 Ar (même opérateur)
3. Transaction DB :
   - INSERT Mouvement (somme=5000, frais=50, commission=0)
   - UPDATE Compte émetteur : solde - 5050
   - UPDATE Compte destinataire : solde + 5000
    ↓
Retour : { balance: 4950, transaction: {...} }
```

### 💸 Flow Transfert Simple (Avec commission)

**Cas : Opérateurs différents (032 → 033) - Commission 2%**

```
Client entre '0330000000' + 5000 Ar
    ↓
updateFeePreview('transfert')
    ↓
1. Détecte préfixe '033' → opérateur différent
2. Calcul frais : 50 Ar
3. Calcul commission : 5000 × 2% = 100 Ar
4. Affiche ligne "Commission inter-opérateur : 100 Ar"
5. Total débité : 5000 + 50 + 100 = 5150 Ar
    ↓
Confirmation
    ↓
Mouvement::enregistrerOperation()
    ↓
Transaction DB :
   - INSERT Mouvement (somme=5000, frais=50, commission=100)
   - Émetteur : solde - 5150
   - Destinataire : solde + 5000
   - Opérateur destinataire : montantCommission + 100
```


### 💸 Flow Transfert avec "Inclure frais de retrait"

**Option `includeFee = true`**

```
Client coche "Inclure les frais dans le montant envoyé"
    ↓
updateFeePreview('transfert')
    ↓
1. Calcul frais de transfert : 50 Ar
2. Calcul frais de retrait : 50 Ar
3. Destinataire reçoit : montant + frais de retrait = 950 Ar
4. Total débité : montant + frais transfert + frais retrait = 1000 Ar
    ↓
Confirmation
    ↓
Mouvement::enregistrerOperation(..., includeFee: true)
    ↓
Transaction DB :
   - Frais total = 100 Ar (50+50)
   - Émetteur : solde - 1000
   - Destinataire : solde + 950 (peut retirer 900 gratuitement)
```

**Avantage :** Le destinataire peut retirer le montant sans payer de frais supplémentaires.

### 👥 Flow Transfert Multiple

**Exemple : 10000 Ar vers 3 destinataires**

```
Client ajoute 3 destinataires : 0320000000, 0330000000, 0340000000
Client entre 10000 Ar
    ↓
updateMultipleFeePreview()
    ↓
1. Par personne : 10000 / 3 = 3333 Ar
2. Frais par transfert : 50 Ar
3. Frais total : 50 × 3 = 150 Ar
4. Commission :
   - 032 (même op) : 0 Ar
   - 033 (2%) : 3333 × 2% = 67 Ar
   - 034 (3%) : 3333 × 3% = 100 Ar
   Total commission : 167 Ar
5. Total débité : 10000 + 150 + 167 = 10317 Ar
    ↓
Confirmation → doMultipleTransfert()
    ↓
POST { type: 'transfert-multiple', amount: 10000, targets: [...] }
    ↓
Mouvement::enregistrerMultipleTransferts()
    ↓
Pour chaque destinataire :
   - enregistrerOperation($compte, 'transfert', 3333, target)
   - Recharge le compte pour avoir solde à jour
    ↓
3 mouvements créés en base
```


---

## 🛠️ Exemples de Modifications

### Exemple 1 : Ajouter un nouveau type d'opération

**Objectif :** Ajouter une opération "Paiement facture"

#### Étape 1 : Base de données
```sql
-- Créer un nouveau barème
INSERT INTO Bareme (libelle, idOperateur) VALUES ('Paiement', 1);

-- Créer le type d'opération
INSERT INTO TypeOperation (libelle, idBareme) VALUES ('Paiement', <id_bareme>);

-- Ajouter des tranches de frais
INSERT INTO Tranche (min, max, montant, idBareme) VALUES
(100, 1000, 25, <id_bareme>),
(1001, 5000, 50, <id_bareme>);
```

#### Étape 2 : Modèle `Mouvement.php`
```php
private const TYPES = ['depot', 'retrait', 'transfert', 'paiement']; // Ajouter 'paiement'

// Dans enregistrerOperation(), ajouter le cas :
$totalCost = match ($type) {
    'depot' => 0.0,
    'retrait' => $amount + $fee,
    'transfert' => $amount + $fee + $commissionFee,
    'paiement' => $amount + $fee,  // Nouveau cas
};
```


#### Étape 3 : Vue Client (`tab_paiement.php`)
```php
<div id="tab-paiement" class="tab-section">
  <div class="op-card">
    <div class="op-card-header">
      <div class="op-card-icon" style="background:rgba(168,85,247,.1)">
        <svg>...</svg>
      </div>
      <div>
        <div class="op-card-title">Paiement</div>
        <div class="op-card-sub">Payez vos factures</div>
      </div>
    </div>
    <div class="mb-3">
      <label class="nm-label" for="paiement-ref">Référence facture</label>
      <input id="paiement-ref" type="text" class="nm-input">
    </div>
    <div class="mb-3">
      <label class="nm-label" for="paiement-amount">Montant (Ar)</label>
      <input id="paiement-amount" type="number" class="nm-input" 
             oninput="updateFeePreview('paiement')">
    </div>
    <div id="paiement-preview" style="display:none" class="fee-preview mb-3">
      <div class="fee-row"><span>Montant</span><span id="paiement-p-amount">—</span></div>
      <div class="fee-row"><span>Frais</span><span id="paiement-p-fee">—</span></div>
      <div class="fee-row total"><span>Total</span><span id="paiement-p-total">—</span></div>
    </div>
    <button class="btn-confirm" onclick="doOperation('paiement')">Confirmer</button>
  </div>
</div>
```


#### Étape 4 : JavaScript (`client-dashboard.js`)
```javascript
// Ajouter dans SLABS
const SLABS_PAIEMENT = (window.clientFeeSlabs && window.clientFeeSlabs.paiement) || [];

// Modifier updateFeePreview pour supporter 'paiement'
function updateFeePreview(type) {
    const slabs = type === 'retrait' ? SLABS_RETRAIT : 
                  type === 'paiement' ? SLABS_PAIEMENT : 
                  SLABS_TRANSFERT;
    // ... reste du code
}

// Modifier doOperation pour gérer la référence
async function doOperation(type) {
    const payload = { type, amount: parseInt(document.getElementById(type + '-amount').value) };
    
    if (type === 'paiement') {
        payload.reference = document.getElementById('paiement-ref').value;
    }
    // ... reste du code
}
```

#### Étape 5 : Navigation
Ajouter un bouton dans `navigation.php` :
```html
<button class="tab-nav-btn" data-tab="paiement" onclick="switchTab('paiement',this)">
  <svg>...</svg>
  Paiement
</button>
```


### Exemple 2 : Modifier le calcul de la commission

**Objectif :** Changer la commission de pourcentage à montant fixe

#### Étape 1 : Base de données
```sql
-- Modifier la colonne
ALTER TABLE Operateur 
  DROP COLUMN pourcentageCommission,
  ADD COLUMN commissionFixe DECIMAL(15,2) DEFAULT 100;

-- Ou garder les deux et ajouter un type
ALTER TABLE Operateur 
  ADD COLUMN typeCommission ENUM('pourcentage', 'fixe') DEFAULT 'pourcentage';
```

#### Étape 2 : Modèle `Operateur.php`
```php
public function getCommissionPourTransfert(int $idOperateur, float $montant): float
{
    $operateur = $this->find($idOperateur);
    
    if ($operateur['typeCommission'] === 'fixe') {
        return (float) $operateur['commissionFixe'];
    } else {
        return $montant * (float) $operateur['pourcentageCommission'] / 100;
    }
}
```


#### Étape 3 : Modèle `Mouvement.php`
```php
// Dans enregistrerOperation()
if ($senderOperatorId !== $targetOperatorId) {
    // Ancienne version
    // $pourcentage = (float) $operateurModel->getPourcentageCommission($targetOperatorId);
    // $commissionFee = $amount * $pourcentage / 100;
    
    // Nouvelle version
    $commissionFee = $operateurModel->getCommissionPourTransfert($targetOperatorId, $amount);
}
```

#### Étape 4 : Vue Opérateur
Ajouter un champ dans la configuration de l'opérateur :
```html
<div class="form-group">
  <label>Type de commission</label>
  <select id="commission-type">
    <option value="pourcentage">Pourcentage (%)</option>
    <option value="fixe">Montant fixe (Ar)</option>
  </select>
</div>

<div class="form-group" id="commission-input">
  <label>Commission</label>
  <input type="number" id="commission-value">
</div>
```


### Exemple 3 : Ajouter un historique de commissions pour l'opérateur

**Objectif :** Voir toutes les commissions reçues dans un onglet séparé

#### Étape 1 : Modèle `Mouvement.php`
```php
public function getCommissionsRecues(int $idOperateur, $dateMin = null, $dateMax = null): array
{
    $builder = $this->builder();
    
    $builder->select('Mouvement.*, sender.number as senderNumber, receiver.number as receiverNumber')
        ->join('compte as sender', 'sender.id = Mouvement.idSender', 'left')
        ->join('compte as receiver', 'receiver.id = Mouvement.idReceiver', 'left')
        ->where('receiver.idOperateur', $idOperateur)
        ->where('Mouvement.montantCommission >', 0)
        ->orderBy('Mouvement.dateMouvement', 'DESC');
    
    if ($dateMin !== null) {
        $builder->where('Mouvement.dateMouvement >=', $dateMin);
    }
    
    if ($dateMax !== null) {
        $builder->where('Mouvement.dateMouvement <=', $dateMax);
    }
    
    return $builder->get()->getResultArray();
}
```


#### Étape 2 : Controller `OperateurController.php`
```php
public function index()
{
    // ... code existant
    
    // Ajouter
    $commissionsRecues = $mouvementModel->getCommissionsRecues($idOperateur);
    
    return view('operator/operator', [
        // ... données existantes
        'commissionsRecues' => $commissionsRecues
    ]);
}
```

#### Étape 3 : Vue `operator/partials/commissions.php`
```php
<div id="section-commissions" class="section-panel" style="display:none">
  <h2>Commissions reçues</h2>
  
  <div class="stats-card">
    <div class="stat-value"><?= number_format($operateur['montantCommission'], 0, ',', ' ') ?> Ar</div>
    <div class="stat-label">Total des commissions</div>
  </div>
  
  <table class="data-table">
    <thead>
      <tr>
        <th>Date</th>
        <th>De</th>
        <th>Vers</th>
        <th>Montant transfert</th>
        <th>Commission</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($commissionsRecues as $comm): ?>
      <tr>
        <td><?= date('d/m/Y H:i', strtotime($comm['dateMouvement'])) ?></td>
        <td><?= $comm['senderNumber'] ?></td>
        <td><?= $comm['receiverNumber'] ?></td>
        <td><?= number_format($comm['somme'], 0, ',', ' ') ?> Ar</td>
        <td class="text-success">+<?= number_format($comm['montantCommission'], 0, ',', ' ') ?> Ar</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
```


### Exemple 4 : Ajouter une limite de transfert journalière

**Objectif :** Limiter les transferts à 100 000 Ar par jour par client

#### Étape 1 : Modèle `Mouvement.php`
```php
public function getTotalTransfertsToday(int $idCompte): float
{
    $today = date('Y-m-d 00:00:00');
    
    $builder = $this->builder();
    $builder->selectSum('somme', 'total')
        ->join('TypeOperation', 'TypeOperation.id = Mouvement.idTypeOperation')
        ->where('Mouvement.idSender', $idCompte)
        ->where('TypeOperation.libelle', 'Transfert')
        ->where('Mouvement.dateMouvement >=', $today);
    
    $result = $builder->get()->getRowArray();
    return (float) ($result['total'] ?? 0);
}
```

#### Étape 2 : Dans `enregistrerOperation()`
```php
public function enregistrerOperation(array $compte, string $type, float $amount, ...) 
{
    // ... validations existantes
    
    if ($type === 'transfert') {
        $totalToday = $this->getTotalTransfertsToday($compte['id']);
        $limiteJournaliere = 100000; // 100 000 Ar
        
        if ($totalToday + $amount > $limiteJournaliere) {
            throw new RuntimeException(
                "Limite journalière atteinte. Vous avez déjà transféré " . 
                number_format($totalToday, 0, ',', ' ') . " Ar aujourd'hui."
            );
        }
    }
    
    // ... reste du code
}
```


#### Étape 3 : Afficher la limite dans l'interface client
```javascript
// Dans client-dashboard.js
async function loadDailyLimit() {
    const res = await fetch('/client/daily-limit');
    const data = await res.json();
    
    document.getElementById('daily-limit-info').innerHTML = `
        <div class="info-box">
            <strong>Limite journalière :</strong> ${fmtAr(data.limit)}<br>
            <strong>Utilisé aujourd'hui :</strong> ${fmtAr(data.used)}<br>
            <strong>Restant :</strong> ${fmtAr(data.remaining)}
        </div>
    `;
}
```

```php
// Dans ClientController.php
public function dailyLimit()
{
    $compte = $this->getCompteConnecte();
    $totalToday = $mouvementModel->getTotalTransfertsToday($compte['id']);
    $limit = 100000;
    
    return $this->response->setJSON([
        'limit' => $limit,
        'used' => $totalToday,
        'remaining' => max(0, $limit - $totalToday)
    ]);
}
```


---

## 📌 Points Clés à Retenir

### ✅ Bonnes Pratiques

1. **Transactions DB** : Toujours utiliser `transStart()` et `transComplete()` pour les opérations critiques
2. **Réutilisation** : La fonction `enregistrerMultipleTransferts()` appelle `enregistrerOperation()` pour éviter la duplication
3. **Validation** : Toutes les validations sont faites côté serveur (le JS est juste pour l'UX)
4. **Vues partielles** : Diviser les vues en composants réutilisables (header, tabs, etc.)

### 🔐 Sécurité

1. **Sessions** : Vérifier `session('client_id')` ou `session('operator_id')` dans chaque méthode protégée
2. **Input validation** : Ne jamais faire confiance aux données du client
3. **SQL Injection** : Utiliser le query builder de CodeIgniter (jamais de requêtes SQL brutes)
4. **XSS Protection** : CodeIgniter échappe automatiquement les données dans les vues

### 📊 Performance

1. **Indexes** : Créer des index sur `idOperateur`, `idSender`, `idReceiver`, `dateMouvement`
2. **Pagination** : Pour l'historique, implémenter une pagination si > 100 transactions
3. **Cache** : Mettre en cache les tranches de frais (elles changent rarement)


### 🐛 Debugging

#### Vérifier les logs CodeIgniter
```bash
# Logs applicatifs
tail -f writable/logs/log-*.php

# Activer le mode debug dans .env
CI_ENVIRONMENT = development
```

#### Inspecter les requêtes SQL
```php
// Dans le modèle
$builder = $this->builder();
// ... construire la requête
echo $builder->getCompiledSelect(); // Affiche la requête SQL
```

#### Tester une opération
```php
// Dans un contrôleur ou via Spark CLI
$mouvementModel = new \App\Models\Mouvement();
$compte = ['id' => 1, 'solde' => 10000, 'idOperateur' => 1];

try {
    $result = $mouvementModel->enregistrerOperation($compte, 'depot', 5000);
    var_dump($result);
} catch (\Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
```

---

## 📝 Checklist pour ajouter une nouvelle fonctionnalité

- [ ] **Base de données** : Créer/modifier les tables nécessaires
- [ ] **Modèle** : Ajouter les méthodes dans le modèle approprié
- [ ] **Contrôleur** : Créer/modifier les routes et méthodes
- [ ] **Vue** : Créer les fichiers de vue (ou partials)
- [ ] **JavaScript** : Ajouter la logique client-side
- [ ] **CSS** : Ajouter les styles si nécessaire
- [ ] **Validation** : Tester tous les cas (succès, erreurs, edge cases)
- [ ] **Documentation** : Mettre à jour ce fichier si nécessaire


---

## 🗺️ Navigation Rapide dans le Code

### Où trouver quoi ?

| Besoin | Fichier |
|--------|---------|
| Ajouter un onglet client | `app/Views/client/partials/navigation.php` + créer `tab_xxx.php` |
| Modifier le calcul des frais | `app/Models/Mouvement.php` → `enregistrerOperation()` |
| Changer la commission | `app/Models/Mouvement.php` ligne ~85-90 |
| Ajouter un préfixe | Interface opérateur → Préfixes OU `PrefixeController.php` |
| Modifier les tranches | Interface opérateur → Frais OU `TrancheController.php` |
| Bloquer un compte | Interface opérateur → Comptes OU `CompteController.php` |
| Affichage des frais (preview) | `public/js/client-dashboard.js` → `updateFeePreview()` |
| Affichage commission multiple | `public/js/client-dashboard.js` → `updateMultipleFeePreview()` |
| Exécuter une opération | `app/Controllers/ClientController.php` → `operation()` |
| Historique transactions | `app/Models/Mouvement.php` → `getHistoriqueCompte()` |
| Stats opérateur | `app/Models/Mouvement.php` → `calculGain()` |

---

## 🎓 Concepts Importants

### 1. **includeFee = true**
- Émetteur paie : `montant + frais_transfert + frais_retrait`
- Destinataire reçoit : `montant + frais_retrait`
- Le destinataire peut retirer gratuitement car il a déjà les frais de retrait


### 2. **Commission inter-opérateur**
- Se calcule uniquement si `operateur_emetteur ≠ operateur_destinataire`
- Formule : `montant × pourcentage / 100`
- La commission est créditée à l'opérateur du destinataire
- Exemple : Transfer 032→033 avec 2% : commission = montant × 0.02

### 3. **Transfert Multiple**
- Divise le montant total équitablement : `floor(total / nombre_destinataires)`
- Chaque transfert est indépendant (peut avoir sa propre commission)
- Réutilise `enregistrerOperation()` pour chaque destinataire
- Le solde est rechargé avant chaque transfert pour garantir la cohérence

### 4. **Tranches de frais**
- Système de barème par intervalle : `[min, max] → frais`
- Exemple : 100-1000 = 50 Ar, 1001-5000 = 100 Ar
- Fonction `findFeeForAmount()` trouve la tranche correspondante
- Si montant > max de la dernière tranche, on prend les frais de la dernière tranche

### 5. **Transactions en Base**
- `idSender` : NULL pour dépôt, ID du compte pour retrait/transfert
- `idReceiver` : NULL pour retrait, ID du compte pour dépôt/transfert
- `idOperateur` : Toujours l'opérateur de l'émetteur (ou du compte pour dépôt)
- `montantCommission` : 0 si même opérateur, calculé sinon


---

## 🚀 Quick Start pour Développeurs

### Modifier un calcul de frais
```php
// 1. Aller dans app/Models/Mouvement.php
// 2. Trouver la méthode enregistrerOperation()
// 3. Modifier la ligne de calcul :
$fee = $trancheModel->findFeeForAmount($typeOperation['idBareme'], $amount);
// 4. Exemple : ajouter une réduction de 10%
$fee = $fee * 0.9;
```

### Ajouter un nouvel onglet client
```php
// 1. Créer app/Views/client/partials/tab_xxx.php
// 2. Ajouter le bouton dans navigation.php
// 3. Inclure dans dashboard.php : <?= view('client/partials/tab_xxx') ?>
// 4. Ajouter le CSS dans client-dashboard.css si nécessaire
// 5. Ajouter la logique JS dans client-dashboard.js
```

### Débugger une opération
```php
// Dans ClientController::operation()
log_message('debug', 'Operation data: ' . json_encode($data));

// Ou ajouter temporairement :
var_dump($data);
die();
```

### Tester rapidement
```bash
# Lancer le serveur
php spark serve

# Accéder à l'interface
# Opérateur: http://localhost:8080/operator/login
# Client: http://localhost:8080/client/login
```


---

## 📞 Aide Rapide

### Erreur "Solde insuffisant"
- Vérifier le calcul du `totalCost` dans `enregistrerOperation()`
- Pour transfert : `montant + frais + commission`
- Pour transfert avec includeFee : `montant + frais_transfert + frais_retrait + commission`

### Commission ne s'affiche pas
1. Vérifier que les opérateurs sont différents
2. Vérifier `PREFIXES_DATA` dans la console navigateur
3. Vérifier `pourcentageCommission` dans la table `Operateur`
4. Vérifier la fonction `updateFeePreview()` ou `updateMultipleFeePreview()`

### Transfert multiple ne fonctionne pas
1. Vérifier que `enregistrerMultipleTransferts()` appelle bien `enregistrerOperation()`
2. Vérifier le rechargement du compte dans la boucle
3. Vérifier les validations (doublons, auto-envoi)
4. Consulter les logs d'erreur

### Frais incorrects
1. Vérifier les tranches dans la table `Tranche`
2. Vérifier `findFeeForAmount()` dans le modèle `Tranche`
3. S'assurer que les tranches ne se chevauchent pas
4. Vérifier que le barème est bien lié au type d'opération

---

**Document créé le :** <?= date('d/m/Y') ?>  
**Version :** 2.0  
**Dernière mise à jour :** Implémentation commission inter-opérateur + transfert multiple

