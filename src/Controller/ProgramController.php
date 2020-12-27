<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Service\Slugify;
use App\Form\ProgramType;
use App\Entity\Program;
use App\Entity\Season;
use App\Entity\Episode;
use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Email;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use App\Repository\ProgramRepository;
use App\Form\SearchProgramType;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/programs", name="program_")
 */
class ProgramController extends AbstractController
{
    /**
     * Show all rows from Program’s entity
     *
     * @Route("/", name="index")
     * @return Response A response instance
     */
    public function index(Request $request, ProgramRepository $programRepository, SessionInterface $session): Response
    {
        $form = $this->createForm(SearchProgramType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $searchTitle = trim($form->getData()['searchTitle']);
            $searchActor = trim($form->getData()['searchActor']);
            if ($searchTitle === '' && $searchActor === '') {
                $programs = [];
            }
            elseif ($searchActor === '') {
                $programs = $programRepository->findLikeName($searchTitle);
            }
            elseif ($searchTitle === '') {
                $programs = $programRepository->findWithActor($searchActor);
            }
            else {
                $programs = $programRepository->findLikeName($searchTitle, $searchActor);
            }
        } else {
            $programs = $programRepository->findAll();
        }

        if (!$session->has('total')) {
            $session->set('total', 0); // if total doesn’t exist in session, it is initialized.
        }

        return $this->render('program/index.html.twig', [
            'programs' => $programs,
            'form' => $form->createView(),
        ]);
    }

    /**
     * The controller for the program add form
     *
     * @Route("/new", name="new")
     */
    public function new(Request $request, Slugify $slugify,  MailerInterface $mailer) : Response
    {
        // Create a new Category Object
        $program = new Program();
        // Create the associated Form
        $form = $this->createForm(ProgramType::class, $program);
        // Get data from HTTP request
        $form->handleRequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // Get the Entity Manager
            $entityManager = $this->getDoctrine()->getManager();
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            // Persist Category Object
            // Set the program's owner
            $program->setOwner($this->getUser());
            $entityManager->persist($program);
            // Flush the persisted object
            $entityManager->flush();
            // Finally redirect to categories list
            $email = (new Email())
                ->from($this->getParameter('mailer_from'))
                ->to('your_email@example.com')
                ->subject('Une nouvelle série vient d\'être publiée !')
                ->html($this->renderView('program/newProgramEmail.html.twig', ['program' => $program]));
            $mailer->send($email);
            return $this->redirectToRoute('category_show', ['categoryName' => $program->getCategory()->getName()]);
        }
        // Render the form
        return $this->render('program/new.html.twig', [
            "form" => $form->createView(),
        ]);
    }

    /**
     * Getting a program by id
     *
     * @Route("/{program}", requirements={"program"="[\w\-]+"}, methods={"GET"}, name="show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program": "slug"}})
     * @return Response
     */
    public function show(Program $program): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '.$id.' found in program\'s table.'
            );
        }
        $programSeasons = $program->getSeasons();
        return $this->render('program/show.html.twig', [
            'program' => $program,
            'programSeasons' => $programSeasons
        ]);
    }

    /**
     * @Route("/{program}/edit", name="edit", methods={"GET","POST"})
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program": "slug"}})
     * @return Response
     */
    public function edit(Request $request, Program $program, Slugify $slugify): Response
    {
        /* Check wether the logged in user is the owner of the program */
        if (!($this->getUser() == $program->getOwner())) {
            /* If not the owner, throws a 403 Access Denied exception */
            throw new AccessDeniedException('Only the owner can edit the program!');
        }

        $form = $this->createForm(ProgramType::class, $program);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugify->generate($program->getTitle());
            $program->setSlug($slug);
            $program->setOwner($this->getUser());
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('program_index');
        }

        return $this->render('program/edit.html.twig', [
            'program' => $program,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Show season details and list of episodes
     *
     * @Route("/{program}/seasons/{season}", requirements={"program"="[\w\-]+", "season"="\d+"}, methods={"GET"}, name="season_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program": "slug"}})
     * @return Response
     */
    public function showSeason(Program $program, Season $season): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with id : '.$program->getId().' found in program\'s table.'
            );
        }
        if (!$season) {
            throw $this->createNotFoundException(
                'No program with id : '.$season->getId().' found in program\'s table.'
            );
        }
        $seasonEpisodes = $season->getEpisodes();
        return $this->render('program/season_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episodes' => $seasonEpisodes
        ]);
    }

    /**
     * Show episode details
     *
     * @Route("/{program}/seasons/{seasonId}/episodes/{episode}", requirements={"programId"="\d+", "seasonId"="\d+", "episodeId"="\d+"}, methods={"GET","POST"}, name="episode_show")
     * @ParamConverter("program", class="App\Entity\Program", options={"mapping": {"program": "slug"}})
     * @ParamConverter("season", class="App\Entity\Season", options={"mapping": {"seasonId": "id"}})
     * @ParamConverter("episode", class="App\Entity\Episode", options={"mapping": {"episode": "slug"}})
     * @return Response
     */
    public function showEpisode(Program $program, Season $season, Episode $episode, Request $request): Response
    {
        if (!$program) {
            throw $this->createNotFoundException(
                'No program with title : '.$program->getSlug().' found in program\'s table.'
            );
        }
        if (!$season) {
            throw $this->createNotFoundException(
                'No program with title : '.$season->getSlug().' found in program\'s table.'
            );
        }
        if (!$episode) {
            throw $this->createNotFoundException(
                'No program with title : '.$episode->getSlug().' found in program\'s table.'
            );
        }
        // Create a new Category Object
        $comment = new Comment();
        // Create the associated Form
        $form = $this->createForm(CommentType::class, $comment);
        // Get data from HTTP request
        $form->handleRequest($request);
        // Was the form submitted ?
        if ($form->isSubmitted() && $form->isValid()) {
            // Deal with the submitted data
            // Get the Entity Manager
            $entityManager = $this->getDoctrine()->getManager();
            $comment->setEpisode($episode);
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            // returns your User object, or null if the user is not authenticated
            // use inline documentation to tell your editor your exact User class
            /** @var \App\Entity\User $user */
            $user = $this->getUser();
            $comment->setAuthor($user);

            // Persist Category Object
            $entityManager->persist($comment);
            // Flush the persisted object
            $entityManager->flush();
        }

        $comments = $this->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(['episode' => $episode, 'id' => 'DESC'] );
        return $this->render('program/episode_show.html.twig', [
            'program' => $program,
            'season' => $season,
            'episode' => $episode,
            "form" => $form->createView(),
        ]);
    }
}
