<?php


namespace App\Entity;

/**
 * Class NomEtat
 * @package App\Entity
 * Cette classe est static qui me permettera d'appeler les nom des etats
 */
class NomEtat
{

    private $etatCreer = "Créée";
    private $etatEnCours = "Validation en cours";
    private $etatValider = "Validée";
    private $etatRefuser = "Refusée";
    private $etetRembourser = "Remboursée";

    /**
     * @return mixed
     */
    public function getEtatCreer()
    {
        return $this->etatCreer;
    }

    /**
     * @return mixed
     */
    public function getEtatEnCours()
    {
        return $this->etatEnCours;
    }

    /**
     * @return mixed
     */
    public function getEtatValider()
    {
        return $this->etatValider;
    }

    /**
     * @return mixed
     */
    public function getEtatRefuser()
    {
        return $this->etatRefuser;
    }

    /**
     * @return mixed
     */
    public function getEtetRembourser()
    {
        return $this->etetRembourser;
    }


}