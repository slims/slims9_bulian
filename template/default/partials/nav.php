<nav class="s-menu-content animated-fast" role="navigation">
  <a href="#" id="hide-menu" class="s-menu-toggle"><span></span></a>
  <h1>Menu</h1>
  <ul>
    <li><a href="index.php"><?php echo __('Home'); ?></a></li>
    <li><a href="index.php?p=news"><?php echo __('Library News'); ?></a></li>
    <li><a href="index.php?p=libinfo"><?php echo __('Library Information'); ?></a></li>
    <li><a href="index.php?p=peta" class="openPopUp" width="600" height="400"><?php echo __('Library Location'); ?></a></li>
    <li><a href="index.php?p=member"><?php echo __('Member Area'); ?></a></li>
    <li><a href="index.php?p=librarian"><?php echo __('Librarian'); ?></a></li>
    <li><a href="index.php?p=help"><?php echo __('Help on Search'); ?></a></li>
    <li><a href="index.php?p=login"><?php echo __('Librarian LOGIN'); ?></a></li>
    <li><a href="index.php?p=slimsinfo"><?php echo __('About SLiMS'); ?></a></li>
  </ul>

  <!-- Language Translator
  ============================================= -->
  <div class="s-menu-info">
    <form class="language" name="langSelect" action="index.php" method="get">
      <label class="language-info" for="select_lang"><?php echo __('Select Language'); ?></label>
      <span class="custom-dropdown custom-dropdown--emerald custom-dropdown--small">
        <select name="select_lang" id="select_lang" title="Change language of this site" onchange="document.langSelect.submit();" class="custom-dropdown__select custom-dropdown__select--emerald">
          <?php echo $language_select; ?>
        </select>
      </span>
    </form>
  </div>
</nav>
