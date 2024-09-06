<?php

namespace App\Service;

use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EnvoiMail
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @throws TransportExceptionInterface
     */
    public function EnvoiMailCreationCompte(string $destinataireMail, string $leMotDePasse, string $message) : void
    {
        $email = (new Email())
            ->from('no-reply@sortir.com')
            ->to($destinataireMail)
            ->subject('Votre compte a été créé')
            ->text($message . $leMotDePasse);

        $this->mailer->send($email);
    }
}