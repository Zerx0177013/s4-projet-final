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
    protected $allowedFields    = ['idOperateur', 'somme', 'montantFrais', 'idTypeOperation', 'idSender', 'idReceiver', 'dateMouvement'];

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
     * calcule les frais depuis le barème lié au type d'opération, vérifie le
     * solde, met à jour le(s) compte(s) concerné(s) et enregistre le
     * mouvement, tout cela dans une transaction. Toute la logique métier vit
     * ici (dans le Modèle), le Contrôleur ne fait qu'appeler cette méthode.
     *
     * @throws RuntimeException si l'opération ne peut pas être réalisée.
     */
    public function enregistrerOperation(array $compte, string $type, float $amount, ?string $targetNumber = null): array
    {
        if (! in_array($type, self::TYPES, true)) {
            throw new RuntimeException("Type d'opération invalide.");
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
        $fee          = $typeOperation['idBareme'] !== null
            ? $trancheModel->findFeeForAmount((int) $typeOperation['idBareme'], $amount)
            : 0.0;

        $compteModel = new Compte();
        $target      = null;

        if ($type === 'transfert') {
            if ($targetNumber === $compte['number']) {
                throw new RuntimeException("Impossible de vous envoyer de l'argent à vous-même.");
            }

            $target = $targetNumber !== null ? $compteModel->findByNumber($targetNumber) : null;

            if ($target === null) {
                throw new RuntimeException('Compte destinataire introuvable.');
            }

            if ((int) $target['idStatus'] !== 1) {
                throw new RuntimeException('Le compte destinataire est bloqué.');
            }
        }

        if ($type !== 'depot' && (float) $compte['solde'] < $amount + $fee) {
            throw new RuntimeException('Solde insuffisant.');
        }

        $db = Database::connect();
        $db->transStart();

        $idSender   = $type === 'depot' ? null : $compte['id'];
        $idReceiver = $type === 'retrait' ? null : ($type === 'transfert' ? $target['id'] : $compte['id']);

        $this->insert([
            'somme'           => $amount,
            'montantFrais'    => $fee,
            'idTypeOperation' => $typeOperation['id'],
            'idSender'        => $idSender,
            'idReceiver'      => $idReceiver,
            'idOperateur'     => $compte['idOperateur'],
        ]);

        $newBalance = match ($type) {
            'depot'     => $compteModel->ajusterSolde($compte['id'], $amount),
            'retrait'   => $compteModel->ajusterSolde($compte['id'], -($amount + $fee)),
            'transfert' => $compteModel->ajusterSolde($compte['id'], -($amount + $fee)),
        };

        if ($type === 'transfert') {
            $compteModel->ajusterSolde($target['id'], $amount);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            throw new RuntimeException('Une erreur est survenue, veuillez réessayer.');
        }

        return [
            'balance'     => $newBalance,
            'transaction' => [
                'type'          => $type,
                'amount'        => $amount,
                'fee'           => $fee,
                'date'          => date('d/m/Y H:i'),
                'to'            => $type === 'transfert' ? $target['number'] : null,
                'balance_after' => $newBalance,
            ],
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
