<?php

use CMSMS\AdminAlerts\TranslatableAlert;

class CmsVersionCheckTask implements CmsRegularTask
{
    const  LASTEXECUTE_SITEPREF   = __CLASS__;
    const  ENABLED_SITEPREF = 'checkversion';

    public function get_name()
    {
        return __CLASS__; //assume no namespace
    }

    public function get_description()
    {
        return __CLASS__; //lazy
    }

    public function test($time = '')
    {
        if( !cms_siteprefs::get(self::ENABLED_SITEPREF,1) ) return FALSE;

        // do we need to do this task now? (daily intervals)
        if( !$time ) $time = time();
        $last_execute = cms_siteprefs::get(self::LASTEXECUTE_SITEPREF,0);
        return ($time - 24*3600) >= $last_execute;
    }

    private function fetch_latest_cmsms_ver()
    {
        $remote_ver = 'error';
        $req = new cms_http_request();
        $req->setTimeout(10);
        $req->execute(CMS_DEFAULT_VERSIONCHECK_URL);
        if( $req->getStatus() == 200 ) {
            $remote_ver = trim($req->getResult());
            if( strpos($remote_ver,':') !== FALSE ) {
                list($tmp,$remote_ver) = explode(':',$remote_ver,2);
                $remote_ver = trim($remote_ver);
            }
        }
        return $remote_ver;
    }

    public function execute($time = '')
    {
        if( !$time ) $time = time();

        // do the task.
        $remote_ver = $this->fetch_latest_cmsms_ver();
        if( version_compare(CMS_VERSION,$remote_ver) < 0 ) {
            $alert = new TranslatableAlert(['Modify Site Preferences']);
            $alert->name = 'CMSMS Version Check';
            $alert->titlekey = 'new_version_avail_title';
            $alert->msgkey = 'new_version_avail2';
            $alert->msgargs = [ CMS_VERSION, $remote_ver ];
            $alert->save();
            cms_notice('CMSMS version '.$remote_ver.' is available');
        }
        return TRUE;
    }

    public function on_success($time = '')
    {
        if( !$time ) $time = time();
        cms_siteprefs::set(self::LASTEXECUTE_SITEPREF,$time);
    }

    public function on_failure($time = '')
    {
        // nothing here.
    }
}
