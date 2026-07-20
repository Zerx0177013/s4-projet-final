<?php

namespace App\Controllers;

use App\Models\Bareme;
use App\Models\Tranche;

class TrancheController extends BaseController
{
    private function backToOperations()
    {
        return redirect()->to('/operator/dashboard?tab=operations');
    }

    /**
     * Vérifie qu'une tranche [min, max] ne chevauche aucune autre tranche
     * existante du même barème (hors $excludeId, utile en modification).
     */
    private function overlaps(Tranche $trancheModel, int $idBareme, float $min, float $max, ?int $excludeId = null): bool
    {
        $builder = $trancheModel->where('idBareme', $idBareme)
            ->where('min <', $max)
            ->where('max >', $min);

        if ($excludeId !== null) {
            $builder->where('id !=', $excludeId);
        }

        return $builder->first() !== null;
    }

    public function ajouter()
    {
        $idOperateur = session()->get('idOperateur');

        if ($idOperateur === null) {
            return redirect()->to('/operator');
        }

        $idBareme = (int) $this->request->getPost('idBareme');
        $min = $this->request->getPost('min');
        $max = $this->request->getPost('max');
        $montant = $this->request->getPost('montant');

        $baremeModel = new Bareme();

        if ($idBareme <= 0 || $baremeModel->find($idBareme) === null) {
            return $this->backToOperations()->with('opError', 'Barème introuvable.');
        }

        if (! is_numeric($min) || ! is_numeric($max) || ! is_numeric($montant)) {
            return $this->backToOperations()->with('opError', 'Merci de remplir tous les champs de la tranche.');
        }

        $min = (float) $min;
        $max = (float) $max;
        $montant = (float) $montant;

        if ($min < 0 || $max <= $min || $montant < 0) {
            return $this->backToOperations()->with('opError', 'Tranche invalide : min doit être ≥ 0, max > min, et frais ≥ 0.');
        }

        $trancheModel = new Tranche();

        if ($this->overlaps($trancheModel, $idBareme, $min, $max)) {
            return $this->backToOperations()->with('opError', 'Cette tranche chevauche une tranche existante.');
        }

        try {
            $trancheModel->insert([
                'min'      => $min,
                'max'      => $max,
                'montant'  => $montant,
                'idBareme' => $idBareme,
            ]);
        } catch (\Throwable $e) {
            return $this->backToOperations()->with('opError', "Impossible d'ajouter la tranche.");
        }

        return $this->backToOperations();
    }

    public function modifier(int $id)
    {
        $idOperateur = session()->get('idOperateur');

        if ($idOperateur === null) {
            return redirect()->to('/operator');
        }

        $trancheModel = new Tranche();
        $tranche = $trancheModel->find($id);

        if ($tranche === null) {
            return $this->backToOperations()->with('opError', 'Tranche introuvable.');
        }

        $min = $this->request->getPost('min');
        $max = $this->request->getPost('max');
        $montant = $this->request->getPost('montant');

        if (! is_numeric($min) || ! is_numeric($max) || ! is_numeric($montant)) {
            return $this->backToOperations()->with('opError', 'Merci de remplir tous les champs de la tranche.');
        }

        $min = (float) $min;
        $max = (float) $max;
        $montant = (float) $montant;

        if ($min < 0 || $max <= $min || $montant < 0) {
            return $this->backToOperations()->with('opError', 'Tranche invalide : min doit être ≥ 0, max > min, et frais ≥ 0.');
        }

        if ($this->overlaps($trancheModel, (int) $tranche['idBareme'], $min, $max, $id)) {
            return $this->backToOperations()->with('opError', 'Cette tranche chevauche une tranche existante.');
        }

        try {
            $trancheModel->update($id, [
                'min'     => $min,
                'max'     => $max,
                'montant' => $montant,
            ]);
        } catch (\Throwable $e) {
            return $this->backToOperations()->with('opError', "Impossible de modifier la tranche.");
        }

        return $this->backToOperations();
    }

    public function supprimer(int $id)
    {
        $idOperateur = session()->get('idOperateur');

        if ($idOperateur === null) {
            return redirect()->to('/operator');
        }

        (new Tranche())->delete($id);

        return $this->backToOperations();
    }
}
