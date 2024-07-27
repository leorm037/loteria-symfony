<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/', name: 'app_')]
class IndexController extends AbstractController
{
    
    public function __construct(
            private MailerInterface $mailer
    ) {
    }
    
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('app_bolao_index', [], Response::HTTP_SEE_OTHER);
    }
    
    #[Route('/test', name: 'test')]
    public function test(): Response
    {
        $email = (new Email())
            ->from('sistema@paginaemconstrucao.com.br')
            ->to('leonardo@paginaemconstrucao.com.br')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $this->mailer->send($email);
        
        return $this->render('index/index.html.twig');
    }
}
