<?php
assert(empty(CMS_DEPREC), new DeprecationNotice('Class file '.basename(__FILE__).' used'));
require_once __DIR__.DIRECTORY_SEPARATOR.'class.LangOperations.php';
if (!class_exists('CmsLangOperations', false)) {
    class_alias('CMSMS\LangOperations', 'CmsLangOperations', false);
}
