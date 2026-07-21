PRAGMA foreign_keys = ON;

CREATE TABLE Operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE,
    pourcentageCommission REAL NOT NULL DEFAULT 0 CHECK (pourcentageCommission >= 0), 
    montantCommission REAL NOT NULL DEFAULT 0 CHECK (montantCommission >= 0),
    pourcentagePromoFrais REAL NOT  NULL DEFAULT 0
);

CREATE TABLE prefixOperateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    idOperateur INTEGER NOT NULL,
    prefix TEXT NOT NULL UNIQUE,
    FOREIGN KEY (idOperateur) REFERENCES Operateur (id) ON DELETE CASCADE
);

CREATE TABLE Bareme (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle TEXT NOT NULL,
    date DATETIME
);

CREATE TABLE tranche (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    min REAL NOT NULL,
    max REAL NOT NULL,
    montant REAL NOT NULL,
    idBareme INTEGER NOT NULL,
    FOREIGN KEY (idBareme) REFERENCES Bareme (id) ON DELETE CASCADE,
    CHECK (max > min),
    CHECK (montant >= 0)
);

CREATE TABLE TypeOperation (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle TEXT NOT NULL UNIQUE,
    idBareme INTEGER,
    FOREIGN KEY (idBareme) REFERENCES Bareme (id) ON DELETE SET NULL
);

CREATE TABLE compte (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    number TEXT NOT NULL UNIQUE,
    idStatus INTEGER NOT NULL DEFAULT 1,
    idOperateur INTEGER NOT NULL,
    solde REAL NOT NULL DEFAULT 0 CHECK (solde >= 0),
    PourcentageCaisse REAL NOT NULL DEFAULT 0 CHECK (PourcentageCaisse >= 0),
    caisse REAL NOT NULL DEFAULT 0 CHECK (caisse >= 0),
    FOREIGN KEY (idOperateur) REFERENCES Operateur (id),
    FOREIGN KEY (idStatus) REFERENCES statusType (id)
);

CREATE TABLE statusType (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle TEXT NOT NULL UNIQUE
);

CREATE TABLE Mouvement (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    somme REAL NOT NULL CHECK (somme > 0),
    montantFrais REAL NOT NULL DEFAULT 0 CHECK (montantFrais >= 0),
    idTypeOperation INTEGER NOT NULL,
    idSender INTEGER,
    idReceiver INTEGER,
    dateMouvement DATETIME NOT NULL DEFAULT(datetime('now')),
    idOperateur INTEGER NOT NULL,
    montantCommission REAL DEFAULT NULL,
    FOREIGN KEY (idTypeOperation) REFERENCES TypeOperation (id),
    FOREIGN KEY (idSender) REFERENCES compte (id),
    FOREIGN KEY (idReceiver) REFERENCES compte (id),
    FOREIGN KEY (idOperateur) REFERENCES Operateur (id),
    CHECK (
        idSender IS NOT NULL
        OR idReceiver IS NOT NULL
    )
);-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- 1. Opérateurs
INSERT INTO Operateur (nom,pourcentageCommission,pourcentagePromoFrais) VALUES
    ('Telma Mvola', 10, 20),
    ('Orange Money',10, 20),
    ('Airtel Money',10, 20);

-- 2. Préfixes valables par opérateur
INSERT INTO prefixOperateur (idOperateur, prefix) VALUES
    (1, '032'), (1, '037'),
    (2, '033'), (2, '039'),
    (3, '038');

-- 3. Barème unique, réutilisé pour Retrait ET Transfert (même grille tarifaire)
INSERT INTO Bareme (libelle, date) VALUES
    ('Barème Standard Retrait/Transfert', datetime('now'));

-- 4. Tranches du barème (idBareme = 1), d'après le tableau fourni
INSERT INTO tranche (min, max, montant, idBareme) VALUES
    (100,      1000,     50,   1),
    (1001,     5000,     50,   1),
    (5001,     10000,    100,  1),
    (10001,    25000,    200,  1),
    (25001,    50000,    400,  1),
    (50001,    100000,   800,  1),
    (100001,   250000,   1500, 1),
    (250001,   500000,   1500, 1),
    (500001,   1000000,  2500, 1),
    (1000001,  2000000,  3000, 1);

-- 5. Types d'opération : Dépôt gratuit (pas de barème), Retrait et Transfert
--    partagent le MÊME idBareme = 1
INSERT INTO TypeOperation (libelle, idBareme) VALUES
    ('Depot', NULL),
    ('Retrait', 1),
    ('Transfert', 1);

-- 6. Statuts de compte (avant compte, à cause de la FK)
INSERT INTO statusType (id, libelle) VALUES
    (1, 'actif'),
    (2, 'bloque');

-- 7. Comptes clients
-- id=1,2 -> Telma (idOperateur=1) | id=3,4 -> Orange (idOperateur=2) | id=5 -> Airtel (idOperateur=3)
INSERT INTO compte (number, idStatus, idOperateur, solde , PourcentageCaisse , caisse) VALUES
    ('0321234567', 1, 1, 50000 , 20 , 0),   -- id 1, Telma
    ('0371234567', 1, 1, 12000 , 20, 0),   -- id 2, Telma
    ('0331234567', 1, 2, 80000, 20, 0),   -- id 3, Orange
    ('0391234567', 2, 2, 0, 20, 0),       -- id 4, Orange (bloqué)
    ('0381234567', 1, 3, 25000, 20, 0);   -- id 5, Airtel

-- 8. Mouvements (historique) : dépôt, retrait, transfert
-- idOperateur = l'opérateur qui gère/prélève l'opération (celui du compte concerné)
CREATE INDEX idx_mouvement_sender ON Mouvement (idSender);

CREATE INDEX idx_mouvement_receiver ON Mouvement (idReceiver);

CREATE INDEX idx_mouvement_date ON Mouvement (dateMouvement);

CREATE INDEX idx_compte_operateur ON compte (idOperateur);