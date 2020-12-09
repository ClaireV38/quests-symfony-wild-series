<?php

namespace App\DataFixtures;

use Faker;
use App\Entity\Episode;
use Doctrine\Persistence\ObjectManager;
use \Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface
{

    public function getDependencies()
    {
        return [SeasonFixtures::class];
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        $faker  =  Faker\Factory::create('fr_FR');
        for ($i=1; $i<600 ; $i++) {
            $episode = new Episode();
            $episode->setnumber($i%10 + 1);
            $episode->setTitle($faker->sentence($nbWords = 3, $variableNbWords = true));
            $episode->setSynopsis($faker->text);
            $episode->setSeason($this->getReference('season_'. floor($i/10)));
            $manager->persist($episode);
            $this->addReference('episode_' . $i, $episode);
        }
        $manager->flush();
    }
}