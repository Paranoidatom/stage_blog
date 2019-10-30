<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Repository\UserRepository;
use App\Repository\ArticleRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class BlogController extends AbstractController
{
    /** Permet d'afficher un blog
     * 
     * @Route("/blog", name="blog")
     */
    public function index(ArticleRepository $repo)
    {
        $this->denyAccessUnlessGranted('ROLE_USER', null, 'User tried to access a page without having ROLE_ADMIN');
        $user = $this->getUser();
        $articles = $repo->findBy(
            ['author' => $user->getId()],
            ['date' => 'ASC']
        );
        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'user' => $user
        ]);
    }

    /** Permet d'afficher le blog d'un user
     * 
     * @Route("/blog/users/{id}", name="blog_user")
     */
    public function userblog(UserRepository $repo, User $user = null, ArticleRepository $repo2)
    {
        $articles = $repo2->findBy(
            ['author' => $user->getId()],
            ['date' => 'ASC']
        );
        return $this->render('blog/index.html.twig', [
            'articles' => $articles,
            'user' => $user
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home() {
        $date = new \DateTime('2019-10-21');
        dump($date->format("l"));
        return $this->render('blog/home.html.twig');
    }

    /** Permet de créer un nouvel article ou de modifier un article existant
     * 
     * @Route("/blog/new", name="blog_create")
     * @Route("/blog/{id}/edit", name="blog_edit")
     */
    public function form(Article $article = null, Request $request, ObjectManager $manager) {

        if(!$article || ($article && $this->getUser() == $article->getAuthor())) {
        
            if(!$article) {
                $article = new Article();
            }

            $form = $this->createForm(ArticleType::class, $article);
            $user = $this->getUser();
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()) {

                $brochureFile = $form['brochure']->getData();

                if($brochureFile) {
                    $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                    //$safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    $newFilename = $originalFilename.'-'.uniqid().'.'.$brochureFile->guessExtension();

                    $brochureFile->move(
                        $this->getParameter('brochures_directory'),
                        $newFilename
                    );
                    
                    $article->setBrochureFilename($newFilename);
                }

                if(!$article->getID()) {
                    $article->setCreatedAt(new \DateTime());
                    $article->setAuthor($user);
                }

                $manager->persist($article);
                $manager->flush();

                return $this->redirectToRoute('blog_show', ['id' => $article->getId()]);
            }

            return $this->render('blog/create.html.twig', [
                'formArticle' => $form->createView(),
                'articlePhoto' => $article->getBrochureFilename(),
                'editMode' => $article->getId() !== null
            ]);

        }
    }

    /**
     * @Route("/blog/{id}", name="blog_show")
     */
    public function show(Article $article) {
        return $this->render('blog/show.html.twig', [
            'article' => $article
        ]);
    }

    /** Permet de supprimer une annonce
     * 
     * @Route("/blog/{id}/delete", name="blog_delete")
     * @Security("is_granted('ROLE_USER') and user == article.getAuthor()")
     * 
     */
    public function delete(Article $article, ObjectManager $manager) {
        $manager->remove($article);
        $manager->flush();

        return $this->redirectToRoute("blog");
    }

    /** Permet d'afficher les utilisateurs
     * 
     * @Route("/users", name="users_list")
     * 
     */
    public function listusers(UserRepository $repo) {
        $users = $repo->findAll();
        return $this->render('blog/users.html.twig', [
            'users' => $users
        ]);
    }
}
