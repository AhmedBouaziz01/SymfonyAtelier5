<?php

namespace App\Controller;


use App\Form\AuthorType;
use App\Entity\Author;
use App\Form\MinmaxType;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\RangeType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    #[Route('/author', name: 'app_author')]
    public function index(): Response
    {
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
        ]);
    }


    #[Route('/showdbauthor', name: 'showdbauthor')]
    public function showdbauthor(AuthorRepository $authorRepository): Response
    {
        //$author= $authorRepository->findAll();
         $author = $authorRepository->findAllAuthorsOrderedByEmail();


        return $this->render('author/showdbauthor.html.twig', [
           'author'=>$author
        ]);
    }



   

    #[Route('/addformauthor', name: 'addformauthor')]
    public function addformauthor(ManagerRegistry $managerRegistry,Request $req): Response
    {
        $em=$managerRegistry->getManager();
        $author= new Author();
        $form=$this->createForm(AuthorType::class,$author);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()){
        $em->persist($author);
        $em->flush();
        return $this->redirectToRoute('showdbauthor');
        }
        return $this->renderForm('author/addformauthor.html.twig', [
            'f'=>$form
        ]);
    }

    #[Route('/editauthor/{id}', name: 'editauthor')]
    public function editauthor($id,AuthorRepository $authorRepository,ManagerRegistry $managerRegistry,Request $req): Response
    {
        //var_dump($id).die();
        $em=$managerRegistry->getManager();
        $dataid=$authorRepository->find($id);
        //var_dump($dataid).die();
        $form=$this->createForm(AuthorType::class,$dataid);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()){
            $em->persist($dataid);
            $em->flush();
            return $this->redirectToRoute('showdbauthor');

        }
        return $this->renderForm('author/editauthor.html.twig', [
            'f' => $form
        ]);
    }
    #[Route('/deleteauthor/{id}', name: 'deleteauthor')]
    public function deleteauthor($id,AuthorRepository $authorRepository,ManagerRegistry $managerRegistry): Response
    {
        //var_dump($id).die();
        $em=$managerRegistry->getManager();
        $dataid=$authorRepository->find($id);
        //var_dump($dataid).die();
        $em->remove($dataid);
        $em->flush();
        return $this->redirectToRoute('showdbauthor');
    }
    #[Route('/listminmax', name: 'listminmax')]
    #[Route('/minmax', name: 'minmax')]
    public function listBooksByAuthorBookCountRange(Request $request, AuthorRepository $bookRepository): Response
    {
        $form = $this->createForm(MinmaxType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $min = $data['Min'];
            $max = $data['Max'];

            $authors = $bookRepository->findBooksByRange($min, $max);
            return $this->render('author/listminmax.html.twig', [
                'authors' => $authors,
            ]);
        }

        return $this->renderForm('author/minmax.html.twig', [
            'f' => $form,
            'authors' =>Author::class,
        ]);
    }
}