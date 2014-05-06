<?php
// biblio/record detail
// output the buffer
ob_start(); /* <- DONT REMOVE THIS COMMAND */
?>
<span itemscope itemtype="http://schema.org/Book" vocab="http://schema.org/" typeof="Book">
  <div class="sidebar">
   <div class="cover">
   {image}
   </div>
   <div>
    <a class="back" href="javascript: history.back();"> <?php echo __('Back'); ?> </a>
    &nbsp; | &nbsp;
    <a target="_blank" href="<?php echo SWB; ?>index.php?p=show_detail&inXML=true&id=<?php echo $_GET['id'];?>" class="xml"
     title="" style="margin-top:10px;margin-right:20px;">XML</a>
   </div>
  </div>
  
  <div class="section">
   <div class="tagline">
    Book's Detail
   </div>
   <div class="collections-list">
    <div class="collection-detail">
     <table id="review" width="100%">
      <tr>
       <th colspan="2">
       <span itemprop="name" property="name">{title}</span>
       <div>{social_shares}</div>
       </th>
      </tr>
      <tr>
       <td class="abstract" colspan="2">
       <p itemprop="description" property="description">{notes}</p>
       </td>
      </tr>
      <tr>
       <td class="key"><?php print __('Statement of Responsibility'); ?></td>
       <td class="value" itemprop="author" property="author">{sor}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Author(s)'); ?></td>
       <td class="value" itemprop="author" property="author" itemscope itemtype="http://schema.org/Person">{authors}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Edition'); ?></td>
       <td class="value" itemprop="bookEdition" property="bookEdition">{edition}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Call Number'); ?></td>
       <td class="value">{call_number}</td>
      </tr>
      <tr class="isbn">
       <td class="key"><?php print __('ISBN/ISSN'); ?></td>
       <td class="value" itemprop="isbn" property="isbn">{isbn_issn}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Subject(s)'); ?></td>
       <td class="value" itemprop="keywords" property="keywords">{subjects}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Classification'); ?></td>
       <td class="value">{classification}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Series Title'); ?></td>
       <td class="value" itemprop="alternativeHeadline" property="alternativeHeadline">{series_title}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('GMD'); ?></td>
       <td class="value" itemprop="bookFormat" property="bookFormat">{gmd_name}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Language'); ?></td>
       <td class="value"><meta itemprop="inLanguage" property="inLanguage" content="{language_name}" />{language_name}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Publisher'); ?></td>
       <td class="value" itemprop="publisher" property="publisher" itemtype="http://schema.org/Organization" itemscope>{publisher_name}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Publishing Year'); ?></td>
       <td class="value" itemprop="datePublished" property="datePublished">{publish_year}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Publishing Place'); ?></td>
       <td class="value" itemprop="publisher" property="publisher">{publish_place}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Collation'); ?></td>
       <td class="value" itemprop="numberOfPages" property="numberOfPages">{collation}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Specific Detail Info'); ?></td>
       <td class="value">{spec_detail_info}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('File Attachment'); ?></td>
       <td class="value" itemprop="associatedMedia">{file_att}</td>
      </tr>
      <tr>
       <td class="key"><?php print __('Availability'); ?></td>
       <td class="value">{availability}</td>
      </tr>
    </table>
    <?php echo showComment($detail_id); ?>
    </div>
    <div class="clear">&nbsp;</div>
   </div>
  </div>
</span>
<?php
// put the buffer to template var
$detail_template = ob_get_clean();
