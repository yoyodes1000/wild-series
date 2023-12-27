<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use App\Repository\SeasonRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\DataFixtures\SeasonFixtures;
use Faker\Factory;


class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        foreach(ProgramFixtures::getTitles() as $program) {
            for($seasonNumber = 1; $seasonNumber < 6; $seasonNumber++){
                for($episodeNumber = 1; $episodeNumber < 11; $episodeNumber++){
                    $episode = new Episode();
                    $episode->setNumber($episodeNumber);
                    $episode->setTitle($faker->realText($faker->numberBetween(10, 45)));
                    $episode->setSynopsis($faker->realText());
                    $episode->setSeason($this->getReference('program_' . $program . 'season_' . $seasonNumber));
                    $manager->persist($episode);
                }
            }
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            SeasonFixtures::class
        ];
    }
}
