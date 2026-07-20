# Informations
- Rohan ETU003918
- Noah ETU004351

# General
- [x] creation du repository public
- [ ] creation de l'environnement
- [ ] installation de la base SQLite

# Base
- [x] conception de la base
- [ ] creation de la base
- [ ] creation des tables

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
- [ ] creation de template avec figma make
