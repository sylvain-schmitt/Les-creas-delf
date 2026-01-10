<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use App\Model\Media;
use App\Service\MediaService;
use App\Security\UserAuthenticator;
use Ogan\View\Helper\HtmxHelper;


#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class MediaController extends AbstractController
{
    private MediaService $mediaService;
    private ?UserAuthenticator $auth = null;

    public function __construct()
    {
        $this->mediaService = new MediaService();
    }

    private function getAuth(): UserAuthenticator
    {
        if ($this->auth === null) {
            $this->auth = new UserAuthenticator();
        }
        return $this->auth;
    }

    #[Route(path: '/admin/media', methods: ['GET'], name: 'admin_media_index')]
    public function index(): Response
    {
        $media = Media::latest()->paginate(15);

        // Requête HTMX : retourner uniquement le partial (sauf si c'est une navigation boostée)
        if (HtmxHelper::isHtmxRequest() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('admin/media/_partials/_list.ogan', [
                'media' => $media,
                'showFlashOob' => true // Toujours inclure les flashs pour les mises à jour HTMX
            ]);
        }

        return $this->render('admin/media/index.ogan', [
            'title' => 'Médiathèque',
            'media' => $media
        ]);
    }

    #[Route(path: '/admin/media/upload', methods: ['POST'], name: 'admin_media_upload')]
    public function upload(): Response
    {
        $user = $this->getAuth()->getUser($this->session);

        if (!isset($_FILES['file'])) {
            $this->addFlash('error', 'Aucun fichier sélectionné.');

            if (HtmxHelper::isHtmxRequest()) {
                return $this->index();
            }

            return $this->redirect('/admin/media');
        }

        $alt = $this->request->post('alt');
        $result = $this->mediaService->upload($_FILES['file'], $user->getId(), $alt);

        if (!$result) {
            $this->addFlash('error', 'Erreur lors de l\'upload. Vérifiez le format (JPEG, PNG, GIF, WebP) et la taille (max 5 Mo).');
        } else {
            $this->addFlash('success', 'Image uploadée avec succès.');
        }

        // HTMX: Réutiliser la méthode index() et ajouter le trigger
        if (HtmxHelper::isHtmxRequest()) {
            $response = $this->index();

            // Uniquement si succès
            if ($result) {
                $response->setHeader('HX-Trigger', 'closeUploadModal');
            }

            return $response;
        }

        return $this->redirect('/admin/media');
    }

    #[Route(path: '/admin/media/{id:}/delete', methods: ['POST'], name: 'admin_media_delete')]
    public function delete(int $id): Response
    {
        $media = Media::find($id);

        if (!$media) {
            $this->addFlash('error', 'Média non trouvé.');

            if (HtmxHelper::isHtmxRequest()) {
                return $this->render('admin/media/_partials/_deleted.ogan', [
                    'showFlashOob' => true
                ]);
            }

            return $this->redirect('/admin/media');
        }

        $this->mediaService->delete($media);
        $this->addFlash('success', 'Média supprimé avec succès.');

        // HTMX: gestion de l'état vide
        if (HtmxHelper::isHtmxRequest()) {
            // Toujours déclencher un rechargement pour recalculer la pagination
            $response = $this->render('admin/_partials/_deleted.ogan', [
                'showFlashOob' => true
            ]);
            $response->setHeader('HX-Trigger', 'reloadMediaList');
            return $response;
        }

        return $this->redirect('/admin/media');
    }

    #[Route(path: '/admin/media/{id:}/update-alt', methods: ['POST'], name: 'admin_media_update_alt')]
    public function updateAlt(int $id): Response
    {
        $media = Media::find($id);

        if (!$media) {
            return $this->json(['error' => 'Média non trouvé'], 404);
        }

        $alt = $this->request->post('alt');
        $media->setAlt($alt);
        $media->save();

        return $this->json(['success' => true, 'alt' => $alt]);
    }

    /**
     * API endpoint to get all media for picker
     */
    #[Route(path: '/admin/api/media', methods: ['GET'], name: 'admin_api_media')]
    public function apiList(): Response
    {
        $media = Media::all();

        $items = array_map(function ($m) {
            return [
                'id' => $m->getId(),
                'url' => $m->getThumbnailUrl(),
                'originalUrl' => $m->getUrl(),
                'alt' => $m->getAlt(),
                'filename' => $m->getOriginalName(),
            ];
        }, $media);

        return $this->json($items);
    }

    /**
     * Partial for media picker modal
     */
    #[Route(path: '/admin/media/picker', methods: ['GET'], name: 'admin_media_picker')]
    public function picker(): Response
    {
        $media = Media::all();

        return $this->render('admin/media/_picker_field.ogan', [
            'media' => $media
        ]);
    }
}
