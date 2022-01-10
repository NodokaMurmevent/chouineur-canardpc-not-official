<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class PopulateController extends AbstractController
{
    #[Route('/articles/get-news-articles', name: 'article_get_news_articles')]
    public function articleFetch(EntityManagerInterface $em, ArticleRepository $articleRepository)
    {

        $page = 1;
        $parPage = 100;

        $last = $articleRepository->findOneBy([], ['realCreatedAt' => 'DESC']);
        $date = $last->getRealCreatedAt();
        $string = 'https://www.canardpc.com/wp-json/wp/v2/posts?after='.$date->format("Y-m-d\TH:i:s").'&page='.$page.'&per_page='.$parPage.'&order=desc';
        $jsonData = json_decode(file_get_contents($string),true);
        dump( $string);
        
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
                $em->persist($article);   
            }else{
                dump($value['id']);
            }
                 
        }
        $em->flush();
        exit();
    }


    #[Route('/articles/populate-from-start', name: 'populate_from_start')]
    public function populate(EntityManagerInterface $em, ArticleRepository $articleRepository): Response
    {
        $offset = $articleRepository->count([]);
    
        $jsonData = json_decode(file_get_contents('https://www.canardpc.com/wp-json/wp/v2/posts?offset='.$offset.'&order=asc&per_page=100'),true);

        foreach ($jsonData as $value) {
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
                    //throw $th;     
                    $article->setImageALaUne(null);              
                }  
            }
            $em->persist($article); 
            
            // dump($article);
        }
        $em->flush();  
        return $this->render('populate/populate-from-start.html.twig', [
            'offset' => $offset,
        ]);
    }


    #[Route('/articles/populate-not-set-articles', name: 'populate_articles')]
    public function index(EntityManagerInterface $em, ArticleRepository $articleRepository): Response
    {
        

        $articles = $articleRepository->findBy(["chouineurs"=>null,"isFreeContent"=>null,"is404"=>null],["realCreatedAt"=>"DESC"],40);

        $client = Client::createChromeClient();
       

        foreach ($articles as $key => $article) {
            $crawler = $client->request('GET', $article->getLink());

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
             
        dump($articleRepository->count(["chouineurs"=>null,"isFreeContent"=>null,"is404"=>null]));
        exit();
        return $this->render('populate/populate-articles.html.twig', [
            'articles' => $articles,
        ]);
    }
}
