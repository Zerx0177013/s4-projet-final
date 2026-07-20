<?php

namespace App\Controllers;
use App\Models\Compte;

class CompteController extends BaseController
{
    public function dashboard(): string
    {
        return $this->afficherComptes();
    }

    public function afficherComptes(): string
    {
        $compteModel = new Compte();

        $data = [
            'comptes' => $compteModel->getComptesAvecTransactions(),
        ];

        return view('operator/operator', $data);
    }
}
