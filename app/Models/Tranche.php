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

}
