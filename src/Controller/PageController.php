<?php

namespace App\Controller;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentRepository;
use App\Model\Article;
use Ogan\Config\Config;
use Ogan\Mail\Mailer;
use Ogan\Mail\Email;

class PageController extends AbstractController
{
    private ArticleRepository $articleRepository;
    private CategoryRepository $categoryRepository;
    private CommentRepository $commentRepository;

    public function __construct()
    {
        // Pas d'appel au parent
        $this->articleRepository = new ArticleRepository();
        $this->categoryRepository = new CategoryRepository();
        $this->commentRepository = new CommentRepository();
    }

    #[Route(path: '/blog', methods: ['GET'], name: 'blog')]
    public function blog(): Response
    {
        // Récupérer le filtre catégorie depuis la query string
        $categorySlug = $this->request->query['category'] ?? null;
        $category = null;

        if ($categorySlug) {
            $category = $this->categoryRepository->findBySlug($categorySlug);
        }

        // Récupérer les articles publiés, paginés
        if ($category) {
            $articles = $this->articleRepository->findPublishedByCategory($category->getId(), 9);
        } else {
            $articles = $this->articleRepository->findPublishedPaginated(9);
        }

        // Récupérer toutes les catégories pour le filtre
        $categories = $this->categoryRepository->findAll();

        // Si requête HTMX (pas boosted), retourner juste le partial
        if ($this->isHtmx() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('pages/blog/_partials/_content.ogan', [
                'articles' => $articles,
                'categories' => $categories,
                'currentCategory' => $category
            ]);
        }

        return $this->render('pages/blog.ogan', [
            'title' => 'Blog - Les Créas d\'Elf',
            'articles' => $articles,
            'categories' => $categories,
            'currentCategory' => $category
        ]);
    }

    #[Route(path: '/blog/{slug}', methods: ['GET', 'POST'], name: 'blog_show')]
    public function show(string $slug): Response
    {
        $article = $this->articleRepository->findBySlug($slug);

        if (!$article) {
            $this->addFlash('error', 'Article non trouvé.');
            return $this->redirect('/blog');
        }

        // Formulaire de commentaire
        $commentForm = $this->createForm(\App\Form\CommentFormType::class);
        $commentForm->handleRequest($this->request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $data = $commentForm->getData();

            $comment = new \App\Model\Comment();
            $comment->setArticleId($article->getId());
            $comment->setAuthorName($data['author_name']);
            $comment->setAuthorEmail($data['author_email']);
            $comment->setContent($data['content']);
            $comment->setStatus(\App\Enum\CommentStatus::PENDING->value);
            // On s'assure que la date est au bon format string ou DateTime selon le modèle
            // Le modèle Comment semble utiliser des strings directement dans certains cas,
            // ou Ogan gère ça. Assumons string Y-m-d H:i:s
            $comment->setCreatedAt(date('Y-m-d H:i:s'));

            $comment->save();

            $this->addFlash('success', 'Votre commentaire a été envoyé et est en attente de modération.');

            $isHtmx = $this->request->isHtmx() || isset($_SERVER['HTTP_HX_REQUEST']);
            if ($isHtmx) {
                // Pour HTMX, on renvoie uniquement le partial mis à jour
                // On repart d'un formulaire vierge
                $commentForm = $this->createForm(\App\Form\CommentFormType::class);

                // On récupère les commentaires (page 1)
                $comments = $this->commentRepository->findApprovedPaginated($article->getId(), 5, 1);

                return $this->render('pages/blog/_partials/_comments.ogan', [
                    'comments' => $comments,
                    'commentForm' => $commentForm->createView(),
                    'article' => $article
                ]);
            }

            return $this->redirect('/blog/' . $slug . '#comments-section');
        }

        // Commentaires approuvés (paginés)
        $page = (int) ($this->request->get('page') ?? 1);
        $comments = $this->commentRepository->findApprovedPaginated($article->getId(), 5, $page);

        // Si requête HTMX pour la pagination (avec header spécifique ou paramètre page)
        $isHtmx = $this->request->isHtmx() || isset($_SERVER['HTTP_HX_REQUEST']);
        if ($isHtmx && ($this->request->get('page') || isset($_GET['page']))) {
            return $this->render('pages/blog/_partials/_comments.ogan', [
                'comments' => $comments,
                'commentForm' => $commentForm->createView(),
                'article' => $article
            ]);
        }

        // Récupérer les articles similaires (même catégorie)
        $relatedArticles = [];
        if ($article->getCategoryId()) {
            $relatedArticles = $this->articleRepository->findRelated($article->getId(), $article->getCategoryId(), 3);
        }

        // URL complète pour le partage social
        $urlFull = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . $_SERVER['REQUEST_URI'];

        return $this->render('pages/blog/show.ogan', [
            'title' => $article->getTitle() . ' - Les Créas d\'Elf',
            'article' => $article,
            'tags' => $article->getTags(),
            'relatedArticles' => $relatedArticles,
            'url_full' => $urlFull,
            'comments' => $comments,
            'commentForm' => $commentForm->createView()
        ]);
    }

    #[Route(path: '/contact', methods: ['GET', 'POST'], name: 'contact')]
    public function contact(): Response
    {
        $form = $this->createForm(\App\Form\ContactFormType::class);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            try {
                // Config mailer
                $dsn = Config::get('mailer.dsn') ?? Config::get('mail.dsn', 'smtp://localhost:1025');
                $mailer = new Mailer($dsn);

                // Render email template
                $appName = Config::get('app.name', 'Les Créas d\'Elf');
                $htmlContent = $this->view->render('emails/contact.ogan', [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'subject' => $data['subject'],
                    'message' => $data['message'],
                    'appName' => $appName
                ]);

                // Prepare email
                $toEmail = Config::get('mail.contact_address', 'contact@lescreasdelf.fr');
                $fromEmail = Config::get('mail.from', 'noreply@lescreasdelf.fr');
                if (is_array($fromEmail)) $fromEmail = $fromEmail[0];

                $email = (new Email())
                    ->from($fromEmail, $appName)
                    ->to($toEmail)
                    ->replyTo($data['email'], $data['name'])
                    ->subject('[Contact] ' . $data['subject'])
                    ->html($htmlContent);

                $mailer->send($email);

                $this->addFlash('success', 'Votre message a bien été envoyé ! Je vous répondrai dans les plus brefs délais.');
                return $this->redirect('/contact');
            } catch (\Exception $e) {
                // Log error
                error_log('Contact form error: ' . $e->getMessage());
                $this->addFlash('error', 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer.');
            }
        }

        // Récupérer les derniers articles pour le bas de page
        // On utilise la pagination pour en récupérer 3 simplement
        $latestArticles = $this->articleRepository->findPublishedPaginated(3)->items();

        return $this->render('pages/contact.ogan', [
            'title' => 'Contact - Les Créas d\'Elf',
            'form' => $form->createView(),
            'latestArticles' => $latestArticles
        ]);
    }

    #[Route(path: '/a-propos', methods: ['GET'], name: 'about')]
    public function about(): Response
    {
        return $this->render('pages/about.ogan', [
            'title' => 'À propos - Les Créas d\'Elf'
        ]);
    }

    #[Route(path: '/mentions-legales', methods: ['GET'], name: 'legal')]
    public function legal(): Response
    {
        return $this->render('pages/legal.ogan', [
            'title' => 'Mentions Légales - Les Créas d\'Elf'
        ]);
    }

    #[Route(path: '/confidentialite', methods: ['GET'], name: 'privacy')]
    public function privacy(): Response
    {
        return $this->render('pages/privacy.ogan', [
            'title' => 'Politique de Confidentialité - Les Créas d\'Elf'
        ]);
    }

    /**
     * Helper pour créer un formulaire via le FormFactory injecté
     */
    protected function createForm(string $type, $data = null, array $options = []): \Ogan\Form\FormBuilder
    {
        if (!$this->formFactory) {
            throw new \RuntimeException('FormFactory not initialized in controller');
        }
        $builder = $this->formFactory->create($type, $options);

        if (is_array($data)) {
            $builder->setData($data);
        }

        return $builder;
    }

    /**
     * Vérifie si la requête est une requête HTMX
     */
    private function isHtmx(): bool
    {
        return $this->request->getHeader('HX-Request') === 'true';
    }
}
