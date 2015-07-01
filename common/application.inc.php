<?php
/**************************************************************************
* This file is part of the WebIssues Server program
* Copyright (C) 2006 Michał Męciński
* Copyright (C) 2007-2015 WebIssues Team
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

if ( !defined( 'WI_VERSION' ) ) die( -1 );

class Common_Application extends System_Web_Application
{
    protected $isAnonymous = false;

    protected function __construct( $pageClass )
    {
        parent::__construct( $pageClass );
    }

    protected function preparePage()
    {
        parent::preparePage();

        if ( ( $this->request->isRelativePath( '/index.php' ) && $this->request->getQueryString( 'url' ) == null )
                || $this->request->isRelativePath( '/register.php' )
                || $this->request->isRelativePath( '/client/index.php' ) ) {
            if ( $this->isMobileDevice() ) {
                $redirect = false;

                if ( $this->request->isRelativePath( '/client/index.php' ) ) {
                    $principal = System_Api_Principal::getCurrent();
                    if ( !$principal->isAuthenticated() ) {
                        $serverManager = new System_Api_ServerManager();
                        if ( $serverManager->getSetting( 'anonymous_access' ) != 1 )
                            $redirect = true;
                    }
                }

                if ( $redirect )
                    $this->response->redirect( '/mobile/index.php?url=' . urlencode( '/mobile' . $this->getRelativeUrl() ) );
                else
                    $this->response->redirect( '/mobile' . $this->getRelativeUrl() );
            }
        }

        if ( !$this->request->isRelativePath( '/index.php' )
                && !$this->request->isRelativePath( '/register.php' )
                && !$this->request->isRelativePath( '/mobile/index.php' )
                && !$this->request->isRelativePath( '/mobile/register.php' )
                && !$this->request->isRelativePathUnder( '/admin/setup' )
                && !$this->request->isRelativePathUnder( '/common/errors' ) ) {
            $principal = System_Api_Principal::getCurrent();

            if ( !$principal->isAuthenticated() ) {
                $redirect = true;

                if ( $this->request->isRelativePath( '/client/index.php' )
                        || $this->request->isRelativePath( '/client/tools/index.php' )
                        || $this->request->isRelativePath( '/client/tools/gotoitem.php' )
                        || $this->request->isRelativePath( '/client/tools/about.php' )
                        || $this->request->isRelativePath( '/mobile/client/index.php' )
                        || $this->request->isRelativePath( '/mobile/client/tools/index.php' )
                        || $this->request->isRelativePath( '/mobile/client/tools/gotoitem.php' )
                        || $this->request->isRelativePath( '/mobile/client/tools/about.php' ) ) {
                    $serverManager = new System_Api_ServerManager();
                    if ( $serverManager->getSetting( 'anonymous_access' ) == 1 ) {
                        $this->isAnonymous = true;
                        $redirect = false;
                    }
                }

                if ( $redirect )
                    $this->redirectToLoginPage();
            }

            if ( $this->request->isRelativePathUnder( '/admin' ) ) {
                if ( !$principal->isAdministrator() )
                    throw new System_Api_Error( System_Api_Error::AccessDenied );
            }
        }

        $this->page->getView()->setDecoratorClass( 'Common_PageLayout' );
    }

    protected function redirectToLoginPage()
    {
        $this->response->redirect( $this->getLoginPageUrl() );
    }

    public function getLoginPageUrl()
    {
        $url = $this->getRelativeUrl();

        if ( $this->request->isRelativePathUnder( '/mobile' ) )
            return '/mobile/index.php?url=' . urlencode( $url );
        else
            return '/index.php?url=' . urlencode( $url );
    }

    public function getRelativeUrl()
    {
        $url = $this->request->getRelativePath();
        $args = array();
        foreach ( $this->request->getQueryStrings() as $key => $value ) {
            if ( isset( $value ) )
                $args[] = $key . '=' . $value;
        }
        if ( !empty( $args ) )
            $url .= '?' . join( '&', $args );
        return $url;
    }

    protected function handleSetupException( $exception )
    {
        if ( $this->request->isRelativePathUnder( '/common/errors' ) )
            return;

        if ( $exception->getCode() == System_Core_SetupException::SiteConfigNotFound && $this->request->isRelativePath( '/admin/setup/install.php' ) )
            return;

        if ( $exception->getCode() == System_Core_SetupException::DatabaseNotUpdated && $this->request->isRelativePath( '/admin/setup/update.php' ) )
            return;

        $this->handleException( $exception );
        exit;
    }

    protected function displayErrorPage()
    {
        $exception = $this->getFatalError();

        if ( $this->isAnonymous && is_a( $exception, 'System_Api_Error' ) ) {
            $message = $exception->getMessage();
            if ( $message == System_Api_Error::UnknownProject || $message == System_Api_Error::UnknownFolder || $message == System_Api_Error::UnknownIssue
                 || $message == System_Api_Error::UnknownFile || $message == System_Api_Error::UnknownView || $message == System_Api_Error::ItemNotFound )
                $this->redirectToLoginPage();
        }

        if ( is_a( $exception, 'System_Core_SetupException' ) )
            $errorPage = System_Web_Component::createComponent( 'Common_Errors_Setup' );
        else if ( $this->isDebugInfoEnabled() )
            $errorPage = System_Web_Component::createComponent( 'Common_Errors_Debug' );
        else
            $errorPage = System_Web_Component::createComponent( 'Common_Errors_General' );

        $this->response->setContentType( 'text/html; charset=UTF-8' );

        $content = $errorPage->run();
        $this->response->setContent( $content );

        $this->response->send();
    }

    public function getManualUrl()
    {
        $language = $this->translator->getLanguage( System_Core_Translator::UserLanguage );

        while ( $language != '' ) {
            $url = '/doc/' . $language . '/index.html';

            if ( file_exists( WI_ROOT_DIR . $url ) )
                return $url;

            $pos = strrpos( $language, '_' );
            if ( $pos === false )
                break;

            $language = substr( $language, 0, $pos );
        }

        return '/doc/en/index.html';
    }

    private function isMobileDevice()
    {
        $client = $this->session->getCookie( 'wi_client' );

        if ( $client == 'mobile' )
            return true;
        else if ( $client == 'full' )
            return false;

        include_once( WI_ROOT_DIR . '/system/web/mobiledetect.inc.php' );
        $mobileDetect = new Mobile_Detect();

        return $mobileDetect->isMobile() && !$mobileDetect->isTablet();
    }
}
