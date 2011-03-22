<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2011 WebIssues Team
*
* This program is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
**************************************************************************/

require_once( '../../system/bootstrap.inc.php' );

class Admin_Setup_Install extends System_Web_Component
{
    private $rules = null;

    protected function __construct()
    {
        parent::__construct();
    }

    protected function execute()
    {
        if ( $this->checkAccess() ) {
            $locale = new System_Api_Locale();
            $this->languageOptions = $locale->getAvailableLanguages();
            $this->engineOptions = $this->getDatabaseEngines();

            $this->form = new System_Web_Form( 'install', $this );
            $this->form->addViewState( 'page', 'language' );
            $this->form->addPersistentField( 'language', 'en_US' );
            $this->form->addPersistentField( 'engine', key( $this->engineOptions ) );
            $this->form->addPersistentField( 'host', 'localhost' );
            $this->form->addPersistentField( 'database', 'webissues' );
            $this->form->addPersistentField( 'user', 'webissues' );
            $this->form->addPersistentField( 'password' );
            $this->form->addPersistentField( 'prefix' );
            $this->form->addPersistentField( 'serverName' );
            $this->form->addPersistentField( 'adminPassword' );
            $this->form->addPersistentField( 'adminConfirm' );

            if ( $this->form->loadForm() )
                $this->processForm();

            switch ( $this->page ) {
                case 'site':
                    $this->validateSite();
                    break;

                case 'connection':
                    $this->validateConnection();
            }

            $this->showRefresh = $this->disableNext || $this->disableInstall;
            $this->showInstall = ( $this->page == 'new_site' || $this->page == 'existing_site' );
            $this->showBack = ( $this->page != 'language' );
            $this->showNext = !$this->showInstall;

            $this->initializeRules();
        }

        $this->view->setDecoratorClass( 'Common_FixedBlock' );
        $this->view->setSlot( 'page_title', $this->tr( 'Server Configuration' ) );
        $this->view->setSlot( 'header', $this->tr( 'Configure your WebIssues Server' ) );

        if ( $this->showInstall ) {
            $javaScript = new System_Web_JavaScript( $this->view );
            $javaScript->registerBlockUI( $this->form->getSubmitSelector( 'install' ), '#progress' );
        }
    }

    private function checkAccess()
    {
        if ( System_Core_Application::getInstance()->getSite()->isConfigLoaded() ) {
            $this->page = 'config_exists';
            return false;
        }

        return true;
    }

    private function processForm()
    {
        $this->initializeRules();
        $this->form->validate();

        if ( !$this->setupLanguage() ) {
            $this->page = 'language';
            return;
        }

        if ( $this->form->isSubmittedWith( 'back' ) ) {
            switch ( $this->page ) {
                case 'site':
                    $this->page = 'language';
                    break;
                case 'connection':
                    $this->page = 'site';
                    break;
                case 'new_site':
                case 'existing_site':
                    $this->page = 'connection';
                    break;
            }
        }

        if ( $this->form->isSubmittedWith( 'next' ) && !$this->form->hasErrors() ) {
            switch ( $this->page ) {
                case 'language':
                    $this->page = 'site';
                    break;
                case 'site':
                    $this->page = 'connection';
                    break;
                case 'connection':
                    if ( $this->openConnection() )
                        $this->testConnection();
                    break;
            }
        }

        if ( $this->form->isSubmittedWith( 'install' ) && !$this->form->hasErrors() ) {
            switch ( $this->page ) {
                case 'new_site':
                    if ( $this->openConnection() ) {
                        if ( $this->installDatabaseTables() ) {
                            if ( $this->writeSiteConfiguration() ) {
                                $this->startSession();
                                $this->page = 'completed';
                            }
                        }
                    }
                    break;
                case 'existing_site':
                    if ( $this->openConnection() ) {
                        if ( $this->writeSiteConfiguration() ) {
                            $this->startSession();
                            $this->page = 'completed';
                        }
                    }
                    break;
            }
        }
    }

    private function initializeRules()
    {
        if ( $this->rules == $this->page )
            return;

        $this->rules = $this->page;

        $this->form->clearRules();

        switch ( $this->page ) {
            case 'language':
                $this->form->addItemsRule( 'language', $this->languageOptions );
                break;

            case 'connection':
                $this->form->addItemsRule( 'engine', $this->engineOptions );
                $this->form->addTextRule( 'host', System_Const::NameMaxLength );
                $this->form->addTextRule( 'database', System_Const::NameMaxLength );
                $this->form->addTextRule( 'user', System_Const::LoginMaxLength, ( $this->engine == 'mssql' ) ? System_Api_Parser::AllowEmpty : 0 );
                $this->form->addTextRule( 'password', System_Const::PasswordMaxLength, System_Api_Parser::AllowEmpty );
                $this->form->addTextRule( 'prefix', System_Const::NameMaxLength, System_Api_Parser::AllowEmpty );
                break;

            case 'new_site':
                $this->form->addTextRule( 'serverName', System_Const::NameMaxLength );
                $this->form->addTextRule( 'adminPassword', System_Const::PasswordMaxLength );
                $this->form->addTextRule( 'adminConfirm', System_Const::PasswordMaxLength );
                $this->form->addPasswordRule( 'adminConfirm', 'adminPassword' );
                break;
        }
    }

    private function setupLanguage()
    {
        if ( !empty( $this->language ) && isset( $this->languageOptions[ $this->language ] ) ) {
            $translator = System_Core_Application::getInstance()->getTranslator();
            $translator->addModule( 'webissues' );
            $translator->setLanguage( System_Core_Translator::SystemLanguage, $this->language );
            $translator->setLanguage( System_Core_Translator::UserLanguage, $this->language );

            if ( $this->serverName === null )
                $this->serverName = $this->tr( 'My WebIssues Server' );

            return true;
        }

        return false;
    }

    private function validateSite()
    {
        $site = System_Core_Application::getInstance()->getSite();

        $this->siteName = $site->getSiteName();

        $siteDir = $site->getPath( 'site_dir' );
        $this->configError = $this->checkDirectory( $siteDir );

        $storageDir = $siteDir . '/storage';
        $this->storageError = $this->checkDirectory( $storageDir );

        $this->debug = $site->getConfig( 'debug_level' );
        if ( $this->debug ) {
            $debugFile = $site->getPath( 'debug_file' );
            $this->debugError = $this->checkFile( $debugFile );
        }

        $this->debugInfo = $site->getConfig( 'debug_info' );

        if ( !empty( $this->configError ) || !empty( $this->storageError ) || !empty( $this->debugError ) )
            $this->disableNext = true;
    }

    private function validateConnection()
    {
        if ( empty( $this->engineOptions ) ) {
            $this->form->setError( 'engine', $this->tr( 'No supported database engines are available in this PHP installation.' ) );
            $this->disableNext = true;
        }
    }

    private function checkFile( $path )
    {
        $pos = strrpos( $path, '/' );
        $dir = substr( $path, 0, $pos );
        $file = substr( $path, $pos + 1 );

        if ( !System_Core_FileSystem::isValidFileName( $file ) )
            return $this->tr( "Invalid file name '%1'.", null, $file );

        return $this->checkDirectory( $dir );
    }

    private function checkDirectory( $path )
    {
        if ( !System_Core_FileSystem::isDirectory( $path, true ) )
            return $this->tr( "Directory '%1' does not exist.", null, $path );

        if ( !System_Core_FileSystem::isDirectoryWritable( $path ) )
            return $this->tr( "Directory '%1' is not writable.", null, $path );

        return null;
    }

    private function getDatabaseEngines()
    {
        $engines = array();

        if ( function_exists( 'mysqli_connect' ) )
            $engines[ 'mysqli' ] = 'MySQL';

        if ( function_exists( 'pg_connect' ) )
            $engines[ 'pgsql' ] = 'PostgreSQL';

        if ( @class_exists( 'COM', false ) )
            $engines[ 'mssql' ] = 'SQL Server';

        return $engines;
    }

    private function openConnection()
    {
        $connection = System_Core_Application::getInstance()->getConnection();

        try {
            $connection->loadEngine( $this->engine );
            $connection->open( $this->host, $this->database, $this->user, $this->password );
            $connection->setPrefix( $this->prefix );

            return true;
        } catch ( System_Db_Exception $e ) {
            $connection->close();

            $this->page = 'connection';
            $this->form->setError( 'connection', $this->tr( 'Could not connect to database. Please check connection details and try again.' ) );

            return false;
        }
    }

    private function testConnection()
    {
        $connection = System_Core_Application::getInstance()->getConnection();

        try {
            if ( !$this->checkPrerequisites() ) {
                $connection->close();
                return;
            }

            if ( !$connection->checkTableExists( 'server' ) ) {
                $this->page = 'new_site';

                $connection->close();
            } else {
                $this->page = 'existing_site';

                $query = 'SELECT server_name, server_uuid, db_version FROM {server}';
                $this->server = $connection->queryRow( $query );

                if ( $this->server[ 'db_version' ] != WI_VERSION ) {
                    $this->invalidVersion = true;
                    $this->disableInstall = true;

                    $connection->close();
                }
            }
        } catch ( System_Db_Exception $e ) {
            $connection->close();

            $this->page = 'connection';
            $this->form->setError( 'connection', $this->tr( 'Could not retrieve information from the database.' ) );
        }
    }

    private function checkPrerequisites()
    {
        switch ( $this->engine ) {
            case 'mysqli':
                if ( !$this->checkDatabaseVersion( '4.1.2' ) )
                    return false;

                $connection = System_Core_Application::getInstance()->getConnection();

                if ( !$connection->getParameter( 'have_innodb' ) ) {
                    $this->form->setError( 'connection', $this->tr( 'Database does not support InnoDB storage which is required by WebIssues Server.' ) );
                    return false;
                }
                break;

            case 'mssql':
                if ( !$this->checkDatabaseVersion( '09.00.1399' ) )
                    return false;
                break;
        }

        return true;
    }

    private function checkDatabaseVersion( $minVersion )
    {
        $connection = System_Core_Application::getInstance()->getConnection();

        $version = $connection->getParameter( 'version' );

        if ( version_compare( $version, $minVersion ) < 0 ) {
            $this->form->setError( 'connection', $this->tr( 'Database version %1 is older than minimum required version %2.', null, $version, $minVersion ) );
            return false;
        }

        return true;
    }

    private function installDatabaseTables()
    {
        $connection = System_Core_Application::getInstance()->getConnection();

        try {
            $generator = new System_Db_SchemaGenerator();
            $generator->loadEngine( $this->engine );

            $queries = $generator->getDatabaseSchema();

            foreach ( $queries as $query )
                $connection->execute( $query );

            $serverManager = new System_Api_ServerManager();
            $uuid = $serverManager->generateUuid();

            $query = 'INSERT INTO {server} ( server_name, server_uuid, db_version ) VALUES ( %s, %s, %s )';
            $connection->execute( $query, $this->serverName, $uuid, WI_VERSION );

            $passwordHash = new System_Core_PasswordHash();
            $hash = $passwordHash->hashPassword( $this->adminPassword );

            $query = 'INSERT INTO {users} ( user_login, user_name, user_passwd, user_access, passwd_temp ) VALUES ( %s, %s, %s, %d, 0 )';
            $connection->execute( $query, 'admin', $this->tr( 'Administrator' ), $hash, System_Const::AdministratorAccess );

            $settings = System_Core_IniFile::parse( '/common/data/setup/settings.ini' );
            $settings[ 'language' ] = $this->language;

            $query = 'INSERT INTO {settings} ( set_key, set_value ) VALUES ( %s, %s )';
            foreach ( $settings as $key => $value )
                $connection->execute( $query, $key, $value );

            return true;
        } catch ( System_Db_Exception $e ) {
            $connection->close();

            $this->page = 'failed';
            $this->error = $e->__toString();

            return false;
        }
    }

    private function writeSiteConfiguration()
    {
        foreach( array( 'engine', 'host', 'database', 'user', 'password', 'prefix' ) as $key )
            $values[ 'db_' . $key ] = $this->$key;

        $config = System_Web_Component::createComponent( 'Admin_Setup_Config', null, $values );
        $body = "<?php\n" . $config->run();

        $site = System_Core_Application::getInstance()->getSite();

        $siteDir = $site->getPath( 'site_dir' );
        $path = $siteDir . '/config.inc.php';

        if ( @file_put_contents( $path, $body, LOCK_EX ) === false ) {
            $this->error = $this->tr( 'The configuration file could not be written.' );
            $this->page = 'failed';
            return false;
        }

        return true;
    }

    private function startSession()
    {
        System_Core_Application::getInstance()->initializeSession();

        $sessionManager = new System_Api_SessionManager();
        $sessionManager->loginAsAdministrator();
    }
}

System_Bootstrap::run( 'Common_Application', 'Admin_Setup_Install' );
