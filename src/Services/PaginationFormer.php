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

   public function __construct(int $actualPage, BookRepository $bookRepository)
   {
       $countOfPages = $bookRepository->getCountOfPages();
       $this->lastPage = intdiv($countOfPages, 6) + (($countOfPages % 6 === 0) ? 0 : 1);
       if($actualPage > $this->lastPage) $this->actualPage = $this->lastPage;
       else $this->actualPage = $actualPage;
   }

    public function getPagination() : array{
       return array(
           'first_page' => $this->firstPage,
           'actual_page' => $this->actualPage,
           'last_page' => $this->lastPage,
       );
    }
}