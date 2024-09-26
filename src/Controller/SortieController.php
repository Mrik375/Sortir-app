<?php

namespace App\Controller;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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

    #[Route('/new/', name: 'sortie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();
        $etatCreer = $em->getRepository(Etat::class)->findOneBy(['id' => '1']);

        if ($etatCreer) {
            $sortie->setEtatSortie($etatCreer);
        } else {
            throw new \Exception("L'état '1' n'existe pas.");
        }

        $organisateur = $this->getUser();
        $sortie->setOrganisateur($organisateur);

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('publish')->isClicked()) {
                // Changer l'état à "ouverte" si on publie
                $etatOuverte = $em->getRepository(Etat::class)->findOneBy(['id' => '2']);
                if ($etatOuverte) {
                    $sortie->setEtatSortie($etatOuverte);
                } else {
                    throw new \Exception("L'état '2' n'existe pas.");
                }
            }
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Votre sortie a bien été enregistrée');
            return $this->redirectToRoute('home_');

        } elseif ($form->isSubmitted()) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de la sortie');
        }

        return $this->render('sortie/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/show/{id}', name: 'app_sortie_show', methods: ['GET'])]
    public function show(SortieRepository $sortieRepository, string $id): Response
    {
        $id = intval($id);
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('No sortie found for id ' . $id);
        }

        $lieu = $sortie->getLieuSortie();
        $participants = $sortie->getParticipants();

        return $this->render('sortie/show.html.twig', [
            'sortie' => $sortie,
            'lieu' => $lieu,
            'participants' => $participants,
        ]);
    }

    #[Route('/edit/{id}', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Si le bouton "Publier" est cliqué
            if ($form->get('publish')->isClicked()) {
                // Attribuer l'état avec id = 2 (Publier)
                $etatPublie = $em->getRepository(Etat::class)->findOneBy(['id' => '2']);
                if ($etatPublie) {
                    $sortie->setEtatSortie($etatPublie);
                } else {
                    throw new \Exception("L'état '2' n'existe pas.");
                }
            }
            // Si le bouton "Enregistrer" est cliqué
            else if ($form->get('save')->isClicked()) {
                // Attribuer l'état avec id = 1 (Enregistrer)
                $etatEnregistre = $em->getRepository(Etat::class)->findOneBy(['id' => '1']);
                if ($etatEnregistre) {
                    $sortie->setEtatSortie($etatEnregistre);
                } else {
                    throw new \Exception("L'état '1' n'existe pas.");
                }
            }

            // Sauvegarde dans la base de données
            $em->flush();
            $this->addFlash('success', 'Votre sortie a bien été enregistrée.');
            return $this->redirectToRoute('home_');
        }

        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/delete/{id}', name: 'app_sortie_delete', methods: ['POST'])]
    public function delete(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
    {
        // Vérification du token CSRF pour plus de sécurité
        if ($this->isCsrfTokenValid('delete'.$sortie->getId(), $request->request->get('_token'))) {
            $em->remove($sortie);
            $em->flush();

            $this->addFlash('success', 'La sortie a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Échec de la suppression de la sortie.');
        }

        return $this->redirectToRoute('home_'); // Redirection après suppression
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
            'codePostal' => $lieu->getLieuVille()->getCodePostal(),
            'latitude' => $lieu->getLatitude(),
            'longitude' => $lieu->getLongitude()
        ]);
    }
}
