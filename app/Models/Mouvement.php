<?php

namespace App\Models;

use CodeIgniter\Model;

class Mouvement extends Model
{
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
