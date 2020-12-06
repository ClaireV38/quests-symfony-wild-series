<?php


namespace App\Controller;


use http\Env\Response;
use App\Entity\Actor;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class ActorController
 * @Route("/actors", name="actor_")
 */
class ActorController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    /**
     * @route("/{id}", name="show")
     * @return Response
     */
    public function show(Actor $actor): Response
    {
        return $this->render('actor/show.html.twig', [
            'actor' => $actor,
        ]);
    }
}