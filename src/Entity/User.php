<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User implements PasswordAuthenticatedUserInterface, UserInterface {
 
  /**
 * @ORM\Id
 * @ORM\Column(type="integer")
 * @ORM\GeneratedValue(strategy="AUTO")
 */
private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $userName;

    /**
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string")
     */
    private $email;

    /**
     * @ORM\Column(type="string")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string")
     */
    private $lastName;

   /**
 * @ORM\Column(type="json", nullable=true)
 */
private $avatar;

    // Getters and setters
    public function getId() {
        return $this->id;
    }

    public function setUserName($userName) {
        $this->userName = $userName;
    }

    public function getUserName() {
        return $this->userName;
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    /**
     * @return string the hashed password for this user
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function getEmail() {
        return $this->email;
    }

    public function setFirstName($firstName) {
        $this->firstName = $firstName;
    }

    public function getFirstName() {
        return $this->firstName;
    }

    public function setLastName($lastName) {
        $this->lastName = $lastName;
    }

    public function getLastName() {
        return $this->lastName;
    }

    public function setAvatar($avatar) {
        $this->avatar = $avatar;
    }

    public function getAvatar() {
        return $this->avatar;
    }

    public function eraseCredentials()
    {
        // Deja este método vacío, ya que no necesitas realizar ninguna acción específica aquí
    }

    public function getUserIdentifier(): string
{
    return $this->getUsername();
}
   


    public function getRoles(): array
    {
        $roles = [];
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }
}
