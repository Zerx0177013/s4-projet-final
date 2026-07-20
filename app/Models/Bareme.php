<?php

namespace App\Models;

use CodeIgniter\Model;

class Bareme extends Model
{
    protected $table            = 'Bareme';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['libelle', 'date'];
}
