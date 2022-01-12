<?php

namespace App\Repository;

use App\Entity\Article;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Article::class);
    }

    // /**
    //  * @return Article[] Returns an array of Article objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
    
    /**
     * @return Article[] Returns an array of Article objects
     */
    public function findByChouineurs()
    {
        return $this->createQueryBuilder('a')
            ->where('a.chouineurs > 0 ')     
            ->orderBy('a.chouineurs', 'DESC')
            // ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }

    public function countArticleWithChouineurs()
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->where('a.chouineurs > 0 ')     
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

      public function findAllId()
      {
          return $this
                ->createQueryBuilder('a')
                ->select("a.id")
                ->getQuery()
                ->getScalarResult();
      }

        
      public function findRandomArticle()
      {
        $em = $this->getEntityManager();
          return $em->createQuery("SELECT a FROM App\Entity\Article a ORDER BY Rand() ")->setMaxResults(4)->getResult();
          
      }
      

      public function findRecentArticleWithChouineurs(\DateTime $date)
      {
       
        $em = $this->getEntityManager();
          return $em->createQuery("SELECT a FROM App\Entity\Article a WHERE a.realCreatedAt >= :fromTo AND a.chouineurs > 0 ORDER BY a.updatedAt ASC ")
          ->setParameter('fromTo', $date)->setMaxResults(10)->getResult();
          
      }

      public function findLastArticleWithChouineurs()
      {
       
        $em = $this->getEntityManager();
          return $em->createQuery("SELECT a FROM App\Entity\Article a WHERE a.chouineurs > 0 ORDER BY a.updatedAt ASC ")
          ->setMaxResults(10)->getResult();
          
      }

    /**
     * @return Article[] Returns an array of Article objects
     */
      public function findWeeklyArticles(\DateTime $date)
      {
        return $this->createQueryBuilder('a')
        ->select('a')
        ->where('a.realCreatedAt >= :date')     
        ->orderBy("a.realCreatedAt","DESC")
        ->setParameter('date', $date)
        ->getQuery()
        ->getResult()
    ;
      }
    /**
     * @return Article[] Returns an array of Article objects
     */
      public function findByImageLocalNotNull()
      {
        return $this->createQueryBuilder('a')
        ->andWhere('a.localImage is not null')     
        ->getQuery()
        ->getResult();
      }


      
    /**
     * @return Article[] Returns an array of Article objects
     */
    public function findByRandomNoImages()
    {
      return $this->createQueryBuilder('a')
      ->where('a.localImage is null')     
      ->andWhere('a.imageALaUne is not null')
      ->orderBy('Rand()')         
      ->getQuery()->setMaxResults(6)
      ->getResult();
    }

    /*
    public function findOneBySomeField($value): ?Article
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function findByPage($page = 1, $max = 10)
    {
        $dql = $this->createQueryBuilder('p');
        $dql->orderBy('p.updatedAt', Criteria::DESC);

        $firstResult = ($page - 1) * $max;

        $query = $dql->getQuery();
        $query->setFirstResult($firstResult);
        $query->setMaxResults($max);

        $paginator = new Paginator($query);

        if(($paginator->count() <=  $firstResult) && $page != 1) {
            throw new NotFoundHttpException('Page not found');
        }

        return $paginator;
    }
}
