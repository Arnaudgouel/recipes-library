<?php

namespace App\Controller\Admin;

use App\Entity\Unit;
use App\Form\UnitType;
use App\Service\CsvImportService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/unit')]
#[IsGranted('ROLE_ADMIN')]
class UnitController extends AbstractController
{
    #[Route('', name: 'admin_unit_index')]
    public function index(): Response
    {
        return $this->render('admin/unit/index.html.twig', [
            'entityClass' => Unit::class,
        ]);
    }

    #[Route('/new', name: 'admin_unit_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $unit = new Unit();
        $form = $this->createForm(UnitType::class, $unit, ['is_edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($unit);
            $em->flush();
            
            $this->addFlash('success', 'Unité créée avec succès.');
            return $this->redirectToRoute('admin_unit_index');
        }

        return $this->render('admin/unit/form.html.twig', [
            'form' => $form,
            'unit' => $unit,
            'is_edit' => false,
        ]);
    }

    #[Route('/{code}/edit', name: 'admin_unit_edit')]
    public function edit(Unit $unit, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UnitType::class, $unit, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'Unité modifiée avec succès.');
            return $this->redirectToRoute('admin_unit_index');
        }

        return $this->render('admin/unit/form.html.twig', [
            'form' => $form,
            'unit' => $unit,
            'is_edit' => true,
        ]);
    }

    #[Route('/{code}/delete', name: 'admin_unit_delete', methods: ['GET', 'POST'])]
    public function delete(Unit $unit, EntityManagerInterface $em): Response
    {
        $em->remove($unit);
        $em->flush();
        
        $this->addFlash('success', 'Unité supprimée avec succès.');
        return $this->redirectToRoute('admin_unit_index');
    }

    #[Route('/import', name: 'admin_unit_import', methods: ['POST'])]
    public function import(Request $request, CsvImportService $csvImportService): Response
    {
        /** @var UploadedFile|null $file */
        $file = $request->files->get('csv_file');
        
        if ($file) {
            try {
                $count = $csvImportService->importUnits($file);
                $this->addFlash('success', sprintf('%d unités importées avec succès.', $count));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'import : ' . $e->getMessage());
            }
        }

        return $this->redirectToRoute('admin_unit_index');
    }
}

