<?php

/**
 * Tag controller.
 */

namespace App\Controller;

use App\Entity\Tag;
use App\Form\TagType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\TagServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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
     * @param TranslatorInterface $translator Translator
     */
    public function __construct(private readonly TagServiceInterface $tagService, private readonly TranslatorInterface $translator)
    {
    }

    /**
     * Index action.
     *
     * @param Request $request request
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_tag_index',
        methods: ['GET']
    )]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $pagination = $this->tagService->getPaginatedList($page);

        return $this->render('tag/index.html.twig', [
            'pagination' => $pagination,
            'tag' => new Tag(),
        ]);
    }

    /**
     * New action.
     *
     * @param Request $request request
     *
     * @return Response HTTP response
     */
    #[Route(
        '/new',
        name: 'app_tag_new',
        methods: ['GET', 'POST']
    )]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $tag = new Tag();
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$this->tagService->canBeEmpty($tag)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.input_fields')
                );

                return $this->redirectToRoute('app_tag_new');
            }

            if (!$this->tagService->isTitleUnique($tag)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.title_already_exists')
                );

                return $this->redirectToRoute('app_tag_new');
            }

            if ($form->isValid()) {
                $this->tagService->save($tag);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('app_tag_index');
            }
        }

        return $this->render(
            'tag/create.html.twig',
            ['form' => $form->createView()]
        );
    }

//    /**
//     * Show action.
//     *
//     * @param Tag $tag Tag
//     *
//     * @return Response HTTP response
//     */
//    #[Route(
//        '/{id}',
//        name: 'app_tag_show',
//        requirements: ['id' => '[1-9]\d*'],
//        methods: ['GET']
//    )]
//    public function show(Tag $tag): Response
//    {
//        return $this->render('tag/show.html.twig', [
//            'tag' => $tag,
//        ]);
//    }

    /**
     * Edit action.
     *
     * @param Request $request request
     * @param Tag     $tag     Tag
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'app_tag_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'PUT'],
    )]
    public function edit(Request $request, Tag $tag): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(
            TagType::class,
            $tag,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('app_tag_edit', ['id' => $tag->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$this->tagService->canBeEmpty($tag)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.input_fields')
                );

                return $this->redirectToRoute('app_tag_edit', [
                    'id' => $tag->getId(),
                ]);
            }

            if (!$this->tagService->isTitleUnique($tag)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.title_already_exists')
                );

                return $this->redirectToRoute(
                    'app_tag_edit',
                    ['id' => $tag->getId()]
                );
            }

            if ($form->isValid()) {
                $this->tagService->save($tag);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('app_tag_index');
            }
        }

        return $this->render(
            'tag/edit.html.twig',
            [
                'form' => $form->createView(),
                'tag' => $tag,
            ]
        );
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
        '/{id}/delete',
        name: 'app_tag_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'DELETE'],
    )]
    public function delete(Request $request, Tag $tag): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(TagType::class, $tag, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('app_tag_delete', ['id' => $tag->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->tagService->delete($tag);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('app_tag_index');
        }

        return $this->render(
            'tag/delete.html.twig',
            [
                'form' => $form->createView(),
                'tag' => $tag,
            ]
        );
    }
}
