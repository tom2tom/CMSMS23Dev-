<?php
#...
#Copyright (C) 2004-2018 Ted Kulp <ted@cmsmadesimple.org>
#This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>
#
#This program is free software; you can redistribute it and/or modify
#it under the terms of the GNU General Public License as published by
#the Free Software Foundation; either version 2 of the License, or
#(at your option) any later version.
#
#This program is distributed in the hope that it will be useful,
#but WITHOUT ANY WARRANTY; without even the implied warranty of
#MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#GNU General Public License for more details.
#You should have received a copy of the GNU General Public License
#along with this program. If not, see <https://www.gnu.org/licenses/>.

#NLS (National Language System) array.

#The basic idea and values was taken from then Horde Framework (http://horde.org)
#The original filename was horde/config/nls.php.
#The modifications to fit it for Gallery were made by Jens Tkotz
#(http://gallery.meanalto.com) 

#Ideas from Gallery's implementation made to CMS by Ted Kulp

#Created by: Alayn Gortazar (Zurti) < zutoin [at] gmail [dot] com >
#Maintained by: Alayn Gortazar (Zurti) < zutoin [at] gmail [dot] com >
#and : Mikel Etxeberria (Mikel)  < mikel [at]  abartiateam [dot] com >

#Native language name
$nls['language']['eu_ES'] = 'Euskara';
$nls['englishlang']['eu_ES'] = 'Basque';

#Possible aliases for language
$nls['alias']['eu'] = 'eu_ES';
$nls['alias']['basque'] = 'eu_ES' ;
$nls['alias']['baq'] = 'eu_ES' ;
$nls['alias']['eus'] = 'eu_ES' ;
$nls['alias']['eu_ES'] = 'eu_ES' ;
$nls['alias']['eu_ES.ISO8859-1'] = 'eu_ES' ;

#Possible locale for language
$nls['locale']['eu_ES'] = 'eu_ES.utf8,eu_ES.utf-8,eu_ES.UTF-8,eu_ES@euro,basque,Basque_Spain.1252';

#Encoding of the language
$nls['encoding']['eu_ES'] = 'UTF-8';

#Location of the file(s)
$nls['file']['eu_ES'] = array(__DIR__.'/eu_ES/admin.inc.php');

#Language setting for HTML area
$nls['htmlarea']['eu_ES'] = 'en';

?>
