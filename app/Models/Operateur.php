<?php

namespace App\Models;

use CodeIgniter\Model;

class Operateur extends Model
{
    protected $table            = 'Operateur';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['nom', 'pourcentageCommission', 'montantCommission'];
    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    public function getPourcentageCommission(int $id)
    {
        $pourcentage = $this->find($id);
        return $pourcentage['pourcentageCommission'] ?? 0;
    }

    public function getMontantCommission(int $id)
    {
        $montant = $this->find($id);
        return $montant['montantCommission'] ?? 0;
    }

    public function AddToMontantCommission(int $id, float $montant)
    {
        $currentMontant = $this->getMontantCommission($id);
        $newMontant = $currentMontant + $montant;
        return $this->update($id, ['montantCommission' => $newMontant]);
    }
}
