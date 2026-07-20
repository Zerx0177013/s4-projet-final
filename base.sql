PRAGMA foreign_keys = ON;

CREATE TABLE Operateur (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nom TEXT NOT NULL UNIQUE
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
    FOREIGN KEY (idTypeOperation) REFERENCES TypeOperation (id),
    FOREIGN KEY (idSender) REFERENCES compte (id),
    FOREIGN KEY (idReceiver) REFERENCES compte (id),
    CHECK (
        idSender IS NOT NULL
        OR idReceiver IS NOT NULL
    )
);
-- ============================================================
-- DONNÉES DE TEST
-- ============================================================

-- 1. Opérateurs
INSERT INTO
    Operateur (nom)
VALUES ('Telma Mvola'),
    ('Orange Money'),
    ('Airtel Money');

-- 2. Préfixes valables par opérateur
INSERT INTO
    prefixOperateur (idOperateur, prefix)
VALUES (1, '032'),
    (1, '037'),
    (2, '033'),
    (2, '039'),
    (3, '038');

-- 3. Barème unique, réutilisé pour Retrait ET Transfert (même grille tarifaire)
INSERT INTO
    Bareme (libelle, date)
VALUES (
        'Barème Standard Retrait/Transfert',
        datetime('now')
    );

-- 4. Tranches du barème (idBareme = 1), d'après le tableau fourni
INSERT INTO
    tranche (min, max, montant, idBareme)
VALUES (100, 1000, 50, 1),
    (1001, 5000, 50, 1),
    (5001, 10000, 100, 1),
    (10001, 25000, 200, 1),
    (25001, 50000, 400, 1),
    (50001, 100000, 800, 1),
    (100001, 250000, 1500, 1),
    (250001, 500000, 1500, 1),
    (500001, 1000000, 2500, 1),
    (1000001, 2000000, 3000, 1);

-- 5. Types d'opération : Dépôt gratuit (pas de barème), Retrait et Transfert
--    partagent le MÊME idBareme = 1
INSERT INTO
    TypeOperation (libelle, idBareme)
VALUES ('Depot', NULL),
    ('Retrait', 1),
    ('Transfert', 1);

INSERT INTO
    statusType (id, libelle)
VALUES (1, 'actif'),
    (2, 'bloque');

INSERT INTO
    compte (
        number,
        idStatus,
        idOperateur,
        solde
    )
VALUES ('0321234567', 1, 1, 50000),
    ('0371234567', 1, 1, 12000),
    ('0331234567', 1, 2, 80000),
    ('0391234567', 2, 2, 0),
    ('0381234567', 1, 3, 25000);

INSERT INTO
    Mouvement (
        somme,
        montantFrais,
        idTypeOperation,
        idSender,
        idReceiver,
        dateMouvement
    )
VALUES (
        20000,
        0,
        1,
        NULL,
        1,
        '2026-07-01 09:00:00'
    ), -- dépôt de 20000 sur compte 1
    (
        5000,
        50,
        2,
        1,
        NULL,
        '2026-07-02 10:15:00'
    ), -- retrait de 5000 (tranche 1001-5000 -> 50)
    (
        10000,
        200,
        3,
        3,
        5,
        '2026-07-03 14:30:00'
    );
-- transfert de 10000 (tranche 10001-25000 -> 200)

CREATE INDEX idx_mouvement_sender ON Mouvement (idSender);

CREATE INDEX idx_mouvement_receiver ON Mouvement (idReceiver);

CREATE INDEX idx_mouvement_date ON Mouvement (dateMouvement);

CREATE INDEX idx_compte_operateur ON compte (idOperateur);