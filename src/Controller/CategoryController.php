<?php

/**
 * Category controller.
 */

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * @param Request            $request            request
     * @param CategoryRepository $categoryRepository Category repository
     *
     * @return Response HTTP response
     */
    #[Route(
        name: 'app_category_index',
        methods: ['GET', 'POST']
    )]
    public function index(Request $request, CategoryRepository $categoryRepository): Response
    {
        $page = $request->query->getInt('page', 1);
        $pagination = $this->categoryService->getPaginatedList($page);
        $form = $this->createForm(CategoryType::class, new Category());

        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'pagination' => $pagination,
            'form' => $form->createView(),
        ]);
    }

    /**
     * New action.
     *
     * @param Request                $request       request
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
        $category = new Category();
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->save($category);

            $this->addFlash(
                'success',
                $this->translator->trans('message.created_successfully')
            );

            return $this->redirectToRoute('app_category_index');
        }

        return $this->render(
            'category/create.html.twig',
            ['form' => $form->createView()]
        );
    }

    /**
     * Show action.
     *
     * @param Category $category Category
     *
     * @return Response HTTP response
     */
    #[Route(
        '/{id}',
        name: 'app_category_show',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET']
    )]
    public function show(Category $category): Response
    {
        return $this->render('category/show.html.twig', [
            'category' => $category,
        ]);
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

        if ($form->isSubmitted() && $form->isValid()) {
            $this->categoryService->save($category);

            $this->addFlash(
                'success',
                $this->translator->trans('message.updated_successfully')
            );

            return $this->redirectToRoute('app_category_index');
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
        methods: ['GET', 'DELETE']
    )]
    public function delete(Request $request, Category $category): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

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
