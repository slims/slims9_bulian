<nav class="s-menu-content animated-fast" role="navigation">
  <a href="#" id="hide-menu" class="s-menu-toggle"><span></span></a>
  <h1>Menu</h1>
  <ul>
    <li><a href="index.php"><?php echo __('Home'); ?></a></li>
    <li><a href="index.php?p=libinfo"><?php echo __('Library Information'); ?></a></li>
    <li><a href="index.php?p=member"><?php echo __('Member Area'); ?></a></li>
    <li><a href="index.php?p=librarian"><?php echo __('Librarian'); ?></a></li>
    <li><a href="index.php?p=help"><?php echo __('Help on Search'); ?></a></li>
    <li><a href="index.php?p=login"><?php echo __('Librarian LOGIN'); ?></a></li>
  </ul>

  <!-- Language Translator
  ============================================= -->
  <div class="s-menu-info">
    <form class="language" name="langSelect" action="index.php" method="get">
      <label class="language-info" for="select_lang"><?php echo __('Select Language'); ?></label>
      <select name="select_lang" id="select_lang" title="Change language of this site" onchange="document.langSelect.submit();" class="input-medium">
        <?php echo $language_select; ?>
      </select>
    </form>
  </div>
</nav>
