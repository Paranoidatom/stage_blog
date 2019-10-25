<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home2")
     */
    public function index()
    {
        $noms = $repo->findAll();
        return $this->render('home/index2.html.twig', [
            'controller_name' => 'HomeController'
        ]);
    }
}
