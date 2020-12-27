<?php


namespace App\DataFixtures;

use Faker;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use App\Entity\Actor;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;

class ActorFixtures extends Fixture  implements DependentFixtureInterface
{
    const ACTORS = ['Danai Gurira','Andrew Lincoln','Norman Reedus','Lauren Cohan','Isabelle Gelinas','Bruno Salomone','Alexandra Gentil'];

    public function getDependencies()
    {
        return [ProgramFixtures::class];
    }


    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager)
    {
        foreach (self::ACTORS as $key => $actorName) {
            $actor = new Actor();
            $actor->setName($actorName);
            $actor->addProgram($this->getReference('program_0'));
            $actor->addProgram($this->getReference('program_5'));
            $manager->persist($actor);
            $this->addReference('actor_' . $key, $actor);
        }
        $faker  =  Faker\Factory::create('fr_FR');
        for ($i=7; $i<50 ; $i++) {
            $actor = new Actor();
            $actor->setName($faker->name);
            $actor->addProgram($this->getReference('program_'. ceil($i/10)));
            $manager->persist($actor);
            $this->addReference('actor_' . $i, $actor);
        }
        $manager->flush();
    }
}
