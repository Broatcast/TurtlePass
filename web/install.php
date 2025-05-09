<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Validation;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\RuntimeLoader\FactoryRuntimeLoader;


error_reporting(E_ERROR | E_PARSE);

define('SYMFONY_VENDOR_DIR', realpath(__DIR__ . '/../vendor/symfony/symfony/src/Symfony/'));
define('SYMFONY_CONFIG_PATH', realpath(__DIR__ . '/../app/config'));
define('SYMFONY_CACHE_PATH', realpath(__DIR__ . '/../var/cache/prod'));
define('SYMFONY_PARAMETER_FILE', SYMFONY_CONFIG_PATH.'/parameters.yml');

$fileSystem = new Symfony\Component\Filesystem\Filesystem();

function escapeyaml($data) {
    if (strlen($data) < 1) {
        return $data;
    }

    if (substr($data, 0, 1) === '@') {
        $data = '@'.$data;
    }

    if (strlen($data) >= 2 && substr($data, 0, 1) === '%' && substr($data, -1, 1) === '%') {
        $data = '%'.$data.'%';
    }

    return $data;
}

if ($fileSystem->exists(SYMFONY_PARAMETER_FILE)) {

    /**
     * @var Composer\Autoload\ClassLoader
     */
    $loader = require __DIR__.'/../vendor/autoload.php';
    include_once __DIR__.'/../var/bootstrap.php.cache';

    $kernel = new AppKernel('prod', true);
    $kernel->loadClassCache();
    $kernel->boot();
    $container = $kernel->getContainer();

    $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
    $form = $container->get('form.factory')->create(\Main\AppBundle\Form\Type\UpdateFormType::class);

    $form->handleRequest($request);
    $completed = false;

    if ($form->isValid()) {

        /** @var \Doctrine\DBAL\Connection $connection */
        $connection = $container->get('doctrine')->getConnection();

        /** @var \Doctrine\DBAL\Schema\MySqlSchemaManager $schemaManger */
        $schemaManger = $connection->getSchemaManager();

        $columns = $schemaManger->listTableColumns('users');
        if (!array_key_exists('secret', $columns)) {
            $connection->executeQuery('ALTER TABLE users ADD secret VARCHAR(255) DEFAULT NULL;');
        }

        $columns = $schemaManger->listTableColumns('passwords');
        if (!array_key_exists('icon', $columns)) {
            $connection->executeQuery('ALTER TABLE passwords ADD icon VARCHAR(255) DEFAULT \'fa-key\' NOT NULL AFTER `name_canonical`;');
        }

        if (!array_key_exists('is_log_enabled', $columns)) {
            $connection->executeQuery('ALTER TABLE passwords ADD is_log_enabled TINYINT(1) DEFAULT \'0\' NOT NULL AFTER `icon`;');
        }

        if (!array_key_exists('password_type', $columns)) {
            $connection->executeQuery('ALTER TABLE `passwords` ADD `password_type` VARCHAR(255) NOT NULL DEFAULT \'plain\' AFTER `password_group_id`;');
            $connection->executeQuery('ALTER TABLE `passwords` CHANGE `password_type` `password_type` VARCHAR(255) NOT NULL;');
            $connection->executeQuery('ALTER TABLE `passwords` CHANGE username_canonical username_canonical VARCHAR(255) DEFAULT NULL;');
            $connection->executeQuery('ALTER TABLE `passwords` CHANGE password password VARCHAR(255) DEFAULT NULL;');
        }

        if (!array_key_exists('custom_fields', $columns)) {
            $connection->executeQuery('ALTER TABLE passwords ADD custom_fields LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\'');
        }

        $columns = $schemaManger->listTableColumns('password_bank_accounts');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE password_bank_accounts (id INT UNSIGNED NOT NULL, bank_name VARCHAR(255) NOT NULL, account_holder VARCHAR(255) DEFAULT NULL, bank_code VARCHAR(255) DEFAULT NULL, account_number VARCHAR(255) DEFAULT NULL, iban VARCHAR(255) DEFAULT NULL, pin VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE password_bank_accounts ADD CONSTRAINT FK_8CC961B0BF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;');
        }

        $columns = $schemaManger->listTableColumns('password_credit_cards');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE password_credit_cards (id INT UNSIGNED NOT NULL, card_type VARCHAR(255) DEFAULT NULL, card_number VARCHAR(255) DEFAULT NULL, card_cvc VARCHAR(255) DEFAULT NULL, pin VARCHAR(255) DEFAULT NULL, valid_from VARCHAR(255) DEFAULT NULL, valid_to VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE password_credit_cards ADD CONSTRAINT FK_1A78DB52BF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;');
        }

        $columns = $schemaManger->listTableColumns('password_emails');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE password_emails (id INT UNSIGNED NOT NULL, email_type VARCHAR(255) DEFAULT NULL, host VARCHAR(255) DEFAULT NULL, port VARCHAR(255) DEFAULT NULL, auth_method VARCHAR(255) DEFAULT NULL, smtp_host VARCHAR(255) DEFAULT NULL, smtp_port VARCHAR(255) DEFAULT NULL, smtp_auth_method VARCHAR(255) DEFAULT NULL, smtp_username VARCHAR(255) DEFAULT NULL, smtp_password VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE password_emails ADD CONSTRAINT FK_C168796ABF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;');
        }

        $columns = $schemaManger->listTableColumns('password_servers');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE password_servers (id INT UNSIGNED NOT NULL, host VARCHAR(255) DEFAULT NULL, port VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE password_servers ADD CONSTRAINT FK_6705A4F8BF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;');
        }

        $columns = $schemaManger->listTableColumns('password_software_licenses');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE password_software_licenses (id INT UNSIGNED NOT NULL, version VARCHAR(255) DEFAULT NULL, license_key VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE password_software_licenses ADD CONSTRAINT FK_6A0DEDC1BF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;');
        }

        $columns = $schemaManger->listTableColumns('password_logs');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE password_logs (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, log_key VARCHAR(255) NOT NULL, create_date DATETIME NOT NULL, INDEX IDX_62DAC1173E4A79C1 (password_id), INDEX IDX_62DAC117A76ED395 (user_id), INDEX password_log_key (log_key), INDEX password_log_create_date (create_date), INDEX password_log_key_create_date (log_key, create_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE password_logs ADD CONSTRAINT FK_62DAC1173E4A79C1 FOREIGN KEY (password_id) REFERENCES passwords (id) ON DELETE CASCADE;');
            $connection->executeQuery('ALTER TABLE password_logs ADD CONSTRAINT FK_62DAC117A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);');
        }

        $columns = $schemaManger->listTableColumns('settings');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE settings (id VARCHAR(255) NOT NULL, setting_type VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, choices LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        }

        $columns = $schemaManger->listTableColumns('users');
        if (!array_key_exists('type', $columns)) {
            $connection->executeQuery('ALTER TABLE users ADD type VARCHAR(255) NOT NULL, ADD dn VARCHAR(255) DEFAULT NULL, CHANGE first_name first_name VARCHAR(255) DEFAULT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL, CHANGE full_name full_name VARCHAR(255) DEFAULT NULL, CHANGE email email VARCHAR(255) DEFAULT NULL, CHANGE email_canonical email_canonical VARCHAR(255) DEFAULT NULL;');
            $connection->executeQuery('UPDATE users SET `type` = \'user\' WHERE 1;');
            $connection->executeQuery('INSERT INTO `settings` (`id`, `setting_type`, `value`, `description`, `choices`) VALUES (\'LDAP_USER_PROFILE_FULFILLMENT_REQUIREMENT\', \'boolean\', \'1\', \'Is a new LDAP-User forced to fulfill his profile information.\', \'[]\');');
        }

        $columns = $schemaManger->listTableColumns('user_invalid_logins');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE user_invalid_logins (id INT UNSIGNED AUTO_INCREMENT NOT NULL, ip_address VARBINARY(255) NOT NULL, fail_count INT NOT NULL, create_date DATETIME NOT NULL, last_fail_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        }

        $columns = $schemaManger->listTableColumns('user_login_accesses');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE user_login_accesses (id INT UNSIGNED AUTO_INCREMENT NOT NULL, from_ip_address VARBINARY(255) NOT NULL, to_ip_address VARBINARY(255) NOT NULL, is_whitelist TINYINT(1) NOT NULL, create_date DATETIME NOT NULL, expire_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
        }

        $columns = $schemaManger->listTableColumns('password_share_links');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE password_share_links (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_id INT UNSIGNED NOT NULL, `mode` INT NOT NULL, token VARCHAR(255) NOT NULL, create_date DATETIME NOT NULL, valid_to DATETIME DEFAULT NULL, recipient VARCHAR(255) DEFAULT NULL, INDEX IDX_E45F355A3E4A79C1 (password_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE password_share_links ADD CONSTRAINT FK_E45F355A3E4A79C1 FOREIGN KEY (password_id) REFERENCES passwords (id) ON DELETE CASCADE;');
            $connection->executeQuery('ALTER TABLE password_logs ADD share_link_id INT UNSIGNED DEFAULT NULL, CHANGE user_id user_id INT UNSIGNED DEFAULT NULL;');
            $connection->executeQuery('ALTER TABLE password_logs ADD CONSTRAINT FK_62DAC117EFC8A8ED FOREIGN KEY (share_link_id) REFERENCES password_share_links (id);');
            $connection->executeQuery('CREATE INDEX IDX_62DAC117EFC8A8ED ON password_logs (share_link_id);');
        }

        $columns = $schemaManger->listTableColumns('password_group_sorting');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE password_group_sorting (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_group_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, sorting INT UNSIGNED NOT NULL, INDEX IDX_40EEDFC96EC34B87 (password_group_id), INDEX IDX_40EEDFC9A76ED395 (user_id), UNIQUE INDEX password_group_sort_uqe (password_group_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE password_group_sorting ADD CONSTRAINT FK_40EEDFC96EC34B87 FOREIGN KEY (password_group_id) REFERENCES password_groups (id) ON DELETE CASCADE;');
            $connection->executeQuery('ALTER TABLE password_group_sorting ADD CONSTRAINT FK_40EEDFC9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);');
        }

        $columns = $schemaManger->listTableColumns('password_share_links');

        if (!array_key_exists('view_limit', $columns)) {
            $connection->executeQuery('ALTER TABLE password_share_links ADD view_limit INT DEFAULT NULL, ADD view_count INT DEFAULT NULL;');
        }

        $columns = $schemaManger->listTableColumns('user_groups');

        if (!count($columns)) {
            $connection->executeQuery('CREATE TABLE user_groups (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('CREATE TABLE user_group_allocations (id INT UNSIGNED NOT NULL, group_id INT UNSIGNED NOT NULL, INDEX IDX_1D4EE614BF396750 (id), INDEX IDX_1D4EE614FE54D947 (group_id), PRIMARY KEY(id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('CREATE TABLE password_group_user_group_accesses (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_group_id INT UNSIGNED NOT NULL, user_group_id INT UNSIGNED NOT NULL, access_right SMALLINT UNSIGNED NOT NULL, INDEX IDX_F63628536EC34B87 (password_group_id), INDEX IDX_F63628531ED93D47 (user_group_id), UNIQUE INDEX password_group_user_group_access_uqe (password_group_id, user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('CREATE TABLE password_user_group_accesses (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_id INT UNSIGNED NOT NULL, user_group_id INT UNSIGNED NOT NULL, access_right SMALLINT UNSIGNED NOT NULL, INDEX IDX_6502B6663E4A79C1 (password_id), INDEX IDX_6502B6661ED93D47 (user_group_id), UNIQUE INDEX password_user_group_access_uqe (password_id, user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;');
            $connection->executeQuery('ALTER TABLE user_group_allocations ADD CONSTRAINT FK_1D4EE614BF396750 FOREIGN KEY (id) REFERENCES users (id) ON DELETE CASCADE;');
            $connection->executeQuery('ALTER TABLE user_group_allocations ADD CONSTRAINT FK_1D4EE614FE54D947 FOREIGN KEY (group_id) REFERENCES user_groups (id) ON DELETE CASCADE;');
            $connection->executeQuery('ALTER TABLE password_group_user_group_accesses ADD CONSTRAINT FK_F63628536EC34B87 FOREIGN KEY (password_group_id) REFERENCES password_groups (id) ON DELETE CASCADE;');
            $connection->executeQuery('ALTER TABLE password_group_user_group_accesses ADD CONSTRAINT FK_F63628531ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_groups (id) ON DELETE CASCADE;');
            $connection->executeQuery('ALTER TABLE password_user_group_accesses ADD CONSTRAINT FK_6502B6663E4A79C1 FOREIGN KEY (password_id) REFERENCES passwords (id) ON DELETE CASCADE;');
            $connection->executeQuery('ALTER TABLE password_user_group_accesses ADD CONSTRAINT FK_6502B6661ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_groups (id) ON DELETE CASCADE;');

        }

        $columns = $schemaManger->listTableColumns('users');

        if (array_key_exists('locked', $columns)) {
            $connection->executeQuery('ALTER TABLE users DROP locked, DROP expired, DROP expires_at, DROP credentials_expired, DROP credentials_expire_at, CHANGE username username VARCHAR(180) NOT NULL, CHANGE username_canonical username_canonical VARCHAR(180) NOT NULL, CHANGE email email VARCHAR(180) DEFAULT NULL, CHANGE email_canonical email_canonical VARCHAR(180) DEFAULT NULL, CHANGE salt salt VARCHAR(255) DEFAULT NULL, CHANGE last_login last_login DATETIME DEFAULT NULL, CHANGE confirmation_token confirmation_token VARCHAR(180) DEFAULT NULL, CHANGE password_requested_at password_requested_at DATETIME DEFAULT NULL, CHANGE first_name first_name VARCHAR(255) DEFAULT NULL, CHANGE last_name last_name VARCHAR(255) DEFAULT NULL, CHANGE full_name full_name VARCHAR(255) DEFAULT NULL, CHANGE secret secret VARCHAR(255) DEFAULT NULL, CHANGE dn dn VARCHAR(255) DEFAULT NULL;');
        }

        $indexes = $schemaManger->listTableIndexes('users');

        if (!array_key_exists('uniq_1483a5e9c05fb297', $indexes)) {
            $connection->executeQuery('CREATE UNIQUE INDEX UNIQ_1483A5E9C05FB297 ON users (confirmation_token);');
        }

        $fileSystem->remove(__FILE__);
        $completed = true;
    }

    echo $container->get('twig')->render('update.html.twig', array(
        'form' => $form->createView(),
        'completed' => $completed,
    ));

    if ($completed) {
        $fileSystem->remove(SYMFONY_CACHE_PATH);
        $fileSystem->mkdir(SYMFONY_CACHE_PATH);
    }

    exit;
}

if (!isset($_GET['checked_requirements']) || $_GET['checked_requirements'] != '1') {

    require_once dirname(__FILE__) . '/../var/SymfonyRequirements.php';

    $symfonyRequirements = new SymfonyRequirements();

    $symfonyRequirements->addRequirement(
        extension_loaded('intl'),
        'intl extension must be available',
        'Install and enable the <strong>intl</strong> extension.'
    );

    $symfonyRequirements->addRequirement(
        function_exists('mysqli_connect'),
        'mysql extension must be available',
        'Install and enable the <strong>mysql</strong> extension.'
    );

    $symfonyRequirements->addRequirement(
        is_writable(__FILE__),
        'install.php must be writeable',
        'Make sure <strong>install.php</strong> is writeable.'
    );

    $majorProblems = $symfonyRequirements->getFailedRequirements();
    $hasMajorProblems = (bool)count($majorProblems);

    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="robots" content="noindex,nofollow"/>
        <title>Symfony Configuration Checker</title>
        <style>
            /* styles copied from symfony framework bundle */
            html {
                background-color: #181A1E;
            }

            body {
                font: 11px Verdana, Arial, sans-serif;
                color: #333;
            }

            .sf-reset, .sf-reset .block, .sf-reset #message {
                margin: auto;
            }

            img {
                border: 0;
            }

            .clear {
                clear: both;
                height: 0;
                font-size: 0;
                line-height: 0;
            }

            .clear-fix:after {
                content: "\0020";
                display: block;
                height: 0;
                clear: both;
                visibility: hidden;
            }

            .clear-fix {
                display: inline-block;
            }

            * html .clear-fix {
                height: 1%;
            }

            .clear-fix {
                display: block;
            }

            .header {
                padding: 30px 30px 20px 30px;
            }

            .header-logo {
                float: left;
            }

            #content {
                width: 970px;
                margin: 0 auto;
            }

            #content pre {
                white-space: normal;
                font-family: Arial, Helvetica, sans-serif;
            }

            /*
            Copyright (c) 2010, Yahoo! Inc. All rights reserved.
            Code licensed under the BSD License:
            http://developer.yahoo.com/yui/license.html
            version: 3.1.2
            build: 56
            */
            .sf-reset div, .sf-reset dl, .sf-reset dt, .sf-reset dd, .sf-reset ul, .sf-reset ol, .sf-reset li, .sf-reset h1, .sf-reset h2, .sf-reset h3, .sf-reset h4, .sf-reset h5, .sf-reset h6, .sf-reset pre, .sf-reset code, .sf-reset form, .sf-reset fieldset, .sf-reset legend, .sf-reset input, .sf-reset textarea, .sf-reset p, .sf-reset blockquote, .sf-reset th, .sf-reset td {
                margin: 0;
                padding: 0;
            }

            .sf-reset table {
                border-collapse: collapse;
                border-spacing: 0;
            }

            .sf-reset fieldset, .sf-reset img {
                border: 0;
            }

            .sf-reset address, .sf-reset caption, .sf-reset cite, .sf-reset code, .sf-reset dfn, .sf-reset em, .sf-reset strong, .sf-reset th, .sf-reset var {
                font-style: normal;
                font-weight: normal;
            }

            .sf-reset li {
                list-style: none;
            }

            .sf-reset caption, .sf-reset th {
                text-align: left;
            }

            .sf-reset h1, .sf-reset h2, .sf-reset h3, .sf-reset h4, .sf-reset h5, .sf-reset h6 {
                font-size: 100%;
                font-weight: normal;
            }

            .sf-reset q:before, .sf-reset q:after {
                content: '';
            }

            .sf-reset abbr, .sf-reset acronym {
                border: 0;
                font-variant: normal;
            }

            .sf-reset sup {
                vertical-align: text-top;
            }

            .sf-reset sub {
                vertical-align: text-bottom;
            }

            .sf-reset input, .sf-reset textarea, .sf-reset select {
                font-family: inherit;
                font-size: inherit;
                font-weight: inherit;
            }

            .sf-reset input, .sf-reset textarea, .sf-reset select {
                font-size: 100%;
            }

            .sf-reset legend {
                color: #000;
            }

            .sf-reset abbr {
                border-bottom: 1px dotted #000;
                cursor: help;
            }

            .sf-reset p {
                font-size: 14px;
                line-height: 20px;
                padding-bottom: 20px;
            }

            .sf-reset strong {
                color: #313131;
                font-weight: bold;
            }

            .sf-reset a {
                color: #6c6159;
            }

            .sf-reset a img {
                border: none;
            }

            .sf-reset a:hover {
                text-decoration: underline;
            }

            .sf-reset em {
                font-style: italic;
            }

            .sf-reset h2,
            .sf-reset h3 {
                font-weight: bold;
            }

            .sf-reset h1 {
                font-size: 20px;
                color: #313131;
                word-wrap: break-word;
            }

            .sf-reset li {
                padding-bottom: 10px;
            }

            .sf-reset .block {
                -moz-border-radius: 16px;
                -webkit-border-radius: 16px;
                border-radius: 16px;
                margin-bottom: 20px;
                background-color: #FFFFFF;
                border: 1px solid #dfdfdf;
                padding: 40px 50px;
                word-break: break-all;
            }

            .sf-reset h2 {
                font-size: 16px;
            }

            .sf-reset li a {
                background: none;
                color: #868686;
                text-decoration: none;
            }

            .sf-reset li a:hover {
                background: none;
                color: #313131;
                text-decoration: underline;
            }

            .sf-reset ol {
                padding: 10px 0;
            }

            .sf-reset ol li {
                list-style: decimal;
                margin-left: 20px;
                padding: 2px;
                padding-bottom: 20px;
            }

            .sf-reset ol ol li {
                list-style-position: inside;
                margin-left: 0;
                white-space: nowrap;
                font-size: 12px;
                padding-bottom: 0;
            }

            .sf-reset li .selected {
                background-color: #ffd;
            }

            .sf-button {
                display: -moz-inline-box;
                display: inline-block;
                text-align: center;
                vertical-align: middle;
                border: 0;
                background: transparent none;
                text-transform: uppercase;
                cursor: pointer;
                font: bold 11px Arial, Helvetica, sans-serif;
            }

            .sf-button span {
                text-decoration: none;
                display: block;
                height: 28px;
                float: left;
            }

            .sf-button .border-l {
                text-decoration: none;
                display: block;
                height: 28px;
                float: left;
                padding: 0 0 0 7px;
                background: transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAcCAYAAACtQ6WLAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAQtJREFUeNpiPHnyJAMakARiByDWYEGT8ADiYGVlZStubm5xlv///4MEQYoKZGRkQkRERLRYWVl5wYJQyXBZWdkwCQkJUxAHKgaWlAHSLqKiosb//v1DsYMFKGCvoqJiDmQzwXTAJYECulxcXNLoumCSoszMzDzoumDGghQwYZUECWIzkrAkSIIGOmlkLI10AiX//P379x8jIyMTNmPf/v79+ysLCwsvuiQoNi5//fr1Kch4dAyS3P/gwYMTQBP+wxwHw0xA4gkQ73v9+vUZdJ2w1Lf82bNn4iCHCQoKasHsZw4ODgbRIL8c+/Lly5M3b978Y2dn5wC6npkFLXnsAOKLjx49AmUHLYAAAwBoQubG016R5wAAAABJRU5ErkJggg==) no-repeat top left;
            }

            .sf-button .border-r {
                padding: 0 7px 0 0;
                background: transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAcCAYAAACtQ6WLAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAR1JREFUeNpiPHnyZCMDA8MNID5gZmb2nAEJMH7//v3N169fX969e/cYkL8WqGAHXPLv37//QYzfv39/fvPmzbUnT56sAXInmJub/2H5/x8sx8DCwsIrISFhDmQyPX78+CmQXs70798/BmQsKipqBNTgdvz4cWkmkE5kDATMioqKZkCFdiwg1eiAi4tLGqhQF24nMmBmZuYEigth1QkEbEBxTlySYPvJkwSJ00AnjYylgU6gxB8g/oFVEphkvgLF32KNMmCCewYUv4qhEyj47+HDhyeBzIMYOoEp8CxQw56wsLAncJ1//vz5/P79+2svX74EJc2V4BT58+fPd8CE/QKYHMGJOiIiAp6oWW7evDkNSF8DZYfIyEiU7AAQYACJ2vxVdJW4eQAAAABJRU5ErkJggg==) right top no-repeat;
            }

            .sf-button .btn-bg {
                padding: 0 14px;
                color: #636363;
                line-height: 28px;
                background: transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAcCAYAAACgXdXMAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAClJREFUeNpiPnny5EKGf//+/Wf6//8/A4QAcrGzKCZwGc9sa2urBBBgAIbDUoYVp9lmAAAAAElFTkSuQmCC) repeat-x top left;
            }

            .sf-button:hover .border-l,
            .sf-button-selected .border-l {
                background: transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAcCAYAAACtQ6WLAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAR9JREFUeNpi/P//PwMyOHfunDqQSgNiexZkibNnzxYBqZa3HOs5v7PcYQBLnjlzhg1IbfzIdsTjA/t+ht9Mr8GKwZL//v3r+sB+0OMN+zqIEf8gFMvJkyd1gXTOa9YNDP//otrPAtSV/Jp9HfPff78Z0AEL0LUeXxivMfxD0wXTqfjj/2ugkf+wSrL9/YtpJEyS4S8WI5Ek/+GR/POPFjr//cenE6/kP9q4Fo/kr39/mdj+M/zFkGQCSj5i+ccPjLJ/GBgkuYOHQR1sNDpmAkb2LBmWwL///zKCIxwZM0VHR18G6p4uxeLLAA4tJMwEshiou1iMxXaHLGswA+t/YbhORuQUv2DBAnCifvxzI+enP3dQJUFg/vz5sOzgBBBgAPxX9j0YnH4JAAAAAElFTkSuQmCC) no-repeat top left;
            }

            .sf-button:hover .border-r,
            .sf-button-selected .border-r {
                background: transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAcAAAAcCAYAAACtQ6WLAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAT5JREFUeNpiPHv27BkGBoaDQDzLyMjoJgMSYHrM3WX8hn1d0f///88DFRYhSzIuv2X5H8Rg/SfKIPDTkYH/l80OINffxMTkF9O/f/8ZQPgnwyuGl+wrGd6x7vf49+9fO9jYf3+Bkkj4NesmBqAV+SdPntQC6vzHgIz//gOawbqOGchOxtAJwp8Zr4F0e7D8/fuPAR38/P8eZIo0yz8skv8YvoIk+YE6/zNgAyD7sRqLkPzzjxY6/+HS+R+fTkZ8djLh08lCUCcuSWawJGbwMTGwg7zyBatX2Bj5QZKPsBrLzaICktzN8g/NWEYGZgYZjoC/wMiei5FMpFh8QPSU6Ojoy3Cd7EwiDBJsDgxiLNY7gLrKQGIsHAxSDHxAO2TZ/b8D+TVxcXF9MCtYtLiKLgDpfUDVsxITE1GyA0CAAQA2E/N8VuHyAAAAAABJRU5ErkJggg==) right top no-repeat;
            }

            .sf-button:hover .btn-bg,
            .sf-button-selected .btn-bg {
                color: #FFFFFF;
                text-shadow: 0 1px 1px #6b9311;
                background: transparent url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAAcCAIAAAAvP0KbAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAEFJREFUeNpiPnv2LNMdvlymf///M/37B8R/QfQ/MP33L4j+B6Qh7L9//sHpf2h8MA1V+w/KRjYLaDaLCU8vQIABAFO3TxZriO4yAAAAAElFTkSuQmCC) repeat-x top left;
            }

            /* styles copied from bundles/sensiodistribution/webconfigurator/css/install.css */
            body {
                font-size: 14px;
            }

            .sf-reset h1.title {
                font-size: 45px;
                padding-bottom: 30px;
            }

            .sf-reset h2 {
                font-weight: bold;
                color: #FFFFFF;
                margin-bottom: 10px;
                background-color: #aacd4e;
                padding: 2px 4px;
                display: inline-block;
                text-transform: uppercase;
            }

            .sf-reset ul a,
            .sf-reset ul a:hover {
                background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAUAAAAICAYAAAAx8TU7AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAFdJREFUeNpiYACBjjOhDEiACSggCKTLgXQ5TJARqhIkcReIKxgqTGYxwvV0nDEGkmeAOIwJySiQ4HsgvseIpGo3ELsCtZ9lRDIvDCiwhwHJPEFkJwEEGACq6hdnax8y1AAAAABJRU5ErkJggg==) no-repeat right 7px;
                padding-right: 10px;
            }

            .sf-reset ul, ol {
                padding-left: 20px;
            }

            .sf-reset li {
                padding-bottom: 18px;
            }

            .sf-reset ol li {
                list-style-type: decimal;
            }

            .sf-reset ul li {
                list-style-type: none;
            }

            .sf-reset .symfony-blocks-install {
                overflow: hidden;
            }

            .sf-reset .symfony-install-continue {
                font-size: 0.95em;
                padding-left: 0;
            }

            .sf-reset .symfony-install-continue li {
                padding-bottom: 10px;
            }

            .sf-reset .ok {
                color: #fff;
                background-color: #6d6;
                padding: 10px;
                margin-bottom: 20px;
            }

            .sf-reset .ko {
                background-color: #d66;
            }

            .sf-reset p.help {
                padding: 12px 16px;
                word-break: break-word;
            }

            .version {
                text-align: right;
                font-size: 10px;
                margin-right: 20px;
            }

            .sf-reset a,
            .sf-reset li a {
                color: #08C;
                text-decoration: none;
            }

            .sf-reset a:hover,
            .sf-reset li a:hover {
                color: #08C;
                text-decoration: underline;
            }

            .sf-reset textarea {
                padding: 7px;
            }
        </style>
    </head>
    <body>
    <div id="content">
        <div class="header clear-fix">
            <div class="header-logo">
                <img src="img/logo.png" style="height: 80px;" />
            </div>
        </div>

        <div class="sf-reset">
            <div class="block">
                <div class="symfony-block-content">
                    <h1 class="title">Configuration Checker</h1>
                    <p>
                        This script analyzes your system to check whether is
                        ready to run TurtlePass.
                    </p>

                    <?php if ($hasMajorProblems): ?>
                        <h2 class="ko">Major problems</h2>
                        <p>Major problems have been detected and <strong>must</strong> be fixed before continuing:</p>
                        <ol>
                            <?php foreach ($majorProblems as $problem): ?>
                                <li><?php echo $problem->getTestMessage() ?>
                                    <p class="help"><em><?php echo $problem->getHelpHtml() ?></em></p>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>

                    <?php if ($symfonyRequirements->hasPhpIniConfigIssue()): ?>
                        <p id="phpini">*
                            <?php if ($symfonyRequirements->getPhpIniConfigPath()): ?>
                                Changes to the <strong>php.ini</strong> file must be done in "
                                <strong><?php echo $symfonyRequirements->getPhpIniConfigPath() ?></strong>".
                            <?php else: ?>
                                To change settings, create a "<strong>php.ini</strong>".
                            <?php endif; ?>
                        </p>
                    <?php endif; ?>

                    <?php if (!$hasMajorProblems): ?>
                        <p class="ok">All checks passed successfully. Your system is ready to run TurtlePass.</p>
                    <?php endif; ?>

                    <ul class="symfony-install-continue">
                        <?php if (!$hasMajorProblems): ?>
                            <li><a href="install.php?checked_requirements=1">Continue Installation</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

$csrfTokenManager = new CsrfTokenManager();

$validator = Validation::createValidator();

$translator = new Translator('en');
$translator->addLoader('xlf', new XliffFileLoader());
$translator->addResource('xlf', SYMFONY_VENDOR_DIR . '/Component/Form/Resources/translations/validators.en.xlf', 'en', 'validators');
$translator->addResource('xlf', SYMFONY_VENDOR_DIR . '/Component/Validator/Resources/translations/validators.en.xlf', 'en', 'validators');

$twig = new Environment(new FilesystemLoader([
    SYMFONY_VENDOR_DIR . '/Bridge/Twig/Resources/views/Form',
    realpath(__DIR__ . '/../app/Resources/views'),
]));
$formEngine = new TwigRendererEngine(['install_bootstrap_3_horizontal_layout.html.twig'], $twig);
$twig->addRuntimeLoader(new FactoryRuntimeLoader([
    FormRenderer::class => function () use ($formEngine, $csrfTokenManager) {
        return new FormRenderer($formEngine, $csrfTokenManager);
    },
]));
$twig->addExtension(new FormExtension());
$twig->addExtension(new TranslationExtension());

$formFactory = Forms::createFormFactoryBuilder()
    ->addExtension(new CsrfExtension($csrfTokenManager))
    ->addExtension(new ValidatorExtension($validator))
    ->getFormFactory();

$form = $formFactory->createBuilder(\Symfony\Component\Form\Extension\Core\Type\FormType::class, null, [
    'action' => 'install.php?checked_requirements=1'
])
    ->add('database_host', TextType::class, array(
        'attr' => array(
            'placeholder' => 'eg. localhost',
        ),
        'label' => 'Database Host',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
        ),
    ))
    ->add('database_port', NumberType::class, array(
        'attr' => array(
            'placeholder' => 'Default is 3306',
        ),
        'label' => 'Database Port',
        'required' => false,
    ))
    ->add('database_user', TextType::class, array(
        'attr' => array(
            'placeholder' => 'Your MySQL user',
        ),
        'label' => 'User',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
            new Length(array('min' => 2)),
        ),
    ))
    ->add('database_password', PasswordType::class, array(
        'attr' => array(
            'placeholder' => 'Your MySQL password',
        ),
        'label' => 'Password',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
        ),
    ))
    ->add('database_name', TextType::class, array(
        'attr' => array(
            'placeholder' => 'Database name in database (eg. turtlepass)',
        ),
        'label' => 'Database Name',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
            new Length(array('min' => 2)),
        ),
    ))
    ->add('secret_key', TextType::class, array(
        'attr' => array(
            'placeholder' => 'Your own random 256 bit key (32 characters)',
        ),
        'label' => 'Encryption Key',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
            new Length(array('min' => 32, 'max' => 32)),
            new Regex(array('pattern' => '/^[a-zA-Z0-9]{32}/')),
        ),
    ))
    ->add('firstname', TextType::class, array(
        'attr' => array(
            'placeholder' => 'Your first name',
        ),
        'label' => 'First Name',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
            new Length(array('min' => 1)),
        ),
    ))
    ->add('lastname', TextType::class, array(
        'attr' => array(
            'placeholder' => 'Your last name',
        ),
        'label' => 'Last Name',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
            new Length(array('min' => 1)),
        ),
    ))
    ->add('email', EmailType::class, array(
        'attr' => array(
            'placeholder' => 'some@example.com',
        ),
        'label' => 'E-Mail',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
            new Email(),
        ),
    ))
    ->add('username', TextType::class, array(
        'attr' => array(
            'placeholder' => 'The admin username',
        ),
        'label' => 'Username',
        'required' => true,
        'constraints' => array(
            new NotBlank(),
            new Length(array('min' => 4)),
        ),
    ))
    ->add('password', RepeatedType::class, array(
        'type' => PasswordType::class,
        'first_options' => array(
            'label' => 'Password',
            'attr' => array(
                'placeholder' => 'The admin password',

            ),
            'constraints' => array(
                new NotBlank(),
                new Length(array('min' => 5)),
            ),
        ),
        'second_options' => array(
            'label' => 'Repeat Password',
            'attr' => array(
                'placeholder' => 'The admin password again...',
            ),
            'constraints' => array(
                new NotBlank(),
            ),
        ),
        'required' => true,
    ))
    ->add('send', SubmitType::class, array(
        'label' => 'Install',
        'attr' => array(
            'class' => 'btn-primary',
        ),
    ))
    ->getForm();

$form->handleRequest();

if ($form->isSubmitted()) {

    $data = $form->getData();

    $config = new Configuration();

    $connectionParams = array(
        'dbname' => $data['database_name'],
        'user' => $data['database_user'],
        'password' => $data['database_password'],
        'host' => $data['database_host'],
        'port' => $data['database_port'],
        'driver' => 'pdo_mysql',
    );
    $connection = DriverManager::getConnection($connectionParams, $config);

    try {
        $connection->connect();

        $schemaManager = $connection->getSchemaManager();
        if (count($schemaManager->listTables())) {
            $form->get('database_host')->addError(new \Symfony\Component\Form\FormError('Database is not empty'));
        }

    } catch (\Doctrine\DBAL\Exception\ConnectionException $exception) {

        if ($exception->getErrorCode() === 1049) {
            $form->get('database_name')->addError(new \Symfony\Component\Form\FormError('Unknown database'));
        } else if ($exception->getErrorCode() === 1044) {
            $form->get('database_user')->addError(new \Symfony\Component\Form\FormError('Invalid user'));
        } else if ($exception->getErrorCode() === 1045) {
            $form->get('database_password')->addError(new \Symfony\Component\Form\FormError('Invalid password'));
        } else {
            $form->get('database_host')->addError(new \Symfony\Component\Form\FormError('Could not connect to database. Error message: ' . $exception->getMessage()));
        }
    }


    if ($form->isValid()) {

        // write config file
        $parametersContent = array();
        $parametersContent[] = 'parameters:';
        $parametersContent[] = '    database_host: ' . escapeyaml($data['database_host']);
        $parametersContent[] = '    database_port: ' . escapeyaml($data['database_port']);
        $parametersContent[] = '    database_name: ' . escapeyaml($data['database_name']);
        $parametersContent[] = '    database_user: ' . escapeyaml($data['database_user']);
        $parametersContent[] = '    database_password: ' . escapeyaml($data['database_password']);
        $parametersContent[] = '    secret: ' . hash('sha256', md5(__DIR__) . uniqid(time(), true));
        $parametersContent[] = '    secret_key: ' . escapeyaml($data['secret_key']);

        $fileSystem->dumpFile(SYMFONY_PARAMETER_FILE, implode(PHP_EOL, $parametersContent));

        // Create Database Structure
        $databaseStructure = array(
            'CREATE TABLE settings (id VARCHAR(255) NOT NULL, setting_type VARCHAR(255) NOT NULL, value VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, choices LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE api_access_token (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT UNSIGNED NOT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, description VARCHAR(64) DEFAULT NULL, is_default TINYINT(1) NOT NULL, is_custom TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_BCC804C55F37A13B (token), INDEX IDX_BCC804C519EB6921 (client_id), INDEX IDX_BCC804C5A76ED395 (user_id), INDEX api_access_token_default (is_default), INDEX api_access_token_token (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE api_auth_code (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, token VARCHAR(255) NOT NULL, redirect_uri LONGTEXT NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_24E775575F37A13B (token), INDEX IDX_24E7755719EB6921 (client_id), INDEX IDX_24E77557A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE api_client (id INT AUTO_INCREMENT NOT NULL, random_id VARCHAR(255) NOT NULL, redirect_uris LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', secret VARCHAR(255) NOT NULL, allowed_grant_types LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE api_refresh_token (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, user_id INT UNSIGNED DEFAULT NULL, token VARCHAR(255) NOT NULL, expires_at INT DEFAULT NULL, scope VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_6F2294195F37A13B (token), INDEX IDX_6F22941919EB6921 (client_id), INDEX IDX_6F229419A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE user_invalid_logins (id INT UNSIGNED AUTO_INCREMENT NOT NULL, ip_address VARBINARY(255) NOT NULL, fail_count INT NOT NULL, create_date DATETIME NOT NULL, last_fail_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE user_login_accesses (id INT UNSIGNED AUTO_INCREMENT NOT NULL, from_ip_address VARBINARY(255) NOT NULL, to_ip_address VARBINARY(255) NOT NULL, is_whitelist TINYINT(1) NOT NULL, create_date DATETIME NOT NULL, expire_date DATETIME DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE users (id INT UNSIGNED AUTO_INCREMENT NOT NULL, language CHAR(2) NOT NULL, type VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, username_canonical VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, email_canonical VARCHAR(255) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(255) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', deleted TINYINT(1) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, full_name VARCHAR(255) NOT NULL, secret VARCHAR(255) DEFAULT NULL, dn VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_1483A5E9A0D96FBF (email_canonical), INDEX IDX_1483A5E9D4DB71B5 (language), INDEX user_first_name (first_name), INDEX user_last_name (last_name), INDEX user_full_name (full_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE user_groups (id INT UNSIGNED AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE user_group_allocations (id INT UNSIGNED NOT NULL, group_id INT UNSIGNED NOT NULL, INDEX IDX_1D4EE614BF396750 (id), INDEX IDX_1D4EE614FE54D947 (group_id), PRIMARY KEY(id, group_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_group_user_group_accesses (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_group_id INT UNSIGNED NOT NULL, user_group_id INT UNSIGNED NOT NULL, access_right SMALLINT UNSIGNED NOT NULL, INDEX IDX_F63628536EC34B87 (password_group_id), INDEX IDX_F63628531ED93D47 (user_group_id), UNIQUE INDEX password_group_user_group_access_uqe (password_group_id, user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_user_group_accesses (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_id INT UNSIGNED NOT NULL, user_group_id INT UNSIGNED NOT NULL, access_right SMALLINT UNSIGNED NOT NULL, INDEX IDX_6502B6663E4A79C1 (password_id), INDEX IDX_6502B6661ED93D47 (user_group_id), UNIQUE INDEX password_user_group_access_uqe (password_id, user_group_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE passwords (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_group_id INT UNSIGNED NOT NULL, name VARCHAR(255) NOT NULL, name_canonical VARCHAR(255) NOT NULL, icon VARCHAR(255) DEFAULT \'fa-key\' NOT NULL, is_log_enabled TINYINT(1) DEFAULT \'0\' NOT NULL, url VARCHAR(255) DEFAULT NULL, username VARCHAR(255) DEFAULT NULL, username_canonical VARCHAR(255) DEFAULT NULL, password VARCHAR(255) DEFAULT NULL, create_date DATETIME NOT NULL, last_update_date DATETIME DEFAULT NULL, notice LONGTEXT DEFAULT NULL, password_type VARCHAR(255) NOT NULL, custom_fields LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', INDEX IDX_ED822B166EC34B87 (password_group_id), INDEX password_name_canonical (name_canonical), INDEX password_username_canonical (username_canonical), INDEX password_url (url), INDEX password_search (name_canonical, username_canonical, url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_accesses (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, access_right SMALLINT UNSIGNED NOT NULL, INDEX IDX_72A358223E4A79C1 (password_id), INDEX IDX_72A35822A76ED395 (user_id), UNIQUE INDEX password_access_uqe (password_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_groups (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_group_id INT UNSIGNED DEFAULT NULL, name VARCHAR(255) NOT NULL, icon VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_7D84A8486EC34B87 (password_group_id), INDEX password_group_name (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_group_accesses (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_group_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, access_right SMALLINT UNSIGNED NOT NULL, INDEX IDX_8CAE8DB46EC34B87 (password_group_id), INDEX IDX_8CAE8DB4A76ED395 (user_id), UNIQUE INDEX password_group_access_uqe (password_group_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_logs (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, log_key VARCHAR(255) NOT NULL, create_date DATETIME NOT NULL, INDEX IDX_62DAC1173E4A79C1 (password_id), INDEX IDX_62DAC117A76ED395 (user_id), INDEX password_log_key (log_key), INDEX password_log_create_date (create_date), INDEX password_log_key_create_date (log_key, create_date), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_bank_accounts (id INT UNSIGNED NOT NULL, bank_name VARCHAR(255) NOT NULL, account_holder VARCHAR(255) DEFAULT NULL, bank_code VARCHAR(255) DEFAULT NULL, account_number VARCHAR(255) DEFAULT NULL, iban VARCHAR(255) DEFAULT NULL, pin VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_credit_cards (id INT UNSIGNED NOT NULL, card_type VARCHAR(255) DEFAULT NULL, card_number VARCHAR(255) DEFAULT NULL, card_cvc VARCHAR(255) DEFAULT NULL, pin VARCHAR(255) DEFAULT NULL, valid_from VARCHAR(255) DEFAULT NULL, valid_to VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_emails (id INT UNSIGNED NOT NULL, email_type VARCHAR(255) DEFAULT NULL, host VARCHAR(255) DEFAULT NULL, port VARCHAR(255) DEFAULT NULL, auth_method VARCHAR(255) DEFAULT NULL, smtp_host VARCHAR(255) DEFAULT NULL, smtp_port VARCHAR(255) DEFAULT NULL, smtp_auth_method VARCHAR(255) DEFAULT NULL, smtp_username VARCHAR(255) DEFAULT NULL, smtp_password VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_servers (id INT UNSIGNED NOT NULL, host VARCHAR(255) DEFAULT NULL, port VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_software_licenses (id INT UNSIGNED NOT NULL, version VARCHAR(255) DEFAULT NULL, license_key VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE languages (id CHAR(2) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE language_translations (id INT AUTO_INCREMENT NOT NULL, object_id CHAR(2) DEFAULT NULL, locale VARCHAR(8) NOT NULL, field VARCHAR(32) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_E3BB4E52232D562B (object_id), UNIQUE INDEX language_translations_uqe (locale, object_id, field), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_share_links (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_id INT UNSIGNED NOT NULL, `mode` INT NOT NULL, token VARCHAR(255) NOT NULL, create_date DATETIME NOT NULL, valid_to DATETIME DEFAULT NULL, recipient VARCHAR(255) DEFAULT NULL, view_limit INT DEFAULT NULL, view_count INT DEFAULT NULL, INDEX IDX_E45F355A3E4A79C1 (password_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'CREATE TABLE password_group_sorting (id INT UNSIGNED AUTO_INCREMENT NOT NULL, password_group_id INT UNSIGNED NOT NULL, user_id INT UNSIGNED NOT NULL, sorting INT UNSIGNED NOT NULL, INDEX IDX_40EEDFC96EC34B87 (password_group_id), INDEX IDX_40EEDFC9A76ED395 (user_id), UNIQUE INDEX password_group_sort_uqe (password_group_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;',
            'ALTER TABLE password_group_sorting ADD CONSTRAINT FK_40EEDFC96EC34B87 FOREIGN KEY (password_group_id) REFERENCES password_groups (id) ON DELETE CASCADE;',
            'ALTER TABLE password_group_sorting ADD CONSTRAINT FK_40EEDFC9A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);',
            'ALTER TABLE api_access_token ADD CONSTRAINT FK_BCC804C519EB6921 FOREIGN KEY (client_id) REFERENCES api_client (id);',
            'ALTER TABLE api_access_token ADD CONSTRAINT FK_BCC804C5A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);',
            'ALTER TABLE api_auth_code ADD CONSTRAINT FK_24E7755719EB6921 FOREIGN KEY (client_id) REFERENCES api_client (id);',
            'ALTER TABLE api_auth_code ADD CONSTRAINT FK_24E77557A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);',
            'ALTER TABLE api_refresh_token ADD CONSTRAINT FK_6F22941919EB6921 FOREIGN KEY (client_id) REFERENCES api_client (id);',
            'ALTER TABLE api_refresh_token ADD CONSTRAINT FK_6F229419A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);',
            'ALTER TABLE users ADD CONSTRAINT FK_1483A5E9D4DB71B5 FOREIGN KEY (language) REFERENCES languages (id);',
            'ALTER TABLE passwords ADD CONSTRAINT FK_ED822B166EC34B87 FOREIGN KEY (password_group_id) REFERENCES password_groups (id) ON DELETE CASCADE;',
            'ALTER TABLE password_accesses ADD CONSTRAINT FK_72A358223E4A79C1 FOREIGN KEY (password_id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE password_accesses ADD CONSTRAINT FK_72A35822A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);',
            'ALTER TABLE password_groups ADD CONSTRAINT FK_7D84A8486EC34B87 FOREIGN KEY (password_group_id) REFERENCES password_groups (id);',
            'ALTER TABLE password_group_accesses ADD CONSTRAINT FK_8CAE8DB46EC34B87 FOREIGN KEY (password_group_id) REFERENCES password_groups (id) ON DELETE CASCADE;',
            'ALTER TABLE password_group_accesses ADD CONSTRAINT FK_8CAE8DB4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);',
            'ALTER TABLE password_logs ADD CONSTRAINT FK_62DAC1173E4A79C1 FOREIGN KEY (password_id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE password_logs ADD CONSTRAINT FK_62DAC117A76ED395 FOREIGN KEY (user_id) REFERENCES users (id);',
            'ALTER TABLE password_bank_accounts ADD CONSTRAINT FK_8CC961B0BF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE password_credit_cards ADD CONSTRAINT FK_1A78DB52BF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE password_emails ADD CONSTRAINT FK_C168796ABF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE password_servers ADD CONSTRAINT FK_6705A4F8BF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE password_software_licenses ADD CONSTRAINT FK_6A0DEDC1BF396750 FOREIGN KEY (id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE language_translations ADD CONSTRAINT FK_E3BB4E52232D562B FOREIGN KEY (object_id) REFERENCES languages (id) ON DELETE CASCADE;',
            'ALTER TABLE password_share_links ADD CONSTRAINT FK_E45F355A3E4A79C1 FOREIGN KEY (password_id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE password_logs ADD share_link_id INT UNSIGNED DEFAULT NULL, CHANGE user_id user_id INT UNSIGNED DEFAULT NULL;',
            'ALTER TABLE password_logs ADD CONSTRAINT FK_62DAC117EFC8A8ED FOREIGN KEY (share_link_id) REFERENCES password_share_links (id);',
            'ALTER TABLE user_group_allocations ADD CONSTRAINT FK_1D4EE614BF396750 FOREIGN KEY (id) REFERENCES users (id) ON DELETE CASCADE;',
            'ALTER TABLE user_group_allocations ADD CONSTRAINT FK_1D4EE614FE54D947 FOREIGN KEY (group_id) REFERENCES user_groups (id) ON DELETE CASCADE;',
            'ALTER TABLE password_group_user_group_accesses ADD CONSTRAINT FK_F63628536EC34B87 FOREIGN KEY (password_group_id) REFERENCES password_groups (id) ON DELETE CASCADE;',
            'ALTER TABLE password_group_user_group_accesses ADD CONSTRAINT FK_F63628531ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_groups (id) ON DELETE CASCADE;',
            'ALTER TABLE password_user_group_accesses ADD CONSTRAINT FK_6502B6663E4A79C1 FOREIGN KEY (password_id) REFERENCES passwords (id) ON DELETE CASCADE;',
            'ALTER TABLE password_user_group_accesses ADD CONSTRAINT FK_6502B6661ED93D47 FOREIGN KEY (user_group_id) REFERENCES user_groups (id) ON DELETE CASCADE;',
            'CREATE INDEX IDX_62DAC117EFC8A8ED ON password_logs (share_link_id);',
            'CREATE UNIQUE INDEX UNIQ_1483A5E9C05FB297 ON users (confirmation_token);',
            'INSERT INTO languages VALUES ("de", "German"), ("en", "English");',
            'INSERT INTO `api_client` (`id`, `random_id`, `redirect_uris`, `secret`, `allowed_grant_types`) VALUES (1, \''.\FOS\OAuthServerBundle\Util\Random::generateToken().'\', \'a:0:{}\', \''.\FOS\OAuthServerBundle\Util\Random::generateToken().'\', \'a:1:{i:0;s:18:"authorization_code";}\');',
        );

        foreach ($databaseStructure as $sql) {
            $connection->executeQuery($sql);
        }

        $passwordEncoder = new \Symfony\Component\Security\Core\Encoder\Pbkdf2PasswordEncoder('sha512', true, 1009, 40);
        $salt = \Main\AppBundle\Services\StringManager::generateString(31);
        $password = $passwordEncoder->encodePassword($data['password'], $salt);

        $roles = 'a:1:{i:0;s:16:"ROLE_SUPER_ADMIN";}';

        $sql = "INSERT INTO `users` (`language`, `type`, `username`, `username_canonical`, `email`, `email_canonical`, `enabled`, `salt`, `password`, `last_login`, `confirmation_token`, `password_requested_at`, `roles`, `deleted`, `first_name`, `last_name`, `full_name`, `secret`) VALUES
('en', 'user', ?, ?, ?, ?, 1, ?, ?, NULL, NULL, NULL, ?, 0, ?, ?, ?, NULL);";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $data['username']);
        $stmt->bindValue(2, mb_strtolower($data['username']));
        $stmt->bindValue(3, $data['email']);
        $stmt->bindValue(4, mb_strtolower($data['email']));
        $stmt->bindValue(5, $salt);
        $stmt->bindValue(6, $password);
        $stmt->bindValue(7, $roles);
        $stmt->bindValue(8, $data['firstname']);
        $stmt->bindValue(9, $data['lastname']);
        $stmt->bindValue(10, $data['firstname'].' '.$data['lastname']);
        $stmt->execute();

        $userId = $connection->lastInsertId();

        $sql = "INSERT INTO `password_groups` (`id`, `password_group_id`, `name`, `icon`, `description`) VALUES (NULL, NULL, 'Personal Passwords', 'fa-user', 'Your personal passwords.');";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, 'Personal Passwords');
        $stmt->bindValue(2, 'fa-user');
        $stmt->bindValue(3, 'Your personal passwords.');
        $stmt->execute();

        $passwordGroupId = $connection->lastInsertId();

        $sql = "INSERT INTO `password_group_accesses` (`id`, `password_group_id`, `user_id`, `access_right`) VALUES (NULL, ?, ?, ?);";
        $stmt = $connection->prepare($sql);
        $stmt->bindValue(1, $userId);
        $stmt->bindValue(2, $passwordGroupId);
        $stmt->bindValue(3, 3);
        $stmt->execute();

        $fileSystem->remove(__FILE__);

        echo $twig->render('install.html.twig', array(
            'form' => $form->createView(),
            'completed' => true,
        ));

        exit;
    }
}

echo $twig->render('install.html.twig', array(
    'form' => $form->createView(),
    'encryption_key' => md5(uniqid(sha1(time()), true)),
    'ssl_warning' => !isset($_SERVER['HTTPS']) || !$_SERVER['HTTPS'] || $_SERVER['HTTPS'] === 'off',
    'writeable_check' => !is_writable(SYMFONY_CONFIG_PATH),
    'parameter_file_path' => SYMFONY_PARAMETER_FILE,
));
