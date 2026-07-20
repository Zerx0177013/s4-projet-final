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

# Frontend

- [x] creation de template avec figma make
- [x] creation des 4 pages principales 
  - [x] index.html  
  - [x] client-dashboard 
  - [x] client-login 
  - [x] operator.html
- [x] creation des 4 pages pour les css
  - [x] index.css  
  - [x] client-dashboard.css 
  - [x] client-login.css 
  - [x] operator.css
- [x] creation des 4 pages js pour les controle cote front : 
  - [x] index.js  
  - [x] client-dashboard.js 
  - [x] client-login.js 
  - [x] operator.js
