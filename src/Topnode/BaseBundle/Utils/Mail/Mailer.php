<?php

namespace App\Topnode\BaseBundle\Utils\Mail;

/**
 * Defines rules and uses default data from environment and symfony to send
 * emails with SwiftMailer.
 */
class Mailer
{
    /**
     * The environment configuration, such default sender and name.
     *
     * @var array
     */
    private $config;

    /**
     * SwiftMailer's Mailer.
     *
     * @var \Swift_Mailer
     */
    private $mailer;

    /**
     * SwiftMailer's Message.
     *
     * @var \Swift_Message
     */
    private $message;

    /**
     * Twig env for rendering templates.
     *
     * @var \Twig\Environment
     */
    private $twigEnvironment;

    public function __construct(\Twig\Environment $twigEnvironment, \Swift_Mailer $mailer)
    {
        $this->twigEnvironment = $twigEnvironment;
        $this->mailer = $mailer;

        $this->start();
    }

    /**
     * Called on Topnode\BaseBundle\DependencyInjection\TopnodeAppExtension
     * to parse all the configuration from config.yml and inject here.
     */
    public function setConfig(array $config): void
    {
        $this->config = $config['mailer'];

        $this->start();
    }

    /**
     * Creates a SwiftMailer Message instance to.
     */
    public function start(): Mailer
    {
        $this->message = new \Swift_Message();

        if (is_array($this->config) && array_key_exists('default_from_email', $this->config)) {
            $this->setFrom(
                $this->config['default_from_email'],
                $this->config['default_from_name']
            );
        }

        return $this;
    }

    /**
     * Defines the subject of the mail to be sent.
     */
    public function setSubject(string $subject): Mailer
    {
        $this->message->setSubject($subject);

        return $this;
    }

    /**
     * Defines the sender address and optionally the name of the mail to be sent.
     *
     * @param string $subject
     * @param string $name    Optional sender name
     */
    public function setFrom(string $address, ?string $name = null): Mailer
    {
        $this->message->setFrom($address, $name);

        return $this;
    }

    /**
     * Defines the receiver address and optionally the name of the mail to be
     * sent.
     *
     * @param string $subject
     * @param string $name    Optional receiver name
     */
    public function setTo(string $address, ?string $name = null): Mailer
    {
        $this->message->setTo($address, $name);

        return $this;
    }

    /**
     * The body HTML content to be sent.
     */
    public function setBody(string $content, $isHtml = false): Mailer
    {
        $this->message->setBody($content, ($isHtml ? 'text/html' : null));

        return $this;
    }

    /**
     * The attachment to be sent.
     *
     * @param string $fileName Optional file name
     */
    public function attach(string $path, ?string $fileName = null): Mailer
    {
        $attachment = \Swift_Attachment::fromPath($path);

        if ($fileName) {
            $attachment->setFileName($fileName);
        }

        $this->message->attach($attachment);

        return $this;
    }

    /**
     * The HTML content body setted.
     */
    public function getBody(): string
    {
        return $this->message->getBody();
    }

    /**
     * Generates an HTML view with twig from a template path and parameters.
     */
    public function renderView(string $template, array $parameters = []): Mailer
    {
        $this->message->setBody(
            $this->twigEnvironment->render($template, $parameters),
            'text/html'
        );

        return $this;
    }

    /**
     * Shortcut for an email creation.
     */
    public function message(string $subject, string $to, string $body, string $from): Mailer
    {
        return $this
            ->setSubject($subject)
            ->setFrom($from)
            ->setTo($to)
            ->setBody($body)
        ;
    }

    /**
     * Sends and restarts the email.
     *
     * @throws \Exception an error regarding the message data
     */
    public function send(): void
    {
        if (0 === count($this->message->getTo())) {
            throw new \Exception('You must set a non empty receiver');
        }

        if (0 === count($this->message->getFrom())) {
            throw new \Exception('You must set a non empty sender');
        }

        if (0 === strlen($this->message->getSubject())) {
            throw new \Exception('You must set a non empty subject');
        }

        if (0 === strlen($this->message->getBody())) {
            throw new \Exception('You must set a non empty HTML Body');
        }

        $this->mailer->send($this->message);

        $this->start();
    }
}
