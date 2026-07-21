# Guide Complet du Query Builder CodeIgniter 4

## 📋 Table des Matières

1. [Introduction](#introduction)
2. [Bases du Query Builder](#bases-du-query-builder)
3. [Opérations SELECT](#opérations-select)
4. [Opérations INSERT, UPDATE, DELETE](#opérations-insert-update-delete)
5. [Jointures](#jointures)
6. [Agrégations et Grouping](#agrégations-et-grouping)
7. [Query Builder dans NovaMoney](#query-builder-dans-novamoney)
8. [Exemples Pratiques](#exemples-pratiques)
9. [Bonnes Pratiques](#bonnes-pratiques)

---

## 🎯 Introduction

Le **Query Builder** de CodeIgniter 4 permet de construire des requêtes SQL de manière programmatique, offrant :
- ✅ Protection contre les injections SQL
- ✅ Compatibilité multi-bases de données
- ✅ Code plus lisible et maintenable
- ✅ Chaînage de méthodes (fluent interface)

### Accéder au Query Builder

```php
// Dans un contrôleur
$db = \Config\Database::connect();
$builder = $db->table('nom_table');

// Dans un modèle
$builder = $this->builder();
// ou
$builder = $this->db->table('nom_table');
```

---

## 📖 Bases du Query Builder

### 1. **Structure de base**

```php
$builder = $db->table('utilisateurs');
$query = $builder->get();
$results = $query->getResultArray();
```


**Équivalent SQL :**
```sql
SELECT * FROM utilisateurs;
```

### 2. **Méthodes de récupération**

```php
// Retourne un tableau d'objets
$results = $query->getResult();

// Retourne un tableau de tableaux associatifs
$results = $query->getResultArray();

// Retourne la première ligne (objet)
$row = $query->getRow();

// Retourne la première ligne (tableau associatif)
$row = $query->getRowArray();

// Retourne une colonne spécifique
$column = $query->getResultArray('column_name');
```

---

## 🔍 Opérations SELECT

### 1. **Sélectionner des colonnes spécifiques**

```php
// SELECT id, nom, email FROM utilisateurs
$builder->select('id, nom, email');
$query = $builder->get();

// Avec alias
$builder->select('id, nom as name, email as mail');

// Ajouter des colonnes à la sélection existante
$builder->select('id, nom')
        ->select('email'); // Ajoute email à la sélection
```

### 2. **WHERE - Conditions simples**

```php
// WHERE id = 5
$builder->where('id', 5);

// WHERE nom = 'John'
$builder->where('nom', 'John');

// WHERE age > 18
$builder->where('age >', 18);

// WHERE email LIKE '%@gmail.com'
$builder->like('email', '@gmail.com');

// WHERE email NOT LIKE '%@yahoo.com'
$builder->notLike('email', '@yahoo.com');
```


### 3. **WHERE - Conditions multiples**

```php
// WHERE id = 5 AND nom = 'John'
$builder->where('id', 5)
        ->where('nom', 'John');

// WHERE id = 5 OR id = 10
$builder->where('id', 5)
        ->orWhere('id', 10);

// WHERE id IN (1, 2, 3, 4, 5)
$builder->whereIn('id', [1, 2, 3, 4, 5]);

// WHERE id NOT IN (6, 7, 8)
$builder->whereNotIn('id', [6, 7, 8]);

// WHERE age BETWEEN 18 AND 30
$builder->where('age >=', 18)
        ->where('age <=', 30);

// WHERE nom IS NULL
$builder->where('nom', null);

// WHERE nom IS NOT NULL
$builder->where('nom !=', null);
```

### 4. **WHERE - Groupes de conditions**

```php
// WHERE (id = 1 OR id = 2) AND status = 'actif'
$builder->groupStart()
            ->where('id', 1)
            ->orWhere('id', 2)
        ->groupEnd()
        ->where('status', 'actif');
```

**Équivalent SQL :**
```sql
SELECT * FROM utilisateurs 
WHERE (id = 1 OR id = 2) AND status = 'actif';
```

### 5. **ORDER BY - Tri**

```php
// ORDER BY nom ASC
$builder->orderBy('nom', 'ASC');

// ORDER BY age DESC
$builder->orderBy('age', 'DESC');

// ORDER BY nom ASC, age DESC
$builder->orderBy('nom', 'ASC')
        ->orderBy('age', 'DESC');

// ORDER BY RAND() - Ordre aléatoire
$builder->orderBy('id', 'RANDOM');
```


### 6. **LIMIT et OFFSET - Pagination**

```php
// LIMIT 10
$builder->limit(10);

// LIMIT 10 OFFSET 20 (Page 3, 10 par page)
$builder->limit(10, 20);

// Pagination simple
$page = 2;
$perPage = 10;
$offset = ($page - 1) * $perPage;
$builder->limit($perPage, $offset);
```

### 7. **DISTINCT - Valeurs uniques**

```php
// SELECT DISTINCT nom FROM utilisateurs
$builder->distinct()
        ->select('nom')
        ->get();
```

---

## ✏️ Opérations INSERT, UPDATE, DELETE

### 1. **INSERT - Insertion**

```php
// INSERT INTO utilisateurs (nom, email, age) VALUES ('John', 'john@example.com', 25)
$data = [
    'nom' => 'John',
    'email' => 'john@example.com',
    'age' => 25
];
$builder->insert($data);

// Récupérer l'ID inséré
$insertID = $db->insertID();

// Insertion multiple
$data = [
    ['nom' => 'John', 'email' => 'john@example.com'],
    ['nom' => 'Jane', 'email' => 'jane@example.com'],
    ['nom' => 'Bob', 'email' => 'bob@example.com']
];
$builder->insertBatch($data);
```

### 2. **UPDATE - Mise à jour**

```php
// UPDATE utilisateurs SET nom = 'Johnny' WHERE id = 5
$data = ['nom' => 'Johnny'];
$builder->where('id', 5)
        ->update($data);

// Mise à jour avec conditions multiples
$data = ['status' => 'inactif'];
$builder->where('last_login <', '2023-01-01')
        ->update($data);

// Incrémenter/Décrémenter
$builder->where('id', 5)
        ->set('points', 'points + 10', false) // false = pas d'échappement
        ->update();
```


### 3. **DELETE - Suppression**

```php
// DELETE FROM utilisateurs WHERE id = 5
$builder->where('id', 5)
        ->delete();

// DELETE FROM utilisateurs WHERE age < 18
$builder->where('age <', 18)
        ->delete();

// Vider toute la table
$builder->emptyTable(); // TRUNCATE

// Supprimer toutes les lignes (avec conditions possibles)
$builder->delete(); // DELETE FROM utilisateurs
```

---

## 🔗 Jointures

### 1. **INNER JOIN**

```php
// SELECT * FROM utilisateurs 
// INNER JOIN commandes ON utilisateurs.id = commandes.id_utilisateur
$builder->select('utilisateurs.*, commandes.montant')
        ->from('utilisateurs')
        ->join('commandes', 'utilisateurs.id = commandes.id_utilisateur')
        ->get();

// Ou avec alias
$builder->select('u.nom, c.montant')
        ->from('utilisateurs u')
        ->join('commandes c', 'u.id = c.id_utilisateur')
        ->get();
```

### 2. **LEFT JOIN**

```php
// SELECT * FROM utilisateurs 
// LEFT JOIN commandes ON utilisateurs.id = commandes.id_utilisateur
$builder->select('utilisateurs.*, commandes.montant')
        ->from('utilisateurs')
        ->join('commandes', 'utilisateurs.id = commandes.id_utilisateur', 'left')
        ->get();
```

### 3. **RIGHT JOIN et OUTER JOIN**

```php
// RIGHT JOIN
$builder->join('table2', 'table1.id = table2.id_table1', 'right');

// OUTER JOIN
$builder->join('table2', 'table1.id = table2.id_table1', 'outer');
```


### 4. **Jointures multiples**

```php
// Joindre plusieurs tables
$builder->select('u.nom, c.montant, p.nom_produit')
        ->from('utilisateurs u')
        ->join('commandes c', 'u.id = c.id_utilisateur')
        ->join('produits p', 'c.id_produit = p.id')
        ->where('c.status', 'payée')
        ->get();
```

**Équivalent SQL :**
```sql
SELECT u.nom, c.montant, p.nom_produit
FROM utilisateurs u
INNER JOIN commandes c ON u.id = c.id_utilisateur
INNER JOIN produits p ON c.id_produit = p.id
WHERE c.status = 'payée';
```

---

## 📊 Agrégations et Grouping

### 1. **Fonctions d'agrégation**

```php
// COUNT - Compter les lignes
$builder->selectCount('id', 'total');
$total = $builder->get()->getRowArray()['total'];

// SUM - Somme
$builder->selectSum('montant', 'total_ventes');
$result = $builder->get()->getRowArray();
echo $result['total_ventes'];

// AVG - Moyenne
$builder->selectAvg('age', 'age_moyen');

// MAX - Maximum
$builder->selectMax('prix', 'prix_max');

// MIN - Minimum
$builder->selectMin('prix', 'prix_min');
```

### 2. **GROUP BY**

```php
// SELECT ville, COUNT(*) as total 
// FROM utilisateurs 
// GROUP BY ville
$builder->select('ville, COUNT(*) as total')
        ->groupBy('ville')
        ->get();

// GROUP BY multiple
$builder->select('ville, pays, COUNT(*) as total')
        ->groupBy(['ville', 'pays'])
        ->get();
```


### 3. **HAVING - Filtrer après GROUP BY**

```php
// SELECT ville, COUNT(*) as total 
// FROM utilisateurs 
// GROUP BY ville 
// HAVING total > 10
$builder->select('ville, COUNT(*) as total')
        ->groupBy('ville')
        ->having('total >', 10)
        ->get();

// Avec plusieurs conditions
$builder->select('ville, AVG(age) as age_moyen')
        ->groupBy('ville')
        ->having('age_moyen >', 25)
        ->having('age_moyen <', 40)
        ->get();
```

---

## 🚀 Query Builder dans NovaMoney

Voici les requêtes utilisées dans votre projet avec explications détaillées.

### 1. **Calculer les gains d'un opérateur** (`Mouvement.php`)

```php
public function calculGain(int $idTypeOperation, int $idOperateur, $dateMin = null, $dateMax = null)
{
    $builder = $this->builder();
    
    // SELECT SUM(montantFrais) as frais FROM Mouvement
    $builder->selectSum('montantFrais', 'frais')
            ->where('idTypeOperation', $idTypeOperation)
            ->where('idOperateur', $idOperateur);
    
    // Filtres optionnels
    if ($dateMin !== null) {
        $builder->where('dateMouvement >=', $dateMin);
    }
    
    if ($dateMax !== null) {
        $builder->where('dateMouvement <=', $dateMax);
    }
    
    return $builder->get()->getRowArray()['frais'];
}
```

**Équivalent SQL :**
```sql
SELECT SUM(montantFrais) as frais 
FROM Mouvement 
WHERE idTypeOperation = ? 
  AND idOperateur = ? 
  AND dateMouvement >= ? 
  AND dateMouvement <= ?;
```


### 2. **Historique des mouvements avec jointures** (`Mouvement.php`)

```php
public function getHistoriqueCompte(int $idCompte, float $soldeActuel): array
{
    $rows = $this->select(
        'Mouvement.id, Mouvement.somme, Mouvement.montantFrais, Mouvement.dateMouvement, ' .
        'Mouvement.idSender, Mouvement.idReceiver, TypeOperation.libelle AS typeLibelle, ' .
        'sender.number AS senderNumber, receiver.number AS receiverNumber'
    )
    ->join('TypeOperation', 'TypeOperation.id = Mouvement.idTypeOperation')
    ->join('compte AS sender', 'sender.id = Mouvement.idSender', 'left')
    ->join('compte AS receiver', 'receiver.id = Mouvement.idReceiver', 'left')
    ->groupStart()
        ->where('Mouvement.idSender', $idCompte)
        ->orWhere('Mouvement.idReceiver', $idCompte)
    ->groupEnd()
    ->orderBy('Mouvement.dateMouvement', 'DESC')
    ->orderBy('Mouvement.id', 'DESC')
    ->findAll();
    
    return $rows;
}
```

**Équivalent SQL :**
```sql
SELECT 
    Mouvement.id, 
    Mouvement.somme, 
    Mouvement.montantFrais, 
    Mouvement.dateMouvement,
    Mouvement.idSender, 
    Mouvement.idReceiver, 
    TypeOperation.libelle AS typeLibelle,
    sender.number AS senderNumber, 
    receiver.number AS receiverNumber
FROM Mouvement
INNER JOIN TypeOperation ON TypeOperation.id = Mouvement.idTypeOperation
LEFT JOIN compte AS sender ON sender.id = Mouvement.idSender
LEFT JOIN compte AS receiver ON receiver.id = Mouvement.idReceiver
WHERE (Mouvement.idSender = ? OR Mouvement.idReceiver = ?)
ORDER BY Mouvement.dateMouvement DESC, Mouvement.id DESC;
```

**Explications :**
- `LEFT JOIN` : pour récupérer les comptes même s'ils sont NULL (dépôt/retrait)
- `groupStart()` / `groupEnd()` : crée des parenthèses pour OR
- `orderBy()` multiple : tri par date puis par ID


### 3. **Détails des mouvements avec type d'opération** (`Mouvement.php`)

```php
public function getMouvementDetails(int $idOperateur, $dateMin = null, $dateMax = null): array
{
    $builder = $this->builder();
    
    $builder->select('Mouvement.id, Mouvement.idSender, Mouvement.idReceiver, ' .
                     'Mouvement.somme, Mouvement.montantFrais, Mouvement.dateMouvement, ' .
                     'Mouvement.idTypeOperation, TypeOperation.libelle as typeLibelle')
            ->join('TypeOperation', 'TypeOperation.id = Mouvement.idTypeOperation')
            ->where('Mouvement.idOperateur', $idOperateur);
    
    if ($dateMin !== null) {
        $builder->where('Mouvement.dateMouvement >=', $dateMin);
    }
    
    if ($dateMax !== null) {
        $builder->where('Mouvement.dateMouvement <=', $dateMax);
    }
    
    return $builder->get()->getResultArray();
}
```

**Équivalent SQL :**
```sql
SELECT 
    Mouvement.id,
    Mouvement.idSender,
    Mouvement.idReceiver,
    Mouvement.somme,
    Mouvement.montantFrais,
    Mouvement.dateMouvement,
    Mouvement.idTypeOperation,
    TypeOperation.libelle as typeLibelle
FROM Mouvement
INNER JOIN TypeOperation ON TypeOperation.id = Mouvement.idTypeOperation
WHERE Mouvement.idOperateur = ?
  AND Mouvement.dateMouvement >= ?
  AND Mouvement.dateMouvement <= ?;
```

### 4. **Récupérer les tranches de frais** (`Tranche.php`)

```php
public function getTranchesByBareme(int $idBareme): array
{
    return $this->where('idBareme', $idBareme)
                ->orderBy('min', 'ASC')
                ->findAll();
}
```

**Équivalent SQL :**
```sql
SELECT * FROM Tranche 
WHERE idBareme = ? 
ORDER BY min ASC;
```


### 5. **Trouver les frais pour un montant** (`Tranche.php`)

```php
public function findFeeForAmount(int $idBareme, float $amount): float
{
    $tranche = $this->where('idBareme', $idBareme)
                    ->where('min <=', $amount)
                    ->where('max >=', $amount)
                    ->first();
    
    if ($tranche) {
        return (float) $tranche['montant'];
    }
    
    // Si aucune tranche trouvée, prendre la dernière
    $lastTranche = $this->where('idBareme', $idBareme)
                        ->orderBy('max', 'DESC')
                        ->first();
    
    return $lastTranche ? (float) $lastTranche['montant'] : 0.0;
}
```

**Équivalent SQL :**
```sql
-- Première tentative
SELECT * FROM Tranche 
WHERE idBareme = ? 
  AND min <= ? 
  AND max >= ?
LIMIT 1;

-- Si rien trouvé, prendre la dernière
SELECT * FROM Tranche 
WHERE idBareme = ? 
ORDER BY max DESC 
LIMIT 1;
```

### 6. **Récupérer les comptes par opérateur** (`Compte.php`)

```php
public function getComptesByOperateur(int $idOperateur): array
{
    return $this->select('compte.*, StatusType.libelle as statusLibelle')
                ->join('StatusType', 'StatusType.id = compte.idStatus', 'left')
                ->where('compte.idOperateur', $idOperateur)
                ->orderBy('compte.id', 'DESC')
                ->findAll();
}
```

**Équivalent SQL :**
```sql
SELECT compte.*, StatusType.libelle as statusLibelle
FROM compte
LEFT JOIN StatusType ON StatusType.id = compte.idStatus
WHERE compte.idOperateur = ?
ORDER BY compte.id DESC;
```


### 7. **Récupérer les préfixes d'un opérateur** (`PrefixOperateur.php`)

```php
public function getPrefixesByOperateur(int $idOperateur): array
{
    return $this->where('idOperateur', $idOperateur)
                ->orderBy('prefixe', 'ASC')
                ->findAll();
}
```

**Équivalent SQL :**
```sql
SELECT * FROM PrefixOperateur 
WHERE idOperateur = ? 
ORDER BY prefixe ASC;
```

### 8. **Ajuster le solde d'un compte** (`Compte.php`)

```php
public function ajusterSolde(int $idCompte, float $montant): float
{
    $compte = $this->find($idCompte);
    $nouveauSolde = (float) $compte['solde'] + $montant;
    
    $this->update($idCompte, ['solde' => $nouveauSolde]);
    
    return $nouveauSolde;
}
```

**Équivalent SQL :**
```sql
-- Lecture
SELECT * FROM compte WHERE id = ?;

-- Mise à jour
UPDATE compte 
SET solde = ? 
WHERE id = ?;
```

**Alternative plus performante (en une seule requête) :**
```php
public function ajusterSolde(int $idCompte, float $montant): float
{
    $this->where('id', $idCompte)
         ->set('solde', "solde + $montant", false) // false = pas d'échappement
         ->update();
    
    $compte = $this->find($idCompte);
    return (float) $compte['solde'];
}
```

**SQL généré :**
```sql
UPDATE compte 
SET solde = solde + ? 
WHERE id = ?;
```


---

## 💡 Exemples Pratiques

### Exemple 1 : Pagination complète

```php
public function getUtilisateursPaginated(int $page = 1, int $perPage = 10): array
{
    $offset = ($page - 1) * $perPage;
    
    $builder = $this->builder();
    
    // Compter le total
    $total = $builder->countAllResults(false); // false = ne pas réinitialiser le builder
    
    // Récupérer les données
    $data = $builder->limit($perPage, $offset)
                    ->orderBy('id', 'DESC')
                    ->get()
                    ->getResultArray();
    
    return [
        'data' => $data,
        'total' => $total,
        'page' => $page,
        'perPage' => $perPage,
        'totalPages' => ceil($total / $perPage)
    ];
}
```

### Exemple 2 : Recherche avec filtres multiples

```php
public function searchUtilisateurs(array $filters): array
{
    $builder = $this->builder();
    
    // Filtre par nom (recherche partielle)
    if (!empty($filters['nom'])) {
        $builder->like('nom', $filters['nom']);
    }
    
    // Filtre par âge (range)
    if (!empty($filters['age_min'])) {
        $builder->where('age >=', $filters['age_min']);
    }
    if (!empty($filters['age_max'])) {
        $builder->where('age <=', $filters['age_max']);
    }
    
    // Filtre par ville (exacte)
    if (!empty($filters['ville'])) {
        $builder->where('ville', $filters['ville']);
    }
    
    // Filtre par statut (multiple)
    if (!empty($filters['statuts']) && is_array($filters['statuts'])) {
        $builder->whereIn('statut', $filters['statuts']);
    }
    
    return $builder->orderBy('nom', 'ASC')
                   ->get()
                   ->getResultArray();
}
```


### Exemple 3 : Statistiques avancées

```php
public function getStatistiquesVentes(int $idOperateur, string $periode): array
{
    $builder = $this->builder();
    
    // Période
    $dateDebut = match($periode) {
        'jour' => date('Y-m-d 00:00:00'),
        'semaine' => date('Y-m-d 00:00:00', strtotime('-7 days')),
        'mois' => date('Y-m-01 00:00:00'),
        default => date('Y-01-01 00:00:00')
    };
    
    $stats = $builder->select('
            COUNT(*) as nombre_transactions,
            SUM(somme) as montant_total,
            AVG(somme) as montant_moyen,
            MIN(somme) as montant_min,
            MAX(somme) as montant_max,
            SUM(montantFrais) as total_frais,
            SUM(montantCommission) as total_commission
        ')
        ->where('idOperateur', $idOperateur)
        ->where('dateMouvement >=', $dateDebut)
        ->get()
        ->getRowArray();
    
    return $stats;
}
```

**Équivalent SQL :**
```sql
SELECT 
    COUNT(*) as nombre_transactions,
    SUM(somme) as montant_total,
    AVG(somme) as montant_moyen,
    MIN(somme) as montant_min,
    MAX(somme) as montant_max,
    SUM(montantFrais) as total_frais,
    SUM(montantCommission) as total_commission
FROM Mouvement
WHERE idOperateur = ?
  AND dateMouvement >= ?;
```

### Exemple 4 : Rapport par type d'opération

```php
public function getRapportParType(int $idOperateur): array
{
    $builder = $this->builder();
    
    return $builder->select('
            TypeOperation.libelle as type,
            COUNT(*) as nombre,
            SUM(Mouvement.somme) as total_montant,
            SUM(Mouvement.montantFrais) as total_frais
        ')
        ->join('TypeOperation', 'TypeOperation.id = Mouvement.idTypeOperation')
        ->where('Mouvement.idOperateur', $idOperateur)
        ->groupBy('TypeOperation.libelle')
        ->orderBy('total_montant', 'DESC')
        ->get()
        ->getResultArray();
}
```

**Équivalent SQL :**
```sql
SELECT 
    TypeOperation.libelle as type,
    COUNT(*) as nombre,
    SUM(Mouvement.somme) as total_montant,
    SUM(Mouvement.montantFrais) as total_frais
FROM Mouvement
INNER JOIN TypeOperation ON TypeOperation.id = Mouvement.idTypeOperation
WHERE Mouvement.idOperateur = ?
GROUP BY TypeOperation.libelle
ORDER BY total_montant DESC;
```


### Exemple 5 : Top 10 des clients les plus actifs

```php
public function getTopClients(int $idOperateur, int $limit = 10): array
{
    $builder = $this->db->table('Mouvement');
    
    return $builder->select('
            compte.number,
            compte.solde,
            COUNT(DISTINCT Mouvement.id) as nombre_transactions,
            SUM(CASE WHEN Mouvement.idSender = compte.id THEN Mouvement.somme ELSE 0 END) as montant_envoye,
            SUM(CASE WHEN Mouvement.idReceiver = compte.id THEN Mouvement.somme ELSE 0 END) as montant_recu
        ')
        ->join('compte', 'compte.id = Mouvement.idSender OR compte.id = Mouvement.idReceiver')
        ->where('compte.idOperateur', $idOperateur)
        ->groupBy('compte.id, compte.number, compte.solde')
        ->orderBy('nombre_transactions', 'DESC')
        ->limit($limit)
        ->get()
        ->getResultArray();
}
```

### Exemple 6 : Vérifier l'existence avant insertion

```php
public function ajouterPrefixeUnique(string $prefixe, int $idOperateur): bool
{
    // Vérifier si le préfixe existe déjà
    $existe = $this->where('prefixe', $prefixe)
                   ->where('idOperateur', $idOperateur)
                   ->countAllResults() > 0;
    
    if ($existe) {
        return false; // Déjà existant
    }
    
    // Insérer
    $this->insert([
        'prefixe' => $prefixe,
        'idOperateur' => $idOperateur
    ]);
    
    return true;
}
```

### Exemple 7 : Mise à jour conditionnelle

```php
public function bloquerComptesInactifs(int $joursInactivite = 180): int
{
    $dateLimit = date('Y-m-d H:i:s', strtotime("-$joursInactivite days"));
    
    // Trouver les comptes inactifs
    $comptesInactifs = $this->db->table('compte')
        ->select('compte.id')
        ->join('Mouvement', 'Mouvement.idSender = compte.id OR Mouvement.idReceiver = compte.id', 'left')
        ->groupBy('compte.id')
        ->having('MAX(Mouvement.dateMouvement) <', $dateLimit)
        ->orHaving('MAX(Mouvement.dateMouvement) IS NULL')
        ->get()
        ->getResultArray();
    
    $ids = array_column($comptesInactifs, 'id');
    
    if (empty($ids)) {
        return 0;
    }
    
    // Bloquer les comptes
    return $this->whereIn('id', $ids)
                ->set('idStatus', 2) // 2 = bloqué
                ->update();
}
```


---

## ✅ Bonnes Pratiques

### 1. **Toujours échapper les données utilisateur**

```php
// ✅ BON - Échappement automatique
$builder->where('nom', $userInput);

// ❌ MAUVAIS - Risque d'injection SQL
$builder->where("nom = '$userInput'");
```

### 2. **Utiliser les transactions pour les opérations critiques**

```php
$db = \Config\Database::connect();
$db->transStart();

try {
    // Opération 1
    $builder1->insert($data1);
    
    // Opération 2
    $builder2->update($data2);
    
    // Opération 3
    $builder3->delete();
    
    $db->transComplete();
    
    if ($db->transStatus() === false) {
        throw new \Exception('Transaction échouée');
    }
} catch (\Exception $e) {
    $db->transRollback();
    log_message('error', $e->getMessage());
}
```

### 3. **Réutiliser le builder pour plusieurs requêtes**

```php
// ❌ MAUVAIS - Crée plusieurs builders
$total = $this->builder()->where('status', 'actif')->countAllResults();
$data = $this->builder()->where('status', 'actif')->limit(10)->get();

// ✅ BON - Réutilise le même builder
$builder = $this->builder();
$builder->where('status', 'actif');
$total = $builder->countAllResults(false); // false = ne pas reset
$data = $builder->limit(10)->get();
```

### 4. **Utiliser les index de base de données**

```php
// Si vous faites souvent cette requête :
$builder->where('idOperateur', $id)
        ->where('dateMouvement >=', $date)
        ->get();

// Créez un index composite :
// CREATE INDEX idx_operateur_date ON Mouvement(idOperateur, dateMouvement);
```


### 5. **Éviter SELECT * dans les requêtes avec jointures**

```php
// ❌ MAUVAIS - Peut créer des conflits de noms de colonnes
$builder->select('*')
        ->join('autre_table', 'table.id = autre_table.table_id')
        ->get();

// ✅ BON - Spécifier les colonnes nécessaires
$builder->select('table.id, table.nom, autre_table.valeur')
        ->join('autre_table', 'table.id = autre_table.table_id')
        ->get();
```

### 6. **Utiliser les alias pour plus de clarté**

```php
// ✅ BON - Alias clairs
$builder->select('u.nom as client_nom, o.nom as operateur_nom, m.somme')
        ->from('utilisateurs u')
        ->join('operateurs o', 'o.id = u.idOperateur')
        ->join('mouvements m', 'm.idSender = u.id')
        ->get();
```

### 7. **Logger les requêtes en développement**

```php
// Afficher la requête SQL générée (debug)
$builder = $this->builder();
$builder->where('id', 5);
echo $builder->getCompiledSelect(); // Ne pas utiliser en production !
$builder->get(); // Exécuter ensuite

// Ou utiliser le Debug Toolbar de CodeIgniter 4
// Actif automatiquement en mode development
```

### 8. **Gérer les erreurs proprement**

```php
try {
    $result = $builder->insert($data);
    
    if (!$result) {
        throw new \Exception('Insertion échouée');
    }
    
    return $this->db->insertID();
} catch (\Exception $e) {
    log_message('error', 'Erreur insertion: ' . $e->getMessage());
    throw $e;
}
```


### 9. **Optimiser les requêtes lourdes**

```php
// ❌ MAUVAIS - N+1 queries
$utilisateurs = $userModel->findAll();
foreach ($utilisateurs as $user) {
    $user['commandes'] = $commandeModel->where('id_user', $user['id'])->findAll();
}

// ✅ BON - Une seule requête avec jointure
$builder->select('u.*, c.id as commande_id, c.montant')
        ->from('utilisateurs u')
        ->join('commandes c', 'c.id_user = u.id', 'left')
        ->get();
```

### 10. **Utiliser les sous-requêtes quand nécessaire**

```php
// Trouver les utilisateurs qui ont dépensé plus que la moyenne
$subQuery = $this->db->table('commandes')
                     ->select('AVG(montant)')
                     ->getCompiledSelect();

$builder->where("montant_total > ($subQuery)")
        ->get();
```

**Équivalent SQL :**
```sql
SELECT * FROM utilisateurs 
WHERE montant_total > (SELECT AVG(montant) FROM commandes);
```

---

## 🔧 Debugging et Performance

### 1. **Activer le Query Log**

```php
// Dans app/Config/Database.php
public array $default = [
    'DBDebug' => true, // Active les erreurs détaillées
];
```

### 2. **Voir le dernier query exécuté**

```php
$builder->get();
echo $this->db->getLastQuery();
```

### 3. **Compter les requêtes**

```php
$queries = $this->db->getQueries();
echo "Nombre de requêtes : " . count($queries);
```


### 4. **Analyser la performance**

```php
$start = microtime(true);

$builder->where('status', 'actif')->get();

$end = microtime(true);
$time = round(($end - $start) * 1000, 2);

log_message('debug', "Requête exécutée en {$time}ms");
```

### 5. **Utiliser EXPLAIN pour optimiser**

```php
// Obtenir le plan d'exécution de la requête
$builder->where('idOperateur', 1);
$sql = $builder->getCompiledSelect();

$explain = $this->db->query("EXPLAIN $sql")->getResultArray();
var_dump($explain);
```

---

## 📚 Référence Rapide

### Méthodes de sélection
- `select()` - Colonnes à sélectionner
- `selectCount()`, `selectSum()`, `selectAvg()`, `selectMax()`, `selectMin()` - Agrégations
- `distinct()` - Valeurs uniques
- `from()` - Table source

### Méthodes de filtrage
- `where()` - Condition simple
- `orWhere()` - Condition OR
- `whereIn()`, `whereNotIn()` - Valeurs dans une liste
- `like()`, `notLike()` - Recherche partielle
- `groupStart()`, `groupEnd()` - Grouper des conditions

### Méthodes de tri et limite
- `orderBy()` - Tri
- `groupBy()` - Groupement
- `having()` - Filtre après GROUP BY
- `limit()` - Limiter les résultats
- `offset()` - Décalage (pagination)

### Méthodes de jointure
- `join()` - Jointure (INNER par défaut)
- Types: `'inner'`, `'left'`, `'right'`, `'outer'`

### Méthodes d'exécution
- `get()` - Exécuter SELECT
- `insert()` - Insérer une ligne
- `insertBatch()` - Insérer plusieurs lignes
- `update()` - Mettre à jour
- `delete()` - Supprimer
- `countAllResults()` - Compter


### Méthodes de récupération
- `getResult()` - Tableau d'objets
- `getResultArray()` - Tableau de tableaux associatifs
- `getRow()` - Première ligne (objet)
- `getRowArray()` - Première ligne (tableau associatif)
- `first()` - Première ligne (modèle)
- `findAll()` - Toutes les lignes (modèle)

---

## 🎓 Exercices Pratiques

### Exercice 1 : Créer une requête de rapport mensuel

**Objectif :** Obtenir le total des transactions par jour pour le mois en cours.

```php
public function getRapportMensuel(int $idOperateur): array
{
    $builder = $this->builder();
    
    return $builder->select("
            DATE(dateMouvement) as jour,
            COUNT(*) as nombre_transactions,
            SUM(somme) as total_montant,
            SUM(montantFrais) as total_frais
        ")
        ->where('idOperateur', $idOperateur)
        ->where('MONTH(dateMouvement)', date('m'))
        ->where('YEAR(dateMouvement)', date('Y'))
        ->groupBy('DATE(dateMouvement)')
        ->orderBy('jour', 'ASC')
        ->get()
        ->getResultArray();
}
```

### Exercice 2 : Recherche de comptes avec filtres

**Objectif :** Rechercher des comptes avec plusieurs critères optionnels.

```php
public function rechercherComptes(array $criteres): array
{
    $builder = $this->builder();
    
    // Numéro de téléphone (recherche partielle)
    if (!empty($criteres['numero'])) {
        $builder->like('number', $criteres['numero']);
    }
    
    // Solde minimum
    if (isset($criteres['solde_min'])) {
        $builder->where('solde >=', $criteres['solde_min']);
    }
    
    // Solde maximum
    if (isset($criteres['solde_max'])) {
        $builder->where('solde <=', $criteres['solde_max']);
    }
    
    // Opérateur
    if (!empty($criteres['idOperateur'])) {
        $builder->where('idOperateur', $criteres['idOperateur']);
    }
    
    // Statut
    if (!empty($criteres['idStatus'])) {
        $builder->where('idStatus', $criteres['idStatus']);
    }
    
    return $builder->orderBy('solde', 'DESC')
                   ->get()
                   ->getResultArray();
}
```


### Exercice 3 : Classement des opérateurs par volume

**Objectif :** Obtenir un classement des opérateurs selon leur volume de transactions.

```php
public function getClassementOperateurs(): array
{
    $builder = $this->db->table('Mouvement');
    
    return $builder->select("
            Operateur.nom,
            COUNT(Mouvement.id) as nombre_transactions,
            SUM(Mouvement.somme) as volume_total,
            SUM(Mouvement.montantFrais) as gains_frais,
            SUM(Mouvement.montantCommission) as gains_commission
        ")
        ->join('Operateur', 'Operateur.id = Mouvement.idOperateur')
        ->groupBy('Operateur.id, Operateur.nom')
        ->orderBy('volume_total', 'DESC')
        ->get()
        ->getResultArray();
}
```

**SQL équivalent :**
```sql
SELECT 
    Operateur.nom,
    COUNT(Mouvement.id) as nombre_transactions,
    SUM(Mouvement.somme) as volume_total,
    SUM(Mouvement.montantFrais) as gains_frais,
    SUM(Mouvement.montantCommission) as gains_commission
FROM Mouvement
INNER JOIN Operateur ON Operateur.id = Mouvement.idOperateur
GROUP BY Operateur.id, Operateur.nom
ORDER BY volume_total DESC;
```

---

## 🚨 Erreurs Courantes et Solutions

### Erreur 1 : "Call to undefined method"

```php
// ❌ ERREUR
$builder = $this->builder();
$result = $builder->where('id', 5)->first();
// first() n'existe pas sur le builder brut

// ✅ SOLUTION
$result = $builder->where('id', 5)->get()->getRowArray();
// ou utiliser le modèle directement
$result = $this->where('id', 5)->first();
```

### Erreur 2 : "Column not found" après GROUP BY

```php
// ❌ ERREUR - MySQL strict mode
$builder->select('nom, email')
        ->groupBy('nom')
        ->get();

// ✅ SOLUTION - Inclure toutes les colonnes non-agrégées dans GROUP BY
$builder->select('nom, email')
        ->groupBy('nom, email')
        ->get();
```


### Erreur 3 : Builder se réinitialise après get()

```php
// ❌ PROBLÈME
$builder = $this->builder();
$builder->where('status', 'actif');
$count = $builder->countAllResults(); // Builder réinitialisé !
$data = $builder->get(); // WHERE status='actif' perdu !

// ✅ SOLUTION
$builder = $this->builder();
$builder->where('status', 'actif');
$count = $builder->countAllResults(false); // false = ne pas réinitialiser
$data = $builder->get();
```

### Erreur 4 : Jointure ambiguë

```php
// ❌ ERREUR - Colonne 'id' ambiguë (existe dans les deux tables)
$builder->select('id, nom')
        ->join('autre_table', 'table.id = autre_table.table_id')
        ->where('id', 5) // Quelle table ?
        ->get();

// ✅ SOLUTION - Préfixer avec le nom de la table
$builder->select('table.id, table.nom')
        ->join('autre_table', 'table.id = autre_table.table_id')
        ->where('table.id', 5)
        ->get();
```

### Erreur 5 : Utiliser WHERE au lieu de HAVING

```php
// ❌ ERREUR - On ne peut pas utiliser WHERE sur un résultat agrégé
$builder->select('ville, COUNT(*) as total')
        ->groupBy('ville')
        ->where('total >', 10) // ERREUR !
        ->get();

// ✅ SOLUTION - Utiliser HAVING
$builder->select('ville, COUNT(*) as total')
        ->groupBy('ville')
        ->having('total >', 10)
        ->get();
```

---

## 💼 Cas d'Usage Avancés dans NovaMoney

### Cas 1 : Calculer le solde théorique d'un compte

```php
public function calculerSoldeTheorique(int $idCompte): float
{
    $builder = $this->builder();
    
    // Somme de tous les crédits (dépôts + transferts reçus)
    $credits = $builder->selectSum('somme', 'total')
                       ->where('idReceiver', $idCompte)
                       ->get()
                       ->getRowArray()['total'] ?? 0;
    
    $builder = $this->builder();
    
    // Somme de tous les débits (retraits + transferts envoyés + frais)
    $debits = $builder->select('SUM(somme + montantFrais + montantCommission) as total')
                      ->where('idSender', $idCompte)
                      ->get()
                      ->getRowArray()['total'] ?? 0;
    
    return $credits - $debits;
}
```


### Cas 2 : Rapport des commissions par opérateur destinataire

```php
public function getRapportCommissions($dateMin = null, $dateMax = null): array
{
    $builder = $this->db->table('Mouvement');
    
    $builder->select('
            receiver_op.nom as operateur_destinataire,
            sender_op.nom as operateur_emetteur,
            COUNT(*) as nombre_transferts,
            SUM(Mouvement.montantCommission) as total_commission
        ')
        ->join('compte as receiver', 'receiver.id = Mouvement.idReceiver')
        ->join('Operateur as receiver_op', 'receiver_op.id = receiver.idOperateur')
        ->join('Operateur as sender_op', 'sender_op.id = Mouvement.idOperateur')
        ->where('Mouvement.montantCommission >', 0);
    
    if ($dateMin) {
        $builder->where('Mouvement.dateMouvement >=', $dateMin);
    }
    
    if ($dateMax) {
        $builder->where('Mouvement.dateMouvement <=', $dateMax);
    }
    
    return $builder->groupBy('receiver_op.id, sender_op.id, receiver_op.nom, sender_op.nom')
                   ->orderBy('total_commission', 'DESC')
                   ->get()
                   ->getResultArray();
}
```

### Cas 3 : Détecter les comptes suspects (activité anormale)

```php
public function getComptesSuspects(int $idOperateur): array
{
    $builder = $this->db->table('compte');
    
    // Comptes avec plus de 50 transactions en 24h
    return $builder->select('
            compte.id,
            compte.number,
            COUNT(Mouvement.id) as nb_transactions,
            SUM(Mouvement.somme) as volume_total
        ')
        ->join('Mouvement', 'Mouvement.idSender = compte.id OR Mouvement.idReceiver = compte.id')
        ->where('compte.idOperateur', $idOperateur)
        ->where('Mouvement.dateMouvement >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
        ->groupBy('compte.id, compte.number')
        ->having('nb_transactions >', 50)
        ->orHaving('volume_total >', 1000000) // Plus de 1M Ar
        ->orderBy('nb_transactions', 'DESC')
        ->get()
        ->getResultArray();
}
```


### Cas 4 : Statistiques horaires des transactions

```php
public function getStatistiquesHoraires(int $idOperateur, string $date): array
{
    $builder = $this->builder();
    
    return $builder->select("
            HOUR(dateMouvement) as heure,
            COUNT(*) as nombre_transactions,
            SUM(somme) as montant_total,
            AVG(somme) as montant_moyen
        ")
        ->where('idOperateur', $idOperateur)
        ->where('DATE(dateMouvement)', $date)
        ->groupBy('HOUR(dateMouvement)')
        ->orderBy('heure', 'ASC')
        ->get()
        ->getResultArray();
}
```

**Résultat exemple :**
```
heure | nombre_transactions | montant_total | montant_moyen
------|---------------------|---------------|---------------
8     | 45                  | 125000        | 2777.78
9     | 78                  | 234000        | 3000.00
10    | 92                  | 456000        | 4956.52
...
```

---

## 📖 Ressources Supplémentaires

### Documentation officielle CodeIgniter 4
- [Query Builder](https://codeigniter.com/user_guide/database/query_builder.html)
- [Database Reference](https://codeigniter.com/user_guide/database/index.html)
- [Models](https://codeigniter.com/user_guide/models/model.html)

### Commandes utiles

```bash
# Créer une migration
php spark make:migration CreateTableName

# Exécuter les migrations
php spark migrate

# Rollback la dernière migration
php spark migrate:rollback

# Créer un modèle
php spark make:model ModelName

# Ouvrir la console DB
php spark db:table table_name
```

---

## 🎯 Résumé

Le Query Builder de CodeIgniter 4 offre :

1. **Sécurité** : Protection automatique contre les injections SQL
2. **Lisibilité** : Code plus clair qu'avec du SQL brut
3. **Flexibilité** : Chaînage de méthodes pour construire des requêtes complexes
4. **Portabilité** : Compatible avec MySQL, PostgreSQL, SQLite, etc.
5. **Performance** : Requêtes optimisées et mise en cache possible

**Points clés à retenir :**
- Toujours utiliser `where()` plutôt que concaténer des chaînes SQL
- Utiliser des transactions pour les opérations critiques
- Préfixer les colonnes dans les jointures pour éviter les ambiguïtés
- Utiliser `countAllResults(false)` pour préserver le builder
- Logger et profiler les requêtes en développement


---

## 🔍 Index des Requêtes NovaMoney

| Fonction | Fichier | Description |
|----------|---------|-------------|
| `calculGain()` | Mouvement.php | Somme des frais par type et opérateur |
| `getHistoriqueCompte()` | Mouvement.php | Historique avec jointures compte+type |
| `getMouvementDetails()` | Mouvement.php | Détails mouvements avec type |
| `findFeeForAmount()` | Tranche.php | Trouve les frais pour un montant |
| `getTranchesByBareme()` | Tranche.php | Liste des tranches d'un barème |
| `getComptesByOperateur()` | Compte.php | Liste comptes avec statut |
| `getPrefixesByOperateur()` | PrefixOperateur.php | Liste préfixes d'un opérateur |
| `ajusterSolde()` | Compte.php | Mise à jour du solde |

---

**Document créé le :** <?= date('d/m/Y') ?>  
**Version :** 1.0  
**Pour :** NovaMoney - Mobile Money Simulation

**Auteur :** Documentation technique  
**Dernière mise à jour :** Query Builder complet avec exemples pratiques

