<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\VilleRepository;

#[Route('/sortie')]
final class SortieController extends AbstractController
{
    #[Route(name: 'app_sortie_index', methods: ['GET'])]
    public function index(SortieRepository $sortieRepository): Response
    {
        return $this->render('sortie/index.html.twig', [
            'sorties' => $sortieRepository->findAll(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/new', name: 'sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();

        // Définir l'état par défaut à "Créée"
        $etatCreer = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);
        if ($etatCreer) {
            $sortie->setEtatSortie($etatCreer);
        } else {
            throw new \Exception("L'état 'Créée' n'existe pas.");
        }

        // Définir l'organisateur pour l'entité Sortie
        $organisateur = $this->getUser(); // Récupère si l'organisateur est l'utilisateur actuellement connecté
        $sortie->setOrganisateur($organisateur);

        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($sortie);
            $em->flush();

            // Rediriger vers la route home après la validation
            return $this->redirectToRoute('home_'); // Assurez-vous que la route 'home' existe
        }

        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('show/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(SortieRepository $sortieRepository, string $id): Response
    {
        $id = intval($id);

        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('No sortie found for id ' . $id);
        }

        // Récupération automatique du lieu grâce à la relation ManyToOne
        $lieu = $sortie->getLieuSortie();

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'lieu' => $lieu, // Le lieu est passé à la vue
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}/edit', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/lieu-details/{id}', name: 'lieu_details', methods: ['GET'])]
    public function getLieuDetails($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $lieu = $entityManager->getRepository(Lieu::class)->find($id);

        if (!$lieu) {
            return new JsonResponse(['error' => 'Lieu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'rue' => $lieu->getRue(),
            'codePostal' => $lieu->getLieuVille()->getCodePostal(), // Ajuste selon la structure de ta base
        ]);
    }
}
