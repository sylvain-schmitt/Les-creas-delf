<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use App\Model\Comment;
use App\Repository\CommentRepository;
use App\Service\CommentService;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class CommentController extends AbstractController
{
    private CommentRepository $commentRepository;
    private CommentService $commentService;

    public function __construct()
    {
        $this->commentRepository = new CommentRepository();
        $this->commentService = new CommentService();
    }

    #[Route(path: '/admin/comments', methods: ['GET'], name: 'admin_comment_index')]
    public function index(): Response
    {
        $comments = $this->commentRepository->findRecentPaginated(20);
        $pendingCount = $this->commentRepository->countPending();

        return $this->render('admin/comment/index.ogan', [
            'title' => 'Modération des commentaires',
            'comments' => $comments,
            'pendingCount' => $pendingCount
        ]);
    }

    #[Route(path: '/admin/comments/pending', methods: ['GET'], name: 'admin_comment_pending')]
    public function pending(): Response
    {
        $comments = $this->commentRepository->findPending();

        return $this->render('admin/comment/pending.ogan', [
            'title' => 'Commentaires en attente',
            'comments' => $comments
        ]);
    }

    #[Route(path: '/admin/comments/{id:}/approve', methods: ['POST'], name: 'admin_comment_approve')]
    public function approve(int $id): Response
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            $this->addFlash('error', 'Commentaire non trouvé.');
            return $this->redirect('/admin/comments');
        }

        $this->commentService->approve($comment);

        $this->addFlash('success', 'Commentaire approuvé.');
        return $this->redirect('/admin/comments/pending');
    }

    #[Route(path: '/admin/comments/{id:}/reject', methods: ['POST'], name: 'admin_comment_reject')]
    public function reject(int $id): Response
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            $this->addFlash('error', 'Commentaire non trouvé.');
            return $this->redirect('/admin/comments');
        }

        $this->commentService->reject($comment);

        $this->addFlash('success', 'Commentaire rejeté.');
        return $this->redirect('/admin/comments/pending');
    }

    #[Route(path: '/admin/comments/{id:}/delete', methods: ['POST'], name: 'admin_comment_delete')]
    public function delete(int $id): Response
    {
        $comment = $this->commentRepository->find($id);

        if (!$comment) {
            $this->addFlash('error', 'Commentaire non trouvé.');
            return $this->redirect('/admin/comments');
        }

        $this->commentService->delete($comment);

        $this->addFlash('success', 'Commentaire supprimé.');
        return $this->redirect('/admin/comments');
    }
}
