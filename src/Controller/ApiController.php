<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/list/formateurs", name="api_compta_list" , methods={"GET"})
     */
    public function list(SerializerInterface $serializer)
    {
        $role = "ROLE_FORMATEUR";
        $formatreur = $this->getDoctrine()->getRepository(User::class)->findRole($role);

        $resultat = $serializer->serialize($formatreur, 'json', ['groups'=>['listUser']]);

        return new JsonResponse($resultat, 200, [], true);
    }
}
