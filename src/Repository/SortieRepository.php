<?php

namespace App\Repository;

use App\Class\Accueil;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sortie>
 */
class SortieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sortie::class);
    }

    public function findByFilters(Accueil $accueil, ?Participant $participant): array
    {
        $qb = $this->createQueryBuilder('s');

        if ($accueil->getCampus()) {
            $qb->andWhere('s.siteOrganisateur = :campus')
                ->setParameter('campus', $accueil->getCampus());
        }

        if ($accueil->getNom()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%'.$accueil->getNom().'%');
        }

        if ($accueil->getDateDebut()) {
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $accueil->getDateDebut());
        }

        if ($accueil->getDateFin()) {
            $qb->andWhere('s.dateHeureDebut + s.duree *60 <= :dateFin')
                ->setParameter('dateFin', $accueil->getDateFin());
        }

        if ($accueil->getOrganisateur()) {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $accueil->getOrganisateur());
        }

        if ($accueil->getInscrit() && $participant) {
            $qb->andWhere(':participant MEMBER OF s.participants')
                ->setParameter('participant', $participant);
        }

        if ($accueil->getNonInscrit() && $participant) {
            $qb->andWhere(':participant NOT MEMBER OF s.participants')
                ->setParameter('participant', $participant);
        }

        if ($accueil->getSortiesPassees()) {
            $qb->andWhere('s.dateHeureDebut < :now')
                ->setParameter('now', new \DateTime());
        }

        return $qb->getQuery()->getResult();
    }
}
