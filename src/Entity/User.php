<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity(fields: ['email'], message: 'Пользователь с такой почтой уже зарегестрирован.')]
#[UniqueEntity(fields: ['name'], message: 'Пользователь с таким именем уже зарегестрирован.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    #[Assert\NotBlank(['message'=>'Вы не ввели имя'])]
    #[Assert\Length(['min' => 2, 'minMessage' => 'Имя слишком короткое','max' => 50,
        'maxMessage' => 'Имя слишком длинное'])]
    #[Assert\Regex([
        'pattern' => '/^(?!_)(?!.*_$)(?!.*__)[a-zA-Z0-9_]+$/',
        'match' => true,
        'message' => 'Имя может содержать только латиницу цифры и нижнее подчёркивание.
        Также имя не может начиться и заканчиваться нижним подчёркиванием, и содержать два 
        нижних подчёркивания подряд.',
    ])]
    private $name;

    #[ORM\Column(type: 'json')]
    private $roles = [];

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\NotBlank(['message'=>'Вы не ввели пароль'])]
    #[Assert\Length(['min' => 5, 'minMessage' => 'Пароль слишком короткий','max' => 4000])]
    private $password;

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Email(['message' => 'Вы ввели некорректный e-mail'])]
    #[Assert\NotBlank(['message'=>'Вы не ввели почту'])]
    private $email;

    #[ORM\OneToMany(mappedBy: 'adder', targetEntity: Book::class, orphanRemoval: true)]
    private $books;

    public function __construct()
    {
        $this->books = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->name;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBooks(): Collection
    {
        return $this->books;
    }

    public function addBook(Book $book): self
    {
        if (!$this->books->contains($book)) {
            $this->books[] = $book;
            $book->setAdder($this);
        }

        return $this;
    }

    public function removeBook(Book $book): self
    {
        if ($this->books->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getAdder() === $this) {
                $book->setAdder(null);
            }
        }

        return $this;
    }
}
