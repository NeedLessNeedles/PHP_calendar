<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tag')]
class TagController extends AbstractController
{
    #[Route(
        name: 'app_tag_index',
        methods: ['GET', 'POST']
    )]
    public function index(TagRepository $tagRepository): Response
    {
        return $this->render('tag/index.html.twig', [
            'tags' => $tagRepository->findAll(),
            'tag' => new Tag(),
        ]);
    }

    #[Route(
        '/new',
        name: 'app_tag_new',
        methods: ['GET', 'POST']
    )]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tag = new Tag();

        $tag->setTitle($request->request->get('title'));

        if ($tag->getTitle()) {
            $em->persist($tag);
            $em->flush();
        }

        return $this->redirectToRoute('app_tag_index');
    }

    #[Route(
        '/{id}',
        name: 'app_tag_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    public function show(Tag $tag): Response
    {
        return $this->render('tag/show.html.twig', [
            'tag' => $tag,
        ]);
    }

    #[Route(
        '/{id}/edit',
        name: 'app_tag_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['POST']
    )]
    public function edit(Request $request, Tag $tag, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('tag_edit', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF');
        }

        $tag->setTitle($request->request->get('title'));

        $em->flush();

        $this->addFlash('success', 'Tag updated');

        return $this->redirectToRoute('app_tag_index');
    }

    #[Route(
        '/{id}',
        name: 'app_tag_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['POST']
    )]
    public function delete(Request $request, Tag $tag, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete' . $tag->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_tag_index');
        }

        $em->remove($tag);
        $em->flush();

        return $this->redirectToRoute('app_tag_index');
    }
}
