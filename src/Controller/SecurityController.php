<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, on le redirige vers la page d'accueil
        if ($this->getUser()) {
            return $this->redirectToRoute('home_');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Route(path: '/profil', name: 'app_profil')]
    public function profil(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Hash the password if it has been changed
            if ($form->get('motPasse')->getData()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $form->get('motPasse')->getData());
                $user->setMotPasse($hashedPassword);
            }

            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('profilImage')->getData();
            if ($imageFile) {
                // Créer un nom unique pour le fichier image
                $newFilename = uniqid().'.'.$imageFile->guessExtension();

                // Déplacer le fichier dans le répertoire désigné
                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'), // Paramètre configuré dans services.yaml
                        $newFilename
                    );
                } catch (FileException $e) {
                    // Gérer l'exception si le fichier ne peut pas être déplacé
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image de profil.');
                }

                // Mettre à jour l'image de profil de l'utilisateur
                $user->setImageProfile($newFilename);
            }

            // Sauvegarder les modifications dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_profil');
        }

        return $this->render('security/profil.html.twig', [
            'form' => $form->createView(),
            'user' => $user, // Passer l'utilisateur à la vue
        ]);
    }

    #[Route('/participant/{id}', name: 'participant_profile')]
    public function participantProfile(int $id, EntityManagerInterface $entityManager): Response
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant not found');
        }

        return $this->render('security/participant_profile.html.twig', [
            'participant' => $participant,
        ]);
    }
}
