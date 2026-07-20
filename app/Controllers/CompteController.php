<?php

namespace App\Controllers;

use App\Models\Compte;
use App\Models\Mouvement;
use App\Models\PrefixOperateur;

class CompteController extends BaseController
{
    public function afficherComptes(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $idOperateur = session()->get('idOperateur');

        if ($idOperateur === null) {
            return redirect()->to('/operator');
        }

        $compteModel = new Compte();
        $mouvementModel = new Mouvement();
        $prefixModel = new PrefixOperateur();

        $gains = $mouvementModel->calculGainParOperateur($idOperateur);
        $gains['total'] = array_sum($gains);
        $gains['liste'] = $mouvementModel->getMouvementDetails($idOperateur);

        $data = array_merge($gains, [
            'comptes'  => $compteModel->getComptesAvecTransactions($idOperateur),
            'prefixes' => $prefixModel->getPrefixesByOperateur($idOperateur),
        ]);

        return view('operator/operator', $data);
    }
}
