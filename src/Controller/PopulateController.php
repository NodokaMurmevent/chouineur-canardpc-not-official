<?php

namespace App\Controller;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Panther\Client;
use Symfony\Component\Routing\Annotation\Route;

class PopulateController extends AbstractController
{
    #[Route('/populate', name: 'populate')]
    public function index(EntityManagerInterface $em, ArticleRepository $articleRepository): Response
    {
        $i = 0;
        $a = 0;
        $page = 1;
        $parPage = 10;
        $client = Client::createChromeClient();
        // $header = get_headers("https://www.canardpc.com/wp-json/wp/v2/posts?per_page=".$parPage,true);
        $json = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/posts?page='.$page.'&per_page='.$parPage);
        $jsonData = json_decode($json, true);
        // dump($jsonData);
        while ($a < $parPage - 1) {
            // dump("i".$i);
            // dump("a".$a);
            $article = $articleRepository->findOneByGuid($jsonData[$i]['id']);

            if ($article) {
                if (
                        (!$article->getIs404()) &&
                        ($article->getUpdatedAt() < $jsonData[$i]['modified_gmt']) &&
                        (!$article->getIsFreeContent())
                    ) {
                    $article->setModifiedAt(new \DateTimeImmutable($jsonData[$i]['modified_gmt']));
                    $article->setUpdatedAt(new \DateTimeImmutable('now'));
                    $crawler = $client->request('GET', $jsonData[$i]['link']);
                    $chouineur = $crawler->filter('.whines')->filter('p')->text();
                    $article->setChouineurs(intval($chouineur));
                    dump('article mis a jour');
                    ++$a;
                }
                //    dump("article présent et ignoré");
            } else {
                $article = new Article();

                $article->setExcerpt(strip_tags($jsonData[$i]['excerpt']['rendered']));
                $article->setGuid($jsonData[$i]['id']);
                $article->setLink($jsonData[$i]['link']);
                
                if ('' !== $jsonData[$i]['featured_media']) {
                    $article->setImageALaUne($jsonData[$i]['featured_media']);
                    $imageUrl = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/media/'.$article->getImageALaUne());
                    $imageUrlData = json_decode($imageUrl, true);
                    $article->setImageUrl($imageUrlData['media_details']['sizes']['flex-config-product']['source_url']);
                }
                $article->setTitle(strip_tags($jsonData[$i]['title']['rendered']));
                $article->setCreatedAt(new \DateTimeImmutable($jsonData[$i]['date_gmt']));
                $article->setModifiedAt(new \DateTimeImmutable($jsonData[$i]['modified_gmt']));
                $article->setUpdatedAt(new \DateTimeImmutable('now'));
                $crawler = $client->request('GET', $article->getLink());
                dump($article->getLink());
                if ($crawler->filter('.error-404')->count() > 0) {     
                    $article->setIs404(true);
                    $article->setChouineurs(0);
                    $article->setIsFreeContent(false);

                } elseif ($access = $crawler->filter('.post-access') ) {
                    if ('Accessible à tout le monde' == $access->text()) {
                        $article->setIsFreeContent(true);
                        $article->setChouineurs(0);
                    }elseif('Accessible uniquement aux abonnés' == $access->text()){
                        $article->setIsFreeContent(false);
                        $chouineur = $crawler->filter('.whines')->filter('p')->text();
                        $article->setChouineurs(intval($chouineur));
                    }  
                }

                dump('nouvel article');
                ++$a;
            }
            dump($article);
            $em->persist($article);
            $em->flush();
            if ($i == $parPage - 1) {
                ++$page;
                $json = file_get_contents('https://www.canardpc.com/wp-json/wp/v2/posts?page='.$page.'&per_page='.$parPage);
                $jsonData = json_decode($json, true);
                dump('nouvelle requette');
                // dump($jsonData);
                $i = 0;
            }
            ++$i;
        }

        exit();

        return $this->render('populate/index.html.twig', [
            'controller_name' => 'PopulateController',
        ]);
    }
}