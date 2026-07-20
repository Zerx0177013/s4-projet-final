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

}
