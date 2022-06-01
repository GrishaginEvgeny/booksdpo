<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    //создаём объект сидов с нужными нам сервисами, тут это хэшэр паролей
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    //сиды
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $users = [];
        $books = [];
        $genres = ['Фантастика', 'Триллер', 'Научная литература','Роман','Детектив'];
        $authors =['Author1','Author2','Author3','Author4','Author5','Author6','Author7','Author8','Author9','Author10'];

        for ($i = 0; $i < 5; $i++) {
            $user = new User();
            $user->setName('Bob'.($i+1));
            $user->setEmail('bobmail'.($i+1).'@mail.test');
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->hasher->hashPassword($user, 'test'));
            $manager->persist($user);
            array_push($users, $user);
        }

        for ($i = 0; $i < 20; $i++) {
            $book = new Book();
            $book->setAdder($users[($i) % 5]);
            $book->setBook(($i % 5 + 1).'.txt');
            $book->setGenre($genres[($i+1) % 5]);
            $book->setPoster(($i % 5 + 1).'.jpg');
            $book->setReadDate(new \DateTime());
            $book->setTitle('Книга'.($i+1));
            $book->setAuthor($authors[$i % 10]);
            $manager->persist($book);
            array_push($books, $book);
        }

        $manager->flush();
    }
}
