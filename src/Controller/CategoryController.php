<?php

/**
 * Category controller.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\CategoryServiceInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class CategoryController.
 */
#[Route('/category')]
class CategoryController extends AbstractController
{
    /**
     * Constructor.
     *
     * @param CategoryServiceInterface $categoryService Category service
     * @param TranslatorInterface      $translator      Translator
     */
    public function __construct(private readonly CategoryServiceInterface $categoryService, private readonly TranslatorInterface $translator)
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
        name: 'app_category_index',
        methods: ['GET']
    )]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $pagination = $this->categoryService->getPaginatedList($page);

        return $this->render('category/index.html.twig', [
            'pagination' => $pagination,
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
        name: 'app_category_new',
        methods: ['GET', 'POST']
    )]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$this->categoryService->canBeEmpty($category)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.input_fields')
                );

                return $this->redirectToRoute('app_category_new');
            }

            if (!$this->categoryService->isTitleUnique($category)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.title_already_exists')
                );

                return $this->redirectToRoute('app_category_new');
            }

            if ($form->isValid()) {
                $this->categoryService->save($category);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('app_category_index');
            }
        }

        return $this->render(
            'category/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Edit action.
     *
     * @param Request  $request  request
     * @param Category $category Category
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/edit',
        name: 'app_category_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'PUT'],
    )]
    public function edit(Request $request, Category $category): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $form = $this->createForm(
            CategoryType::class,
            $category,
            [
                'method' => 'PUT',
                'action' => $this->generateUrl('app_category_edit', ['id' => $category->getId()]),
            ]
        );
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if (!$this->categoryService->canBeEmpty($category)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.input_fields')
                );

                return $this->redirectToRoute('app_category_edit', [
                    'id' => $category->getId(),
                ]);
            }

            if (!$this->categoryService->isTitleUnique($category)) {
                $this->addFlash(
                    'warning',
                    $this->translator->trans('message.title_already_exists')
                );

                return $this->redirectToRoute(
                    'app_category_edit',
                    ['id' => $category->getId()]
                );
            }

            if ($form->isValid()) {
                $this->categoryService->save($category);

                $this->addFlash(
                    'success',
                    $this->translator->trans('message.created_successfully')
                );

                return $this->redirectToRoute('app_category_index');
            }
        }

        return $this->render(
            'category/edit.html.twig',
            [
                'form' => $form->createView(),
                'category' => $category,
            ]
        );
    }

    /**
     * Delete action.
     *
     * @param Request  $request  request
     * @param Category $category Category
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}/delete',
        name: 'app_category_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'DELETE'],
    )]
    public function delete(Request $request, Category $category): Response
    {
        if (!$this->categoryService->canBeDeleted($category)) {
            $this->addFlash(
                'warning',
                $this->translator->trans('message.category_contains_events')
            );

            return $this->redirectToRoute('app_category_index');
        }

        $form = $this->createForm(CategoryType::class, $category, [
            'method' => 'DELETE',
            'action' => $this->generateUrl('app_category_delete', ['id' => $category->getId()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->delete($category);

            $this->addFlash(
                'success',
                $this->translator->trans('message.deleted_successfully')
            );

            return $this->redirectToRoute('app_category_index');
        }

        return $this->render(
            'category/delete.html.twig',
            [
                'form' => $form->createView(),
                'category' => $category,
            ]
        );
    }
}
