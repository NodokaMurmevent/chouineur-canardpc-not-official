<?php

namespace App\Twig;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PaginationExtension extends AbstractExtension
{
    /**
     * PaginationExtension constructor.
     * @param $templateFile
     */
    public function __construct(private Environment $template, private RequestStack $requestStack, private string $templateFile = 'pagination.html.twig')
    {
    }

    public function getFunctions(): array {
        return [
            new TwigFunction('pagination', [$this, 'paginationFunction'], ['is_safe' => ['html']])
        ];
    }

    public function paginationFunction(Paginator $paginator, $get = 'page') {
        $request = $this->requestStack->getCurrentRequest();
        $pages = ceil($paginator->count() / $paginator->getQuery()->getMaxResults());
        $page = $request->query->getInt($get, 1);

        if($page > $pages) {
            $page = 1;
        }

        return $this->template->render($this->templateFile, [
            'pages' => $pages,
            'page' => $page,
            'get' => $get
        ]);
    }
}