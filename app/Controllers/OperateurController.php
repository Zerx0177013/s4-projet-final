<?php

namespace App\Controllers;

use App\Models\Operateur;

class OperateurController extends BaseController
{
    /**
     * Affiche la liste des opérateurs disponibles.
     * C'est la page d'entrée de l'Espace Opérateur (route /operator).
     */
    public function choisir(): string
    {
        $operateurModel = new Operateur();

        $data = [
            'operateurs' => $operateurModel->orderBy('nom', 'ASC')->findAll(),
        ];

        return view('operator/select', $data);
    }

    /**
     * Enregistre l'opérateur choisi dans la session (idOperateur).
     * Cette valeur sera ensuite lue par toutes les pages de l'Espace Opérateur
     * (CompteController, DashboardController, ...) au lieu de la passer dans l'URL.
     */
    public function selectionner(int $idOperateur)
    {
        $operateurModel = new Operateur();
        $operateur = $operateurModel->find($idOperateur);

        if ($operateur === null) {
            return redirect()->to('/operator')->with('error', "Opérateur introuvable.");
        }

        session()->set([
            'idOperateur'  => $operateur['id'],
            'operateurNom' => $operateur['nom'],
        ]);

        return redirect()->to('/operator/dashboard');
    }

    /**
     * Réinitialise l'opérateur actif en session ("changer d'opérateur").
     */
    public function deconnecter()
    {
        session()->remove(['idOperateur', 'operateurNom']);

        return redirect()->to('/operator');
    }
}
