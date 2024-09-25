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
        $qb = $this->createQueryBuilder('s')
            ->join('s.etatSortie', 'e')
            ->join('s.organisateur', 'o')
            ->select('s, e, o')
        ;

        if ($accueil->getCampus()) {
            $qb->andWhere('s.siteOrganisateur = :campus')
                ->setParameter('campus', $accueil->getCampus());
        }

        if ($accueil->getNom()) {
            $qb->andWhere('s.nom LIKE :nom')
                ->setParameter('nom', '%'.$accueil->getNom().'%');
        }

        if ($accueil->getDateDebut()) {
            $dateDebut = $accueil->getDateDebut();
            if (!$dateDebut instanceof \DateTime) {
                $dateDebut = new \DateTime($dateDebut);
            }
            $qb->andWhere('s.dateHeureDebut >= :dateDebut')
                ->setParameter('dateDebut', $dateDebut);
        }

        if ($accueil->getDateFin()) {
            $dateFin = $accueil->getDateFin();
            if (!$dateFin instanceof \DateTime) {
                $dateFin = new \DateTime($dateFin);
            }
            $qb->andWhere('s.dateHeureDebut + s.duree * 60 >= :dateDebut AND s.dateHeureDebut <= :dateFin')
                ->setParameter('dateFin', $dateFin);
        }

        if ($accueil->getOrganisateur()) {
            $qb->andWhere('s.organisateur = :organisateur')
                ->setParameter('organisateur', $participant);
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
            $qb->andWhere('e.id = :etat')
                ->setParameter('etat', 5);
        }

        return $qb->getQuery()->getResult();
    }
}
