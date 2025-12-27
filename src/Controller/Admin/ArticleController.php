<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use App\Model\Article;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\TagRepository;
use App\Service\ArticleService;
use App\Security\UserAuthenticator;
use App\Form\ArticleFormType;
use App\Enum\ArticleStatus;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class ArticleController extends AbstractController
{
    private ArticleRepository $articleRepository;
    private CategoryRepository $categoryRepository;
    private TagRepository $tagRepository;
    private ArticleService $articleService;
    private ?UserAuthenticator $auth = null;

    public function __construct()
    {
        $this->articleRepository = new ArticleRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->tagRepository = new TagRepository();
        $this->articleService = new ArticleService();

    }

    private function getAuth(): UserAuthenticator
    {
        if ($this->auth === null) {
            $this->auth = new UserAuthenticator();
        }
        return $this->auth;
    }

    #[Route(path: '/admin/articles', methods: ['GET'], name: 'admin_article_index')]
    public function index(): Response
    {
        $articles = $this->articleRepository->findRecentPaginated(15);

        return $this->render('admin/article/index.ogan', [
            'title' => 'Gestion des articles',
            'articles' => $articles
        ]);
    }

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
                return $this->redirect('/admin/articles/' . $article->id . '/edit');
            }
        }
        return $this->render('admin/article/create.ogan', [
            'title' => 'Nouvel article',
            'form' => $form->createView(),
            'categories' => $categories,
            'tags' => $tags
        ]);
    }

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
                return $this->redirect('/admin/articles');
            }
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
        return $this->redirect('/admin/articles');
    }

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
        return $this->redirect('/admin/articles');
    }

    #[Route(path: '/admin/articles/{id:}/delete', methods: ['POST'], name: 'admin_article_delete')]
    public function delete(int $id): Response
    {
        $article = $this->articleRepository->find($id);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect('/admin/articles');
        }

        $this->articleService->delete($article);

        $this->addFlash('success', 'Article supprimé avec succès.');
        return $this->redirect('/admin/articles');
    }
}
