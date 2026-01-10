<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use Ogan\View\Helper\HtmxHelper;
use App\Model\Tag;
use App\Form\TagFormType;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class TagController extends AbstractController
{
    #[Route(path: '/admin/tags', methods: ['GET'], name: 'admin_tag_index')]
    public function index(): Response
    {
        $tags = Tag::latest()->paginate(15);

        // HTMX: retourner uniquement le partial
        if (HtmxHelper::isHtmxRequest() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('admin/tag/_partials/_list.ogan', [
                'tags' => $tags
            ]);
        }

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
                $tag->setName($data['name']);
                $tag->setColor($data['color'] ?? '#C07459');
                $tag->setCreatedAt(new \DateTime());
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

    #[Route(path: '/admin/tags/{id}/edit', methods: ['GET', 'POST'], name: 'admin_tag_edit')]
    public function edit(int $id): Response
    {
        $tag = Tag::find($id);

        if (!$tag) {
            $this->addFlash('error', 'Tag non trouvé.');
            return $this->redirect('/admin/tags');
        }

        $form = $this->formFactory->create(TagFormType::class, [
            'action' => '/admin/tags/' . $id . '/edit',
            'method' => 'POST'
        ]);

        $form->setData([
            'name' => $tag->getName(),
            'color' => $tag->getColor()
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $tag->setName($data['name']);
                $tag->setColor($data['color'] ?? '#C07459');
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

    #[Route(path: '/admin/tags/{id}/delete', methods: ['POST'], name: 'admin_tag_delete')]
    public function delete(int $id): Response
    {
        $tag = Tag::find($id);

        if (!$tag) {
            $this->addFlash('error', 'Tag non trouvé.');
            return $this->redirect('/admin/tags');
        }

        if ($tag->articlesCount() > 0) {
            $this->addFlash('error', 'Impossible de supprimer un tag associé à des articles.');
            return $this->redirect('/admin/tags');
        }

        $tag->delete();
        $this->addFlash('success', 'Tag supprimé avec succès.');

        // HTMX: retourner le partial de suppression
        if (HtmxHelper::isHtmxRequest()) {
            $response = $this->render('admin/_partials/_deleted.ogan', [
                'showFlashOob' => true
            ]);

            if (Tag::count() === 0) {
                $response->setHeader('HX-Trigger', 'reloadTagsList');
            }

            return $response;
        }

        return $this->redirect('/admin/tags');
    }
}
