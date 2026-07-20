<?php

namespace App\Controllers;

use App\Models\Compte;
use App\Models\Mouvement;
use App\Models\PrefixOperateur;
use App\Models\Tranche;
use App\Models\TypeOperation;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\ResponseInterface;
use RuntimeException;

class ClientController extends BaseController
{
    public function login(): string
    {
        $prefixModel = new PrefixOperateur();

        return view('client/login', [
            'prefixes' => array_column($prefixModel->findAll(), 'prefix'),
        ]);
    }

    /**
     * Traite la soumission du formulaire de connexion. Toute la logique
     * (création automatique du compte si le numéro est inconnu, ou connexion
     * simple s'il existe déjà) est déléguée à Compte::loginOuCreer().
     */
    public function authenticate(): RedirectResponse
    {
        $number = preg_replace('/\D/', '', (string) $this->request->getPost('phone'));

        if (strlen($number) !== 10) {
            return redirect()->back()->withInput()->with('error', 'Numéro invalide (10 chiffres requis).');
        }

        $compteModel = new Compte();

        try {
            $compte = $compteModel->loginOuCreer($number);
        } catch (RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        session()->set([
            'client_id'     => $compte['id'],
            'client_number' => $compte['number'],
            'isLoggedIn'    => true,
        ]);

        return redirect()->to('/client/dashboard');
    }

    public function dashboard(): string|RedirectResponse
    {
        $compte = $this->currentCompte();

        if ($compte === null) {
            return redirect()->to('/client/login');
        }

        $typeOperationModel = new TypeOperation();
        $trancheModel        = new Tranche();

        $feeSlabs = [];
        foreach (['retrait', 'transfert'] as $type) {
            $typeOperation   = $typeOperationModel->findByLibelle(ucfirst($type));
            $feeSlabs[$type] = ($typeOperation !== null && $typeOperation['idBareme'] !== null)
                ? $trancheModel->getSlabsByBareme((int) $typeOperation['idBareme'])
                : [];
        }

        $mouvementModel = new Mouvement();
        $prefixModel    = new PrefixOperateur();

        return view('client/dashboard', [
            'compte'   => $compte,
            'feeSlabs' => $feeSlabs,
            'history'  => $mouvementModel->getHistoriqueCompte((int) $compte['id'], (float) $compte['solde']),
            'prefixes' => array_column($prefixModel->findAll(), 'prefix'),
        ]);
    }

    /**
     * Endpoint AJAX exécutant un dépôt, un retrait ou un transfert pour le
     * client connecté. Toute la logique (frais, validations, mise à jour des
     * soldes, enregistrement du mouvement) vit dans Mouvement::enregistrerOperation().
     */
    public function operate(): ResponseInterface
    {
        $compte = $this->currentCompte();

        if ($compte === null) {
            return $this->response->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Session expirée. Veuillez vous reconnecter.',
            ]);
        }

        $input  = $this->request->getJSON(true) ?? $this->request->getPost();
        $type   = (string) ($input['type'] ?? '');
        $amount = (float) ($input['amount'] ?? 0);
        $target = isset($input['target']) ? preg_replace('/\D/', '', (string) $input['target']) : null;

        $mouvementModel = new Mouvement();

        try {
            $result = $mouvementModel->enregistrerOperation($compte, $type, $amount, $target);
        } catch (RuntimeException $e) {
            return $this->response->setJSON(['success' => false, 'message' => $e->getMessage()]);
        }

        return $this->response->setJSON(array_merge(['success' => true], $result));
    }

    public function logout(): RedirectResponse
    {
        session()->destroy();

        return redirect()->to('/client/login');
    }

    /**
     * Retourne le compte du client actuellement connecté (via la session),
     * ou null si personne n'est connecté / si le compte n'existe plus.
     */
    private function currentCompte(): ?array
    {
        if (! session()->get('isLoggedIn')) {
            return null;
        }

        $compteModel = new Compte();
        $compte      = $compteModel->find(session()->get('client_id'));

        if ($compte === null) {
            session()->destroy();

            return null;
        }

        return $compte;
    }
}
