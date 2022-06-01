<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    //запрос к странице определенного юзера
    #[Route('/users/{id}', name: 'app_profile')]
    public function index(UserRepository $userRepository, BookRepository $bookRepository, int $id): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        $countOfBooks = count($bookRepository->findBy(['adder' => $user]));
        return $this->render('profile/index.html.twig', [
            'user' => $user,
            'controller_name' => 'ProfileController',
            'countOfBooks' => $countOfBooks,
        ]);
    }
}
