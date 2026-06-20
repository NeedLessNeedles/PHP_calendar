<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\EventRepository;
use App\Service\CategoryServiceInterface;

//#[IsGranted('ROLE_ADMIN')]
#[Route('/category')]
class CategoryController extends AbstractController
{
    public function __construct(
        private readonly CategoryServiceInterface $categoryService
    ) {
    }
    #[Route(
        name: 'app_category_index',
        methods: ['GET', 'POST']
    )]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $form = $this->createForm(CategoryType::class, new Category());

        return $this->render('category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        '/new',
        name: 'app_category_new',
        methods: ['GET', 'POST']
    )]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $category = new Category();
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('app_category_index');
        }

        return $this->redirectToRoute('app_category_index');
    }

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

    #[Route(
        '/{id}/edit',
        name: 'app_category_edit',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['GET', 'POST'],
    )]
    public function edit(Request $request, Category $category, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('category_edit', $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF');
        }

        $title = $request->request->all('category')['title'];
        $this->categoryService->edit($category, $title);

        $em->flush();

        $this->addFlash('success', 'Category updated');

        return $this->redirectToRoute('app_category_index');
    }

    #[Route(
        '/{id}',
        name: 'app_category_delete',
        requirements: ['id' => '[1-9]\d*'],
        methods: ['POST']
    )]
    public function delete(Request $request, Category $category, EntityManagerInterface $em, EventRepository $eventRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            return $this->redirectToRoute('app_category_index');
        }
        $this->categoryService->delete($category);

        return $this->redirectToRoute('app_category_index');
    }
}
