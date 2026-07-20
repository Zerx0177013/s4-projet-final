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
}
