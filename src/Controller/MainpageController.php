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
    //запрос на главную
    #[Route('/', name: 'app_mainpage')]
    public function index(BookRepository $bookRepository, Request $request): Response
    {
        //получаю номер страницу из get параметров
        $page = $request->query->get('page');
        //если get параметра нет то будет выведена 1 страница
        if(!$page) $page = 1;
        //обращение к сервису формирующим номера страниц для пагинации
        $paginationFormer = new PaginationFormer($page, $bookRepository);
        //обращение к репозиторию для получения книг по странице
        $books = $bookRepository->findByPage($paginationFormer->getPagination()['actual_page']);
        return $this->render('mainpage/index.html.twig', [
            'controller_name' => 'MainpageController',
            'books' => $books,
            'actual_page' => $paginationFormer->getPagination()['actual_page'],
            'last_page' => $paginationFormer->getPagination()['last_page'],
        ]);
    }
}
        