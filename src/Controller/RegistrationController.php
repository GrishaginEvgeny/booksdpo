<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    //запрос на регистрацию
    #[Route('/registration', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        //если зашёл авторизированный юзер ридеерект на главную
        if($this->getUser()) return $this->redirect('/');
        $user = new User();
        //отрисовка формы
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        //проверка на валдиность
        if ($form->isSubmitted() && $form->isValid()) {
            //обновление полей
            $user->setPassword(
            $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user->setRoles(["ROLE_USER"]);
            //добавление в бд
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_mainpage');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
