<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminModifyType;
use App\Form\AdminType;
use App\Service\PasswordGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{


    /**
     * @Route("/admin/list_user", name="admin_list")
     */
    public function listUser()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class)->findAll();
        $users = $userRepo;
        return $this->render('admin/listUser.html.twig', [
            'users' => $users
        ]);
    }

    /**
     * @Route("/admin/add_user", name="admin_add")
     * @Route("/admin/edit_user/{id}", name="admin_edit", methods={"GET|POST"}, requirements={"id": "\d+"})
     */
    public function addAndEditUser(User $user=null, EntityManagerInterface $manager, Request $request, UserPasswordEncoderInterface $encoder, PasswordGenerator $generator, \Swift_Mailer $mailer)
    {
        $password = $generator->generateRandomStrongPassword(20);
        if(!$user)
        {
            $date = new \DateTime();
            $user = new User();
            $user->setActif(true);
            $user->setPassword($password);
            $user->setUpdatedAt($date);
        }
        $userForm = $this->createForm(AdminType::class, $user);
        $userForm->handleRequest($request);
        $passUser = $user->getPassword();
        if($userForm->isSubmitted() && $userForm->isValid())
        {
            $hashed = $encoder->encodePassword($user, $user->getPassword());
            $user->setPassword($hashed);
            $manager->persist($user);
            $manager->flush();
            $this->addFlash('success', 'L\'utilisteur à bien été enregistrer');
            return $this->redirectToRoute('admin_list');
        }

        return $this->render('admin/addUser.html.twig',
        [
            "user"=>$user,
            "password"=>$passUser,
            "userForm"=> $userForm->createView(),
            "edit"=> $user->getId() !== null
        ]);
    }

    /**
     * @Route("/admin/see_user/{id}", name="admin_seeUser", requirements={"id": "\d+"})
     * @param $id
     * @return Response
     */
    public function show($id): Response
    {
        $user = $this->getDoctrine()->getRepository(User::class)->find($id);
        return $this->render('admin/showUser.html.twig',[
            "user"=> $user,
            "password"=>$user->getPassword()
        ]);
    }

    /**
     * @Route("/admin/delete/{id}", name="admin_delete", methods={"POST"}, requirements={"id": "\d+"})
     *
     */
    public function deleteUser(User $user, Request $request, EntityManagerInterface $manager)
    {
        if($this->isCsrfTokenValid("SUPUSER".$user->getId(), $request->get('_token')))
        {
            $manager->remove($user);
            $manager->flush();
            $this->addFlash("successDeleteUser", "L'utilisateur a bien été supprimer");
            return $this->redirectToRoute('admin_list');
        }
    }
}
