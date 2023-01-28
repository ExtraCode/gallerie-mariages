<?php

namespace App\Controller;

use App\Entity\Album;
use App\Form\AlbumType;
use App\Form\PhotoType;
use App\Repository\AlbumRepository;
use App\Service\PhotoService;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'app_admin_')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(AlbumRepository $albumRepository): Response
    {

        $albums = $albumRepository->findAll();

        return $this->render('admin/album/index.html.twig', [
            'albums' => $albums
        ]);
    }

    #[Route('/ajouter', name: 'ajouter')]
    #[Route('/modifier/{id}', name: 'modifier')]
    public function editer(AlbumRepository $albumRepository, Request $request, EntityManagerInterface $entityManager, int $id = null): Response
    {
        if($request->attributes->get('_route') == "app_admin_ajouter") {
            $album = new Album();
        }else{
            $album = $albumRepository->find($id);
            $oldPath = $album->getPathName();
        }

        $formAlbum = $this->createForm(AlbumType::class,$album);

        $formAlbum->handleRequest($request);
        if ($formAlbum->isSubmitted() && $formAlbum->isValid()) {

            // création du dossier
            $filesystem = new Filesystem();
            if($request->attributes->get('_route') == "app_admin_ajouter") {

                try {
                    $filesystem->mkdir($album->getPathName());

                } catch (IOExceptionInterface $exception) {
                    echo "An error occurred while creating your directory at ".$exception->getPath();
                }
            }else{

                // rename le dossier
                $filesystem->rename($oldPath,$album->getPathName());

            }

            $entityManager->persist($album);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Album créé !'
            );

            return $this->redirectToRoute('app_admin_home');
        }

        return $this->render('admin/album/editer.html.twig', [
            'formAlbum' => $formAlbum->createView()
        ]);
    }

    #[Route('/supprimer/{id}', name: 'supprimer')]
    public function supprimer(AlbumRepository $albumRepository, EntityManagerInterface $entityManager, int $id): Response
    {

        $album = $albumRepository->find($id);
        $filesystem = new Filesystem();
        $filesystem->remove($album->getPathName());

        $entityManager->remove($album);
        $entityManager->flush();

        $this->addFlash(
            'success',
            'Album supprimé !'
        );

        return $this->redirectToRoute('app_admin_home');
    }

    #[Route('/photos/{id_album}', name: 'photos')]
    public function photos(AlbumRepository $albumRepository, PhotoService $photoService, UploadService $uploadService, Request $request, int $id_album){

        // récupère l'album
        $album = $albumRepository->find($id_album);
        if(empty($album)){
            return $this->redirectToRoute('app_admin_home');
        }

        // Ajout de photos
        $form = $this->createForm(PhotoType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $count = 1;
            foreach($form->get('files')->getData() as $file){

                $uploadService->upload($file, $count, $album->getPathName());
                $count++;

            }

            return $this->redirectToRoute('app_admin_photos', ['id_album' => $album->getId()]);

        }

        // mise à jour des photos
        if($request->request->get('maj_photos')){

            $photoService->renamePhotos($album, $request->get('nom_image'));
            return $this->redirectToRoute('app_admin_photos', ['id_album' => $album->getId()]);

        }

        // récupère toutes les images du dossier
        $files = $photoService->getPhotosFromAlbum($album);

        return $this->renderForm('admin/album/photos.html.twig', [
            'album' => $album,
            'images' => $files["images"],
            'videos' => $files["videos"],
            'form' => $form
        ]);

    }

}