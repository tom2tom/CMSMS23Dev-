<?php
assert(empty(CMS_DEPREC), new DeprecationNotice('class','CMSMS\\Lock'));
require_once __DIR__.DIRECTORY_SEPARATOR.'class.Lock.php';
\class_alias('CMSMS\Lock', 'CmsLock', false);
