<?php

namespace App\Models;

use CodeIgniter\Model;

class Tranche extends Model
{
    protected $table            = 'tranche';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['min', 'max', 'montant', 'idBareme'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    /**
     * Retourne les tranches d'un barème, triées par montant minimum croissant.
     */
    public function getSlabsByBareme(int $idBareme): array
    {
        return $this->where('idBareme', $idBareme)->orderBy('min', 'ASC')->findAll();
    }

    /**
     * Calcule le montant des frais applicable pour un montant donné, selon
     * les tranches d'un barème. Si le montant dépasse la tranche la plus
     * haute, les frais de cette dernière s'appliquent.
     */
    public function findFeeForAmount(int $idBareme, float $amount): float
    {
        $slabs = $this->getSlabsByBareme($idBareme);

        if ($slabs === []) {
            return 0.0;
        }

        foreach ($slabs as $slab) {
            if ($amount >= $slab['min'] && $amount <= $slab['max']) {
                return (float) $slab['montant'];
            }
        }

        $last = $slabs[array_key_last($slabs)];

        return $amount > $last['max'] ? (float) $last['montant'] : 0.0;
    }

}
