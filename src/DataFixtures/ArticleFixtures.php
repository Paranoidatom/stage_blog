<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class ArticleFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder) {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');
        
        for($j=0;$j<3;$j++) {
            $user = new User;
            $hash = $this->encoder->encodePassword($user, 'password');            
            $user->setEmail($faker->email)
                ->setUsername($faker->userName)
                ->setPassword($hash)
                ->setRole("ROLE_USER");
            $manager->persist($user);
            for($i=0;$i<91;$i++) {
                $date = new \DateTime('2019-10-21');
                $date2 = date_add($date, date_interval_create_from_date_string($i.' days'));
                if($date2->format("l") != 'Saturday' && $date2->format("l") != 'Sunday') {
                    $article = new Article();
                    $article->setTitle($faker->sentence())
                            ->setContent('<p>'.join($faker->paragraphs(5), '</p><p>').'</p>')
                            ->setTagline($faker->sentence())
                            ->setCreatedAt(new \DateTime())
                            ->setBrochureFilename('smile_logo.png')
                            ->setDone(0)
                            ->setDate($date2)
                            ->setAuthor($user);
                    $manager->persist($article);
                }
            }
        $manager->flush();
        }
    }
}
