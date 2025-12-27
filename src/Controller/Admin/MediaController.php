<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use App\Model\Media;
use App\Security\UserAuthenticator;
use Ogan\Database\Database;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class MediaController extends AbstractController
{
    private ?UserAuthenticator $auth = null;

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
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM media ORDER BY created_at DESC");
        $mediaItems = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $media = array_map(fn($row) => new Media($row), $mediaItems);

        return $this->render('admin/media/index.ogan', [
            'title' => 'Médiathèque',
            'media' => $media
        ]);
    }

    #[Route(path: '/admin/media/upload', methods: ['POST'], name: 'admin_media_upload')]
    public function upload(): Response
    {
        $user = $this->getAuth()->getUser($this->session);

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
            return $this->redirect('/admin/media');
        }

        $file = $_FILES['file'];

        // Validate file type (images only)
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedTypes)) {
            $this->addFlash('error', 'Seules les images (JPEG, PNG, GIF, WebP) sont acceptées.');
            return $this->redirect('/admin/media');
        }

        // Validate file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->addFlash('error', 'Le fichier est trop volumineux (max 5 Mo).');
            return $this->redirect('/admin/media');
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('media_') . '.' . strtolower($extension);

        // Create upload directory if not exists
        $uploadDir = dirname(__DIR__, 3) . '/public/uploads/media/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $relativePath = 'media/' . date('Y/m') . '/' . $filename;
        $absolutePath = $uploadDir . '/' . $filename;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
            $this->addFlash('error', 'Erreur lors de l\'enregistrement du fichier.');
            return $this->redirect('/admin/media');
        }

        // Save to database
        $media = new Media();
        $media->filename = $filename;
        $media->original_name = $file['name'];
        $media->path = $relativePath;
        $media->mime_type = $mimeType;
        $media->size = $file['size'];
        $media->alt = $this->request->post('alt') ?? pathinfo($file['name'], PATHINFO_FILENAME);
        $media->user_id = $user->getId();
        $media->created_at = date('Y-m-d H:i:s');
        $media->save();

        $this->addFlash('success', 'Image uploadée avec succès.');
        return $this->redirect('/admin/media');
    }

    #[Route(path: '/admin/media/{id:}/delete', methods: ['POST'], name: 'admin_media_delete')]
    public function delete(int $id): Response
    {
        $media = Media::find($id);

        if (!$media) {
            $this->addFlash('error', 'Média non trouvé.');
            return $this->redirect('/admin/media');
        }

        // Delete file from disk
        $filePath = dirname(__DIR__, 3) . '/public/uploads/' . $media->path;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $media->delete();

        $this->addFlash('success', 'Média supprimé avec succès.');
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
        $media->alt = $alt;
        $media->save();

        return $this->json(['success' => true, 'alt' => $alt]);
    }
}
