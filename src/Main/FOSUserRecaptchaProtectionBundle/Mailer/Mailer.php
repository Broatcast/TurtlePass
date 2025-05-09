<?php

namespace Main\FOSUserRecaptchaProtectionBundle\Mailer;

use Main\AppBundle\Entity\Setting;
use Main\AppBundle\Services\DatabaseMailer;
use Main\AppBundle\Services\SettingManager;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Mailer\MailerInterface;

class Mailer implements MailerInterface
{
    /**
     * @var DatabaseMailer
     */
    protected $databaseMailer;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var array
     */
    protected $parameters;

    /**
     * @var SettingManager
     */
    protected $settingManager;

    /**
     * @param DatabaseMailer  $databaseMailer
     * @param RouterInterface $router
     * @param EngineInterface $templating
     * @param array           $parameters
     * @param SettingManager  $settingManager
     */
    public function __construct(DatabaseMailer $databaseMailer, RouterInterface $router, EngineInterface $templating, array $parameters, SettingManager $settingManager)
    {
        $this->databaseMailer = $databaseMailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
        $this->settingManager = $settingManager;
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['confirmation.template'];
        $url = $this->router->generate('fos_user_registration_confirm', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'confirmationUrl' =>  $url
        ));
        $this->sendEmailMessage($rendered, $user->getEmail());
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['resetting.template'];
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);
        $rendered = $this->templating->render($template, array(
            'user' => $user,
            'confirmationUrl' => $url
        ));
        $this->sendEmailMessage($rendered, $user->getEmail());
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
