<?php
#BEGIN_LICENSE
#-------------------------------------------------------------------------
# Module: RegularTask (c) 2016 by Robert Campbell
#         (calguy1000@cmsmadesimple.org)
#
#-------------------------------------------------------------------------
# CMS - CMS Made Simple is (c) 2005 by Ted Kulp (wishy@cmsmadesimple.org)
# This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
#
#-------------------------------------------------------------------------
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <https://www.gnu.org/licenses/>.
#
#-------------------------------------------------------------------------
#END_LICENSE

/**
 * This file provides a utility for processing old style pseudocron tasks as new asynchronous jobs.
 *
 * @package CMS
 */
namespace CMSMS\Async;


/**
 * This class allows converting an old CmsRegularTask pseudocron task into an asynchronous background job.
 *
 * @package CMS
 * @author Robert Campbell
 * @copyright Copyright (c) 2015, Robert Campbell <calguy1000@cmsmadesimple.org>
 * @since 2.2
 * @property CmsRegularTask $task The task to convert.
 */
class RegularTask extends Job
{
    /**
     * @ignore
     */
    private $_task;

    /**
     * Constructor.
     *
     * @param CmsRegularTask $task
     */
    public function __construct(\CmsRegularTask $task)
    {
        parent::__construct();
        $this->_task = $task;
        $this->name = $task->get_name();
    }

    /**
     * @ignore
     */
    public function __get($key)
    {
        switch( $key ) {
        case 'task':
            return $this->_task;
        default:
            return parent::__get($key);
        }
    }

    /**
     * @ignore
     */
    public function __set($key,$val)
    {
        switch( $key ) {
        case 'task':
            if( !$val instanceof \CmsRegularTask ) throw new \LogicException('Invalid value for '.$key.' in a '.__CLASS__);
            $this->_task = $val;
            break;

        default:
            return parent::__set($key,$val);
        }
    }

    /**
     * @ignore
     */
    public function execute()
    {
        // no testing, just execute the damned thing
        if( !$this->_task ) throw new \LogicException(__CLASS__.' job is being executed, but has no task associated');
        $task = $this->_task;
        $now = time();
        $res = $task->execute($now);
        if( $res ) {
            $task->on_success($now);
        } else {
            $task->on_failure($now);
        }
    }
}