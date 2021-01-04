<?php
/*
DesignManager module strings-translation data.
Copyright (C) 2012-2021 CMS Made Simple Foundation <foundation@cmsmadesimple.org>
Thanks to Robert Campbell and all other contributors from the CMSMS Development Team.

This file is a component of CMS Made Simple <http://www.cmsmadesimple.org>

CMS Made Simple is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of that license, or
(at your option) any later version.

CMS Made Simple is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of that license along with CMS Made Simple.
If not, see <https://www.gnu.org/licenses/>.
*/

//A
$lang['apply'] = 'Apply'; //OR use lang(same)
$lang['attached_stylesheets'] = 'Included Stylesheets';
$lang['attached_templates'] = 'Included Templates';
$lang['available_stylesheets'] = 'Other Stylesheets';
$lang['available_templates'] = 'Other Templates';

//C
$lang['cancel'] = 'Cancel'; //OR use lang(same)
$lang['confirm_delete_1'] = 'Are you sure you want to delete this design?';
$lang['confirm_delete_2a'] = 'Yes, I am sure I want to delete this item';
$lang['confirm_delete_2b'] = 'Yes, I am <strong>really</strong> sure I want to delete this item';
$lang['confirm_import_1'] = 'Yes, I am sure I want to import this design';
$lang['confirm_import'] = 'Confirm Import Design';
$lang['create_design'] = 'Add New Design';

//D
$lang['delete_attached_stylesheets'] = 'Delete assigned and orphaned stylesheets';
$lang['delete_attached_templates'] = 'Delete assigned and orphaned templates';
$lang['delete_design'] = 'Delete Design';

//E
$lang['edit_design'] = 'Edit design';
$lang['error_create_tempfile'] = 'Error creating temporary file';
$lang['error_design_empty'] = 'Could not find any stylesheets';
$lang['error_filenotfound'] = 'Could not find a file we were expecting: %s';
$lang['error_fileopen'] = 'Could not open %s for reading.  Permissions problem?';
$lang['error_missingparam'] = 'A required parameter is missing or invalid';
$lang['error_nofileuploaded'] = 'No file was uploaded';
$lang['error_nophysicalfile'] = 'An error occurred parsing the stylesheets and/or templates of the design.  The URL %s could not be located as a physical file.  This probably indicates that the template and/or stylesheets of this theme are using advanced logic that the design manager cannot process.';
$lang['error_notconfirmed'] = 'The action was not confirmed';
$lang['error_readxml'] = 'A problem occurred reading the XML file (possible syntax error)';
$lang['error_upload_filetype'] = 'The file uploaded is not of the proper type (%s)';
$lang['error_uploading'] = 'Problem uploading file (perhaps it is too large)';
$lang['error_xmlstructure'] = 'Error in the structure of the XML File';
$lang['event_desc_adddesignpost'] = 'Sent after a design is saved';
$lang['event_desc_adddesignpre'] = 'Sent before a design is saved to the database';
$lang['event_desc_editdesignpost'] = 'Sent after a design is modified (before saved)';
$lang['event_desc_editdesignpre'] = 'Sent before a design is modified';
$lang['event_desc_deletedesignpost'] = 'Sent after a design is removed';
$lang['event_desc_deletedesignpre'] = 'Sent prior to a design being removed';
$lang['export_design'] = 'Export Design to XML';
$lang['export'] = 'Export';

//F
$lang['friendlyname'] = 'Designs'; //'Design Manager';

//H
$lang['group_desc'] = 'Members of this group can manage designs';

//H
$lang['help_design_created'] = 'The date and time when the design was originally created';
$lang['help_design_description'] = 'Specify a description for this design (text only).  This might be useful for future reference, or when sharing this design with others';
$lang['help_design_modified'] = 'The date and time when the design was last modified';
$lang['help_design_name'] = 'This field contains the design\'s name, which must be unique in this system';
$lang['help_import_cmsversion'] = 'This design file was generated from an earlier version of CMSMS.  This might cause difficulties, so please take care!';
$lang['help_import_created'] = 'The date that the XML file was created.  For themes created from the older CMSMS Theme manager there is no embedded creation date so &quot;unknown&quot; will be displayed';
$lang['help_import_newname'] = 'If there is already a design with the name specified, the system will suggest a replacement  name.  Design names must be unique to the system.';
$lang['help_import_xml_file'] = 'Select an XML File to import.  That file must have been generated by the CMSMS Design Manager, or by the older CMSMS Theme Manager';
$lang['help_css_mediaquery'] = <<<'EOT'
<p>A media query consists of a media type and at least one expression that limits the style sheet's scope by using media features such as width, height, and color. Added in CSS3, media queries let the presentation of content be tailored to a specific range of output devices without having to change the content itself. For reference see <a href="https://developer.mozilla.org/en/docs/CSS/Media_queries">this page</a>.</p><br/>
<p>CMSMS supports associating a media query with a stylesheet. When <code>{cms_stylesheet}</code> generates its output, the media query will be automatically placed in the stylesheet tag.</p>
EOT;
$lang['help_rm_css'] = 'This will remove all stylesheets from this design. It does not affect the actual stylesheets.';
$lang['help_rm_tpl'] = 'This will remove all templates from this design. It does not affect the actual templates.';
$lang['help_template_multiple_designs'] = 'This template is assigned to multiple designs';
$lang['help_template_name'] = 'Specify a name for this template.  The name must contain only alphanumeric characters, and must be unique to the system';
$lang['help_template_no_designs'] = 'This template is not assigned to any designs';

//I
//$lang['info_css_content_file'] = 'If authorized, you can also edit the content of this stylesheet by working directly on file <strong>&lt;website root folder&gt;%s</strong>.';
$lang['import_design_step1'] = 'Import Design Step 1';
$lang['import_design_step2'] = 'Import Design Step 2';
$lang['import_design'] = 'Import Design';
$lang['import'] = 'Import';
$lang['info_designs'] = 'A design is a container for template(s) and/or stylesheet(s). Its purpose is to facilitate sharing of its contents, to help propagate those aspects of the website look-and-feel. So designs are for site developers and maintainers. Designs have no direct role in the operation of any site.';
$lang['info_edittemplate_stylesheets_tab'] = 'To add stylesheets to the design, drag them from the \'Other\' box and drop them into the \'Included\' box. To remove stylesheets, drag-and-drop in the reverse direction. If useful (for appearance only) re-order stylesheets by drag-and-drop within a box. When finalized, hit Apply or Submit.';
$lang['info_edittemplate_templates_tab'] = 'To add templates to the design, drag them from the \'Other\' box and drop them into the \'Included\' box. To remove templates, drag-and-drop in the reverse direction. If useful (for appearance only) re-order templates by drag-and-drop within a box. When finalized, hit Apply or Submit.';
$lang['info_import_xml_step1'] = 'Step 1: Choose a (XML) design-file to import';
$lang['info_import_xml_step2'] = 'Step 2: Read information about this design before importing it to your CMSMS installation.';
$lang['info_no_stylesheets'] = 'No stylesheet is recorded';
$lang['info_no_templates'] = 'No template is recorded';
$lang['info_nodescription'] = 'There is no description entered for this item';

//M
$lang['moddescription'] = 'Add, change or remove designs';
$lang['msg_cancelled'] = 'Operation cancelled';
    $lang['msg_design_deleted'] = 'Design deleted';
$lang['msg_design_migrated']  = 'Design stylesheet(s) exported to %s';
$lang['msg_design_imported']  = 'Design imported';
$lang['msg_design_saved'] = 'Design saved';
$lang['msg_dflt_design_saved'] = 'Default design changed';

//N
$lang['name'] = 'Name'; //or lang()
$lang['new_design'] = 'New Design';
$lang['newname'] =  'New Name';
$lang['next'] = 'Next';
$lang['no_design'] = 'No design is registered';

//P
$lang['perm_designs'] = 'Manage Designs';
$lang['postinstall'] = 'Design Manager module installed';
$lang['postuninstall'] = 'Design Manager module uninstalled';
$lang['prompt_action_designs'] = 'Designs';
//$lang['prompt_action_settings'] = 'Presentation Settings';
//$lang['prompt_action_styles'] = 'Page Styling';
//$lang['prompt_action_templates'] = 'Templates';
$lang['prompt_cmsversion'] = 'CMSMS Version';
$lang['prompt_copyrightlicense'] = 'Copyright and License';
$lang['prompt_created'] = 'Created';
$lang['prompt_description'] = 'Description';
$lang['prompt_design'] = 'Design';
$lang['prompt_import_xml_file'] = 'Imprt a Design File';
$lang['prompt_media_type'] = 'Media Type';
$lang['prompt_modified'] = 'Modified';
$lang['prompt_name'] = 'Name'; // or lang(same)
$lang['prompt_none'] = 'None';
$lang['prompt_orig_name'] = 'Original Name';
$lang['prompt_stylesheets'] = 'Stylesheets';
$lang['prompt_templates'] = 'Templates';
$lang['prompt_unknown'] = 'Unknown';
$lang['prompt_user'] = 'User'; //or lang(same)

//S
$lang['submit'] = 'Submit'; //or lang(same)

//T
$lang['table_droptip'] = 'Drop items here';
$lang['title_action_designs'] = 'Add, change or remove designs';
$lang['title_action_settings'] = 'Adjust settings for managing templates and stylesheets';
$lang['title_action_styles'] = 'Add, change or remove stylesheets applied to frontend pages';
$lang['title_action_templates'] = 'Add, change or remove templates';
$lang['title_css_designs'] = 'This column displays the design(s) (if any) that a stylesheet is assigned to.';
$lang['title_delete'] = 'Delete the selected items';
$lang['title_import_design'] = 'Import a design from XML';
$lang['type'] = 'Type'; //or lang(same)

//U
$lang['unknown'] = 'Unknown';

//W
$lang['warning_deletedesign'] = 'TODO';
$lang['warning_deletestylesheet_attachments'] = 'TODO';
$lang['warning_deletetemplate_attachments'] = 'TODO';
$lang['warning_group_dragdrop'] = 'TODO'; //info

//multi-line
$lang['event_help_adddesignpre'] = "<h4>Parameters</h4>
<ul>
  <li>'Design' - Reference to the affected design object.</li>
</ul>
";
$lang['event_help_adddesignpost'] = "<h4>Parameters</h4>
<ul>
  <li>'Design' - Reference to the affected design object.</li>
</ul>
";
$lang['event_help_editdesignpost'] = '<h4>Parameters</h4>
<ul>
  <li>\'Design\' - A reference to the affected design object.</li>
</ul>
';
$lang['event_help_editdesignpre'] = '<h4>Parameters</h4>
<ul>
  <li>\'Design\' - A reference to the affected design object.</li>
</ul>
';
$lang['event_help_deletedesignpost'] = '<h4>Parameters</h4>
<ul>
  <li>\'Design\' - A reference to the affected design object.</li>
</ul>
';
$lang['event_help_deletedesignpre'] = '<h4>Parameters</h4>
<ul>
  <li>\'Design\' - A reference to the affected design object.</li>
</ul>
';

$lang['help_module'] = <<<'EOT'
<h3>What is this?</h3>
<p>Design Manager is a module for managing designs.</p>

<h3>What is a Design ?</h3>
<p>It is a container of sorts, providing an association among stylesheet(s) and template(s). It allows e.g. managing all of the stylesheets and templates required to implement the look and feel of a site. Designs can be exported to a single file for sharing, and imported from such a file.</p>
<p>Any template or stylesheet may belong to zero or more designs.</p>

<h3>Managing Designs</h3>
<p>The designs interface is available to users with the &quot;Manage Designs&quot; permission. It displays a list of known designs, in a tabular format. Each row of the table represents a single design. The columns of the table display summary information about the design and provide some ability to interact with it.</p>
<p>This interface does not provide filtering, pagination, or bulk actions. It is intended that the number of designs associated with a website be kept small and manageable.</p>
<p>Links on the page enable creating a new design, or importing a design from XML file.</p>
<h4>Table Columns</h4>
<ul>
  <li>Name:
    <p>This column displays a link containing the name for the design. Clicking on this link will display the edit design form.</p>
  </li>
  <li>Actions:
    <p>This column displays various links and icons representing actions that can be performed with designs:</p>
    <ul>
      <li>Edit - Display a form to allow editing the design.</li>
      <li>Export - Export the design to an XML file that can be imported into other websites.</li>
      <li>Delete - Display a form that asks for confirmation about deleting the design.</li>
    </ul>
  </li>
</ul>
<h4>Editing Designs:</h4>
<p>The edit interface allows management of all attributes of a design. Unlike editing stylesheets and templates, this does not support &quot;dirtyform&quot; or locking functionality.</p>
<p>Some of the attributes of a design that can be edited are:</p>
<ul>
  <li>Name:
  </li>
  <li>Templates:
    <p>This tab allows [de]selecting the templates belonging to the design. Templates can be dragged between the &quot;Available Templates&quot; list and the &quot;Assigned Templates&quot; list, and to order templates within the ones assigned. (The order of the assigned templates is not operationally significant.)</p>
  </li>
  <li>Stylesheets:
    <p>This tab allows [de]selecting the stylesheets belonging to the design. Stylesheets can be dragged between the &quot;Available Stylesheets&quot; list and the &quot;Assigned Stylesheets&quot; list, and to order stylesheets within the ones assigned. (The order of the assigned stylesheets is not operationally significant.)</p>
  </li>
  <li>Description:
    <p>This tab provides a free form text area where a description of the design, and additional notes can be entered. the description is also useful to other users when deciding to share a design.</p>
  </li>
</ul>
<h4>Importing Designs</h4>
<p>The Design Manager module can import XML themes that were exported from CMSMS Design Manager, or from the older CMSMS theme manager. It expands the uploaded XML file, and extracts templates, stylesheets, and other useful information from the file. It also performs some minor transformation on the extracted data to try to adjust for overlapping names, etc.</p>
<p>The import process is divided into a few steps:</p>
<ul>
  <li>Step 1: Upload the file:
    <p>This step uploads the user-sel//$lang['help_module']
ected XML file and valides its content. This step is subject to PHP limits on file size, memory usage, and timeout for form processing. You might need to increase those limits on overly-restricted sites when uploading larger design files.</p>
    <p>After the XML file has been validated, it is copied to a temporary location for processing in step 2.</p>
  </li>
  <li>Step 2: Verification:
    <p>The second step is for verifying and previewing the new design that will be created from the XML file. After this you can display and edit various aspects of the design.</p>
</ul>
<h4>Deleting Designs:</h4>

<h3>Upgrade Notes</h3>
EOT;

/* +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
$lang['help'] = <<<'EOT'
<h3>What does this do?</h3>
<p>The &quot;DesignManager&quot; module provides interfaces for authorized users to add, remove or modify templates, stylesheets, and &quot;designs&quot; used on this website.</p>

<h3>Templates Explained:</h3>
<p>A template is a collection of HTML and/or smarty code.  Templates are usually re-used multiple times on a website, and can include or inherit from other templates.  CMSMS provides numerous templates for various purposes.  These purposes include defining the structure of a web page, a navigation menu, or displaying news article summaries or details.<p>
<p>Each template must have a name that must be unique across the entire installation.  Additionally, each template is capable of having a description that allows providing human readable information and notes about the characteristics of the template.  You can optionally assign each template to a group to aide in quickly finding the template when edits are required.</p>
<p>Templates can optionally be assigned to one or more designs. When exporting a design all the templates that are assigned to the design will be exported.</p>
<p>Depending upon the template type <em>(see below)</em> A template can be set as the &quot;default&quot; for that type.  This functionality allows a module to find a template to use of an appropriate type if no template name is specified in the module tag, or via any other means.  For example, in a default installation the &quot;News Summary Sample&quot; template is the default template for the News default <em>(summary)</em> action.  Therefore calling <em>{News}</em> without specifying a template will use this template.</p>
<p>Optionally, you can select one or more admin user accounts, or admin user groups that have the ability to edit the template.  This gives the ability for restricted users to have limited access to some templates.  This might be useful for editing seasonal messages, or for modifying API keys or RSS feed URLS.</p>
<p>When templates are edited the user(s) selected syntax highlighter module will be used, assuming that a syntax highlighter module has been installed, and that the user has selected one from within his user preferences.</p>
<p>To call a template you can either specify the template name in a module call or explicitly call/include the template from another one with the <code>{include file='cms_template:&lt;template_name&gt;'}</code> syntax.  See the smarty {include} tag.  Additionally, for backwards compatibility purposes the <code>{global_content name='&lt;template_name&gt;'}</code> syntax still works.</p>

<h4>Template Types Explained:</h4>
<p>A template type loosely indicates the general purpose for the template.  Template types indicate the module or code that uses them, and a subtype.  For example two common template types are Core::Page indicating a template used by the core CMSMS system to structure a web page, and News::Summary indicating a template that the News module can use to create a summary listing.</p>
<p>Modules might create new template types on installation, and delete template types when they uninstall.  Most modules will delete all templates that are assigned to its types when the module is uninstalled.</p>
<p>Template types can optionally contain a &quot;Prototype Template&quot;.  The prototype is used when creating a new template of that type.  For example, if you create a new template of type &quot;News::Detail&quot; the template will initially be filled with the prototype data from that type.  You can then change the template to your liking.</p>

<h3>Groups Explained:</h3>
<p>Templates can be assigned to one or more groups to facilitate their management.  The DesignManager interface supports filtering templates by group.</p>

<h3>Stylesheets Explained:</h3>
<p>Stylesheets are CSS text stored within the system, for application to content (i.e. frontend) pages.
Each stylesheet must have a unique name, and may have a description and a media query.</p>
<p>When a content page is rendered, the stylesheet(s) assigned to the page will be combined into one file for processing by the browser.</p>
<p>Each stylesheet can optionally be assigned to one or more groups, and such groups may be assigned to pages along with, or instead of, individual stylesheets. The order of the stylesheets within a group is significant: they will be applied to the page in that same order.</p>
<p>Stylesheets can optionally be assigned to one or more designs. When exporting a design all the stylesheets that are assigned to the design will be exported.</p>
<p>Stylesheets may include smarty tags to allow doing logic within the stylesheet, or creating variables for reuse. However instead of the normal { and } delimiters for smarty, the [[  and ]] delimiters are used.  For example:</p>
<pre><code><span style="color: blue;">[[&#36;red='#f00']]</span>
div.error {
   color: <span style="color: blue;">[[&#36;red]]</span>;
}


</code></pre>
<p><strong>Note:</strong> Because styles can be applied to pages via groups and/or individually, and because stylesheets are combined and cached for all visitors, on the browser you must be careful when including smarty in templates.  Here are a few notes:</p>
  <ul>
    <li>Never put server-specific conditions, state conditions, time-related, or page-specific conditions into a stylesheet.</li>
    <li>It should be assumed that each stylesheet might be called individually, so smarty variable initialization should be included in each stylesheet.</li>
  </ul>
<p><strong>Note:</strong> Other templates may be included stylesheets using the <code>[[include file='cms_template::&lt;template_name&gt;']]</code> syntax. However such inclusions must comply with the non-standard smarty delimiters as noted above.  For example:</p>

  <ul>
    <li>Stylesheet &quot;page&quot;<br/>
<pre><code><span style="color: blue;">[[include file='cms_template::my_colors']]</span>
body {
  background-color: <span style="color: blue;">[[&#36;my_background]]</span>;
  color: <span style="color: blue;">[[&#36;dflt_foreground]]</span>;
}
</code></pre>
    </li>
    <li>Template: &quot;my_colors&quot;<br/>
<pre><code style="color: blue;">[[&#36;my_background='#fff']]
[[&#36;dflt_foreground='#000']]
</code></pre>
    </li>
  </ul>

<h3>How do Content Pages Figure In?</h3>
<p>Each content page has a template (with included sub-templates, blocks, etc as appropriate). That template determines the content blocks and types of content blocks, and other elements of the page, hence what and how content is to be served up for display.</p>
<p>Each content page has stylesheet(s), individual(s) and/or group(s). Those are applied, in sequence, to the content page by the browser as it renders the page.</p>

<h3>What is a &quot;Design&quot; ?</h3>
<p>A &quot;Design&quot; is merely a vehicle for grouping template(s) and/or stylesheet(s) which might be used on this website or on another CMSMS-powered site.</p>
<p>A design's purpose is to facilitate sharing - import and export of the design's contents. Designs have no direct role in the operation of the site.</p>

<h3>Importing and Exporting:</h3>
<p>DesignManager includes functionality to export designs (templates, stylesheets and stylesheet order) to an XML file for use on another compatible CMS Made Simple website.  To export a design just click on the export icon on the relevant row in the displayed list of designs.</p>
<p>The system will find all templates and stylesheets assigned to a design, and for each template, parse it to find templates that are <strong>directly</strong> included via module calls or the include statement, and assign those too.  It will also find links to local images, and include those files in the output file.  Similarly stylesheets are parsed for links to local images, and those images are included in the xml file.</p>

<h3>Template Locking:</h3>
<p>In order to prevent one designer or site developer from accidentally overwriting the work of another developer the DesignManager provides locking for templates.  When a site developer begins an edit session on a template the template is locked from edits from other developers.</p>
<p>Frequently, during the edit session the software pings the server to indicate that the developer is still working on the template.  If edit activity on the template ceases for a period of time, other developers can &quot;steal&quot; the lock.  Any unsaved changes by the first developer would be lost.</p>
<p><strong>Note:</strong> At this time there is no locking functionality for stylesheets.</p>

<h3>Permissions and Visibility</h3>
<p>Numerous permissions are used to control access to the DesignManager module and its visibility in the CMSMS Admin navigation:</p>
<ul>
  <li>Modify Templates:
    <p>Admin users with this permission may to completely manage templates, including adding/editing/deleting. They may also adjust the ownership, and additional editors of a template.</li>
  </li>
  <li>Add Templates:
    <p>Admin users with this permission may create new templates, but may not modify existing templates <em>(that they do not otherwise have authority over)</em></p>
  </li>
  <li>Manage Stylesheets:
    <p>Admin users with this permission may completely manage stylesheets. There is no functionality for individual ownership, or additional editors, of stylesheets.</p>
  </li>
  <li>Manage Designs:
    <p>Admin users with this permission may manage designs, including importing and exporting designs.  When importing a design the currently logged in user is marked as the owner for all new templates and stylesheets.</p>
  </li>
  <li>Owner:
    <p>Each admin user may edit or delete the templates she/he owns. She/he can also grant additional editor rights to other users or admin groups.</p>
  </li>
  <li>Additional Editor:
    <p>An admin user who has been recorded as an &quot;Additional Editor&quot; may edit the corresponding template including its description.  However she/he may not change any associations of the template, delete the template or grant additional privileges to other users.</p>
  </li>
</ul>

<h3>History:</h3>
<p>Prior to CMSMS 2, admin operations and modules managed their own templates. This lead to different interfaces for doing essentially the same things.</p>

<h3>Compatibility:</h3>
<p>Module templates are now stored together with all other site templates, and can be dealt with using the DesignManager UI.</p>
<p>The former API for managing module templates remains. This means that modules can continue to deploy custom interfaces if that remains effective.</p>
<br />
EOT;
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++ */
