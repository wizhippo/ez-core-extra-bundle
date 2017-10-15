<?php

namespace Wizhippo\Bundle\EzCoreExtraBundle\Mailer;

use Swift_Mailer;
use Swift_Message;
use Symfony\Component\Translation\TranslatorInterface;
use Twig_Environment;

class TemplateMailer
{
    /**
     * @var Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Twig_Environment
     */
    protected $twig;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * Mailer constructor.
     * @param Swift_Mailer $mailer
     * @param Twig_Environment $twig
     * @param TranslatorInterface $translator
     */
    public function __construct(Swift_Mailer $mailer, Twig_Environment $twig, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    /**
     * @param string $templateName
     * @param string $fromAddresses
     * @param string $toAddresses
     * @param array $parameters
     * @param null $locale
     * @param callable $callback
     * @throws \Exception
     */
    public function send(
        $templateName,
        $fromAddresses,
        $toAddresses,
        $parameters = [],
        $locale = null,
        $callback = null
    ) {
        $oldLocale = null;
        if (null !== $locale) {
            $oldLocale = $this->translator->getLocale();
            $this->translator->setLocale($locale);
        }

        /* @var $template \Twig_Template */
        $template = $this->twig->loadTemplate($templateName);

        /* @var $message \Swift_Message */
        $message = Swift_Message::newInstance()
            ->setFrom($fromAddresses)
            ->setTo($toAddresses);

        $subject = trim($template->renderBlock('subject', $parameters));
        $message->setSubject($subject);

        if (!$template->hasBlock('body_html', $parameters) && !$template->hasBlock('body_text', $parameters)) {
            throw new \Exception('body_html or body_text must be defined in mailer template');
        }

        if ($template->hasBlock('body_html', $parameters)) {
            $message->setBody($template->renderBlock('body_html', $parameters), 'text/html');
            if ($template->hasBlock('body_text', $parameters)) {
                $message->addPart($template->renderBlock('body_text', $parameters), 'text/plain');
            }
        } elseif ($template->hasBlock('body_text', $parameters)) {
            $message->setBody($template->renderBlock('body_text', $parameters), 'text/plain');
        }

        if (null !== $callback) {
            call_user_func($callback, $parameters, $message);
        }

        $this->mailer->send($message);

        if (null !== $oldLocale) {
            $this->translator->setLocale($oldLocale);
        }
    }
}
