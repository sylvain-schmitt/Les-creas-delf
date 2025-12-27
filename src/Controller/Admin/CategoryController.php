<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use App\Model\Category;
use App\Repository\CategoryRepository;
use App\Form\CategoryFormType;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class CategoryController extends AbstractController
{
    private CategoryRepository $categoryRepository;

    public function __construct()
    {
        $this->categoryRepository = new CategoryRepository();
    }

    #[Route(path: '/admin/categories', methods: ['GET'], name: 'admin_category_index')]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findWithArticleCount();

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
                $category->name = $data['name'];
                $category->description = $data['description'] ?? null;
                $category->created_at = date('Y-m-d H:i:s');
                $category->updated_at = date('Y-m-d H:i:s');

                // Generate slug
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

    #[Route(path: '/admin/categories/{id:}/edit', methods: ['GET', 'POST'], name: 'admin_category_edit')]
    public function edit(int $id): Response
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            $this->addFlash('error', 'Catégorie non trouvée.');
            return $this->redirect('/admin/categories');
        }

        $form = $this->formFactory->create(CategoryFormType::class, [
            'action' => '/admin/categories/' . $id . '/edit',
            'method' => 'POST'
        ]);

        // Pre-fill with current data
        $form->setData([
            'name' => $category->name,
            'description' => $category->description
        ]);

        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $data = $form->getData();

                $category->name = $data['name'];
                $category->description = $data['description'] ?? null;
                $category->updated_at = date('Y-m-d H:i:s');

                // Regenerate slug if name changed
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

    #[Route(path: '/admin/categories/{id:}/delete', methods: ['POST'], name: 'admin_category_delete')]
    public function delete(int $id): Response
    {
        $category = $this->categoryRepository->find($id);

        if (!$category) {
            $this->addFlash('error', 'Catégorie non trouvée.');
            return $this->redirect('/admin/categories');
        }

        // Check if category has articles
        if ($category->articlesCount() > 0) {
            $this->addFlash('error', 'Impossible de supprimer une catégorie contenant des articles.');
            return $this->redirect('/admin/categories');
        }

        $category->delete();

        $this->addFlash('success', 'Catégorie supprimée avec succès.');
        return $this->redirect('/admin/categories');
    }
}
