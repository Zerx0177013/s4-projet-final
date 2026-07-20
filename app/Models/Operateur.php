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
    protected $allowedFields    = ['nom'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

}
