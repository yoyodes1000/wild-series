<?php

namespace App\DataFixtures;


use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Program;

class ProgramFixtures extends Fixture implements DependentFixtureInterface
{
    const PROGRAMS = [
        [
            'title' => 'Les Simpson',
            'synopsis' => 'Des bonhommes jaunes vivent des aventures loufoques',
            'poster'=> 'https://pixabay.com/fr/photos/simpson-lego-famille-bart-hom%C3%A8re-4082521/',
            'category' => 'category_Animation'
        ],
        [
            'title' => 'The Shield',
            'synopsis' => 'La vie dans un commissariat',
            'poster'=> 'https://pixabay.com/fr/illustrations/doigt-empreinte-digitale-s%C3%A9curit%C3%A9-2081169/',
            'category' => 'category_Action'
        ],
        [
            'title' => 'Murder',
            'synopsis' => 'Des étudiants en droit vivent de droles d\'aventures',
            'poster'=> 'https://pixabay.com/fr/illustrations/ai-g%C3%A9n%C3%A9r%C3%A9-jack-l%C3%A9ventreur-sc%C3%A9l%C3%A9rat-7867113/',
            'category' => 'category_Aventure'
        ],
        [
            'title' => 'One of us is lying',
            'synopsis' => 'Des lycéens se retrouvent en retenue, un n\'y survivra pas... Mais qui l\'a tué?',
            'poster'=> 'https://pixabay.com/fr/vectors/menteur-mensonge-v%C3%A9rit%C3%A9-pinocchio-7074360/',
            'category' => 'category_Aventure'
        ],
        [
            'title' => 'Stranger Things',
            'synopsis' => 'Des ados, des monstres, de la musique des années 80',
            'poster'=> 'https://pixabay.com/fr/illustrations/choses-%C3%A9tranges-fond-d%C3%A9cran-7318215/',
            'category' => 'category_Fantastique'
        ],
        [
            'title' => 'Ash vs Evil Dead',
            'synopsis' => 'Alors qu\'il vit caché depuis 30 ans, Ash se voit contraint de reprendre du service et d\'affronter ses démons. Au sens propre comme au figuré. Mais cette fois, il n\'est plus seul pour combattre les forces du Mal. ',
            'poster'=> 'https://pixabay.com/fr/illustrations/choses-%C3%A9tranges-fond-d%C3%A9cran-7318215/',
            'category' => 'category_Horreur'
        ],

    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::PROGRAMS as $programData) {
            $program = new Program();
            $program->setTitle($programData['title']);
            $program->setSynopsis($programData['synopsis']);
            $program->setPoster($programData['poster']);
            $program->setCategory($this->getReference($programData['category']));
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