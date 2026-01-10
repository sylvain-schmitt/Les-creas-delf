<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use Ogan\Security\PasswordHasher;
use App\Security\UserAuthenticator;
use App\Form\ProfileFormType;
use App\Model\Article;
use App\Model\Category;
use App\Model\Media;
use App\Model\Comment;
use App\Enum\ArticleStatus;
use App\Enum\CommentStatus;

#[IsGranted('ROLE_USER', message: 'Accès réservé aux utilisateurs.')]
class DashboardController extends AbstractController
{
    private ?UserAuthenticator $auth = null;

    private function getAuth(): UserAuthenticator
    {
        if ($this->auth === null) {
            $this->auth = new UserAuthenticator();
        }
        return $this->auth;
    }

    #[Route(path: '/dashboard', methods: ['GET'], name: 'dashboard_index')]
    public function index(): Response
    {
        if (!$this->getAuth()->isLoggedIn($this->session)) {
            return $this->redirect('/login');
        }

        $user = $this->getAuth()->getUser($this->session);

        // Récupérer les statistiques réelles
        $stats = [
            'articles' => Article::count(),
            'categories' => Category::count(),
            'drafts' => Article::where('status', '=', ArticleStatus::DRAFT->value)->count(),
            'media' => Media::count(),
            'pendingComments' => Comment::where('status', '=', CommentStatus::PENDING->value)->count(),
        ];

        // Récupérer les 5 derniers articles
        $recentArticles = Article::latest()->paginate(5)->items();

        // Afficher le dashboard selon le rôle
        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            return $this->render('admin/dashboard/index.ogan', [
                'user' => $user,
                'title' => 'Tableau de bord Admin',
                'stats' => $stats,
                'recentArticles' => $recentArticles
            ]);
        }

        // Dashboard utilisateur standard
        return $this->render('admin/dashboard/user.ogan', [
            'user' => $user,
            'title' => 'Mon espace',
            'stats' => $stats
        ]);
    }

    #[Route(path: '/profile', methods: ['GET'], name: 'user_profile')]
    public function profile(): Response
    {
        if (!$this->getAuth()->isLoggedIn($this->session)) {
            return $this->redirect('/login');
        }

        $user = $this->getAuth()->getUser($this->session);

        return $this->render('admin/user/profile.ogan', [
            'title' => 'Mon Profil',
            'user' => $user
        ]);
    }

    #[Route(path: '/profile/edit', methods: ['GET', 'POST'], name: 'user_profile_edit')]
    public function editProfile(): Response
    {
        if (!$this->getAuth()->isLoggedIn($this->session)) {
            return $this->redirect('/login');
        }

        $user = $this->getAuth()->getUser($this->session);

        $form = $this->formFactory->create(ProfileFormType::class, [
            'action' => '/profile/edit',
            'method' => 'POST',
            'user_id' => $user->getId()
        ]);

        // Pré-remplir avec les données actuelles
        $form->setData([
            'name' => $user->getName(),
            'email' => $user->getEmail()
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                // Mettre à jour les informations de base
                $user->setName($data['name']);
                $user->setEmail($data['email']);

                // Si un nouveau mot de passe est fourni
                if (!empty($data['new_password'])) {
                    $hasher = new PasswordHasher();
                    // Vérifier le mot de passe actuel
                    if (empty($data['current_password']) || !$hasher->verify($data['current_password'], $user->getPassword())) {
                        $form->addError('current_password', 'Le mot de passe actuel est incorrect.');
                        return $this->render('admin/user/edit.ogan', [
                            'title' => 'Modifier mon profil',
                            'user' => $user,
                            'form' => $form->createView()
                        ]);
                    }
                    $user->setPassword($hasher->hash($data['new_password']));
                }

                $user->save();

                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
                return $this->redirect('/profile');
            }
        }

        return $this->render('admin/user/edit.ogan', [
            'title' => 'Modifier mon profil',
            'user' => $user,
            'form' => $form->createView()
        ]);
    }
}
