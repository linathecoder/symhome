<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Meuble;
use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $passwordHasher) {}

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles([]);
        $manager->persist($user);

        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $categorie = new Categorie();
        $categorie->setNom('Canapés');
        $manager->persist($categorie);

        $meuble1 = new Meuble();
        $meuble1->setNom('Canapé Luxe');
        $meuble1->setDescription('Très confortable et design');
        $meuble1->setPrix(1500);
        $meuble1->setStock(10);
        $meuble1->setImage('canape1.jpg');
        $meuble1->setCategorie($categorie);
        $manager->persist($meuble1);

        $manager->flush();
    }
}
