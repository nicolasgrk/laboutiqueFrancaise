<?php

namespace App\Controller;

use App\Classe\Mail;
use App\Entity\User;
use App\Form\RegisterType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager){
        $this->entityManager = $entityManager;
    }
    #[Route('/inscription', name: 'app_register')]
    public function index(Request $request,UserPasswordHasherInterface $passwordHasher): Response
    {
        $notification = null;
        $user = new User();
        $form = $this->createForm(RegisterType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $user = $form->getData();

            $search_email = $this->entityManager->getRepository(User::class)->findOneByEmail($user->getEmail());

            if(!$search_email){
                $password = $passwordHasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $mail = new Mail();
                $content= "Bonjour ". $user->getFirstname()."<br>Bienvenue sur la premiere boutique dédié au made in France.";
                $mail->send($user->getEmail(), $user->getFirstname(), 'Bienvenue sur la boutique Française', $content);
                $notification = "Votre inscription c'est correctement déroulé, vous pouvez dés à présent vous connecter";

            }else{
                $notification = "L'e-mail existe déja ";

            }

        }

        return $this->render('register/index.html.twig', [
            'form'=> $form->createView(),
            'notification'=> $notification
        ]);
    }
}
