<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    private const USERS = [
        [
            'email' => 'admin@monsite.com',
        'password' => 'admin',
        'roles' => ['ROLE_ADMIN'],
        ],
        [
            'email' => 'contributor@monsite.com',
            'password' => 'contributor',
            'roles' => ['ROLE_CONTRIBUTOR'],
        ],
    ];

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::USERS as $user) {
            $newUser = new User();
            $newUser->setEmail($user['email']);
            $newUser->setRoles($user['roles']);
            $hashedPassword = $this->passwordHasher->hashPassword(
                $newUser,
                $user['password']
            );
            $newUser->setPassword($hashedPassword);
            $manager->persist($newUser);
        }
        $manager->flush();
    }
}
