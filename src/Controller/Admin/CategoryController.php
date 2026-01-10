<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use Ogan\View\Helper\HtmxHelper;
use App\Model\Category;
use App\Form\CategoryFormType;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class CategoryController extends AbstractController
{
    #[Route(path: '/admin/categories', methods: ['GET'], name: 'admin_category_index')]
    public function index(): Response
    {
        $categories = Category::latest()->paginate(15);

        // HTMX: retourner uniquement le partial
        if (HtmxHelper::isHtmxRequest() && !$this->request->getHeader('HX-Boosted')) {
            return $this->render('admin/category/_partials/_list.ogan', [
                'categories' => $categories
            ]);
        }

        return $this->render('admin/category/index.ogan', [
            'title' => 'Gestion des catégories',
            'categories' => $categories
        ]);
    }

    #[Route(path: '/admin/categories/create', methods: ['GET', 'POST'], name: 'admin_category_create')]
    public function create(): Response
    {
        $form = $this->formFactory->create(CategoryFormType::class, [
            'action' => '/admin/categories/create',
            'method' => 'POST'
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $category = new Category();
                $category->setName($data['name']);
                $category->setDescription($data['description'] ?? null);
                $category->setCreatedAt(new \DateTime());
                $category->setUpdatedAt(new \DateTime());
                $category->generateUniqueSlug();
                $category->save();

                $this->addFlash('success', 'Catégorie créée avec succès.');
                return $this->redirect('/admin/categories');
            }
        }

        return $this->render('admin/category/create.ogan', [
            'title' => 'Nouvelle catégorie',
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/admin/categories/{id}/edit', methods: ['GET', 'POST'], name: 'admin_category_edit')]
    public function edit(int $id): Response
    {
        $category = Category::find($id);

        if (!$category) {
            $this->addFlash('error', 'Catégorie non trouvée.');
            return $this->redirect('/admin/categories');
        }

        $form = $this->formFactory->create(CategoryFormType::class, [
            'action' => '/admin/categories/' . $id . '/edit',
            'method' => 'POST'
        ]);

        $form->setData([
            'name' => $category->getName(),
            'description' => $category->getDescription()
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $category->setName($data['name']);
                $category->setDescription($data['description'] ?? null);
                $category->setUpdatedAt(new \DateTime());
                $category->regenerateSlug();
                $category->save();

                $this->addFlash('success', 'Catégorie modifiée avec succès.');
                return $this->redirect('/admin/categories');
            }
        }

        return $this->render('admin/category/edit.ogan', [
            'title' => 'Modifier la catégorie',
            'category' => $category,
            'form' => $form->createView()
        ]);
    }

    #[Route(path: '/admin/categories/{id}/delete', methods: ['POST'], name: 'admin_category_delete')]
    public function delete(int $id): Response
    {
        $category = Category::find($id);

        if (!$category) {
            $this->addFlash('error', 'Catégorie non trouvée.');
            return $this->redirect('/admin/categories');
        }

        if ($category->articlesCount() > 0) {
            $this->addFlash('error', 'Impossible de supprimer une catégorie contenant des articles.');
            return $this->redirect('/admin/categories');
        }

        $category->delete();
        $this->addFlash('success', 'Catégorie supprimée avec succès.');

        // HTMX: retourner le partial de suppression
        if (HtmxHelper::isHtmxRequest()) {
            $response = $this->render('admin/_partials/_deleted.ogan', [
                'showFlashOob' => true
            ]);

            // Rechargement si liste vide
            if (Category::count() === 0) {
                $response->setHeader('HX-Trigger', 'reloadCategoriesList');
            }

            return $response;
        }

        return $this->redirect('/admin/categories');
    }
}
