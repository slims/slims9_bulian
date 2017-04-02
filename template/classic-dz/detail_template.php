<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-19 13:33:35
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-22 10:59:23
 */

?>

<div class="slims-card slims-card--info">
	<div class="slims-row">
		<div class="slims-8">
			<h3 itemprop="name" property="name"><?php echo $title; ?></h3>
			<h5 itemprop="author" property="author" itemscope itemtype="http://schema.org/Person"><?php echo  $authors ?></h5>
		</div>
		<div class="slims-4" id="cover">
			<?php echo $image; ?>
		</div>
	</div>
</div>

<!-- Notes -->
<?php if($notes != 'Includes index.') : ?>
<div class="slims-card slims-card--default" itemprop="description" property="description">
	<?php echo $notes; ?>
</div>
<?php else : ?>
<div class="slims-card slims-card--warning">
	<em><?php echo __('Description Not Available'); ?></em>
</div>
<?php endif; ?>

<!-- Availability -->
<?php if($availability) : ?>
<div class="slims-card slims-card--default">
	<div class="slims-card--header">
		<?php echo __('Availability'); ?>
	</div>
	<?php echo $availability; ?>
</div>
<?php else : ?>
<div class="slims-card slims-card--error">
	<?php echo __('No copy data'); ?>
</div>
<?php endif ?>

<!-- Detail Informasi -->
<div class="slims-row">
	<div class="slims-6">
		<div class="slims-card slims-card--default">
			<div class="slims-card--header">
				<?php echo __('Detail Information'); ?>
			</div>

			<div>
				<h5><?php echo __('Series Title'); ?></h5>
				<p itemprop="alternativeHeadline" property="alternativeHeadline"><?php echo ($series_title) ? $series_title : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Call Number'); ?></h5>
				<p><?php echo ($call_number) ? $call_number : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Publisher'); ?></h5>
				<p>
					<span itemprop="publisher" property="publisher" itemtype="http://schema.org/Organization" itemscope><?php echo $publisher_name ?></span> :
			        <span itemprop="publisher" property="publisher"><?php echo $publish_place ?></span>.,
			        <span itemprop="datePublished" property="datePublished"><?php echo $publish_year ?></span>
				</p>
			</div>
			
			<div>
				<h5><?php echo __('Collation'); ?></h5>
				<p itemprop="numberOfPages" property="numberOfPages"><?php echo ($collation) ? $collation : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Language'); ?></h5>
				<p>
					<meta itemprop="inLanguage" property="inLanguage" content="<?php echo $language_name ?>" /><?php echo $language_name ?>
				</p>
			</div>
			
			<div>
				<h5><?php echo __('ISBN/ISSN'); ?></h5>
				<p itemprop="isbn" property="isbn"><?php echo ($isbn_issn) ? $isbn_issn : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Classification'); ?></h5>
				<p><?php echo ($classification) ? $classification : '-'; ?></p>
			</div>
		</div>
	</div>
	<div class="slims-6">
		<div class="slims-card slims-card--default">
			<div class="slims-card--header">
				<?php echo __('Detail Information'); ?>
			</div>

			<div>
				<h5><?php echo __('Content Type'); ?></h5>
				<p itemprop="bookFormat" property="bookFormat"><?php echo ($content_type) ? $content_type : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Media Type'); ?></h5>
				<p itemprop="bookFormat" property="bookFormat"><?php echo ($media_type) ? $media_type : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Carrier Type'); ?></h5>
				<p itemprop="bookFormat" property="bookFormat"><?php echo ($carrier_type) ? $carrier_type : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Edition'); ?></h5>
				<p itemprop="bookEdition" property="bookEdition"><?php echo ($edition) ? $edition : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Subject(s)'); ?></h5>
				<p itemprop="keywords" property="keywords"><?php echo ($subjects) ? $subjects : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Specific Detail Info'); ?></h5>
				<p><?php echo ($spec_detail_info) ? $spec_detail_info : '-'; ?></p>
			</div>
			
			<div>
				<h5><?php echo __('Statement of Responsibility'); ?></h5>
				<p itemprop="author" property="author"><?php echo ($sor) ? $sor : '-';  ?></p>
			</div>
		</div>
	</div>
</div>

<!-- Biblio relation -->
<?php if($related) : ?>
	
<div class="slims-card slims-card--default">
	<div class="slims-card--header">
		<?php echo __('Other version/related'); ?>
	</div>
	<?php echo $related; ?>
</div>

<?php else : ?>

<div class="slims-card slims-card--error">
	<?php echo __('No other version available'); ?>
</div>

<?php endif ?>

<!-- File Attachment -->
<?php if($file_att) : ?>
	
<div class="slims-card slims-card--default">
	<div class="slims-card--header">
		<?php echo __('File Attachment'); ?>
	</div>
	<?php echo $file_att; ?>
</div>

<?php endif ?>

<!-- Comment -->
<?php // if(isset($_SESSION['mid'])) : ?>

<div class="slims-card slims-card--default">
	<div class="slims-card--header">
		<?php echo __('Comments'); ?>
	</div>
	<?php echo showComment($biblio_id); ?>
</div>

<?php // endif ?>