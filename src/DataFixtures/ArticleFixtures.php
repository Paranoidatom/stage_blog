<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Article;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create('fr_FR');

        for($i=1;$i<51;$i++) {
            $article = new Article();
            $article->setTitle("Day $i")
                    ->setContent('<p>'.join($faker->paragraphs(5), '</p><p>').'</p>')
                    ->setTagline($faker->sentence())
                    ->setImage($faker->imageUrl())
                    ->setCreatedAt(new \DateTime())
                    ->setBrochureFilename('smile_logo.png');
            $manager->persist($article);
        }
        $manager->flush();
    }
}
