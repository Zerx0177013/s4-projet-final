<?php

namespace App\Models;

use CodeIgniter\Model;

class Compte extends Model
{
    protected $table            = 'compte';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['number', 'idStatus', 'idOperateur', 'solde', 'PourcentageCaisse' , 'caisse'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    /**
     * Recherche un compte par numéro de téléphone.
     */
    public function findByNumber(string $number): ?array
    {
        return $this->where('number', $number)->first();
    }

    /**
     * Crée un compte client (statut actif, solde à 0) pour un numéro donné
     * rattaché à l'opérateur fourni, puis retourne le compte créé.
     */
    public function createForNumber(string $number, int $idOperateur): array
    {
        $id = $this->insert([
            'number'      => $number,
            'idStatus'    => 1,
            'idOperateur' => $idOperateur,
            'solde'       => 0,
        ], true);

        return $this->find($id);
    }
    public function getourcentageCaisse(String $idCompte){
        $compte = $this->find($idCompte);
        return $compte['PourcentageCaisse'] ?? null;
    }
    public function getCaisse(String $idCompte){
        $compte = $this->find($idCompte);
        return $compte['caisse'] ?? null;
    }


    /**
     * Connecte un client à partir de son numéro : si le numéro n'existe pas
     * encore en base, un compte est créé automatiquement pour l'opérateur
     * correspondant à son préfixe ; sinon le compte existant est simplement
     * retourné. Lève une exception si le préfixe n'est pas reconnu ou si le
     * compte est bloqué.
     *
     * @throws \RuntimeException
     */
    public function loginOuCreer(string $number): array
    {
        $compte = $this->findByNumber($number);

        if ($compte === null) {
            $prefixModel = new PrefixOperateur();
            $prefix      = substr($number, 0, 3);
            $idOperateur = $prefixModel->findOperateurIdByPrefix($prefix);

            if ($idOperateur === null) {
                throw new \RuntimeException("Le préfixe {$prefix} n'est pris en charge par aucun opérateur.");
            }

            $compte = $this->createForNumber($number, $idOperateur);
        }

        if ((int) $compte['idStatus'] !== 1) {
            throw new \RuntimeException('Ce compte est bloqué. Contactez votre opérateur.');
        }

        return $compte;
    }

    /**
     * Ajuste le solde d'un compte d'un delta (positif pour créditer, négatif
     * pour débiter) et retourne le nouveau solde.
     */
    public function ajusterSolde(int $id, float $delta): float
    {
        $compte       = $this->find($id);
        $nouveauSolde = (float) $compte['solde'] + $delta;

        $this->update($id, ['solde' => $nouveauSolde]);

        return $nouveauSolde;
    }

    public function getComptesAvecTransactions(?int $idOperateur = null): array
    {
        $builder = $this->select('id, number, solde, idOperateur')
                        ->where('idStatus', 1)
                        ->orderBy('number', 'ASC');

        if ($idOperateur !== null) {
            $builder->where('idOperateur', $idOperateur);
        }

        $comptes = $builder->findAll();

        foreach ($comptes as &$compte) {
            $transactionsCount = $this->db->table('Mouvement')
                ->groupStart()
                    ->where('idSender', $compte['id'])
                    ->orWhere('idReceiver', $compte['id'])
                ->groupEnd()
                ->countAllResults();

            $compte['name'] = 'Compte #' . $compte['id'];
            $compte['phone'] = $compte['number'];
            $compte['balance'] = (float) $compte['solde'];
            $compte['transactionsCount'] = $transactionsCount;
        }

        return $comptes;
    }

    public function getOperateurIdByCompteId(int $idCompte): ?int
    {
        $compte = $this->find($idCompte);
        return $compte['idOperateur'] ?? null;
    }
}
