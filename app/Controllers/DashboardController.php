<?php

namespace App\Controllers;

use App\Models\Mouvement;
use App\Models\TypeOperation;

class DashboardController extends BaseController
{

   
  

    public function calculGainParOperateur(int $idOperateur, $dateMin = null, $dateMax = null): array
    {
        $typeOperationModel = new TypeOperation();
        $typeOperations = $typeOperationModel->findAll();

        $answer = [];

        foreach ($typeOperations as $typeOperation) {
            $answer[$typeOperation['libelle']] = $this->calculGainParTypeOperation(
                $idOperateur,
                $typeOperation['id'],
                $dateMin,
                $dateMax
            );
        }

        return $answer;
    }
    public function calculGain(int $idTypeOperation, int $idOperateur, $dateMin = null, $dateMax = null)

    {
        $mouvementModel = new Mouvement();
        $answer = $mouvementModel->calculGainParOperateur($idOperateur, $dateMin, $dateMax);
        // $answer = ['Depot' => 0, 'Retrait' => 50, 'Transfert' => 200]
        $answer['total'] = array_sum($answer);
        $answer['liste'] = $mouvementModel->getMouvementDetails($idOperateur, $dateMin, $dateMax);

        return view('operator/operator', $answer);
    }
}
