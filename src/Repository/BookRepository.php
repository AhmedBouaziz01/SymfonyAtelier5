<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 *
 * @method Book|null find($id, $lockMode = null, $lockVersion = null)
 * @method Book|null findOneBy(array $criteria, array $orderBy = null)
 * @method Book[]    findAll()
 * @method Book[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    // BookRepository.php
public function findByRef($ref)
{
    return $this->createQueryBuilder('b')
        ->andWhere('b.ref = :ref')
        ->setParameter('ref', $ref)
        ->getQuery()
        ->getResult();
}
// BookRepository.php

public function findPublishedBooks2023() 
    {
        return $this->createQueryBuilder('b')
        ->join('b.author', 'author')
        ->Where('author.nb_book > 35')
        ->andWhere('b.publicationDate < :date')
        ->groupBy('author.nb_book')
        ->setParameter('date', new \DateTime('2023-01-01'))
        ->getQuery()
        ->getResult();
    }
    public function updateShakespeareBooks()
    {
        return $this->createQueryBuilder('book')
            ->innerJoin('book.author', 'author')
            ->where('author.username = :authorName')
            ->setParameter('authorName', 'William Shakespeare')
            ->getQuery()
            ->getResult();
    }
    public function countCategory()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT COUNT(book.ref) as total
             FROM App\Entity\Book book
             WHERE book.category = :category'
        );
        $query->setParameter('category', 'Science-Fiction');
        return $query->getSingleScalarResult();
    }
    public function BetweenDatesBooks()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery(
            'SELECT book
             FROM App\Entity\Book book
             WHERE book.publicationDate between :startDate and :endDate'
        );
        $query->setParameter('startDate', '2014-01-01');
        $query->setParameter('endDate', '2018-12-31');
        return $query->getResult();
    }

//    /**
//     * @return Book[] Returns an array of Book objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Book
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
