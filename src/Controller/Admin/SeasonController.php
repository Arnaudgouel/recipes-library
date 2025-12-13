<?php

namespace App\Controller\Admin;

use App\Entity\Season;
use App\Form\SeasonType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/season')]
#[IsGranted('ROLE_ADMIN')]
class SeasonController extends AbstractController
{
    #[Route('', name: 'admin_season_index')]
    public function index(): Response
    {
        return $this->render('admin/season/index.html.twig', [
            'entityClass' => Season::class,
        ]);
    }

    #[Route('/new', name: 'admin_season_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($season);
            $em->flush();
            
            $this->addFlash('success', 'Saison créée avec succès.');
            return $this->redirectToRoute('admin_season_index');
        }

        return $this->render('admin/season/form.html.twig', [
            'form' => $form,
            'season' => $season,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_season_edit')]
    public function edit(Season $season, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            
            $this->addFlash('success', 'Saison modifiée avec succès.');
            return $this->redirectToRoute('admin_season_index');
        }

        return $this->render('admin/season/form.html.twig', [
            'form' => $form,
            'season' => $season,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_season_delete', methods: ['GET', 'POST'])]
    public function delete(Season $season, EntityManagerInterface $em): Response
    {
        $em->remove($season);
        $em->flush();
        
        $this->addFlash('success', 'Saison supprimée avec succès.');
        return $this->redirectToRoute('admin_season_index');
    }
}

