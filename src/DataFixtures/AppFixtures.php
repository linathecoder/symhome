<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Meuble;
use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword(password_hash('password123', PASSWORD_DEFAULT));
        $user->setRoles([]);
        $manager->persist($user);

        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setPassword(password_hash('admin123', PASSWORD_DEFAULT));
        $admin->setRoles(['ROLE_ADMIN']);
        $manager->persist($admin);

        $categoriesData = [
            ['nom' => 'Canapés', 'description' => 'Canapés design et confort premium pour votre salon.'],
            ['nom' => 'Tables', 'description' => 'Tables élégantes et robustes pour toutes les pièces.'],
            ['nom' => 'Chaises', 'description' => 'Chaises stylées et confortables pour repas et bureau.'],
            ['nom' => 'Lits', 'description' => 'Lits luxueux pour des nuits reposantes.'],
            ['nom' => 'Bureaux', 'description' => 'Espaces de travail fonctionnels et modernes pour bureau et télétravail.'],
            ['nom' => 'Rangements', 'description' => 'Solutions de rangement pratiques et raffinées pour toute la maison.'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $categorie = new Categorie();
            $categorie->setNom($data['nom']);
            $categorie->setDescription($data['description']);
            $manager->persist($categorie);
            $categories[$data['nom']] = $categorie;
        }

        $meublesData = [
            ['nom' => 'Canapé Luxe', 'description' => 'Canapé moderne 3 places en velours', 'prix' => 1790.00, 'stock' => 8, 'image' => 'canape1.png', 'categorie' => 'Canapés'],
            ['nom' => 'Canapé Cuir Élégant', 'description' => 'Canapé en cuir premium avec assise profonde', 'prix' => 2190.00, 'stock' => 5, 'image' => 'canape2.png', 'categorie' => 'Canapés'],
            ['nom' => 'Table à Manger Oslo', 'description' => 'Table scandinave en chêne massif', 'prix' => 1290.00, 'stock' => 6, 'image' => 'table1.png', 'categorie' => 'Tables'],
            ['nom' => 'Table Épure', 'description' => 'Table ronde en verre et chêne blanchi', 'prix' => 1090.00, 'stock' => 7, 'image' => 'table2.png', 'categorie' => 'Tables'],
            ['nom' => 'Chaise Minimaliste', 'description' => 'Chaise design légère et confortable', 'prix' => 190.00, 'stock' => 14, 'image' => 'chaise1.png', 'categorie' => 'Chaises'],
            ['nom' => 'Chaise Lounge', 'description' => 'Chaise d’appoint cosy au style contemporain', 'prix' => 250.00, 'stock' => 12, 'image' => 'chaise2.png', 'categorie' => 'Chaises'],
            ['nom' => 'Lit Contemporain', 'description' => 'Lit avec tête de lit rembourrée et rangements', 'prix' => 2390.00, 'stock' => 4, 'image' => 'lit1.png', 'categorie' => 'Lits'],
            ['nom' => 'Lit King Size Oslo', 'description' => 'Lit premium King Size avec matelas intégré', 'prix' => 2890.00, 'stock' => 3, 'image' => 'lit2.png', 'categorie' => 'Lits'],
            ['nom' => 'Bureau Executive', 'description' => 'Bureau modulable avec surface en noyer', 'prix' => 1490.00, 'stock' => 9, 'image' => 'bureau1.png', 'categorie' => 'Bureaux'],
            ['nom' => 'Étagère Urban', 'description' => 'Étagère murale en métal et bois', 'prix' => 690.00, 'stock' => 11, 'image' => 'etagere1.png', 'categorie' => 'Rangements'],
            ['nom' => 'Commode Élégante', 'description' => 'Commode spacieuse avec finitions dorées', 'prix' => 890.00, 'stock' => 5, 'image' => 'commode1.png', 'categorie' => 'Rangements'],
        ];

        foreach ($meublesData as $data) {
            $meuble = new Meuble();
            $meuble->setNom($data['nom']);
            $meuble->setDescription($data['description']);
            $meuble->setPrix($data['prix']);
            $meuble->setStock($data['stock']);
            $meuble->setImage($data['image']);
            $meuble->setCategorie($categories[$data['categorie']]);
            $manager->persist($meuble);
        }

        $manager->flush();
    }
}
