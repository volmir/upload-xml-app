<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;

class AuthController extends AbstractController
{
    /**
     *
     * @var Session
     */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }    

    /**
     * @Route("/login", name="login")
     */    
    public function login(Request $request) {
        if ($request->get('email') && $request->get('pwd')) {            
            $entityManager = $this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository(User::class);
            
            $user = $repository->findOneBy([
                'email' => $request->get('email'),
                'password' => $request->get('pwd'),
            ]);
            
            if ($user instanceof User) {
                $user->setLastLogin(new \DateTime());
                $this->session->set('auth', $user->getId());
                
                $entityManager->persist($user);
                $entityManager->flush();
            }
            return $this->redirect('/upload');
        }
        
        return $this->render('auth/login.html.twig', [
                'controller_name' => 'Login',
        ]); 
    }    
    
    /**
     * @Route("/logout", name="logout")
     */    
    public function logout() {
        $this->session->set('auth', false);
        
        return $this->redirect('/');
    }    
}
