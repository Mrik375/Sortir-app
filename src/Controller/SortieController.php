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
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;

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
    #[Route('/new/', name: 'sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();
        $etatCreer = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);

        if ($etatCreer) {
            $sortie->setEtatSortie($etatCreer);
        } else {
            throw new \Exception("L'état 'Créée' n'existe pas.");
        }

        $organisateur = $this->getUser();
        $sortie->setOrganisateur($organisateur);

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($form->get('publish')->isClicked()) {
                    $etatOuverte = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']);
                    if ($etatOuverte) {
                        $sortie->setEtatSortie($etatOuverte);
                    } else {
                        throw new \Exception("L'état 'Ouverte' n'existe pas.");
                    }
                }
                $em->persist($sortie);
                $em->flush();

                $this->addFlash('success', 'Votre sortie a bien été enregistrée');
                return $this->redirectToRoute('home_');
            } else {
                $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de la sortie');
            }
        }

        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(SortieRepository $sortieRepository, string $id): Response
    {
        $id = intval($id);

        // Récupère la sortie à partir de l'ID
        $sortie = $sortieRepository->find($id);

        // Si la sortie n'existe pas, une exception est levée
        if (!$sortie) {
            throw $this->createNotFoundException('No sortie found for id ' . $id);
        }

        // Récupération du lieu de la sortie
        $lieu = $sortie->getLieuSortie();

        // Récupération des participants inscrits à la sortie
        $participants = $sortie->getParticipants(); // Méthode qui récupère les participants

        // Rendu de la vue Twig avec les données de la sortie, du lieu, et des participants
        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'lieu' => $lieu,
            'participants' => $participants, // Transmettre les participants à la vue
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/edit/{id}', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
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
    #[Route('/sortie/delete/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $request->get('_token'))) {
            $entityManager->remove($sortie);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sortie_index', [], Response::HTTP_SEE_OTHER);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/lieu-details/{id}', name: 'lieu_details', methods: ['GET'])]
    public function getLieuDetails($id, EntityManagerInterface $entityManager): JsonResponse
    {
        $lieu = $entityManager->getRepository(Lieu::class)->find($id);

        if (!$lieu) {
            return new JsonResponse(['error' => 'Lieu non trouvé'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'rue' => $lieu->getRue(),
            'codePostal' => $lieu->getLieuVille()->getCodePostal(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude()
        ]);
    }
}
