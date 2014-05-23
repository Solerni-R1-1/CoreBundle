<?php

/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Claroline\CoreBundle\Library\Installation;

use Claroline\CoreBundle\Library\Installation\Updater\MaintenancePageUpdater;
use Claroline\CoreBundle\Library\Workspace\TemplateBuilder;
use Symfony\Bundle\SecurityBundle\Command\InitAclCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Claroline\InstallationBundle\Additional\AdditionalInstaller as BaseInstaller;

class AdditionalInstaller extends BaseInstaller
{
    private $logger;
    private $currentVersion;
    private $targetVersion;

    public function __construct()
    {
        $self = $this;
        $this->logger = function ($message) use ($self) {
            $self->log($message);
        };
    }

    public function preInstall()
    {
        $this->setLocale();
        $this->buildDefaultTemplate();
    }

    public function preUpdate($currentVersion, $targetVersion)
    {
        if ( strpos( $targetVersion, 'dev' ) !== false ) {
            $this->targetVersion = $this->getTargetVersion();
            $targetVersion = $this->targetVersion;
        }
        
        if ( strpos( $currentVersion, 'dev' ) !== false ) {
            $this->currentVersion = $this->getCurrentVersion();
            $currentVersion = $this->currentVersion;
        }
        
        $maintenanceUpdater = new Updater\WebUpdater($this->container->getParameter('kernel.root_dir'));
        $maintenanceUpdater->preUpdate();

        $this->setLocale();

        if (version_compare($currentVersion, '2.0', '<') && version_compare($targetVersion, '2.0', '>=') ) {
            $updater020000 = new Updater\Updater020000($this->container);
            $updater020000->setLogger($this->logger);
            $updater020000->preUpdate();
        }

        if (version_compare($currentVersion, '2.9.0', '<') ) {
            $updater020900 = new Updater\Updater020900($this->container);
            $updater020900->setLogger($this->logger);
            $updater020900->preUpdate();
        }
    }

    public function postUpdate($currentVersion, $targetVersion)
    {

        $this->setLocale();

        if ( strpos( $targetVersion, 'dev' ) !== false ) {
            $targetVersion = $this->targetVersion;
        }
        
        if ( strpos( $currentVersion, 'dev' ) !== false ) {
            $currentVersion = $this->currentVersion;
        }
        
        if (version_compare($currentVersion, '2.0', '<')  && version_compare($targetVersion, '2.0', '>=') ) {
            $updater020000 = new Updater\Updater020000($this->container);
            $updater020000->setLogger($this->logger);
            $updater020000->postUpdate();
        }

        if (version_compare($currentVersion, '2.1.2', '<')) {
            $updater020102 = new Updater\Updater020102($this->container);
            $updater020102->setLogger($this->logger);
            $updater020102->postUpdate();
        }

        if (version_compare($currentVersion, '2.1.5', '<')) {
             $this->createAclTablesIfNotExist();
        }

        if (version_compare($currentVersion, '2.2.0', '<')) {
            $updater020200 = new Updater\Updater020200($this->container);
            $updater020200->setLogger($this->logger);
            $updater020200->postUpdate();
        }

        if (version_compare($currentVersion, '2.3.1', '<')) {
            $updater020301 = new Updater\Updater020301($this->container);
            $updater020301->setLogger($this->logger);
            $updater020301->postUpdate();
        }

        if (version_compare($currentVersion, '2.3.4', '<')) {
            $updater020304 = new Updater\Updater020304($this->container);
            $updater020304->setLogger($this->logger);
            $updater020304->postUpdate();
        }

        if (version_compare($currentVersion, '2.5.0', '<')) {
            $updater020500 = new Updater\Updater020500($this->container);
            $updater020500->setLogger($this->logger);
            $updater020500->postUpdate();
        }

        if (version_compare($currentVersion, '2.8.0', '<')) {
            $updater020800 = new Updater\Updater020800($this->container);
            $updater020800->setLogger($this->logger);
            $updater020800->postUpdate();
        }

        if (version_compare($currentVersion, '2.9.0', '<')) {
            $updater020900 = new Updater\Updater020900($this->container);
            $updater020900->setLogger($this->logger);
            $updater020900->postUpdate();
        }

        if (version_compare($currentVersion, '2.10.0', '<')) {
            $updater021000 = new Updater\Updater021000($this->container);
            $updater021000->setLogger($this->logger);
            $updater021000->postUpdate();
        }

        if (version_compare($currentVersion, '2.11.0', '<')) {
            $this->buildDefaultTemplate();
            $updater021000 = new Updater\Updater021100($this->container);
            $updater021000->setLogger($this->logger);
            $updater021000->postUpdate();
        }

        if (version_compare($currentVersion, '2.12.0', '<')) {
            $this->buildDefaultTemplate();
            $updater021200 = new Updater\Updater021200($this->container);
            $updater021200->setLogger($this->logger);
            $updater021200->postUpdate();
        }

        if (version_compare($currentVersion, '2.12.1', '<')) {
            $this->buildDefaultTemplate();
            $updater021200 = new Updater\Updater021201($this->container);
            $updater021200->setLogger($this->logger);
            $updater021200->postUpdate();
        }

        if (version_compare($currentVersion, '2.14.0', '<')) {
            $this->buildDefaultTemplate();
            $updater021400 = new Updater\Updater021400($this->container);
            $updater021400->setLogger($this->logger);
            $updater021400->postUpdate();
        }
        
        // Update or create version.json file with current version
        if (  version_compare( $currentVersion, $targetVersion, '<') ) {
           $this->writeVersionFile( $targetVersion );
        }
    }

    private function setLocale()
    {
        $ch = $this->container->get('claroline.config.platform_config_handler');
        $locale = $ch->getParameter('locale_language');
        $translator = $this->container->get('translator');
        $translator->setLocale($locale);
    }

    private function buildDefaultTemplate()
    {
        $this->log('Creating default workspace template...');
        $defaultTemplatePath = $this->container->getParameter('kernel.root_dir') . '/../templates/default.zip';
        TemplateBuilder::buildDefault($defaultTemplatePath);
    }
    
    private function createAclTablesIfNotExist()
    {
        $this->log('Checking acl tables are initialized...');
        $command = new InitAclCommand();
        $command->setContainer($this->container);
        $command->run(new ArrayInput(array()), new NullOutput() );
    }
    
    public function getCurrentVersion()
    {
        $fileUri = $this->getVersionFileUri( 'version.json' );
        
        if ( $fileUri ) {
            $decodedFile = $this->getContentAndDecode( $fileUri );
            $versionNumber = $decodedFile['currentVersion'];
        } else {
            $versionNumber = 2.13;
        }
        $this->log( 'Current version: ' . $versionNumber );
        return $versionNumber;
    }
    
    private function getTargetVersion()
    {
        
        $fileUri = $this->getVersionFileUri( 'composer.json' );
        
        if ( $fileUri ) {
            $decodedFile = $this->getContentAndDecode( $fileUri );
            $versionNumber = $decodedFile['extra']['branch-alias'];
            if ( $versionNumber ) {
                foreach ( $versionNumber as $key => $value ) {
                   $versionNumber = str_replace ( '.x-dev', '', $value );
                }
            } else {
                $this->log( 'No extra section in composer.json' );
                $versionNumber = null;
            }
        } else {
            $versionNumber = false;
        }

        $this->log('Target version: '. $versionNumber );
        return $versionNumber;
    }
    
    private function writeVersionFile( $targetVersion )
    {
        $fileUri = $this->getVersionFileUri( 'version.json' );
        
        if ( ! $fileUri ) {
            $fileUri = $this->getVersionFileUri( 'composer.json' );
            $fileUri = str_replace( '/composer.json', '/version.json', $fileUri );
        }
        
        $this->log( 'Updating version.json to : ' . $targetVersion );
        $newVersion = json_encode( array( 'currentVersion' => $targetVersion ) );
        $writing = file_put_contents( $fileUri, $newVersion);
    }
    
    private function getVersionFileUri( $file )
    {
        $kernel = $this->container->get('kernel');
        
         try {
            $fileUri = $kernel->locateResource('@ClarolineCoreBundle/'. $file );
        } catch (\InvalidArgumentException $ex) {
            $this->log( $file . ' not found.' );
            return false;
        } catch (\RuntimeException $ex ) {
            $this->log( 'Illegal characters in file name' );
            return false;
        }
        return $fileUri;        
    }
    
    private function getContentAndDecode( $fileUri )
    {
        return json_decode( file_get_contents( $fileUri ), true );
    }
}
