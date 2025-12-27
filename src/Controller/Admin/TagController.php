<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use App\Model\Tag;
use App\Repository\TagRepository;
use App\Form\TagFormType;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class TagController extends AbstractController
{
    private TagRepository $tagRepository;

    public function __construct()
    {
        $this->tagRepository = new TagRepository();
    }

    #[Route(path: '/admin/tags', methods: ['GET'], name: 'admin_tag_index')]
    public function index(): Response
    {
        $tags = $this->tagRepository->findWithArticleCount();

        return $this->render('admin/tag/index.ogan', [
            'title' => 'Gestion des tags',
            'tags' => $tags
        ]);
    }

    #[Route(path: '/admin/tags/create', methods: ['GET', 'POST'], name: 'admin_tag_create')]
    public function create(): Response
    {
        $form = $this->formFactory->create(TagFormType::class, [
            'action' => '/admin/tags/create',
            'method' => 'POST'
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $tag = new Tag();
                $tag->name = $data['name'];
                $tag->color = $data['color'] ?? '#C07459';
                $tag->created_at = date('Y-m-d H:i:s');

                // Generate slug
                $tag->generateUniqueSlug();
                $tag->save();

                $this->addFlash('success', 'Tag créé avec succès.');
                return $this->redirect('/admin/tags');
            }
        }

        return $this->render('admin/tag/create.ogan', [
            'title' => 'Nouveau tag',
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/admin/tags/{id:}/edit', methods: ['GET', 'POST'], name: 'admin_tag_edit')]
    public function edit(int $id): Response
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            $this->addFlash('error', 'Tag non trouvé.');
            return $this->redirect('/admin/tags');
        }

        $form = $this->formFactory->create(TagFormType::class, [
            'action' => '/admin/tags/' . $id . '/edit',
            'method' => 'POST'
        ]);

        // Pre-fill with current data
        $form->setData([
            'name' => $tag->name,
            'color' => $tag->color
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $tag->name = $data['name'];
                $tag->color = $data['color'] ?? '#C07459';

                // Regenerate slug if name changed
                $tag->regenerateSlug();
                $tag->save();

                $this->addFlash('success', 'Tag modifié avec succès.');
                return $this->redirect('/admin/tags');
            }
        }

        return $this->render('admin/tag/edit.ogan', [
            'title' => 'Modifier le tag',
            'tag' => $tag,
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/admin/tags/{id:}/delete', methods: ['POST'], name: 'admin_tag_delete')]
    public function delete(int $id): Response
    {
        $tag = $this->tagRepository->find($id);

        if (!$tag) {
            $this->addFlash('error', 'Tag non trouvé.');
            return $this->redirect('/admin/tags');
        }

        // Check if tag has articles
        if ($tag->articlesCount() > 0) {
            $this->addFlash('error', 'Impossible de supprimer un tag associé à des articles.');
            return $this->redirect('/admin/tags');
        }

        $tag->delete();

        $this->addFlash('success', 'Tag supprimé avec succès.');
        return $this->redirect('/admin/tags');
    }
}
