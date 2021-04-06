<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="home_FE")
     * @Route("/{route}", name="vue_pages", requirements={"route"="^(?!.*_wdt|_profiler|api|devices_api|test|pass_recovery_report).+"})
     * @param Request $request
     * @return Response
     */
    public function indexAction(Request $request): Response
    {
        return $this->render('vue_app.html.twig', []);
    }
}
