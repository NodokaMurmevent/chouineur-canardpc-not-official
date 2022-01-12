<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class PopulateController extends AbstractController
{
    #[Route('/get-news-articles/{token}', name: 'article_get_news_articles')]
    public function articleFetch(EntityManagerInterface $em, ArticleRepository $articleRepository,$token = null)
    {
        if ($token !== $this->getParameter('app.securetoken')) {throw new AccessDeniedHttpException('No token given or token is wrong.');}
        $page = 1;
        $parPage = 100;

        $last = $articleRepository->findOneBy([], ['realCreatedAt' => 'DESC']);
        $date = $last->getRealCreatedAt();
        $string = 'https://www.canardpc.com/wp-json/wp/v2/posts?after='.$date->format("Y-m-d\TH:i:s").'&page='.$page.'&per_page='.$parPage.'&order=desc';
        $jsonData = json_decode(file_get_contents($string),true);
        $browser = new HttpBrowser(HttpClient::create());       
        foreach ($jsonData as $value) {
            $exist = $articleRepository->findOneByGuid($value['id']);
            if (!$exist) {
      
                $article = new Article();           

                $article->setTitle(strip_tags($value['title']['rendered']));
                $article->setExcerpt(strip_tags($value['excerpt']['rendered']));
                $article->setGuid($value['id']);
                $article->setLink($value['link']);

                $article->setRealCreatedAt(new \DateTimeImmutable($value['date_gmt']));
                $article->setRealUpdatedAt(new \DateTimeImmutable($value['modified_gmt']));

                if ('' !== $value['featured_media']) {
                    $article->setImageALaUne($value['featured_media']);
                    try {
                        $imageUrl = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/media/'.$value['featured_media']);
                        $imageUrlData = json_decode($imageUrl, true);
                        if ([] != $imageUrlData['media_details']['sizes']) {
                            $article->setImageUrl($imageUrlData['media_details']['sizes']['flex-config-product']['source_url']);
                        } else {
                            $article->setImageUrl('https://cdn.canardware.com/'.$imageUrlData['media_details']['file']);
                        }
                    } catch (\Throwable $th) {                      
                        $article->setImageALaUne(null);              
                    }  
                }

                $crawler = $browser->request('GET', $article->getLink());        
                if ($crawler->filter('.error-404')->count() > 0) {
                    $article->setIs404(true);
                } elseif ($access = $crawler->filter('.post-access')) {
                    $article->setIs404(false);
                    if ('Accessible à tout le monde' == $access->text()) {
                        $article->setIsFreeContent(true);
                    } elseif ('Accessible uniquement aux abonnés' == $access->text()) {
                        $article->setIsFreeContent(false);
                        $chouineur = $crawler->filter('.whines')->filter('p')->text();                       
                        $article->setChouineurs(intval($chouineur));
                        $article->setLastCheckedAt(new \DateTimeImmutable("now"));
                    }
                }
                $em->persist($article);                
            }
                 
        }
        $em->flush();       
        return new Response('success'); 
    }




    #[Route('/updating-never-set-articles/{token}', name: 'updating_never_set_articles')]
    public function index(EntityManagerInterface $em, ArticleRepository $articleRepository,$token = null): Response
    {
        
        if ($token !== $this->getParameter('app.securetoken')) {throw new AccessDeniedHttpException('No token given or token is wrong.');}
        
        $articles = $articleRepository->findBy(["chouineurs"=>null,"isFreeContent"=>null,"is404"=>null],["realCreatedAt"=>"DESC"],40);

        $browser = new HttpBrowser(HttpClient::create());       

        foreach ($articles as $key => $article) {
            $article->setlastCheckedAt(new \DateTimeImmutable("now"));
            try {
                usleep(500000);           
                    
                $crawler = $browser->request('GET', $article->getLink());        
                $errorGet = false;

            } catch (\Throwable $th) {
                
                $errorGet = true;
            }
           
            if (!$errorGet) {        
              
                if ($crawler->filter('.error-404')->count() > 0) {
                    $article->setIs404(true);
                } elseif ($access = $crawler->filter('.post-access')) {
                    if ('Accessible à tout le monde' == $access->text()) {
                        $article->setIsFreeContent(true);
                    } elseif ('Accessible uniquement aux abonnés' == $access->text()) {
                        $article->setIsFreeContent(false);
                        $chouineur = $crawler->filter('.whines')->filter('p')->text();                        
                        $article->setChouineurs(intval($chouineur));
                    }
                }
                $em->persist($article);               
                $em->flush();
            }  
        }
        return new Response('success'); 
    }


    #[Route('/updating-ten-recent/{token}', name: 'updating_ten_recent')]
    public function scrapping(EntityManagerInterface $em, ArticleRepository $articleRepository,$token = null )
    {
        
        if ($token !== $this->getParameter('app.securetoken')) {throw new AccessDeniedHttpException('No token given or token is wrong.');}
        $date = new \DateTime("now");
        $date->modify('-3 month');

        $articles = $articleRepository->findLastArticleWithChouineurs();
     
        $browser = new HttpBrowser(HttpClient::create());


        foreach ($articles as $key => $article) {
            $article->setlastCheckedAt(new \DateTimeImmutable("now"));
            try {
                sleep(1);           
                    
                $crawler = $browser->request('GET', $article->getLink());        
                $errorGet = false;

            } catch (\Throwable $th) {
               
                $errorGet = true;
            }
           
            if (!$errorGet) {        
              
                if ($crawler->filter('.error-404')->count() > 0) {
                    $article->setIs404(true);
                } elseif ($access = $crawler->filter('.post-access')) {
                    if ('Accessible à tout le monde' == $access->text()) {
                        $article->setIsFreeContent(true);
                    } elseif ('Accessible uniquement aux abonnés' == $access->text()) {
                        $article->setIsFreeContent(false);
                        $chouineur = $crawler->filter('.whines')->filter('p')->text();
                        
                        $article->setChouineurs(intval($chouineur));
                    }
                }
                $em->persist($article);               
                $em->flush();
            }  
        }   
        
        return new Response('success'); 
    }

    #[Route('/checking-weekly-articles/{token}', name: 'checking_weekly_articles')]
    public function checkingWeeklyArticles(EntityManagerInterface $em, ArticleRepository $articleRepository, $token = null )
    {

        if ($token !== $this->getParameter('app.securetoken')) {throw new AccessDeniedHttpException('No token given or token is wrong.');}
        $date = new \DateTime();
        $date->setTimestamp(strtotime('Monday this week'));
        $articles = $articleRepository->findWeeklyArticles($date);

        $browser = new HttpBrowser(HttpClient::create());

        foreach ($articles as $key => $article) {
            $article->setLastCheckedAt(new \DateTimeImmutable("now"));
            try {             
                    
                $crawler = $browser->request('GET', $article->getLink());        
                $errorGet = false;

            } catch (\Throwable $th) {              
                $errorGet = true;
            }
           
            if (!$errorGet) {       
              
                if ($crawler->filter('.error-404')->count() > 0) {
                    $article->setIs404(true);
                } elseif ($access = $crawler->filter('.post-access')) {
                    if ('Accessible à tout le monde' == $access->text()) {
                        $article->setIsFreeContent(true);
                    } elseif ('Accessible uniquement aux abonnés' == $access->text()) {
                        $article->setIsFreeContent(false);
                        $chouineur = $crawler->filter('.whines')->filter('p')->text();                        
                        $article->setChouineurs(intval($chouineur));
                    }
                }
              
                $em->persist($article);
                $em->flush();
            }  
        }
        return new Response('success');

    }
}
