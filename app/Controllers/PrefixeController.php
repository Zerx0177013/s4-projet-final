<?php

namespace App\Controllers;

use App\Models\PrefixOperateur;

class PrefixeController extends BaseController
{
    public function ajouter()
    {
        $idOperateur = session()->get('idOperateur');

        if ($idOperateur === null) {
            return redirect()->to('/operator');
        }

        $prefix = trim((string) $this->request->getPost('prefix'));

        if (! preg_match('/^\d{3}$/', $prefix)) {
            return redirect()->to('/operator/dashboard')->with('prefixError', 'Préfixe invalide (3 chiffres requis).');
        }

        $prefixModel = new PrefixOperateur();

        if ($prefixModel->where('prefix', $prefix)->first() !== null) {
            return redirect()->to('/operator/dashboard')->with('prefixError', 'Ce préfixe existe déjà.');
        }

        $prefixModel->insert([
            'idOperateur' => $idOperateur,
            'prefix'      => $prefix,
        ]);

        return redirect()->to('/operator/dashboard');
    }

    public function supprimer(int $id)
    {
        $idOperateur = session()->get('idOperateur');

        if ($idOperateur === null) {
            return redirect()->to('/operator');
        }

        $prefixModel = new PrefixOperateur();
        $prefixModel->where('id', $id)->where('idOperateur', $idOperateur)->delete();

        return redirect()->to('/operator/dashboard');
    }
}
