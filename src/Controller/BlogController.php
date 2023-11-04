<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class BlogController extends AbstractController
{
    #[Route('/', name: 'acceuil')]
    public function index(TranslatorInterface $translator): Response
    {
        // Get the translation catalog for the specified locale
        $catalogue = $translator->getCatalogue($translator->getLocale());
        $sectors = json_decode($catalogue->all('messages')['organisation.sectors'], true);

        return $this->render('blog/index.html.twig', ['sectors' => $sectors]);
    }

    #[Route('/expertises', name: 'expertises')]
    public function expertises(): Response
    {
        return $this->render('blog/expertise.html.twig', []);
    }

    #[Route('/team', name: 'equipes')]
    public function team(): Response
    {
        return $this->render('blog/team.html.twig', []);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('blog/contact.html.twig', []);
    }
}
