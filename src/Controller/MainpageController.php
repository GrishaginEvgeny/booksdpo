<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Services\PaginationFormer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainpageController extends AbstractController
{
    #[Route('/', name: 'app_mainpage')]
    public function index(BookRepository $bookRepository, Request $request): Response
    {
        $page = $request->query->get('page');
        if(!$page) $page = 1;
        $paginationFormer = new PaginationFormer($page, $bookRepository);
        $books = $bookRepository->findByPage($paginationFormer->getPagination()['actual_page']);
        return $this->render('mainpage/index.html.twig', [
            'controller_name' => 'MainpageController',
            'books' => $books,
            'actual_page' => $paginationFormer->getPagination()['actual_page'],
            'last_page' => $paginationFormer->getPagination()['last_page'],
        ]);
    }
}
        