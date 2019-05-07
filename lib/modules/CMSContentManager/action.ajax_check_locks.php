<?php
# CMSContentManger module action: ajax_check_locks
# Copyright (C) 2019 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
# This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
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

use CMSMS\LockOperations;

if( !isset($gCms) ) exit;
// no permissions checks here

$handlers = ob_list_handlers();
for( $i = 0, $n = count($handlers); $i < $n; ++$i ) { ob_end_clean(); }

$userid = get_userid();
$lock_timeout = cms_siteprefs::get('lock_timeout');
$now = time();

$list = LockOperations::get_locks('content');
$locks = [];
foreach( $list as $lock ) {
    if( $lock['uid'] != $userid) {
        $id = $lock['oid'];
        if( $lock_timeout && $lock['expires'] < $now ) {
            $locks[$id] = 1; // stealable
        } else { 
            $locks[$id] = -1; // blocked
        }
    }
}

$out = json_encode($locks, JSON_NUMERIC_CHECK+JSON_FORCE_OBJECT);
echo $out;
exit;