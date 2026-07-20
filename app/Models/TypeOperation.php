<?php

namespace App\Models;

use CodeIgniter\Model;

class TypeOperation extends Model
{
    protected $table            = 'TypeOperation';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['libelle', 'idBareme'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

}
