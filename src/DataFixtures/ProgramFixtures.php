<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Program;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    const PROGRAMS = [
        [
            'title' => 'Les Simpson',
            'synopsis' => 'Des bonhommes jaunes vivent des aventures loufoques',
            'poster'=> 'Simpson.png',
            'category' => 'category_Animation',
            'country' => 'USA',
            'year' => '1989',
        ],
        [
            'title' => 'The Shield',
            'synopsis' => 'La vie dans un commissariat',
            'poster'=> 'Shield.png',
            'category' => 'category_Action',
            'country' => 'USA',
            'year' => '2002',
        ],
        [
            'title' => 'Murder',
            'synopsis' => 'Des étudiants en droit vivent de droles d\'aventures',
            'poster'=> 'Murder.png',
            'category' => 'category_Aventure',
            'country' => 'USA',
            'year' => '2015',
        ],
        [
            'title' => 'One of us is lying',
            'synopsis' => 'Des lycéens se retrouvent en retenue, un n\'y survivra pas... Mais qui l\'a tué?',
            'poster'=> 'Lying.png',
            'category' => 'category_Aventure',
            'country' => 'USA',
            'year' => '2020',
        ],
        [
            'title' => 'Stranger Things',
            'synopsis' => 'Des ados, des monstres, de la musique des années 80',
            'poster'=> 'Stranger_things.png',
            'category' => 'category_Fantastique',
            'country' => 'USA',
            'year' => '2018',
        ],
        [
            'title' => 'Ash vs Evil Dead',
            'synopsis' => 'Alors qu\'il vit caché depuis 30 ans, Ash se voit contraint de reprendre du service et d\'affronter ses démons. Au sens propre comme au figuré. Mais cette fois, il n\'est plus seul pour combattre les forces du Mal. ',
            'poster'=> 'Evil_Dead.png',
            'category' => 'category_Horreur',
            'country' => 'USA',
            'year' => '2015',
        ],

    ];

    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        foreach (self::PROGRAMS as $programData) {
            $users = ['contributor@monsite.com', 'admin@monsite.com'];
            $program = new Program();
            $program->setTitle($programData['title']);
            $program->setSynopsis($programData['synopsis']);
            $program->setPoster($programData['poster']);
            $program->setCountry($programData['country']);
            $program->setYear($programData['year']);
            $program->setCategory($this->getReference($programData['category']));
            $program->setOwner($this->getReference('user_' . $users[array_rand($users)]));
            $slug = $this->slugger->slug($program->getTitle());
            $program->setSlug($slug);
            $manager->persist($program);
            $this->addReference('program_' . $programData['title'], $program);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class,
        ];
    }

    static function getTitles(): array
    {
        return array_map(fn ($arr) => $arr['title'], self::PROGRAMS);
    }
}