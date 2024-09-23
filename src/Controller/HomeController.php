<?php

namespace App\Controller;

use App\Class\Accueil;
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
        $participant = $security->getUser();
        $accueil = new Accueil(null, null, null, null, false, false, false, false);
        $form = $this->createForm(CheckSortiesFormType::class, $accueil);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $filteredSorties = $sortieRepository->findByFilters($accueil, $participant);
        } else {
            $filteredSorties = $sortieRepository->findAll();
        }
        return $this->render('home/index.html.twig', [
            'checkSortiesForm' => $form->createView(),
            'sorties' => $filteredSorties,
        ]);
    }
}


