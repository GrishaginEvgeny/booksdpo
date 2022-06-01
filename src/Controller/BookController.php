<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\AddBookFormType;
use App\Form\RegistrationFormType;
use App\Repository\BookRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class BookController extends AbstractController
{
    //запрос на показ определённой книги
    #[Route('/books/{id}', name: 'app_book')]
    public function index(int $id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->findOneBy(['id'=>$id]);
        if($book == null) return $this->redirect('/');
        return $this->render('book/index.html.twig', [
            'book' => $book,
        ]);
    }

    //запрос на создание новой книги
    #[Route('/books/crud/new', name: 'app_new_book')]
    public function new(Request $request, EntityManagerInterface $entityManager, BookRepository $bookRepository,SluggerInterface $slugger)
    {
        //проверка на авторизировнного пользователя, если его нет то пользователя ридеректит на главную
        if(!$this->getUser()) return $this->redirect('/');
        $book = new Book();
        //отрисовка формы
        $form = $this->createForm(AddBookFormType::class, $book, ['allow_extra_fields' => true]);
        $form->handleRequest($request);
        //действия, когда отправляется валидная форма
        if ($form->isSubmitted() && $form->isValid()) {
            //правильной заполнение незаполненных в форме полей
            $time = new \DateTime('now');
            $book->setReadDate($time);
            $book->setAdder($this->getUser());
            $bookFile = $form->get('bookFile')->getData();
            $posterFile = $form->get('posterFile')->getData();
            //добавление файлов в бд и public
            if ($bookFile) {
                $originalFilename = pathinfo($bookFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$bookFile->guessExtension();
                try {
                    $bookFile->move(
                        $this->getParameter('books_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $book->setBook($newFilename);
            }

            if ($posterFile) {
                $originalFilename = pathinfo($posterFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$posterFile->guessExtension();
                try {
                    $posterFile->move(
                        $this->getParameter('posters_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $book->setPoster($newFilename);
            }

            //отправка в бд
            $entityManager->persist($book);
            $entityManager->flush();

            $addedBook = $bookRepository->findOneBy([
                'title' => $book->getTitle(),
                'genre' => $book->getGenre(),
                'adder' => $book->getAdder(),
                'author' => $book->getAuthor(),
                'readDate' => $book->getReadDate(),
                'poster' => $book->getPoster(),
                'book' => $book->getBook(),
            ]);
            //редирект на страницу созданной книги
            return $this->redirect('/books/'.$addedBook->getId());
        }
        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView(),
            'header' => 'Добавление книги',
        ]);
    }

    //запрос на удаление определённой книги
    #[Route('/books/crud/remove/{id}', name: 'app_remove_book')]
    public function remove(int $id, BookRepository $bookRepository)
    {
        $book = $bookRepository->findOneBy(['id'=> $id]);
        //если создатель книги и пользователь не совпадают, то происходит ридерект на главную без удаления
        if($this->getUser() !== $book->getAdder()) return $this->redirect('/');
        $bookRepository->remove($book);
        return $this->redirect('/');
    }

    //запрос на обновление определённой книги
    #[Route('/books/crud/update/{id}', name: 'app_update_book')]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, BookRepository $bookRepository,SluggerInterface $slugger)
    {
        $book = $bookRepository->findOneBy(['id'=> $id]);
        //если книги с таким Id нет, то редирект на галвную
        if(!$book) return $this->redirect('/');
        //если создатель книги и пользователь не совпадают, то происходит ридерект на главную без обновлденгия
        if($this->getUser() !== $book->getAdder()) return $this->redirect('/');
        //отрисовка формы, которая создаётся из формы создания, но с другими полями для файлов
        $form = $this->createForm(AddBookFormType::class, $book,['allow_extra_fields' => true]);
        $form->handleRequest($request);
        //проверка на валидность формы и действия если форма валидна
        if ($form->isSubmitted() && $form->isValid()) {
            //обновление полей
            $time = new \DateTime('now');
            $book->setReadDate($time);
            $bookFile = $form->get('editedBookFile')->getData();
            $posterFile = $form->get('editedPosterFile')->getData();


            //обновление файлов
            if ($bookFile) {
                $originalFilename = pathinfo($bookFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$bookFile->guessExtension();
                try {
                    $bookFile->move(
                        $this->getParameter('books_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $book->setBook($newFilename);
            }

            if ($posterFile) {
                $originalFilename = pathinfo($posterFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$posterFile->guessExtension();
                try {
                    $posterFile->move(
                        $this->getParameter('posters_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                }
                $book->setPoster($newFilename);
            }

            //обновление
            $bookRepository->add($book);


            //ридерект на страницу книги
            return $this->redirect('/books/'.$book->getId());
        }
        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView(),
            'header' => 'Обновление книги '.$book->getTitle(),
        ]);
    }

}
