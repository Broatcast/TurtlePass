<?php

namespace Main\PasswordBundle\Event;

use Main\AppBundle\Entity\Setting;
use Main\AppBundle\Services\DatabaseMailer;
use Main\AppBundle\Services\SettingManager;
use Main\PasswordBundle\Container\LogKeys;
use Main\PasswordBundle\Services\PasswordLogManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Templating\EngineInterface;

class ShareLinkSubscriber implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var PasswordLogManager
     */
    protected $passwordLogManager;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var DatabaseMailer
     */
    protected $databaseMailer;

    /**
     * @var SettingManager
     */
    protected $settingManager;

    /**
     * @param TokenStorageInterface $tokenStorage
     * @param PasswordLogManager    $passwordLogManager
     * @param EngineInterface       $templating
     * @param RouterInterface       $router
     * @param DatabaseMailer        $databaseMailer
     * @param SettingManager        $settingManager
     */
    public function __construct(TokenStorageInterface $tokenStorage, PasswordLogManager $passwordLogManager, EngineInterface $templating, RouterInterface $router, DatabaseMailer $databaseMailer, SettingManager $settingManager)
    {
        $this->tokenStorage = $tokenStorage;
        $this->passwordLogManager = $passwordLogManager;
        $this->templating = $templating;
        $this->router = $router;
        $this->databaseMailer = $databaseMailer;
        $this->settingManager = $settingManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            ShareLinkEvent::NAME => [
                ['onPasswordSharedLogging', 0],
                ['onPasswordSharedMail', 0],
            ],
        ];
    }

    /**
     * @param ShareLinkEvent $event
     */
    public function onPasswordSharedLogging(ShareLinkEvent $event)
    {
        $user = $this->tokenStorage->getToken()->getUser();

        $this->passwordLogManager->createPasswordLog($event->getPasswordShareLink()->getPassword(), LogKeys::KEY_SHARED, $user, true);
    }

    /**
     * @param ShareLinkEvent $event
     */
    public function onPasswordSharedMail(ShareLinkEvent $event)
    {
        if ($event->getPasswordShareLink()->getRecipient() !== null && filter_var($event->getPasswordShareLink()->getRecipient(), FILTER_VALIDATE_EMAIL)) {
            $link = $this->router->generate('main_password_share_index', [], UrlGeneratorInterface::ABSOLUTE_URL) . sprintf('#!/password-share/%s/%s', $event->getPasswordShareLink()->getId(), urlencode($event->getPasswordShareLink()->getToken()));

            $template = $this->templating->render('@MainPassword/Email/shareLink.html.twig', [
                'link' => $link,
                'valid_until' => $event->getPasswordShareLink()->getValidTo(),
                'sharing_user' => $this->tokenStorage->getToken()->getUser(),
            ]);

            $this->sendEmailMessage($template, $event->getPasswordShareLink()->getRecipient());
        }
    }

    /**
     * @param string $renderedTemplate
     * @param string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $sender = $this->settingManager->getSetting(Setting::ID_MAIL_SENDER_ADDRESS)->getValueAllowNull();

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($sender)
            ->setTo($toEmail)
            ->setBody($body);

        $this->databaseMailer->getMailer()->send($message);
    }
}
