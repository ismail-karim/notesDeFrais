<?php

namespace App\Controller;

use App\Entity\Depense;
use App\Entity\Etat;
use App\Entity\Message;
use App\Entity\Note;
use App\Entity\User;
use App\Form\RefusNoteType;
use App\Repository\NoteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ComptaController extends AbstractController
{

    const ETAT_EN_COURS = "Validation en cours";
    const ETAT_VALIDER = "Validée";
    const ETAT_REFUSER = "Refusée";
    const ETAT_REMBOURSER = "Remboursée";
    /**
     * @Route("/comptabilite", name="compta_home")
     */
    public function index(): Response
    {

       $lesNotes = $this->getDoctrine()->getRepository(Note::class)->findNoteByEtat(self::ETAT_EN_COURS);
        return $this->render('compta/index.html.twig', [
            'notes'=>$lesNotes
        ]);
    }


    /**
     * @Route("/comptabilite/all", name="compta_toutes_notes")
     */
    public function toutesNotes()
    {
        $ids = [2,3,4,5];
        $notes = $this->getDoctrine()->getRepository(Note::class)->findAllNotesByEtat($ids);
        return $this->render('compta/toutesNotes.html.twig', [
            'allNotes' => $notes
        ]);
    }

    /**
     * @Route("/comptabilite/formateur", name="compta_formateur")
     */
    public function findFormateur()
    {
        $role = "ROLE_FORMATEUR";
        $users = $this->getDoctrine()->getRepository(User::class)->findRole($role);
       // dump($users); die();
        return $this->render('compta/formateur.html.twig',[
            'users' =>$users
        ]);
    }


    /**
     * @return Response
     * Fonction permet de récupérer le nombre de notes avec l'états validation en cours et qu'elle le retourn au twig
     */
    public function nbNoteAValide()
    {
        $nombre = $this->getDoctrine()->getRepository(Note::class)->findNbNoteByEtat(self::ETAT_EN_COURS);
        return $this->render('compta/badgeNote.html.twig',
        [
            'nombre'=>$nombre
        ]);
    }

    public function nbToutesNotes()
    {
        $nombre = $this->getDoctrine()->getRepository(Note::class)->findAll();
        return $this->render('compta/badgeNoteAll.html.twig',
        [
            'nb' => $nombre
        ]);
    }

    /**
     * @param $id
     * @Route("/comptabilite/detail/{id}" , name="compta_detail", requirements={"id":"\d+"})
     * @Route("/comptabilite/refus/{id}" , name="compta_refus", requirements={"id":"\d+"})
     */
    public function detail($id, EntityManagerInterface $manager, Request $request)
    {

        $note = $this->getDoctrine()->getRepository(Note::class)->find($id);
        $depenses = $this->getDoctrine()->getRepository(Depense::class)->findAllById($note->getId());
        $message = new Message();
        $message->setNote($note);
        $message->setDate(new \DateTime());

        $form = $this->createForm(RefusNoteType::class, $message);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $etatRefuser = $this->getDoctrine()->getRepository(Etat::class)->findOneBy(['libelle' => self::ETAT_REFUSER]);
            $note->setEtat($etatRefuser);
            $manager->persist($note);
            $manager->persist($message);
            $manager->flush();

            $this->addFlash('danger', 'La note a été refusée');

            return $this->redirectToRoute('compta_detail',
                [
                    'id'=> $note->getId()
                ]);
        }

        return $this->render('compta/detail.html.twig',
        [
           // 'notes' => $notes,
            'note' => $note,
            'depenses' => $depenses,
            "formRefus" => $form->createView()
        ]);
    }


    /**
     * @param $id
     * @param EntityManagerInterface $manager
     * @return Response
     * @Route("/comptabilite/validation/{id}", name="compta_validation", requirements={"id":"\d+"})
     */
    public function validationNote($id, EntityManagerInterface $manager)
    {

        $note = $this->getDoctrine()->getRepository(Note::class)->find($id);
        $depenses = $this->getDoctrine()->getRepository(Depense::class)->findAllById($note->getId());
        $etat = $this->getDoctrine()->getRepository(Etat::class)->findOneBy(['libelle'=>self::ETAT_VALIDER]);
        
        $note->setEtat($etat);

        $manager->persist($note);
        $manager->flush();

        $this->addFlash('success', 'la note a bien été validée');

        return $this->render('compta/validation.html.twig',
        [
            'note' => $note,
            'depenses' => $depenses
        ]);
    }


    /**
     * @param $id
     * @param EntityManagerInterface $manager
     * @return Response
     * @Route("/comptabilite/rembourser/{id}", name="compta_remboursement", requirements={"id":"\d+"})
     */
    public function rembourser($id, EntityManagerInterface $manager)
    {
        $note = $this->getDoctrine()->getRepository(Note::class)->find($id);
        $depenses = $this->getDoctrine()->getRepository(Depense::class)->findAllById($note->getId());
        $etatRem = $this->getDoctrine()->getRepository(Etat::class)->findOneBy(['libelle'=>self::ETAT_REMBOURSER]);
        $date = new \DateTime();
        $note->setEtat($etatRem);

        $manager->persist($note);
        $manager->flush();
        $this->addFlash('rem', 'le remboursemment a été éffectuer');

        return $this->render('compta/remb.html.twig',[
            'note' => $note,
            'date'=> $date,
            'depenses' => $depenses
        ]);
    }

    /**
     * @param $id
     * @param EntityManagerInterface $manager
     * @return Response
     * @Route("/comptabilite/facture/{id}", name="compta_facture", methods={"GET"}, requirements={"id":"\d+"})
     */
    public function facture($id)
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        // Instantiate Dompdf with our options
        $dompdf = new Dompdf($pdfOptions);

        $note = $this->getDoctrine()->getRepository(Note::class)->find($id);
        $depenses = $this->getDoctrine()->getRepository(Depense::class)->findAllById($note->getId());
        $date = new \DateTime();

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView('compta/facture.html.twig',[
            'note' => $note,
            'date'=> $date,
            'depenses' => $depenses
        ]);

        // Load HTML to Dompdf
        $dompdf->loadHtml($html);

        // (Optional) Setup the paper size and orientation 'portrait' or 'portrait'
        $dompdf->setPaper('A4', 'portrait');

        // Render the HTML as PDF
        $dompdf->render();

        // Output the generated PDF to Browser (force download)
        $dompdf->stream("facture.pdf", [
            "Attachment" => false
        ]);

    }
}
