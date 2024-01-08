<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Service\ProgramDuration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProgramRepository;
use App\Form\ProgramType;
use App\Form\CommentType;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route('/program', name: 'program_')]
class ProgramController extends AbstractController
{
    #[Route('/', name: 'index')]
    public function index(ProgramRepository $programRepository): Response
    {
        $program = $programRepository->findAll();
        return $this->render('program/index.html.twig', [
            'program' => $program,
        ]);
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, MailerInterface $mailer) : Response
    {
        $program = new Program();
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());
            $entityManager->persist($program);
            $entityManager->flush();

            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('Program/newProgramEmail.html.twig', ['program' => $program]));
            $mailer->send($email);

            // add flash message
            $this->addFlash('success', 'The new program has been created');
            // Redirect to categories list
            return $this->redirectToRoute('program_index');
        }

        // Render the form
        return $this->render('program/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'show', requirements: ['id'=>'\d+'], methods: ['GET'])]
    public function show(Program $program, ProgramDuration $programDuration): Response
    {
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'programDuration' => $programDuration->calculate($program),
        ]);
    }

    #[Route('/show/{slug}/seasons/{number}', name: 'season_show', requirements: ['programId'=>'\d+', 'seasonId'=>'\d+'], methods: ['GET'])]
    public function showSeason(
        #[MapEntity(mapping: ['slug' => 'slug'])] Program $program,
        #[MapEntity(mapping: ['number' => 'number'])] Season $season): Response
    {
        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
        ]);
    }

    #[Route('/{slug}/seasons/{seasonId}/episode/{episodeSlug}', name: 'episode_show')]
    public function showEpisode(#[MapEntity(mapping: ['slug' => 'slug'])] Program $program,
                                #[MapEntity(mapping: ['seasonId' => 'id'])] Season $season,
                                #[MapEntity(mapping: ['episodeSlug' => 'slug'])] Episode $episode,
                                Request $request,
                                EntityManagerInterface $entityManager,
                                CommentRepository $commentRepository):Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();

            $comment->setEpisode($episode);
            $comment->setAuthor($user);
            $comment->setCreatedAt(new \DateTimeImmutable());

            $entityManager->persist($comment);
            $entityManager->flush();

            return $this->redirectToRoute('program_episode_show', [
                'slug' => $program->getSlug(),
                'seasonId' => $season->getId(),
                'episodeSlug' => $episode->getSlug(),
            ]);
        }

        $commentsSorted =  $commentRepository->findBy(
            ['episode' => $episode],
            ['createdAt' => 'ASC']
        );

        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            'form' => $form,
            'commentsSorted' => $commentsSorted,
            'commentRepository' => $commentRepository,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Program $program, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$program->getId(), $request->request->get('_token'))) {
            $entityManager->remove($program);
            $entityManager->flush();
            $this->addFlash('danger', 'Série effacée.');
        }

        return $this->redirectToRoute('program_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/edit/{slug}', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Program $program, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        if ($this->getUser() !== $program->getOwner()) {
            // If not the owner, throws a 403 Access Denied exception
            throw $this->createAccessDeniedException('Only the owner can edit the program!');
        }
        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $entityManager->flush();
            return $this->redirectToRoute('program_index');
        }
        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form,
        ]);
    }

    #[Route('/comment/{id}/delete', name: 'comment_delete', methods: ['POST'])]
    public function deleteComment(Comment $comment, Security $security, EntityManagerInterface $entityManager): Response
    {
        // Vérifier si l'utilisateur a le droit de supprimer le commentaire
        if (!($security->isGranted('ROLE_ADMIN') || ($security->isGranted('ROLE_CONTRIBUTOR') && $security->getUser() === $comment->getAuthor()))) {
            throw $this->createAccessDeniedException('Vous n\'avez pas le droit de supprimer ce commentaire.');
        }

        // Supprimer le commentaire de la base de données
        $entityManager->remove($comment);
        $entityManager->flush();

        // Ajouter un message flash de succès
        $this->addFlash('success', 'Le commentaire a bien été supprimé.');

        // Rediriger vers la page du programme associé à l'épisode
        $programSlug = $comment->getEpisode()->getSeason()->getProgram()->getSlug();
        return $this->redirectToRoute('program_show', ['slug' => $programSlug]);
    }
}