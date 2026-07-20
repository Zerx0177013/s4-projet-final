<?php

namespace App\Controllers;

use App\Models\Mouvement;
use App\Models\TypeOperation;

class DashboardController extends BaseController
{
    public function dashboard(): string
    {
        return view('dashboard');
    }

    // Controller
    public function afficherGainParOperateur(int $idOperateur, $dateMin = null, $dateMax = null)
    {
        $mouvementModel = new Mouvement();
        $answer = $mouvementModel->calculGainParOperateur($idOperateur, $dateMin, $dateMax);
        // $answer = ['Depot' => 0, 'Retrait' => 50, 'Transfert' => 200]
        $answer['total'] = array_sum($answer);
        $answer['liste'] = $mouvementModel->getMouvementDetails($idOperateur, $dateMin, $dateMax);

        return view('operator/operator', $answer);
    }
}
