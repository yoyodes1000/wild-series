<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    private const USERS = [
        [
            'email' => 'contributor@monsite.com',
            'roles' => ['ROLE_USER'],
            'password' => 'pimpoye_contributor',
        ],
        [
            'email' => 'admin@monsite.com',
            'roles' => ['ROLE_ADMIN'],
            'password' => 'pimpoye_admin',
        ]
    ];

    public function __construct(UserPasswordHasherInterface $passwordHasher){
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        // $product = new Product();
        // $manager->persist($product);

        foreach (self::USERS as $user){
            $newUser = new User();
            $newUser->setEmail($user['email']);
            $newUser->setRoles([$user['roles']]);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $newUser,
                $user['password'],
            );
            $newUser->setPassword($hashedPassword);
            $this->addReference('user_' . $user['email'], $newUser);
            $manager->persist($newUser);
        }

        $manager->flush();
    }
}
