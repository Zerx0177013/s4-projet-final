<?php

namespace App\Models;

use CodeIgniter\Model;

class PrefixOperateur extends Model
{
    protected $table            = 'prefixOperateur';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['idOperateur', 'prefix'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    public function getPrefixesByOperateur(int $idOperateur): array
    {
        return $this->where('idOperateur', $idOperateur)
            ->orderBy('prefix', 'ASC')
            ->findAll();
    }
    /**
     * Retourne l'id de l'opérateur correspondant à un préfixe, ou null si
     * ce préfixe n'est rattaché à aucun opérateur.
     */
    public function findOperateurIdByPrefix(string $prefix): ?int
    {
        $row = $this->where('prefix', $prefix)->first();

        return $row['idOperateur'] ?? null;
    }

}
