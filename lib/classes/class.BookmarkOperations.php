<?php
#Class of bookmark-related functions
#Copyright (C) 2004-2010 Ted Kulp <ted@cmsmadesimple.org>
#Copyright (C) 2011-2018 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
#This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
#
#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#BUT withOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program. If not, see <https://www.gnu.org/licenses/>.

namespace CMSMS;

use CmsApp;
use CMSMS\Bookmark;
use const CMS_DB_PREFIX;
use const CMS_ROOT_URL;
use const CMS_SECURE_PARAM_NAME;
use const CMS_USER_KEY;
use function startswith;

/**
 * Class for doing bookmark related functions.  Maybe of the Bookmark object functions
 * are just wrappers around these.
 *
 * @package CMS
 * @license GPL
 */

class BookmarkOperations
{
	/**
	 * @ignore
	 */
	private static $_instance = null;

	/**
	 * @ignore
	 */
	private function __construct() {}

	/**
	 * @ignore
	 */
	private function __clone() {}

	/**
	 * Return a reference to the only allowed instance of this singleton object
	 * @return BookmarkOperations
	 */
	final public static function get_instance() : self
	{
		if( !self::$_instance ) self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Prepares a url for saving by replacing security tags with a holder
	 * string so it can be replaced when retrieved and not break security.
	 *
	 * @param string $url The url to save
	 * @return string The fixed url
	 * @internal
	 */
	private function _prep_for_saving(string $url) : string
	{
		$urlext = CMS_SECURE_PARAM_NAME.'='.$_SESSION[CMS_USER_KEY];
		if( startswith($url,CMS_ROOT_URL) ) $url = str_replace(CMS_ROOT_URL,'[ROOT_URL]',$url);
		$url = str_replace($urlext,'[SECURITYTAG]',$url);
		return $url;
	}

	/**
	 * Prepares a url for displaying by replacing the holder for the security
	 * tag with the actual value.
	 *
	 * @param string $url The url to display
	 * @return string The fixed url
	 * @internal
	 */
	private function _prep_for_display(string $url) : string
	{
		$urlext = CMS_SECURE_PARAM_NAME.'='.$_SESSION[CMS_USER_KEY];

		$map = ['[SECURITYTAG]'=>$urlext,'[ROOT_URL]'=>CMS_ROOT_URL];
		foreach( $map as $from => $to ) {
			$url = str_replace($from,$to,$url);
		}

		$url = str_replace($from,$to,$url);
		return $url;
	}

	/**
	 * Gets a list of all bookmarks for a given user
	 *
	 * @param int $user_id The desired user id.
	 * @return array An array of Bookmark objects
	 */
	public function LoadBookmarks($user_id)
	{
		$gCms = CmsApp::get_instance();
		$db = $gCms->GetDb();

		$result = [];
		$query = 'SELECT bookmark_id, user_id, title, url FROM '.CMS_DB_PREFIX.'admin_bookmarks WHERE user_id = ? ORDER BY title';
		$dbresult = $db->Execute($query, [$user_id]);

		while ($dbresult && $row = $dbresult->FetchRow()) {
			$onemark = new Bookmark();
			$onemark->bookmark_id = $row['bookmark_id'];
			$onemark->user_id = $row['user_id'];
			$onemark->url = $this->_prep_for_display($row['url']);
			$onemark->title = $row['title'];
			$result[] = $onemark;
		}

		return $result;
	}

	/**
	 * Loads a bookmark by bookmark_id.
	 *
	 * @param int $id bookmark_id to load
	 * @return Bookmark
	 * @since 0.6.1
	 */
	public function LoadBookmarkByID($id)
	{
		$result = null;
		$db = CmsApp::get_instance()->GetDb();

		$query = 'SELECT bookmark_id, user_id, title, url FROM '.CMS_DB_PREFIX.'admin_bookmarks WHERE bookmark_id = ?';
		$dbresult = $db->Execute($query, [$id]);

		while ($dbresult && $row = $dbresult->FetchRow()) {
			$onemark = new Bookmark();
			$onemark->bookmark_id = $row['bookmark_id'];
			$onemark->user_id = $row['user_id'];
			$onemark->url = $this->_prep_for_display($row['url']);
			$onemark->title = $row['title'];
			$result = $onemark;
		}

		return $result;
	}

	/**
	 * Saves a new bookmark to the database.
	 *
	 * @param Bookmark $bookmark Bookmark object to save
	 * @return int The new bookmark_id.  If it fails, it returns -1.
	 */
	public function InsertBookmark(Bookmark $bookmark)
	{
		$result = -1;
		$db = CmsApp::get_instance()->GetDb();

		$bookmark->url = $this->_prep_for_saving($bookmark->url);
		$new_bookmark_id = $db->GenID(CMS_DB_PREFIX.'admin_bookmarks_seq');
		$query = 'INSERT INTO '.CMS_DB_PREFIX.'admin_bookmarks (bookmark_id, user_id, url, title) VALUES (?,?,?,?)';
		$dbresult = $db->Execute($query, [$new_bookmark_id, $bookmark->user_id, $bookmark->url, $bookmark->title]);
		if ($dbresult !== false) $result = $new_bookmark_id;

		return $result;
	}

	/**
	 * Updates an existing bookmark in the database.
	 *
	 * @param Bookmark $bookmark object to save
	 * @return bool
	 */
	public function UpdateBookmark(Bookmark $bookmark)
	{
		$result = false;
		$db = CmsApp::get_instance()->GetDb();

		$bookmark->url = $this->_prep_for_saving($bookmark->url);
		$query = 'UPDATE '.CMS_DB_PREFIX.'admin_bookmarks SET user_id = ?, title = ?, url = ? WHERE bookmark_id = ?';
		$dbresult = $db->Execute($query, [$bookmark->user_id, $bookmark->title, $bookmark->url, $bookmark->bookmark_id]);
		if ($dbresult !== false) $result = true;

		return $result;
	}

	/**
	 * Deletes an existing bookmark from the database.
	 *
	 * @param int $id Id of the bookmark to delete
	 * @return bool
	 */
	public function DeleteBookmarkByID($id)
	{
		$result = false;
		$db = CmsApp::get_instance()->GetDb();

		$query = 'DELETE FROM '.CMS_DB_PREFIX.'admin_bookmarks where bookmark_id = ?';
		$dbresult = $db->Execute($query, [$id]);
		if ($dbresult !== false) $result = true;
		return $result;
	}
} //class

//backward-compatibility shiv
\class_alias(BookmarkOperations::class, 'BookmarkOperations', false);
