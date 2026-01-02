<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use Ogan\View\Helper\HtmxHelper;
use App\Model\Article;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use App\Service\ArticleService;
use App\Security\UserAuthenticator;
use App\Form\ArticleFormType;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class ArticleController extends AbstractController
{
    private ArticleRepository $articleRepository;
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private UserRepository $userRepository;
    private ArticleService $articleService;
    private ?UserAuthenticator $auth = null;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->tagRepository = new TagRepository();
        $this->userRepository = new UserRepository();
        $this->articleService = new ArticleService();
    }

    private function getAuth(): UserAuthenticator
    {
        if ($this->auth === null) {
            $this->auth = new UserAuthenticator();
        }
        return $this->auth;
    }

    // ══════════════════════════════════════════════════════════════════════
    // 📋 INDEX - Liste des articles avec pagination
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/articles', methods: ['GET'], name: 'admin_article_index')]
    public function index(): Response
    {
        $articles = Article::paginate(15);
        $categories = $this->categoryRepository->findAll();

        // Requête HTMX : retourner uniquement le partial (sauf si c'est une navigation boostée)
        if (HtmxHelper::isHtmxRequest() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('admin/article/_partials/_list.ogan', [
                'articles' => $articles,
                'categories' => $categories
            ]);
        }

        // Sinon, retourner la page complète avec layout
        return $this->render('admin/article/index.ogan', [
            'title' => 'Gestion des articles',
            'articles' => $articles,
            'categories' => $categories
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // ➕ CREATE - Création d'un article
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/articles/create', methods: ['GET', 'POST'], name: 'admin_article_create')]
    public function create(): Response
    {
        $user = $this->getAuth()->getUser($this->session);
        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();

        $form = $this->formFactory->create(ArticleFormType::class, [
            'action' => '/admin/articles/create',
            'method' => 'POST',
            'categories' => $categories,
            'tags' => $tags
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $article = $this->articleService->create($data, $user->getId());

                $this->addFlash('success', 'Article créé avec succès.');

                if (HtmxHelper::isHtmxRequest()) {
                    $response = new Response('');
                    $response->setHeader('HX-Redirect', '/admin/articles');
                    return $response;
                }

                return $this->redirect('/admin/articles');
            }
        }

        // Afficher le formulaire (partial uniquement si HTMX ciblé, pas boost)
        if (HtmxHelper::isHtmxRequest() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('admin/article/_partials/_form.ogan', [
                'form' => $form->createView(),
                'categories' => $categories,
                'tags' => $tags,
                'action' => '/admin/articles/create',
                'submitLabel' => 'Créer l\'article'
            ]);
        }

        return $this->render('admin/article/create.ogan', [
            'title' => 'Nouvel article',
            'form' => $form->createView(),
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // 👁️ SHOW - Voir un article
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/articles/{id:}', methods: ['GET'], name: 'admin_article_show')]
    public function show(int $id): Response
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect('/admin/articles');
        }

        $author = $article->getUserId() ? $this->userRepository->find($article->getUserId()) : null;

        return $this->render('admin/article/show.ogan', [
            'article' => $article,
            'author' => $author
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // ✏️ EDIT - Modification d'un article
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/articles/{id:}/edit', methods: ['GET', 'POST'], name: 'admin_article_edit')]
    public function edit(int $id): Response
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect('/admin/articles');
        }

        $categories = $this->categoryRepository->findAll();
        $tags = $this->tagRepository->findAll();
        $articleTags = $this->tagRepository->findByArticle($id);
        $articleTagIds = array_map(fn($tag) => $tag->id, $articleTags);

        $form = $this->formFactory->create(ArticleFormType::class, [
            'action' => '/admin/articles/' . $id . '/edit',
            'method' => 'POST',
            'categories' => $categories,
            'tags' => $tags
        ]);

        // Pre-fill with current data
        $form->setData([
            'title' => $article->title,
            'excerpt' => $article->excerpt,
            'content' => $article->content,
            'category_id' => $article->category_id,
            'tags' => $articleTagIds,
            'status' => $article->status
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();
                $this->articleService->update($article, $data);

                $this->addFlash('success', 'Article modifié avec succès.');

                if (HtmxHelper::isHtmxRequest()) {
                    $response = new Response('');
                    $response->setHeader('HX-Redirect', '/admin/articles');
                    return $response;
                }

                return $this->redirect('/admin/articles');
            }
        }

        // Afficher le formulaire
        if (HtmxHelper::isHtmxRequest() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('admin/article/_partials/_form.ogan', [
                'form' => $form->createView(),
                'article' => $article,
                'categories' => $categories,
                'tags' => $tags,
                'articleTagIds' => $articleTagIds,
                'action' => '/admin/articles/' . $id . '/edit',
                'submitLabel' => 'Mettre à jour'
            ]);
        }

        return $this->render('admin/article/edit.ogan', [
            'title' => 'Modifier l\'article',
            'article' => $article,
            'form' => $form->createView(),
            'categories' => $categories,
            'tags' => $tags,
            'articleTagIds' => $articleTagIds
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // ✅ PUBLISH - Publier un article
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/articles/{id:}/publish', methods: ['POST'], name: 'admin_article_publish')]
    public function publish(int $id): Response
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect('/admin/articles');
        }

        $this->articleService->publish($article);
        $this->addFlash('success', 'Article publié avec succès.');

        // HTMX : retourner la ligne mise à jour
        if (HtmxHelper::isHtmxRequest()) {
            // Recharger l'article pour avoir les données fraîches
            $article = Article::find($id);

            return $this->render('admin/article/_partials/_row.ogan', [
                'article' => $article,
                'showFlashOob' => true
            ]);
        }

        return $this->redirect('/admin/articles');
    }

    // ══════════════════════════════════════════════════════════════════════
    // ⏸️ UNPUBLISH - Dépublier un article
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/articles/{id:}/unpublish', methods: ['POST'], name: 'admin_article_unpublish')]
    public function unpublish(int $id): Response
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect('/admin/articles');
        }

        $this->articleService->unpublish($article);
        $this->addFlash('success', 'Article dépublié avec succès.');

        // HTMX : retourner la ligne mise à jour
        if (HtmxHelper::isHtmxRequest()) {
            // Recharger l'article pour avoir les données fraîches
            $article = Article::find($id);

            return $this->render('admin/article/_partials/_row.ogan', [
                'article' => $article,
                'showFlashOob' => true
            ]);
        }

        return $this->redirect('/admin/articles');
    }

    // ══════════════════════════════════════════════════════════════════════
    // 🗑️ DELETE - Suppression d'un article
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/articles/{id:}/delete', methods: ['DELETE', 'POST'], name: 'admin_article_delete')]
    public function delete(int $id): Response
    {
        $article = $this->articleRepository->find($id);

        if ($article) {
            $this->articleService->delete($article);
            $this->addFlash('success', 'Article supprimé avec succès.');
        }

        // HTMX : retourner une réponse vide (la ligne disparaît via outerHTML swap)
        if (HtmxHelper::isHtmxRequest()) {
            return $this->render('admin/article/_partials/_deleted.ogan', [
                'showFlashOob' => true
            ]);
        }

        return $this->redirect('/admin/articles');
    }
}
