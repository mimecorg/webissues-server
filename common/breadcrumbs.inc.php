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

/**
* Helper class for generating breadcrumbs and links to parent pages.
*/
class Common_Breadcrumbs extends System_Web_Base
{
    const GeneralInformation = 1;
    const EventLog = 2;
    const UserAccounts = 3;
    const IssueTypes = 4;
    const ViewSettings = 5;
    const Project = 6;
    const ProjectMembers = 7;
    const Folder = 8;
    const ManageViews = 9;
    const ManageAlerts = 10;
    const Issue = 11;
    const Tools = 12;
    const ManageProjects = 13;
    const UserProjects = 14;
    const RegistrationRequests = 15;
    const ProjectsArchive = 16;
    const Mobile = 17;

    private $page = null;

    private $prefix = '';

    private $urls = array();
    private $names = array();

    /**
    * Constructor.
    * @param $page The page to which the breadcrumbs are added.
    */
    public function __construct( $page )
    {
        parent::__construct();

        $this->page = $page;

        if ( $this->request->isRelativePathUnder( '/mobile' ) )
            $this->prefix = '/mobile';
    }

    /**
    * Initialize breadcrumbs for the specified parent node.
    * @param $node On of the parent node constants.
    * @param $object An optional data object (project, folder or issue).
    */
    public function initialize( $node, $object = null )
    {
        $this->append( $node, $object );

        $breadcrumbs = array();
        foreach ( $this->urls as $i => $url )
            $breadcrumbs[ $url ] = $this->names[ $i ];

        $this->page->getView()->setSlot( 'breadcrumbs', $breadcrumbs );
    }

    /**
    * Return the URL of the parent page.
    */
    public function getParentUrl()
    {
        return $this->urls[ count( $this->urls ) - 1 ];
    }

    /**
    * Return the URL of the specified ancestor page.
    * @param $index Index of ancestor (counting from the parent).
    */
    public function getAncestorUrl( $index )
    {
        return $this->urls[ count( $this->urls ) - $index - 1 ];
    }

    private function append( $node, $object = null )
    {
        switch ( $node ) {
            case self::GeneralInformation:
                $this->urls[] = '/admin/info/index.php';
                $this->names[] = $this->tr( 'General Information' );
                break;

            case self::EventLog:
                $this->urls[] = $this->filterQueryString( '/admin/events/index.php', array( 'sort', 'order', 'page', 'type' ) );
                $this->names[] = $this->tr( 'Event Log' );
                break;

            case self::UserAccounts:
                $this->urls[] = $this->filterQueryString( '/admin/users/index.php', array( 'sort', 'order', 'page', 'type' ) );
                $this->names[] = $this->tr( 'User Accounts' );
                break;

            case self::UserProjects:
                $this->append( self::UserAccounts );
                $this->urls[] = $this->filterQueryString( '/admin/users/projects.php', array( 'id', 'sort', 'order', 'page', 'type', 'psort', 'porder', 'ppage' ) );
                $this->names[] = $this->tr( 'Manage Permissions' );
                break;

            case self::RegistrationRequests:
                $this->urls[] = $this->filterQueryString( '/admin/register/index.php', array( 'sort', 'order', 'page' ) );
                $this->names[] = $this->tr( 'Registration Requests' );
                break;

            case self::IssueTypes:
                $this->urls[] = $this->filterQueryString( '/admin/types/index.php', array( 'sort', 'order', 'page' ) );
                $this->names[] = $this->tr( 'Issue Types' );
                break;

            case self::ViewSettings:
                $this->append( self::IssueTypes );
                $this->urls[] = $this->filterQueryString( '/admin/views/index.php', array( 'sort', 'order', 'page', 'type', 'vsort', 'vorder', 'vpage' ) );
                $this->names[] = $this->tr( 'View Settings' );
                break;

            case self::ProjectsArchive:
                $this->urls[] = $this->filterQueryString( '/admin/archive/index.php', array( 'sort', 'order', 'page' ) );
                $this->names[] = $this->tr( 'Projects Archive' );
                break;

            case self::Project:
                $this->urls[] = $this->filterQueryString( $this->prefix . '/client/index.php', array( 'ps', 'po', 'ppg' ), array( 'project' => $object[ 'project_id' ] ) );
                $this->names[] = $object[ 'project_name' ];
                break;

            case self::ProjectMembers:
                $this->append( self::ManageProjects );
                $this->urls[] = $this->filterQueryString( $this->prefix . '/client/projects/members.php', array( 'project', 'sort', 'order', 'page', 'msort', 'morder', 'mpage' ) );
                $this->names[] = $this->tr( 'Manage Permissions' );
                break;

            case self::Folder:
                if ( (int)$this->request->getQueryString( 'type' ) ) {
                    $this->urls[] = $this->filterQueryString( $this->prefix . '/client/index.php', array( 'ps', 'po', 'ppg', 'sort', 'order', 'page', 'view', 'q', 'qc' ), array( 'type' => $object[ 'type_id' ] ) );
                    $this->names[] = $object[ 'type_name' ];
                } else {
                    $this->append( self::Project, $object );
                    $this->urls[] = $this->filterQueryString( $this->prefix . '/client/index.php', array( 'ps', 'po', 'ppg', 'sort', 'order', 'page', 'view', 'q', 'qc' ), array( 'folder' => $object[ 'folder_id' ] ) );
                    $this->names[] = $object[ 'folder_name' ];
                }
                break;

            case self::ManageViews:
                $this->append( self::Folder, $object );
                $this->urls[] = $this->filterQueryString( '/client/views/index.php', array( 'ps', 'po', 'ppg', 'sort', 'order', 'page', 'view', 'q', 'qc', 'folder', 'type', 'vsort', 'vorder', 'vpage' ) );
                $this->names[] = $this->tr( 'Manage Views' );
                break;

            case self::ManageAlerts:
                $this->append( self::Folder, $object );
                $this->urls[] = $this->filterQueryString( '/client/alerts/index.php', array( 'ps', 'po', 'ppg', 'sort', 'order', 'page', 'view', 'q', 'qc', 'folder', 'type', 'asort', 'aorder', 'apage' ) );
                $this->names[] = $this->tr( 'Manage Alerts' );
                break;

            case self::Issue:
                $this->append( self::Folder, $object );
                $this->urls[] = $this->filterQueryString( $this->prefix . '/client/index.php', array( 'ps', 'po', 'ppg', 'sort', 'order', 'page', 'view', 'q', 'qc', 'hpg', 'hflt', 'unread', 'type' ), array( 'issue' => $object[ 'issue_id' ] ) );
                $this->names[] = $object[ 'issue_name' ];
                break;

            case self::Tools:
                $this->urls[] = '/client/tools/index.php';
                $this->names[] = $this->tr( 'Tools' );
                break;

            case self::Mobile:
                $this->urls[] = '/mobile/client/index.php';
                $this->names[] = $this->tr( 'Tools' );
                break;

            case self::ManageProjects:
                $this->urls[] = $this->filterQueryString( '/client/projects/index.php', array( 'sort', 'order', 'page' ) );
                $this->names[] = $this->tr( 'Manage Projects' );
                break;
        }
    }
}
