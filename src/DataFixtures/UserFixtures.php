<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    public function load(ObjectManager $manager)
    {
        // Création d’un utilisateur de type “contributeur” (= auteur)
        $contributor1 = new User();
        $contributor1->setEmail('contributor1@monsite.com');
        $contributor1->setRoles(['ROLE_CONTRIBUTOR']);
        $contributor1->setName('contributor1');
        $contributor1->setBio('toute ma vie j\'ai contribué contribué et fait des commentaires');
        $contributor1->setPassword($this->passwordEncoder->encodePassword(
            $contributor1,
            'contributorpassword'
        ));

        $manager->persist($contributor1);
        $this->addReference('contributor1', $contributor1);

        // Création d’un utilisateur de type “contributeur” (= auteur)
        $contributor2 = new User();
        $contributor2->setEmail('contributor2@monsite.com');
        $contributor2->setRoles(['ROLE_CONTRIBUTOR']);
        $contributor2->setName('contributor2');
        $contributor2->setBio('toute ma vie j\'ai bcp contribué et fait des commentaires');
        $contributor2->setPassword($this->passwordEncoder->encodePassword(
            $contributor2,
            'contributorpassword'
        ));

        $manager->persist($contributor2);
        $this->addReference('contributor2', $contributor2);

        // Création d’un utilisateur de type “administrateur”
        $admin = new User();
        $admin->setEmail('admin@monsite.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordEncoder->encodePassword(
            $admin,
            'adminpassword'
        ));

        $manager->persist($admin);
        $this->addReference('admin', $admin);
        // Sauvegarde des 2 nouveaux utilisateurs :
        $manager->flush();
    }
}
