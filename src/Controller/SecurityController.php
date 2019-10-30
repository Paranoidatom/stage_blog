<?php

namespace App\Controller;

use Faker\Factory;
use App\Entity\User;
use App\Entity\Article;
use App\Form\RegistrationType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscription", name="security_registration")
     */
    public function registration(Request $request, ObjectManager $manager, UserPasswordEncoderInterface $encoder)
    {
        $user = new User();

        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $faker = \Faker\Factory::create('fr_FR');
            $hash = $encoder->encodePassword($user, $user->getPassword());
            $user->SetPassword($hash);
            $user->SetRole("ROLE_USER");
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

            return $this->redirectToRoute('blog');
        }

        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/connexion", name="security_login")
     */
    public function login(AuthenticationUtils $authenticationUtils) {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout() {
        
    }
}
