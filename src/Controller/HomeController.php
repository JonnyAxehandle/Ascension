<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route("/", name: 'home')]
    public function index(): \Symfony\Component\HttpFoundation\RedirectResponse
    {
        return $this->redirectToRoute('forums_index');
    }

}