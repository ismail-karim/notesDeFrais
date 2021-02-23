<?php

namespace App\Controller;

use App\Form\UpdatePassType;
use App\Form\UpdateUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{

    /**
     * @Route("/user/modifier/profil", name="user_edit_profil")
     */
    public function editProfil(EntityManagerInterface $manager, Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(UpdateUserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'votre profil a été modifier');
            return $this->redirectToRoute('formateur');
        }
        return $this->render('user/index.html.twig', [
            'user' => $user,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/user/profil", name="user_show_profil")
     */
    public function showProfil()
    {
        $user = $this->getUser();
        return $this->render('user/profil.html.twig',[
            'user' => $user
        ]);
    }

    /**
     * @Route("/forgotPassword", name="forgot_password")
     */
    public function forgotPassword()
    {
        return $this->render('user/forgot.html.twig');
    }

    /**
     * @Route("/user/modifier_mot_de_passe", name="reset_password")
     */
    public function resetPassword(EntityManagerInterface $manager, Request $request)
    {
        $user = $this->getUser();
        $form = $this->createForm(UpdatePassType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('successResetPass', 'Votre mot de passe est modifié');
        }
        
        return $this->render('user/profil.html.twig',[
            'form' => $form
        ]);
    }
}
