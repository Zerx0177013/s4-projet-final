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

}
