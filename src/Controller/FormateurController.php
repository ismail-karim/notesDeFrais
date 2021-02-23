<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Entity\Etat;
use App\Entity\Note;
use App\Entity\RechercheNote;
use App\Entity\User;
use App\Form\AddDepenseType;
use App\Form\AddNoteType;
use App\Form\ModifierDepType;
use App\Form\RechercheNoteFType;
use App\Form\UpdateUserType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FormateurController extends AbstractController
{
    const ETAT_EN_COURS = "Validation en cours";
    const ETAT_VALIDER = "Validée";
    const ETAT_REFUSER = "Refusée";
    const ETAT_REMBOURSER = "Remboursée";

    /**
     * @Route("/formateur", name="formateur")
     */
   public function index(NoteRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        $rechercheNote = new RechercheNote(); // Créer une nouvelle entité basé sur le fomulaire
        $form = $this->createForm(RechercheNoteFType::class,$rechercheNote);
        $user = $this->getUser();
        $form->handleRequest($request);
        $notes = $paginator->paginate(
            $repository->findWithPagination($rechercheNote, $user),
            $request->query->getInt('page', 1), /*page number*/
            3); /*limit per page*/
        return $this->render('formateur/index.html.twig', [
            'notes'=> $notes,
            'form_filtre' => $form->createView()
        ]);

    }


    /**
     * @Route("/formateur/ajouter" , name="formateur_add")
     * @Route("/formateur/modifier/{id}", name="formateur_edit", methods={"GET|POST"}, requirements={"id": "\d+"})
     * Methode permet d'ajouter une note ou de la modifier
     */
    public function addAndEditNote(EntityManagerInterface $manager, Request $request, Note $note = null)
    {
        if(!$note)
        {
            $note = new Note();
            $note->setDateCreation(new \DateTime());
            $note->setUser($this->getUser());
        }

        $form = $this->createForm(AddNoteType::class, $note);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $etat = $this->getDoctrine()->getRepository(Etat::class)->findOneBy(['libelle' => 'Créée']);
            $note->setEtat($etat);
            $manager->persist($note);
            $manager->flush();
            $this->addFlash('success', 'La note de frais à bien éta ajoutée');
            return $this->redirectToRoute('formateur_detail', [
                'id' =>$note->getId()
            ]);
        }

        return $this->render('formateur/add.html.twig',[
            'note'=> $note,
            'formNote' => $form->createView(),
            'edit' => $note->getId() !== null
        ]);
    }

    /**
     * @param $id
     * @Route("/formateur/detail/{id}", name="formateur_detail", requirements={"id":"\d+"})
     * Methode pour avoir le detail d'une note et aussi la modifier
     */
    public function detail($id, EntityManagerInterface $manager, Request $request)
    {
        $note = $this->getDoctrine()->getRepository(Note::class)->find($id);
        if(empty($note))
        {
            throw $this->createNotFoundException("Aucune note n'a été trouver");
        }

        $depense = new Depense();
        $depense->setNote($note);


        $depenses = $this->getDoctrine()->getRepository(Depense::class)->findAllById($note->getId());
        $total = $this->getDoctrine()->getRepository(Depense::class)->findSum($note->getId());
        $formDep = $this->createForm(AddDepenseType::class, $depense);
        $formDep->handleRequest($request);
        if($formDep->isSubmitted() && $formDep->isValid())
        {
            $manager->persist($depense);
            $manager->flush();
            $this->addFlash('success', 'la dépense à bien été ajouter');
            return $this->redirectToRoute('formateur_detail_depense', [
                'id' =>$depense->getId()
            ]);
        }
        return $this->render("formateur/detail.html.twig", [
            'note'=>$note,
            'depenses'=>$depenses,
            'total' => $total,
            'formDep'=>$formDep->createView()
        ]);
    }

    /**
     * @param $id
     * @Route("/formateur/supprimer/note/{id}" , name="formateur_delete_note", methods={"POST"})
     */
    public function deleteNote(Note $note, EntityManagerInterface $manager, Request $request)
    {
        if($this->isCsrfTokenValid('SUPNOTE'. $note->getId(), $request->get('_token')))
        {
            $manager->remove($note);
            $manager->flush();
            $this->addFlash("successDelete", "La note à bien été supprimer");
            return $this->redirectToRoute('formateur');
        }
    }
    /**
     * @Route("/formateur/validation/{id}", name="validation", requirements={"id":"\d+"})
     */
    public function validationNote($id, EntityManagerInterface $manager)
    {
        $etatRepo = $this->getDoctrine()->getRepository(Etat::class)->findOneBy(['libelle'=>'Validation en cours']);
        $note = $this->getDoctrine()->getRepository(Note::class)->find($id);
        $note->setEtat($etatRepo);
        $manager->persist($note);
        $manager->flush();
        $message = "La note numéro " . $id . " est en cours de validation";
        $this->addFlash('successValid', $message);

        return $this->redirectToRoute('formateur');
    }


    /**
     * @Route("/formateur/modifier/depense/{id}" , name="formateur_edit_depense" , requirements={"id":"\d+"})
     */
    public function modifierDepense(EntityManagerInterface $manager, Request $request, Depense $depense)
    {
        $form = $this->createForm(ModifierDepType::class, $depense);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $manager->persist($depense);
            $manager->flush();
            $this->addFlash('successModif', 'la dépense a bien été mofifier');
            return $this->redirectToRoute('formateur_detail_depense',
                [
                    'id' => $depense->getId()
                ]);
        }
        return $this->render('formateur/modifDep.html.twig',
        [
            'form'=> $form->createView()
        ]);
    }

    /**
     * @param $id
     * @Route("/formateur/detail/depenses/{id}", name="formateur_detail_depense", requirements={"id":"\d+"})
     * Methode pour avoir le detail d'une depense
     * @return Response
     */
    public function detailDepense($id): Response
    {
        $depenses = $this->getDoctrine()->getRepository(Depense::class)->find($id);

        return $this->render('formateur/depense.html.twig',
        [
            'depenses' => $depenses
           //dump($depense),
          // die(),
        ]);

    }

    /**
     * @Route("/formateur/supprimer/depense/{id}" , name="formateur_delete_depense", methods={"POST"})
     */
    public function deleteDepense(Depense $depense, Request $request, EntityManagerInterface $em)
    {
        if($this->isCsrfTokenValid("SUPDEP".$depense->getId(), $request->get('_token')))
        {
            $idNote = $depense->getNote()->getId();
            $note = $this->getDoctrine()->getRepository(Note::class)->find($idNote);
            $note->removeDepense($depense);
            $em->persist($note);
            $em->flush();
           /* $em->remove($depense);
            $em->flush();*/
            $this->addFlash('deleteDep', 'La dépense à bien été supprimer');
        }
        return $this->redirectToRoute('formateur_detail',
            [
                'id'=>$idNote
            ]);
    }

}
