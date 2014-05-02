<?php
// biblio/record detail
// output the buffer
ob_start(); /* <- DONT REMOVE THIS COMMAND */
?>
<table class="border margined" style="width: 99%;" cellpadding="5" cellspacing="0">
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Title'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{title}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Edition'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{edition}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Call Number'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{call_number}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('ISBN/ISSN'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{isbn_issn}</td>
</tr>
<tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Author(s)'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{authors}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Subject(s)'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{subjects}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Classification'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{classification}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Series Title'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{series_title}</td>
</tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('GMD'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{gmd_name}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Language'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{language_name}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Publisher'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{publisher_name}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Publishing Year'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{publish_year}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Publishing Place'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{publish_place}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Collation'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{collation}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Abstract/Notes'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{notes}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Specific Detail Info'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{spec_detail_info}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Image'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{image}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('File Attachment'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{file_att}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top"><?php print __('Availability'); ?></td>
<td class="tblContent" style="width: 80%;" valign="top">{availability}</td>
</tr>
<tr>
<td class="tblHead" style="width: 20%;" valign="top">&nbsp;</td>
<td class="tblContent" style="width: 80%;" valign="top">Press &quot;Back&quot; Button on your browser to back to previous result</td>
</tr>
</table>
<?php
// put the buffer to template var
$detail_template = ob_get_clean();
?>
