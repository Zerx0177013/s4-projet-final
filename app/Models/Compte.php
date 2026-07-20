<?php

namespace App\Models;

use CodeIgniter\Model;

class Compte extends Model
{
    protected $table            = 'compte';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['number', 'idStatus', 'idOperateur', 'solde'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    public function getComptesAvecTransactions(?int $idOperateur = null): array
    {
        $builder = $this->select('id, number, solde, idOperateur')
                        ->where('idStatus', 1)
                        ->orderBy('number', 'ASC');

        if ($idOperateur !== null) {
            $builder->where('idOperateur', $idOperateur);
        }

        $comptes = $builder->findAll();

        foreach ($comptes as &$compte) {
            $transactionsCount = $this->db->table('Mouvement')
                ->groupStart()
                    ->where('idSender', $compte['id'])
                    ->orWhere('idReceiver', $compte['id'])
                ->groupEnd()
                ->countAllResults();

            $compte['name'] = 'Compte #' . $compte['id'];
            $compte['phone'] = $compte['number'];
            $compte['balance'] = (float) $compte['solde'];
            $compte['transactionsCount'] = $transactionsCount;
        }

        return $comptes;
    }

    
    
    


}
