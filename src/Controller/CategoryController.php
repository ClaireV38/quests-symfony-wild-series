<?php


namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/category", name="category_")
 */
class CategoryController extends AbstractController
{
    /**
     *Show all rows from Categry’s entity
     *
     * @Route("/", name="index")
     * @return Response
     */
    public function index(): Response
    {

    }
}