<?php

/**
 * Tag controller.
 */

namespace App\Controller;

use App\Entity\Tag;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\TagServiceInterface;

/**
 * Class TagController.
 */
#[Route('/tag')]
class TagController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param TagServiceInterface $tagService Tag service
     */
    public function __construct(private readonly TagServiceInterface $tagService)
    {
    }

    /**
     * Index action.
     *
     * @param TagRepository $tagRepository Tag repository
     *
     * @return Response HTTP response
     */
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

    /**
     * New action.
     *
     * @param Request                $request       request
     * @param EntityManagerInterface $entityManager entityManager
     *
     * @return Response HTTP response
     */
    #[Route(
        '/new',
        name: 'app_tag_new',
        methods: ['GET', 'POST']
    )]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tag = new Tag();

        $tag->setTitle($request->request->get('title'));

        if ($tag->getTitle()) {
            $entityManager->persist($tag);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tag_index');
    }

    /**
     * Show action.
     *
     * @param Tag $tag Tag
     *
     * @return Response HTTP response
     */
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

    /**
     * Edit action.
     *
     * @param Request                $request       request
     * @param Tag                    $tag           Tag
     * @param EntityManagerInterface $entityManager entityManager
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'app_tag_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['POST']
    )]
    public function edit(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('tag_edit', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF');
        }

        $title = $request->request->get('title');
        $this->tagService->edit($tag, $title);

        $entityManager->flush();

        $this->addFlash('success', 'Tag updated');

        return $this->redirectToRoute('app_tag_index');
    }

    /**
     * Delete action.
     *
     * @param Request $request request
     * @param Tag     $tag     Tag
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'app_tag_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['POST']
    )]
    public function delete(Request $request, Tag $tag): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete'.$tag->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_tag_index');
        }
        $this->tagService->delete($tag);

        return $this->redirectToRoute('app_tag_index');
    }
}
