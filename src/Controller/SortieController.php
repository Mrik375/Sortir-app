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

        $sortie->addParticipant($organisateur);

        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                if ($form->get('publish')->isClicked()) {
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
                return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
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

    #[Route('/edit/{id}', name: 'app_sortie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
    {
        // Création du formulaire pour la sortie
        $form = $this->createForm(SortieType::class, $sortie);
        $form->handleRequest($request);

        // Gestion de la suppression si le bouton "Supprimer" est cliqué
        if ($request->isMethod('POST') && $request->request->get('delete')) {
            // Vérification du CSRF token pour la sécurité
            $token = $request->request->get('_token');
            if ($this->isCsrfTokenValid('delete' . $sortie->getId(), $token)) {
                // Suppression de la sortie si le token est valide
                $em->remove($sortie);
                $em->flush();

                // Message de succès et redirection après suppression
                $this->addFlash('success', 'La sortie a été supprimée avec succès.');
                return $this->redirectToRoute('home_');
            } else {
                // Message d'erreur si le token CSRF est invalide
                $this->addFlash('error', 'Token CSRF invalide.');
            }
        }

        // Si le formulaire est soumis et valide, gérer la modification ou la publication
        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier si le bouton "Publier" a été cliqué
            if ($form->get('publish')->isClicked()) {
                $etatOuverte = $em->getRepository(Etat::class)->findOneBy(['id' => '2']);
                if ($etatOuverte) {
                    $sortie->setEtatSortie($etatOuverte);
                } else {
                    throw new \Exception("L'état '2' n'existe pas.");
                }
            }

            // Enregistrer les modifications dans la base de données
            $em->flush();

            // Message de succès et redirection après modification
            $this->addFlash('success', 'Votre sortie a bien été enregistrée.');
            return $this->redirectToRoute('home_');
        }

        // Si le formulaire est soumis mais invalide, afficher un message d'erreur
        if ($form->isSubmitted()) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de la sortie.');
        }

        // Rendu de la vue pour l'édition de la sortie
        return $this->render('sortie/edit.html.twig', [
            'sortie' => $sortie,
            'form' => $form->createView(),
        ]);
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

    #[Route('/inscription/{id}', name: 'sortie_inscription', methods: ['POST'])]
    public function inscription(Request $request, SortieRepository $sortieRepository, EntityManagerInterface $em, int $id): Response
    {
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('No sortie found for id ' . $id);
        }
        $user = $this->getUser();

        if (!$sortie->getParticipants()->contains($user)) {

            $sortie->addParticipant($user);
            $em->persist($sortie);
            $em->flush();

            $this->addFlash('success', 'Vous êtes maintenant inscrit à la sortie.');
        } else {
            $this->addFlash('warning', 'Vous êtes déjà inscrit à cette sortie.');
        }
        return $this->redirectToRoute('app_sortie_show', ['id' => $id]);
    }

    #[Route('/desistement/{id}', name: 'sortie_desistement', methods: ['POST'])]
    public function desistement(Request $request, Sortie $sortie, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();

        if (!$sortie->getParticipants()->contains($user)) {
            $this->addFlash('error', 'Vous n\'êtes pas inscrit à cette sortie.');
            return $this->redirectToRoute('app_sortie_show', ['id' => $sortie->getId()]);
        }

        $sortie->removeParticipant($user);
        $em->persist($sortie);
        $em->flush();

        $this->addFlash('success', 'Vous vous êtes bien désisté de la sortie.');

        return $this->redirectToRoute('home_', ['id' => $sortie->getId()]);
    }
}
