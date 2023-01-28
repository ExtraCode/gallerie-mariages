<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AlbumController extends AbstractController
{
    #[Route('/ca', name: 'accueil')]
    public function index(): Response
    {
        die();
    }

    #[Route('/albuma', name: 'voir')]
    public function voir($couple, $album): Response
    {

        return $this->render('album/voir.html.twig', [
            'album' => $album,
            'images' => glob($couple.'/'.$album)
        ]);

    }
}
