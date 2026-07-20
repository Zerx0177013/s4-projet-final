<?php

namespace App\Controllers;

use App\Models\Mouvement;

class DashboardController extends BaseController
{
    public function afficherGainParOperateur($dateMin = null, $dateMax = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $idOperateur = session()->get('idOperateur');

        if ($idOperateur === null) {
            return redirect()->to('/operator');
        }

        $mouvementModel = new Mouvement();
        $answer = $mouvementModel->calculGainParOperateur($idOperateur, $dateMin, $dateMax);
        // $answer = ['Depot' => 0, 'Retrait' => 50, 'Transfert' => 200]
        $answer['total'] = array_sum($answer);
        $answer['liste'] = $mouvementModel->getMouvementDetails($idOperateur, $dateMin, $dateMax);

        return view('operator/operator', $answer);
    }
}
