<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Author;
use App\Form\BookSearchType;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\PseudoTypes\True_;

class BookController extends AbstractController
{
    #[Route('/book', name: 'app_book')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
        ]);
    }

   


    #[Route('/addformbook', name: 'addformbook')]
    public function addformbook(ManagerRegistry $managerRegistry,Request $req): Response
    {
        $em=$managerRegistry->getManager();
        $book= new Book();
        $form=$this->createForm(BookType::class,$book);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid() ){
            $author = $book->getAuthor();
            if ($book->isPublished()) { 
                $author->setNbBook($author->getNbBook() + 1);
            } 
         $em->persist($author);
        $em->persist($book);
        $em->flush();
        return $this->redirectToRoute('showdbbook');
        }
        return $this->renderForm('book/addformbook.html.twig', [
            'f'=>$form
        ]);
    }






    #[Route('/editbook/{id}', name: 'editbook')]
    public function editbook($id,BookRepository $bookRepository,ManagerRegistry $managerRegistry,Request $req): Response
    {
        //var_dump($id).die();
        $em=$managerRegistry->getManager();
        $dataid=$bookRepository->find($id);
        //var_dump($dataid).die();
        $form=$this->createForm(BookType::class,$dataid);
        $form->handleRequest($req);
        if ($form->isSubmitted() and $form->isValid()){
            $author = $dataid->getAuthor();
            if (!$dataid->isPublished()) { 
                $author->setNbBook($author->getNbBook() - 1);
            } 
            $em->persist($dataid);
            $em->flush();
            return $this->redirectToRoute('showdbbook');

        }
        return $this->renderForm('book/editbook.html.twig', [
            'f' => $form
        ]);
    }





    #[Route('/deletebook/{id}', name: 'deletebook')]
    public function deletebook($id,BookRepository $authorRepository,ManagerRegistry $managerRegistry): Response
    {
        //var_dump($id).die();
        $em=$managerRegistry->getManager();
        $dataid=$authorRepository->find($id);
        $author = $dataid->getAuthor();

        // Decrement the author's nbBooks
        $author->setNbBook($author->getNbBook() - 1);
        //var_dump($dataid).die();
        $em->remove($dataid);
        $em->flush();
        return $this->redirectToRoute('showdbbook');
    }







    #[Route('/showdbbook', name: 'showdbbook')]
    public function publishedBooks(BookRepository $bookRepository ,Request $req,AuthorRepository $authorRepository): Response
    {
        $searchRef = $req->query->get('ref');
        $book = [];
        if ($searchRef) {
            $book = $bookRepository->findByRef($searchRef);
        } else {
           
            $book = $bookRepository->findBy(['published' => true]);
        }
        // Récupérez la liste des livres publiés
        $totalBooks = $bookRepository->countCategory();

        $form=$this->createForm(BookSearchType::class,);
       
       
         $form->handleRequest($req);
         if ($form->isSubmitted()){
         $ref = $form->get('ref')->getData();
   
   $book = $bookRepository->findByRef($ref);
   }
         if ($book === null) {
            throw $this->createNotFoundException('Aucun Livre.');
        }

        $publishedCount = $bookRepository->count(['published' => true]);
        $unpublishedCount = $bookRepository->count(['published' => false]);

        return $this->renderForm('book/showdbbook.html.twig', [
            'book' => $book,
            'f'=>$form,
            'publishedCount' => $publishedCount,
            'unpublishedCount' => $unpublishedCount,
            'totalBooks' => $totalBooks,
        ]);

    }

    #[Route('/showbyidauthor/{ref}', name: 'showbyidauthor')]
    public function showidbyauthor($ref,BookRepository $BookRepository , ManagerRegistry $managerRegistry): Response
    {
        $em=$managerRegistry->getManager();
        $book = $BookRepository->find($ref);
        $em->persist($book);
        $em->flush();
        return $this->render('book/showbyidauthor.html.twig', [
            'book' => $book,
        ]);
    }





    #[Route('/book/show/{id}', name: 'show_book')]
    public function showBook($id, ManagerRegistry $managerRegistry): Response
    {
        // Récupérez le livre depuis la base de données en utilisant Doctrine
        $entityManager =$managerRegistry->getManager();
        $bookRepository = $entityManager->getRepository(Book::class);
        $book = $bookRepository->find($id);

        if ($book === null) {
            throw $this->createNotFoundException('Le livre n\'existe pas.');
        }

        // Utilisez la méthode render pour afficher un template avec les détails du livre
        return $this->render('book/show.html.twig', [
            'book' => $book,
        ]);
    }




    #[Route('/deleteZeroBooks', name: 'deleteZeroBooks')]
    public function deleteZeroBooks(ManagerRegistry $managerRegistry): Response
    {
        $entityManager =$managerRegistry->getManager();
        $authorRepository = $entityManager->getRepository(Author::class);
        $bookRepository = $entityManager->getRepository(Book::class);

        // Récupérer la liste des auteurs avec nb_books égal à zéro
        $authorsToDelete = $authorRepository->findBy(['nb_book' => 0]);

        foreach ($authorsToDelete as $author) {
            // Retrieve the associated books
            $books = $bookRepository->findBy(['author' => $author]);

            foreach ($books as $book) {
                $entityManager->remove($book);
            }

            $entityManager->remove($author);
        }

        $entityManager->flush();

        return $this->redirectToRoute('showdbbook');
    }
    #[Route('/book35', name: 'book35')]
    public function findPublishedBooks2023(BookRepository $bookRepository): Response
    {
        $books = $bookRepository->findPublishedBooks2023();

        return $this->render('book/book35.html.twig', [
            'books' => $books,
        ]);
    }
    #[Route('/updateShakespeareBooks', name: 'updateShakespeareBooks')]
    public function updateShakespeareBooks(BookRepository $bookRepository, ManagerRegistry $managerRegistry)
    {
        $entityManager =$managerRegistry->getManager();

        $ShakespeareBooks = $bookRepository->updateShakespeareBooks();

        foreach ($ShakespeareBooks as $book) {
            $book->setCategory('Romance');
            $entityManager->persist($book);
        }

        $entityManager->flush();

        return $this->redirectToRoute('showdbbook'); // Redirect to the list of books or another appropriate route
    }
    #[Route('/BetweenDatesBooks', name: 'BetweenDatesBooks')]
    public function BetweenDatesBooks(BookRepository $bookRepository)
    {
        $books = $bookRepository->BetweenDatesBooks();

        return $this->render('book/BetweenDatesBooks.html.twig', [
            'books' => $books,
        ]);
    }


    

}
