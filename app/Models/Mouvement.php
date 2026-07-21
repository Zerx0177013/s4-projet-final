<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;
use RuntimeException;

class Mouvement extends Model
{
    private const TYPES = ['depot', 'retrait', 'transfert'];
    protected $table            = 'Mouvement';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['montantCommission', 'idOperateur', 'somme', 'montantFrais', 'idTypeOperation', 'idSender', 'idReceiver', 'dateMouvement'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    public function calculGain(int $idTypeOperation, int $idOperateur, $dateMin = null, $dateMax = null)
    {
        $mouvementModel = new Mouvement();

        $builder = $mouvementModel->builder();

        $builder->selectSum('montantFrais', 'frais')
            ->where('idTypeOperation', $idTypeOperation)
            ->where('idOperateur', $idOperateur);

        if ($dateMin !== null) {
            $builder->where('dateMouvement >=', $dateMin);
        }

        if ($dateMax !== null) {
            $builder->where('dateMouvement <=', $dateMax);
        }

        return $builder->get()->getRowArray()['frais'];
    }


    public function calculGainParOperateur(int $idOperateur, $dateMin = null, $dateMax = null): array
    {
        $typeOperationModel = new TypeOperation();
        $typeOperations = $typeOperationModel->findAll();

        $answer = [];

        foreach ($typeOperations as $typeOperation) {
            $answer[$typeOperation['libelle']] = $this->calculGainParTypeOperation(
                $idOperateur,
                $typeOperation['id'],
                $dateMin,
                $dateMax
            );
        }

        return $answer;
    }

    public function calculGainParTypeOperation(int $idOperateur, int $idTypeOperation, $dateMin = null, $dateMax = null)
    {
        $mouvementModel = new Mouvement();

        $builder = $mouvementModel->builder();

        $builder->selectSum('montantFrais', 'frais')
            ->where('idOperateur', $idOperateur)
            ->where('idTypeOperation', $idTypeOperation);

        if ($dateMin !== null) {
            $builder->where('dateMouvement >=', $dateMin);
        }

        if ($dateMax !== null) {
            $builder->where('dateMouvement <=', $dateMax);
        }

        $result = $builder->get()->getRowArray();

        return $result['frais'] ?? 0;
    }

    public function getMouvementDetails(int $idOperateur, $dateMin = null, $dateMax = null): array
    {
        $builder = $this->builder();

        $builder->select('Mouvement.id, Mouvement.idSender, Mouvement.idReceiver, Mouvement.somme, Mouvement.montantFrais, Mouvement.dateMouvement, Mouvement.idTypeOperation, TypeOperation.libelle as typeLibelle')
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

    /**
     * Exécute un dépôt, un retrait ou un transfert pour un compte client :
     * calcule les frais depuis le barème lié au type d'opération, applique la
     * commission inter-opérateur si nécessaire, vérifie le solde, met à jour
     * les comptes concernés et enregistre le mouvement dans une transaction.
     *
     * Si $includeFee est true pour un transfert, le montant saisi inclut les
     * frais de transfert; sinon, les frais s'ajoutent au montant envoyé.
     *
     * @throws RuntimeException si l'opération ne peut pas être réalisée.
     */
    public function enregistrerOperation(array $compte, string $type, float $amount, ?string $targetNumber = null, bool $includeFee = false): array
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new RuntimeException('Type d\'opération invalide.');
        }

        if ($amount < 100) {
            throw new RuntimeException('Montant minimum : 100 Ar.');
        }

        $typeOperationModel = new TypeOperation();
        $typeOperation      = $typeOperationModel->findByLibelle(ucfirst($type));

        if ($typeOperation === null) {
            throw new RuntimeException("Type d'opération non configuré.");
        }

        $trancheModel = new Tranche();
        $fee = $typeOperation['idBareme'] !== null
            ? $trancheModel->findFeeForAmount((int) $typeOperation['idBareme'], $amount)
            : 0.0;

        $compteModel = new Compte();
        $operateurModel = new Operateur();
        $target = null;
        $commissionFee = 0.0;
        $amountReceived = $amount;
        $senderOperatorId = (int) $compte['idOperateur'];

        if ($type === 'transfert') {
            if ($targetNumber === null || $targetNumber === '') {
                throw new RuntimeException('Compte destinataire introuvable.');
            }

            if ($targetNumber === $compte['number']) {
                throw new RuntimeException("Impossible de vous envoyer de l'argent à vous-même.");
            }

            $target = $compteModel->findByNumber($targetNumber);

            // Si le numéro est valide mais n'a pas encore été créé en base,
            // on crée le compte comme lors de la connexion client.
            if ($target === null) {
                $target = $compteModel->loginOuCreer($targetNumber);
            }

            if ((int) $target['idStatus'] !== 1) {
                throw new RuntimeException('Le compte destinataire est bloqué.');
            }

            if ($includeFee) {
                if ($fee >= $amount) {
                    throw new RuntimeException('Montant insuffisant pour couvrir les frais.');
                }

                // Ajouter les frais de retrait du destinataire
                $typeOperationRetrait = $typeOperationModel->findByLibelle('Retrait');
                $retraitFee = 0.0;
                if ($typeOperationRetrait !== null && $typeOperationRetrait['idBareme'] !== null) {
                    $retraitFee = $trancheModel->findFeeForAmount((int) $typeOperationRetrait['idBareme'], $amount);
                }

                // Le destinataire reçoit le montant + frais de retrait pour pouvoir retirer sans frais
                $amountReceived = $amount + $retraitFee;
                // On ajoute les frais de retrait au coût total pour l'émetteur
                $fee += $retraitFee;
            }

            $targetOperatorId = (int) $target['idOperateur'];
            if ($senderOperatorId !== $targetOperatorId) {
                $pourcentage = (float) $operateurModel->getPourcentageCommission($targetOperatorId);
                $commissionFee = $amount * $pourcentage / 100;
            }
        }

        $totalCost = match ($type) {
            'depot' => 0.0,
            'retrait' => $amount + $fee,
            'transfert' => $amount + $fee + $commissionFee,
        };

        if ($type !== 'depot' && (float) $compte['solde'] < $totalCost) {
            throw new RuntimeException('Solde insuffisant.');
        }

        $db = Database::connect();
        $db->transStart();

        try {
            $idSender = $type === 'depot' ? null : $compte['id'];
            $idReceiver = $type === 'retrait' ? null : ($type === 'transfert' ? $target['id'] : $compte['id']);

            $this->insert([
                'somme'              => $amount,
                'montantFrais'       => $fee,
                'idTypeOperation'    => $typeOperation['id'],
                'idSender'           => $idSender,
                'idReceiver'         => $idReceiver,
                'idOperateur'        => $senderOperatorId,
                'montantCommission'  => $commissionFee,
            ]);

            $newBalance = match ($type) {
                'depot' => $compteModel->ajusterSolde($compte['id'], $amount),
                'retrait' => $compteModel->ajusterSolde($compte['id'], -($amount + $fee)),
                'transfert' => $compteModel->ajusterSolde($compte['id'], -$totalCost),
            };

            if ($type === 'transfert') {
                $compteModel->ajusterSolde($target['id'], $amountReceived);

                if ($commissionFee > 0) {
                    $operateurModel->AddToMontantCommission((int) $target['idOperateur'], $commissionFee);
                }
            }

            $db->transComplete();
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }

        if ($db->transStatus() === false) {
            throw new RuntimeException('Une erreur est survenue, veuillez réessayer.');
        }

        return [
            'balance' => $newBalance,
            'transaction' => [
                'type' => $type,
                'amount' => $amount,
                'fee' => $fee,
                'commission' => $commissionFee,
                'date' => date('d/m/Y H:i'),
                'to' => $type === 'transfert' ? $target['number'] : null,
                'balance_after' => $newBalance,
                'amountReceived' => $type === 'transfert' ? $amountReceived : null,
            ],
        ];
    }

    /**
     * Effectue plusieurs transferts en divisant le montant total entre les destinataires.
     * Réutilise enregistrerOperation() pour chaque transfert afin de bénéficier de toute
     * la logique existante (commission inter-opérateur, validation, frais, etc.)
     * 
     * @param array $compte Le compte émetteur
     * @param float $totalAmount Le montant total à répartir
     * @param array $targetNumbers Les numéros des destinataires
     * @throws RuntimeException si l'opération ne peut pas être réalisée.
     */
    public function enregistrerMultipleTransferts(array $compte, float $totalAmount, array $targetNumbers): array
    {
        if ($totalAmount < 100) {
            throw new RuntimeException('Montant minimum : 100 Ar.');
        }

        if (empty($targetNumbers)) {
            throw new RuntimeException('Aucun destinataire fourni.');
        }

        // Vérifier les doublons
        if (count($targetNumbers) !== count(array_unique($targetNumbers))) {
            throw new RuntimeException('Vous avez entré le même numéro plusieurs fois.');
        }

        // Vérifier qu'on ne s'envoie pas à soi-même
        foreach ($targetNumbers as $targetNumber) {
            if ($targetNumber === $compte['number']) {
                throw new RuntimeException("Impossible de vous envoyer de l'argent à vous-même.");
            }
        }

        $perPerson = floor($totalAmount / count($targetNumbers));

        if ($perPerson < 100) {
            throw new RuntimeException('Montant par destinataire trop faible (minimum 100 Ar).');
        }

        // Calculer le coût total estimé (on utilisera le premier destinataire pour estimer)
        // Note: Le coût réel peut varier si certains destinataires sont d'opérateurs différents
        $typeOperationModel = new TypeOperation();
        $typeOperation = $typeOperationModel->findByLibelle('Transfert');
        
        if ($typeOperation === null) {
            throw new RuntimeException("Type d'opération non configuré.");
        }

        $trancheModel = new Tranche();
        $estimatedFee = $typeOperation['idBareme'] !== null
            ? $trancheModel->findFeeForAmount((int) $typeOperation['idBareme'], $perPerson)
            : 0.0;

        // Estimation du coût total (sans commission pour l'instant)
        $estimatedTotalCost = ($perPerson + $estimatedFee) * count($targetNumbers);

        if ((float) $compte['solde'] < $estimatedTotalCost) {
            throw new RuntimeException('Solde insuffisant.');
        }

        // Exécuter les transferts un par un en utilisant enregistrerOperation()
        $transactions = [];
        $totalDebited = 0;
        $compteModel = new Compte();

        foreach ($targetNumbers as $targetNumber) {
            // Recharger le compte pour avoir le solde à jour
            $compteActuel = $compteModel->find($compte['id']);
            
            if ($compteActuel === null) {
                throw new RuntimeException('Erreur lors du rechargement du compte émetteur.');
            }

            try {
                // Appeler enregistrerOperation pour bénéficier de toute la logique
                $result = $this->enregistrerOperation($compteActuel, 'transfert', $perPerson, $targetNumber, false);
                
                // Récupérer le nouveau solde et la transaction
                $compte['solde'] = $result['balance'];
                $transactions[] = $result['transaction'];
                
                // Calculer le montant réellement débité pour ce transfert
                $previousBalance = $compteActuel['solde'];
                $currentBalance = $result['balance'];
                $totalDebited += ($previousBalance - $currentBalance);
                
            } catch (RuntimeException $e) {
                // Si un transfert échoue, on propage l'erreur
                // Les transferts précédents ont déjà été commités par enregistrerOperation()
                throw new RuntimeException("Erreur lors du transfert vers {$targetNumber}: " . $e->getMessage());
            }
        }

        // Retourner le solde final et toutes les transactions
        return [
            'balance'      => $compte['solde'],
            'transactions' => $transactions,
        ];
    }

    /**
     * Historique complet des mouvements d'un compte (émetteur ou destinataire),
     * du plus récent au plus ancien, avec le libellé du type d'opération, le
     * numéro de la contrepartie éventuelle, et le solde du compte immédiatement
     * après chaque mouvement (reconstitué à partir du solde actuel).
     */
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

        $running = $soldeActuel;
        $history = [];

        foreach ($rows as $row) {
            $isSender   = (int) $row['idSender'] === $idCompte;
            $type       = strtolower($row['typeLibelle']);
            $amount     = (float) $row['somme'];
            $fee        = (float) $row['montantFrais'];

            $entry = [
                'type'          => $type,
                'amount'        => $amount,
                'fee'           => $fee,
                'date'          => $row['dateMouvement'],
                'to'            => $type === 'transfert' && $isSender ? $row['receiverNumber'] : null,
                'from'          => $type === 'transfert' && ! $isSender ? $row['senderNumber'] : null,
                'balance_after' => $running,
            ];
            $history[] = $entry;

            // Reconstitue le solde juste avant ce mouvement pour l'entrée suivante.
            if ($type === 'depot') {
                $running -= $amount;
            } elseif ($type === 'retrait') {
                $running += $amount + $fee;
            } elseif ($type === 'transfert') {
                $running += $isSender ? ($amount + $fee) : -$amount;
            }
        }

        return $history;
    }
}
