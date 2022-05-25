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
    #[Route('/books/{id}', name: 'app_book')]
    public function index(int $id, BookRepository $bookRepository): Response
    {
        $book = $bookRepository->findOneBy(['id'=>$id]);
        if($book == null) return $this->redirect('/');
        return $this->render('book/index.html.twig', [
            'book' => $book,
        ]);
    }

    #[Route('/books/crud/new', name: 'app_new_book')]
    public function new(Request $request, EntityManagerInterface $entityManager, BookRepository $bookRepository,SluggerInterface $slugger)
    {
        if(!$this->getUser()) return $this->redirect('/');
        $book = new Book();
        $form = $this->createForm(AddBookFormType::class, $book, ['allow_extra_fields' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $time = new \DateTime('now');
            $book->setReadDate($time);
            $book->setAdder($this->getUser());
            $bookFile = $form->get('bookFile')->getData();
            $posterFile = $form->get('posterFile')->getData();

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

            return $this->redirect('/books/'.$addedBook->getId());
        }
        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView(),
            'header' => 'Добавление книги',
        ]);
    }

    #[Route('/books/crud/remove/{id}', name: 'app_remove_book')]
    public function remove(int $id, BookRepository $bookRepository)
    {
        $book = $bookRepository->findOneBy(['id'=> $id]);
        if($this->getUser() !== $book->getAdder()) return $this->redirect('/');
        $bookRepository->remove($book);
        return $this->redirect('/');
    }

    #[Route('/books/crud/update/{id}', name: 'app_update_book')]
    public function update(int $id, Request $request, EntityManagerInterface $entityManager, BookRepository $bookRepository,SluggerInterface $slugger)
    {
        $book = $bookRepository->findOneBy(['id'=> $id]);
        if(!$book) return $this->redirect('/');
        if($this->getUser() !== $book->getAdder()) return $this->redirect('/');
        $form = $this->createForm(AddBookFormType::class, $book,['allow_extra_fields' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $time = new \DateTime('now');
            $book->setReadDate($time);
            $bookFile = $form->get('editedBookFile')->getData();
            $posterFile = $form->get('editedPosterFile')->getData();

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

            $bookRepository->add($book);

            return $this->redirect('/books/'.$book->getId());
        }
        return $this->render('book/new.html.twig', [
            'bookForm' => $form->createView(),
            'header' => 'Обновление книги '.$book->getTitle(),
        ]);
    }

}
