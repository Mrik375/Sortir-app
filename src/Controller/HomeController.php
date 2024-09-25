<?php

namespace App\Controller;

use App\Class\Accueil;
use App\Entity\Participant;
use App\Form\CheckSortiesFormType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home_')]
    public function index(Request $request, EntityManagerInterface $entityManager, SortieRepository $sortieRepository, Security $security): Response
    {
        /** @var Participant $participant */
        $participant = $security->getUser();
        $accueil = new Accueil();
        $accueil->setCampus($participant->getEstRattacheA());
        $form = $this->createForm(CheckSortiesFormType::class, $accueil);
        $form->handleRequest($request);

        $filteredSorties = $sortieRepository->findByFilters($accueil, $participant);

        return $this->render('home/index.html.twig', [
            'checkSortiesForm' => $form->createView(),
            'sorties' => $filteredSorties,
        ]);
    }
}


