<?php

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Program;
use App\Entity\Season;
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
use Symfony\Component\String\Slugger\SluggerInterface;

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

    #[Route('/show/{programId}/seasons/{seasonId}', name: 'season_show', requirements: ['programId'=>'\d+', 'seasonId'=>'\d+'], methods: ['GET'])]
    public function showSeason(
        #[MapEntity(mapping: ['programId' => 'id'])] Program $program,
        #[MapEntity(mapping: ['seasonId' => 'id'])] Season $season): Response
    {
        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
        ]);
    }

    #[Route('/show/{programId}/seasons/{seasonId}/episode/{episodeId}', name: 'episode_show')]
    public function showEpisode(#[MapEntity(mapping: ['programId' => 'id'])] Program $program,
                                #[MapEntity(mapping: ['seasonId' => 'id'])] Season $season,
                                #[MapEntity(mapping: ['episodeId' => 'id'])] Episode $episode):Response
    {
        return $this->render('program/program_episode.html.twig',[
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
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
}