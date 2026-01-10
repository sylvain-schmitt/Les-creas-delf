<?php

namespace App\Controller\Admin;

use Ogan\Controller\AbstractController;
use Ogan\Http\Response;
use Ogan\Router\Attributes\Route;
use Ogan\Security\Attribute\IsGranted;
use App\Repository\SettingRepository;
use App\Form\SettingGroupFormType;
use App\Model\Media;

#[IsGranted('ROLE_ADMIN', message: 'Accès réservé aux administrateurs.')]
class SettingController extends AbstractController
{
    private SettingRepository $settingRepository;

    public function __construct()
    {
        $this->settingRepository = new SettingRepository();
    }

    // ══════════════════════════════════════════════════════════════════════
    // 📋 INDEX - Liste des groupes de settings
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/settings', methods: ['GET'], name: 'admin_setting_index')]
    public function index(): Response
    {
        $groups = $this->settingRepository->getGroups();

        // Construire les données des groupes
        $groupsData = [];
        foreach ($groups as $group) {
            $groupsData[] = [
                'name' => $group,
                'label' => $this->settingRepository->getGroupLabel($group),
                'icon' => $this->settingRepository->getGroupIcon($group),
                'count' => $this->settingRepository->countByGroup($group)
            ];
        }

        return $this->render('admin/setting/index.ogan', [
            'title' => 'Paramètres du site',
            'groups' => $groupsData
        ]);
    }

    // ══════════════════════════════════════════════════════════════════════
    // ✏️ EDIT - Édition des settings d'un groupe
    // ══════════════════════════════════════════════════════════════════════

    #[Route(path: '/admin/settings/{group}', methods: ['GET', 'POST'], name: 'admin_setting_edit')]
    public function edit(string $group): Response
    {
        $settings = $this->settingRepository->findByGroup($group);

        if (empty($settings)) {
            $this->addFlash('error', 'Groupe de paramètres non trouvé.');
            return $this->redirect('/admin/settings');
        }

        // Préparer les données des images pour le media picker
        $settingsWithMedia = [];
        $imageSettings = [];
        foreach ($settings as $setting) {
            $currentImage = null;
            if ($setting->getType() === 'image' && $setting->getValue()) {
                $currentImage = Media::find((int) $setting->getValue());
            }
            $settingsWithMedia[] = [
                'setting' => $setting,
                'currentImage' => $currentImage
            ];

            // Garder trace des settings image pour le template
            if ($setting->getType() === 'image') {
                $imageSettings[] = [
                    'setting' => $setting,
                    'currentImage' => $currentImage
                ];
            }
        }

        // Créer le formulaire via formFactory
        $form = $this->formFactory->create(SettingGroupFormType::class, [
            'action' => '/admin/settings/' . $group,
            'method' => 'POST',
            'settingsWithMedia' => $settingsWithMedia
        ]);

        // Pre-fill avec les valeurs actuelles
        $initialData = [];
        foreach ($settings as $setting) {
            if ($setting->getType() !== 'image') {
                $initialData[$setting->getKey()] = $setting->getValue();
            }
        }
        $form->setData($initialData);

        // Traitement du formulaire
        if ($this->request->isMethod('POST')) {
            $form->handleRequest($this->request);

            if ($form->isSubmitted() && $form->isValid()) {
                $formData = $form->getData();
                $postData = $this->request->post;

                foreach ($settings as $setting) {
                    $key = $setting->getKey();

                    // Pour les images : vérifier dans POST directement
                    if ($setting->getType() === 'image') {
                        if (array_key_exists($key, $postData)) {
                            $value = $postData[$key] ?? null;
                            if ($value === '') {
                                $value = null;
                            }
                            $setting->setValue($value);
                            $setting->setUpdatedAt(new \DateTime());
                            $setting->save();
                        }
                    } else {
                        // Pour les autres types : utiliser les données du formulaire
                        if (array_key_exists($key, $formData)) {
                            $setting->setValue($formData[$key]);
                            $setting->setUpdatedAt(new \DateTime());
                            $setting->save();
                        }
                    }
                }

                $this->settingRepository->clearCache();
                $this->addFlash('success', 'Paramètres enregistrés avec succès.');

                return $this->redirect('/admin/settings/' . $group);
            }
        }

        // Préparer les champs pour le template (avec leurs clés)
        $formView = $form->createView();
        $formFields = [];
        foreach ($settings as $setting) {
            if ($setting->getType() !== 'image') {
                $key = $setting->getKey();
                $formFields[$key] = [
                    'key' => $key,
                    'label' => $setting->getLabel(),
                    'type' => $setting->getType()
                ];
            }
        }

        return $this->render('admin/setting/edit.ogan', [
            'title' => $this->settingRepository->getGroupLabel($group),
            'group' => $group,
            'groupLabel' => $this->settingRepository->getGroupLabel($group),
            'settings' => $settings,
            'settingsWithMedia' => $settingsWithMedia,
            'imageSettings' => $imageSettings,
            'form' => $formView,
            'formFields' => $formFields
        ]);
    }
}
