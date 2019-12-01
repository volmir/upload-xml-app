<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Entity\Report;
use App\Entity\User;

class UploadController extends AbstractController {

    /**
     *
     * @var Session
     */
    private $session;

    /**
     *
     * @var \Swift_Mailer
     */
    private $mailer;

    public function __construct(
    SessionInterface $session, \Swift_Mailer $mailer
    ) {
        $this->session = $session;
        $this->mailer = $mailer;
    }

    /**
     * @Route("/upload", name="upload_form", methods={"GET"})
     * @param Request $request
     */
    public function index() {
        if ($this->session->get('auth')) {
            return $this->render('upload/index.html.twig', [
                        'controller_name' => 'Upload report',
            ]);
        } else {
            return $this->redirect('/login');
        }
    }

    /**
     * @Route("/upload", name="upload_report", methods={"POST"})
     * @param Request $request
     */
    public function upload(Request $request, ValidatorInterface $validator) {
        if ($this->session->get('auth')) {
            /** $file UploadedFile */
            $file = $request->files->get('file');
            if ($file->getMimeType() != 'text/xml') {
                throw new \Exception('Incorrect file type');
            }

            /** $user User */
            $user = $this->getDoctrine()
                    ->getRepository(User::class)
                    ->find($this->session->get('auth'));
            if (!$user instanceof User) {
                throw new \Exception('User not found');
            }            

            $this->validateXml($file, $user);
            $this->addReport($file, $request, $validator, $user);   
            
            $this->addFlash('success', 'XML Report created!');

            return $this->redirect('/');
        } else {
            return $this->redirect('/login');
        }
    }    

    /**
     * 
     * @param UploadedFile $file
     * @param User $user
     * @throws \Exception
     */
    private function validateXml(UploadedFile $file, User $user) {
        libxml_use_internal_errors(true);

        $xml = new \DOMDocument();
        $xml->load($file->getRealPath());

        /**
         * @todo get schema file name from xml
         */
        $schemaFile = 'IrregEm.xsd';
        $schemaPath = dirname(__FILE__) . '/../../schemas/' . $schemaFile;
        if (!$xml->schemaValidate($schemaPath)) {
            $errors = libxml_get_errors();
            foreach ($errors as $error) {
                $validateError = '';
                switch ($error->level) {
                    case LIBXML_ERR_WARNING:
                        $validateError .= "<b>Warning $error->code</b>: ";
                        break;
                    case LIBXML_ERR_ERROR:
                        $validateError .= "<b>Error $error->code</b>: ";
                        break;
                    case LIBXML_ERR_FATAL:
                        $validateError .= "<b>Fatal Error $error->code</b>: ";
                        break;
                }

                $validateError .= $error->message;
                if ($error->file) {
                    $validateError .= " in <b>" . $file->getClientOriginalName() . "</b>";
                }
                $validateError .= " on line <b>$error->line</b><br>" . PHP_EOL;
                echo $validateError;
            }
            libxml_clear_errors();
            
            $this->sendErrorEmail($validateError, $file, $user);

            throw new \Exception('Schema validate error');
        }
    }    

    /**
     * 
     * @param UploadedFile $file
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param User $user
     * @return Response
     */
    private function addReport(
            UploadedFile $file, 
            Request $request, 
            ValidatorInterface $validator, 
            User $user
    ) {
        $entityManager = $this->getDoctrine()->getManager();

        /** $report Report */
        $report = new Report();
        $report->setUser($user);
        $report->setEdrpou($request->get('edrpou'));
        $report->setName($request->get('name'));
        $report->setNreg($request->get('nreg'));
        $report->setFile($file->getClientOriginalName());
        $report->setPublicDt(new \DateTime());

        $errors = $validator->validate($report);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response($errorsString);
        }

        $entityManager->persist($report);
        $entityManager->flush();
        
        $this->sendSuccessEmail($report, $user);
    }

    /**
     * 
     * @param Report $report
     * @param User $user
     */
    private function sendSuccessEmail(Report $report, User $user) {
        $message = (new \Swift_Message('Report ' . $report->getId() . ' created'))
                ->setFrom('send@example.com')
                ->setTo($user->getEmail())
                ->setBody('Report ' . $report->getFile() . ' successfully created.' . PHP_EOL
                . 'ID: ' . $report->getId() . PHP_EOL
                . 'Date: ' . $report->getPublicDt()->format('Y-m-d'));

        $this->mailer->send($message);
    }

    /**
     * 
     * @param string $validateError
     * @param UploadedFile $file
     * @param User $user
     */
    private function sendErrorEmail($validateError, UploadedFile $file, User $user) {
        $message = (new \Swift_Message('Report error'))
                ->setFrom('send@example.com')
                ->setTo($user->getEmail())
                ->setBody('Report file ' . $file->getClientOriginalName() 
                        . ' have errors:' . PHP_EOL
                        . strip_tags($validateError) . PHP_EOL
                        );

        $this->mailer->send($message);
    }    
    
}
