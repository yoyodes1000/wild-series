<?php

namespace App\DataFixtures;

use App\Entity\Actor;
use App\Entity\Program;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    public function load(ObjectManager $manager): void
    {
        $faker = \Faker\Factory::create('fr_Fr');

        // Création de 10 acteurs
        for ($i = 0; $i < 10; $i++) {
            $actor = new Actor();
            $actor->setName($faker->firstName . ' ' . $faker->lastName);
            $actor->setPicture($faker->imageUrl(100, 250, 'people', true));

            // Utilisation du SluggerInterface pour générer un slug à partir du nom de l'acteur
            $slug = $this->slugger->slug($actor->getName())->lower();
            $actor->setSlug($slug);

            $manager->persist($actor);

            // Ajout aléatoire de 3 programmes à chaque acteur
            $programs = $manager->getRepository(Program::class)->findAll();
            $randomPrograms = $faker->randomElements($programs, 3);

            foreach ($randomPrograms as $program) {
                $actor->addProgram($program);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ProgramFixtures::class,
        ];
    }
}