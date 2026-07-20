<?php

namespace App\Controllers;

use App\Models\Compte;
use App\Models\Mouvement;
use App\Models\PrefixOperateur;
use App\Models\Tranche;
use App\Models\TypeOperation;
use App\Models\Operateur;


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
        $typeOperationModel = new TypeOperation();
        $trancheModel = new Tranche();
        $operateurModel = new Operateur();

        $gains = $mouvementModel->calculGainParOperateur($idOperateur);
        $gains['total'] = array_sum($gains);
        $gains['liste'] = $mouvementModel->getMouvementDetails($idOperateur);
        $gains['commission'] = $operateurModel->getMontantCommission($idOperateur);
        $typeOperations = $typeOperationModel->orderBy('id', 'ASC')->findAll();
        foreach ($typeOperations as &$typeOperation) {
            $typeOperation['tranches'] = $typeOperation['idBareme'] !== null
                ? $trancheModel->getSlabsByBareme((int) $typeOperation['idBareme'])
                : [];
        }
        unset($typeOperation);

        $data = array_merge($gains, [
            'comptes'        => $compteModel->getComptesAvecTransactions($idOperateur),
            'prefixes'       => $prefixModel->getPrefixesByOperateur($idOperateur),
            'typeOperations' => $typeOperations,
            'commission'     => $gains['commission'],
        ]);

        return view('operator/operator', $data);
    }
}
