Table:
- Operateur:
  - id
  - nom

- prefixOperateur:
  - id
  - idOperateur
  - prefix String

- TypeOperation:
  - id
  - libelle
  - idBareme

- bareme:
  - id
  - libelle

- tranche:
  - id
  - min
  - max
  - montant
  - idBareme

- Mouvement:
  - id
  - somme
  - idTypeOperation
  - idSender
  - idReceiver
  - dateMouvement
  - montantFrais

- compte:
  - id
  - number String
  - idStatus
  - idOperateur
  - Solde

- statusType:
  - id
  - libelle