<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use Ogan\View\Helper\HtmxHelper;
use App\Model\Comment;
use App\Enum\CommentStatus;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class CommentController extends AbstractController
{
    #[Route(path: '/admin/comments', methods: ['GET'], name: 'admin_comment_index')]
    public function index(): Response
    {
        $comments = Comment::latest()->paginate(15);
        $pendingCount = Comment::where('status', '=', CommentStatus::PENDING->value)->count();

        // HTMX: retourner uniquement le partial
        if (HtmxHelper::isHtmxRequest() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('admin/comment/_partials/_list.ogan', [
                'comments' => $comments
            ]);
        }

        return $this->render('admin/comment/index.ogan', [
            'title' => 'Modération des commentaires',
            'comments' => $comments,
            'pendingCount' => $pendingCount
        ]);
    }

    #[Route(path: '/admin/comments/pending', methods: ['GET'], name: 'admin_comment_pending')]
    public function pending(): Response
    {
        $comments = Comment::where('status', '=', CommentStatus::PENDING->value)
            ->paginate(20);

        // HTMX: retourner uniquement le partial
        if (HtmxHelper::isHtmxRequest() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('admin/comment/_partials/_list.ogan', [
                'comments' => $comments
            ]);
        }

        return $this->render('admin/comment/pending.ogan', [
            'title' => 'Commentaires en attente',
            'comments' => $comments
        ]);
    }

    #[Route(path: '/admin/comments/{id}/approve', methods: ['POST'], name: 'admin_comment_approve')]
    public function approve(int $id): Response
    {
        $comment = Comment::find($id);

        if (!$comment) {
            $this->addFlash('error', 'Commentaire non trouvé.');
            return $this->redirect('/admin/comments');
        }

        $comment->setStatus(CommentStatus::APPROVED->value);
        $comment->save();

        $this->addFlash('success', 'Commentaire approuvé.');

        // HTMX: retourner la ligne mise à jour + OOB updates
        if (HtmxHelper::isHtmxRequest()) {
            $pendingCount = Comment::where('status', '=', CommentStatus::PENDING->value)->count();

            $response = $this->render('admin/comment/_partials/_row.ogan', [
                'comment' => $comment
            ]);

            // Ajouter les mises à jour OOB (Flash + Compteur)
            $content = $response->getContent();
            $content .= (string) $this->view->component('flashes', ['oob' => true]);
            $content .= '<span id="admin-pending-count" hx-swap-oob="true">' . $pendingCount . '</span>';

            return $response->setContent($content);
        }

        return $this->redirect('/admin/comments');
    }

    #[Route(path: '/admin/comments/{id}/reject', methods: ['POST'], name: 'admin_comment_reject')]
    public function reject(int $id): Response
    {
        $comment = Comment::find($id);

        if (!$comment) {
            $this->addFlash('error', 'Commentaire non trouvé.');
            return $this->redirect('/admin/comments');
        }

        $comment->setStatus(CommentStatus::REJECTED->value);
        $comment->save();

        $this->addFlash('success', 'Commentaire rejeté.');

        // HTMX: retourner la ligne mise à jour + OOB updates
        if (HtmxHelper::isHtmxRequest()) {
            $pendingCount = Comment::where('status', '=', CommentStatus::PENDING->value)->count();

            $response = $this->render('admin/comment/_partials/_row.ogan', [
                'comment' => $comment
            ]);

            // Ajouter les mises à jour OOB (Flash + Compteur)
            $content = $response->getContent();
            $content .= (string) $this->view->component('flashes', ['oob' => true]);
            $content .= '<span id="admin-pending-count" hx-swap-oob="true">' . $pendingCount . '</span>';

            return $response->setContent($content);
        }

        return $this->redirect('/admin/comments');
    }

    #[Route(path: '/admin/comments/{id}/delete', methods: ['POST'], name: 'admin_comment_delete')]
    public function delete(int $id): Response
    {
        $comment = Comment::find($id);

        if (!$comment) {
            $this->addFlash('error', 'Commentaire non trouvé.');
            return $this->redirect('/admin/comments');
        }

        $comment->delete();
        $this->addFlash('success', 'Commentaire supprimé.');

        // HTMX: retourner le partial de suppression
        if (HtmxHelper::isHtmxRequest()) {
            $response = $this->render('admin/_partials/_deleted.ogan', [
                'showFlashOob' => true
            ]);

            if (Comment::count() === 0) {
                $response->setHeader('HX-Trigger', 'reloadCommentsList');
            }

            // OOB Pending Count
            $pendingCount = Comment::where('status', '=', CommentStatus::PENDING->value)->count();
            $content = $response->getContent();
            $content .= '<span id="admin-pending-count" hx-swap-oob="true">' . $pendingCount . '</span>';
            $response->setContent($content);

            return $response;
        }

        return $this->redirect('/admin/comments');
    }
}
