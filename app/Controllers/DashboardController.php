<?php

namespace App\Controllers;

use App\Models\Mouvement;
use App\Models\PrefixOperateur;
use App\Models\Tranche;
use App\Models\TypeOperation;

class DashboardController extends BaseController
{
    public function afficherGainParOperateur($dateMin = null, $dateMax = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $idOperateur = session()->get('idOperateur');

        if ($idOperateur === null) {
            return redirect()->to('/operator');
        }

        $mouvementModel = new Mouvement();
        $prefixModel = new PrefixOperateur();
        $typeOperationModel = new TypeOperation();
        $trancheModel = new Tranche();

        $answer = $mouvementModel->calculGainParOperateur($idOperateur, $dateMin, $dateMax);
        // $answer = ['Depot' => 0, 'Retrait' => 50, 'Transfert' => 200]
        $answer['total'] = array_sum($answer);
        $answer['liste'] = $mouvementModel->getMouvementDetails($idOperateur, $dateMin, $dateMax);
        $answer['prefixes'] = $prefixModel->getPrefixesByOperateur($idOperateur);

        $typeOperations = $typeOperationModel->orderBy('id', 'ASC')->findAll();
        foreach ($typeOperations as &$typeOperation) {
            $typeOperation['tranches'] = $typeOperation['idBareme'] !== null
                ? $trancheModel->getSlabsByBareme((int) $typeOperation['idBareme'])
                : [];
        }
        unset($typeOperation);
        $answer['typeOperations'] = $typeOperations;

        return view('operator/operator', $answer);
    }
}
