<?php

namespace App\Services;

use App\Entity\Book;
use App\Repository\BookRepository;
use Symfony\Bridge\Doctrine\ManagerRegistry;

class PaginationFormer
{
   private $actualPage;
   private $firstPage;
   private $lastPage;

   //объект, который формирует свои поля в зависимости от того, что к нем упридёт
   public function __construct(int $actualPage, BookRepository $bookRepository)
   {
       //кол-во страниц
       $countOfPages = $bookRepository->getCountOfPages();
       //расчёт номера последней страницы
       $this->lastPage = intdiv($countOfPages, 6) + (($countOfPages % 6 === 0) ? 0 : 1);
       //расчёт актуальной страницы
       if($actualPage > $this->lastPage) $this->actualPage = $this->lastPage;
       else $this->actualPage = $actualPage;
   }

   //функция получения полей
    public function getPagination() : array{
       return array(
           'first_page' => $this->firstPage,
           'actual_page' => $this->actualPage,
           'last_page' => $this->lastPage,
       );
    }
}