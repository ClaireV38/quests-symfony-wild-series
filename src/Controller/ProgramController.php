<?php
// src/Controller/ProgramController.php
namespace App\Controller;

use App\Entity\Program;
use App\Entity\Season;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findAll();

        return $this->render('program/index.html.twig', [
            'programs' => $programs
        ]);
    }

    /**
     * Getting a program by id
     *
     * @Route("/{id}", requirements={"id"="\d+"}, methods={"GET"}, name="show")
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
     * Show season details and list of episodes
     *
     * @Route("/{program}/season/{season}", requirements={"program"="\d+", "season"="\d+"}, methods={"GET"}, name="season_show")
     * @return Response
     */
    public function showSeason(Program $program,Season $season): Response
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
}