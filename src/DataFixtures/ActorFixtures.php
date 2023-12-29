<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Actor;

class ActorFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 10; $i++){
            $program = ProgramFixtures::getTitles();
            $programKey = array_rand($program, 3);
            $actor = new Actor();
            $actor->setName($faker->firstName() . ' ' . $faker->lastName());

            foreach ($programKey as $key){
                $actor->addProgram($this->getReference('program_' . $program[$key]));
            }
            $manager->persist($actor);
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

