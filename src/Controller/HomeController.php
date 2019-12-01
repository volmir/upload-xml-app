<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Report;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $reports = $entityManager->getRepository(Report::class)->findAll();
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'Upload XML',
            'reports' => $reports,
        ]);
    }
}
