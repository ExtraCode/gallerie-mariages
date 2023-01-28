<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MariageController extends AbstractController
{

    #[Route('/', name: 'app_accueil')]
    public function index(): Response
    {
        die();
    }

    #[Route('/{couple}/{album}', name: 'app_mariage')]
    public function mariage($couple,$album): Response
    {
        $menu = array();
        $menuExtract = glob('albums/'.$couple.'/*',GLOB_ONLYDIR);
        foreach($menuExtract as $onglet){
            $arrayOnglet = explode('/',$onglet);
            $menu[] = array(ucfirst(substr($arrayOnglet[2], strpos($arrayOnglet[2], "-") + 1)) => $arrayOnglet[2]);
        }

        return $this->render('mariages/voir.html.twig', [
            'couple' => $couple,
            'album' => $album,
            'menu' => $menu,
            'images' => glob('albums/'.$couple.'/'.$album.'/*')
        ]);
    }
}
