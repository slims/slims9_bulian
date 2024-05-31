<?php
# @Author: Waris Agung Widodo <user>
# @Date:   2018-01-25T10:25:29+07:00
# @Email:  ido.alit@gmail.com
# @Filename: _navbar.php
# @Last modified by:   user
# @Last modified time: 2018-01-25T10:29:27+07:00

$main_menus = [
  'home' => [
    'text' => __('Home'),
    'url' => 'index.php'
  ],
  'libinfo' => [
    'text' => __('Information'),
    'url' => 'index.php?p=libinfo'
  ],
  'news' => [
    'text' => __('News'),
    'url' => 'index.php?p=news'
  ],
  'help' => [
    'text' => __('Help'),
    'url' => 'index.php?p=help'
  ],
  'librarian' => [
    'text' => __('Librarian'),
    'url' => 'index.php?p=librarian'
  ]
];
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-transparent">
    <a class="navbar-brand inline-flex items-center" href="index.php">
        <?php
        if(isset($sysconf['logo_image']) && $sysconf['logo_image'] != '' && $imagesDisk->isExists($path = 'default/'.$sysconf['logo_image'])){
            echo '<img class="h-10 w-15" src="'.SWB . 'lib/minigalnano/createthumb.php?filename=images/' . $path.'&width=350">';
        }
        elseif (file_exists(__DIR__ . '/../assets/images/logo.png')) {
            echo '<img class="h-8 w-8" src="'.assets('images/logo.png').'">';
        } else {
        ?>
        <img src="https://static.slims.web.id/logo.svg" class="fill-current text-white inline-block h-8 w-8"/>
        <?php } ?>
        <div class="inline-flex flex-col leading-tight ml-2">
            <h1 class="text-lg m-0 p-0"><?php echo $sysconf['library_name']; ?></h1>
            <?php if ($sysconf['template']['classic_library_subname']) : ?>
            <h2 class="text-sm lead m-0 p-0"><?php echo $sysconf['library_subname']; ?></h2>
            <?php endif; ?>
        </div>
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
            aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto">
          <?php
          foreach ($main_menus as $key => $main_menu) {
            $active = '';
            if (isset($_GET['p'])) {
              if ($key === $_GET['p']) $active = 'active';
            } elseif ($key === 'home') {
              $active = 'active';
            }
            $menu_str = <<<HTML
<li class="nav-item {$active}">
    <a class="nav-link" href="{$main_menu['url']}">{$main_menu['text']}</a>
</li>
HTML;
            echo $menu_str;
          }
          ?>
          <?php
          $menu_member_active = isset($_GET['p']) && $_GET['p'] === 'member' ? 'active' : '';
          if ($is_login) {
            ?>
              <li class="nav-item <?= $menu_member_active; ?>">
                  <a class="nav-link" href="index.php?p=member&sec=title_basket">
                      <i class="fas fa-shopping-basket"></i>
                    <?php
                    $count_basket = count($_SESSION['m_mark_biblio']);
                    ?>
                      <sup id="count-basket" class="badge badge-danger"><?php echo $count_basket; ?></sup>
                  </a>
              </li>
              <li class="nav-item dropdown <?= $menu_member_active; ?>">
                  <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown"
                     aria-haspopup="true" aria-expanded="false">
                      <img class="w-6 h-6 rounded-full ml-2 mr-2"
                           src="<?php echo $member_image_path; ?>"
                           alt="Avatar of Jonathan Reinink">
                    <?php echo $_SESSION['m_name']; ?>
                  </a>
                  <div class="dropdown-menu dropdown-menu-right">
                      <a class="dropdown-item" href="index.php?p=member"><i class="fas fa-user-circle mr-3"></i> <?= __('Profile');?></a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="index.php?p=member&sec=bookmark"><i class="fas fa-bookmark mr-3"></i> <?= __('Bookmark');?></a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item" href="index.php?p=member&logout=1"><i class="fas fa-sign-out-alt mr-3"></i> <?= __('Logout'); ?></a>
                  </div>
              </li>
          <?php } else { ?>
              <li class="nav-item <?= $menu_member_active; ?>">
                  <a class="nav-link" href="index.php?p=member"><?= __('Member Area') ?></a>
              </li>
          <?php } ?>
            <li class="nav-item dropdown">
              <?php
              $langstr = '';
              $current_lang = '';
              $select_lang = isset($_COOKIE['select_lang'])?$_COOKIE['select_lang']:$sysconf['default_lang'];
              // require_once(LANG . 'localisation.php');
              foreach ($available_languages??[] AS $lang_index) {
                $selected = null;
                $lang_code = $lang_index[0];
                $lang_name = $lang_index[1];
                $code_arr = explode('_', $lang_code);
                $code_flag = strtolower($code_arr[1]);
                if ($lang_code == $select_lang) {
                  $current_lang = [
                    'name' => $lang_name,
                    'code' => $code_flag
                  ];
                }
                $langstr .= <<<HTML
    <a class="dropdown-item" href="index.php?select_lang={$lang_code}">
        <span class="flag-icon flag-icon-{$code_flag} mr-2" style="border-radius: 2px;"></span> {$lang_name}
    </a>
HTML;
              }
              ?>
                <a class="nav-link dropdown-toggle cursor-pointer" type="button" id="languageMenuButton"
                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="flag-icon flag-icon-<?= $current_lang['code'] ?>" style="border-radius: 2px;"></span>
                </a>
                <div class="dropdown-menu bg-grey-lighter dropdown-menu-lg-right" aria-labelledby="dropdownMenuButton">
                    <h6 class="dropdown-header"><?= __('Select Language'); ?> : </h6>
                  <?= $langstr; ?>
                </div>
            </li>
        </ul>
    </div>
</nav>
