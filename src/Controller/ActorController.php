<?php

namespace App\Controller;

use App\Entity\Actor;
use App\Form\ActorType;
use App\Repository\ActorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/actor', name: 'actor_')]
class ActorController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(ActorRepository $actorRepository): Response
    {
        $actors = $actorRepository->findAll();

        return $this->render('Actor/index.html.twig', [
            'actors' => $actors,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $actor = new Actor();
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Utilisation du SluggerInterface pour générer un slug à partir du titre
            $slug = $slugger->slug($actor->getName())->lower();
            $slug = str_replace([' ', '_'], '-', $slug);
            $actor->setSlug($slug);

            $entityManager->persist($actor);
            $entityManager->flush();

            // Message Flash de succès
            $this->addFlash('success', 'Bravo ! Votre acteur a été créé avec succès.');

            // Redirige vers la liste des acteurs
            return $this->redirectToRoute('actor_index');
        }

        // Rendre le formulaire
        return $this->render('actor/new.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET'])]
    public function show(Actor $actor): Response
    {
        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
        ]);
    }

    #[Route('/show-actor-links', name: 'show_actor_links', methods: ['GET'])]
    public function showActorLinks(ActorRepository $actorRepository): Response
    {
        $actors = $actorRepository->findAll();

        return $this->render('actor/_actor_links.html.twig', [
            'actors' => $actors,
        ]);
    }

    #[Route('/{slug}/edit', name: 'app_actor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Actor $actor, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ActorType::class, $actor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Message Flash de succès
            $this->addFlash('success', 'Bravo ! Votre acteur a été édité avec succès.');

            return $this->redirectToRoute('actor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('actor/edit.html.twig', [
            'actor' => $actor,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{slug}/delete', name: 'app_actor_delete', methods: ['POST'])]
    public function delete(Request $request, Actor $actor, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$actor->getSlug(), $request->request->get('_token'))) {
            $entityManager->remove($actor);
            $entityManager->flush();

            // Message Flash de danger
            $this->addFlash('danger', 'Votre acteur a bien été supprimé.');
        }

        return $this->redirectToRoute('actor_index', [], Response::HTTP_SEE_OTHER);
    }

}