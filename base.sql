PRAGMA foreign_keys = ON;

CREATE TABLE Operateur (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    nom     TEXT NOT NULL UNIQUE
);

CREATE TABLE prefixOperateur (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    idOperateur     INTEGER NOT NULL,
    prefix          TEXT NOT NULL UNIQUE,
    FOREIGN KEY (idOperateur) REFERENCES Operateur(id) ON DELETE CASCADE
);

CREATE TABLE Bareme (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle TEXT NOT NULL,
    date    DATETIME NOT NULL
);

CREATE TABLE tranche (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    min         REAL NOT NULL,
    max         REAL NOT NULL,
    montant     REAL NOT NULL,
    idBareme    INTEGER NOT NULL,
    FOREIGN KEY (idBareme) REFERENCES Bareme(id) ON DELETE CASCADE,
    CHECK (max > min),
    CHECK (montant >= 0)
);

CREATE TABLE TypeOperation (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle     TEXT NOT NULL UNIQUE,
    idBareme    INTEGER,
    FOREIGN KEY (idBareme) REFERENCES Bareme(id) ON DELETE SET NULL
);


CREATE TABLE compte (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    number          TEXT NOT NULL UNIQUE,
    idStatus          INTEGER NOT NULL DEFAULT 1,
    idOperateur     INTEGER NOT NULL,
    solde           REAL NOT NULL DEFAULT 0 CHECK (solde >= 0),
    FOREIGN KEY (idOperateur) REFERENCES Operateur(id),
    FOREIGN KEY (idStatus) REFERENCES statusType(id)
);

CREATE TABLE statusType (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    libelle     TEXT NOT NULL UNIQUE
);

CREATE TABLE Mouvement (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    somme           REAL NOT NULL CHECK (somme > 0),
    montantFrais    REAL NOT NULL DEFAULT 0 CHECK (montantFrais >= 0),
    idTypeOperation INTEGER NOT NULL,
    idSender        INTEGER,
    idReceiver      INTEGER,
    dateMouvement   DATETIME NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY (idTypeOperation) REFERENCES TypeOperation(id),
    FOREIGN KEY (idSender) REFERENCES compte(id),
    FOREIGN KEY (idReceiver) REFERENCES compte(id),
    CHECK (idSender IS NOT NULL OR idReceiver IS NOT NULL)
);
CREATE INDEX idx_mouvement_sender   ON Mouvement(idSender);
CREATE INDEX idx_mouvement_receiver ON Mouvement(idReceiver);
CREATE INDEX idx_mouvement_date     ON Mouvement(dateMouvement);
CREATE INDEX idx_compte_operateur   ON compte(idOperateur);
