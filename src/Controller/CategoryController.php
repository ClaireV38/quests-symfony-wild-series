<?php
// src/Controller/CategoryController.php
namespace App\Controller;

use App\Entity\Program;
use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/categories", name="category_")
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
        $categories = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories
        ]);
    }

    /**
     *Show up to 3 series from one category
     *
     * @Route("/{categoryName}", requirements={"category"="\w+"}, methods={"GET"}, name="show")
     * @param string $categoryName
     * @return Response
     */
    public function show(string $categoryName): Response
    {
        $category = $this->getDoctrine()
            ->getRepository(Category::class)
            ->findOneBy(['name' => $categoryName]);
        if (!$category) {
            throw $this->createNotFoundException(
                'No category with name : ' . $categoryName . ' found in program\'s table.'
            );
        }
        $programsByCategory = $this->getDoctrine()
            ->getRepository(Program::class)
            ->findBy(['category' => $category], ['id' => 'DESC'], 3);
        return $this->render('category/show.html.twig', [
            'programs' => $programsByCategory,
        ]);
    }
}