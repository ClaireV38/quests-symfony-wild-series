<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Program;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\CategoryRepository;

class DefaultController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * @Route("/", name="app_index")
     */
    public function index(): Response
    {
        $programs = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy([], ['id' => 'DESC'],3);
        return $this->render('index.html.twig', [
            'website' => 'Wild Series',
            'programs' => $programs,
        ]);
    }

    public function navbarTop(CategoryRepository $categoryRepository): Response
    {
        return $this->render('navbartop.html.twig', [
            'categories' => $categoryRepository->findBy([], ['id' => 'DESC'])
        ]);
    }
}
