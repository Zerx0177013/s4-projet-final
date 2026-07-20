<?php

namespace App\Controllers;

use App\Models\Compte;
use App\Models\Mouvement;

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

        // Gains de l'opérateur, pour que l'onglet "Gains" du tableau de bord soit
        // aussi rempli lorsqu'on arrive sur cette page (même vue operator/operator).
        $gains = $mouvementModel->calculGainParOperateur($idOperateur);
        $gains['total'] = array_sum($gains);
        $gains['liste'] = $mouvementModel->getMouvementDetails($idOperateur);

        $data = array_merge($gains, [
            'comptes' => $compteModel->getComptesAvecTransactions($idOperateur),
        ]);

        return view('operator/operator', $data);
    }
}
