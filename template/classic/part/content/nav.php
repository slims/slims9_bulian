<?php
/**
 * @Author: ido_alit
 * @Date:   2015-11-12 19:55:37
 * @Last Modified by:   ido_alit
 * @Last Modified time: 2015-11-24 10:40:47
 */

//set default index page
$p = 'home';

if (isset($_GET['p']))
{
 if ($_GET['p'] == 'libinfo') {
  $p = 'libinfo';
} elseif ($_GET['p'] == 'help') {
  $p = 'help';
} elseif ($_GET['p'] == 'member') {
  $p = 'member';
} elseif ($_GET['p'] == 'login') {
  $p = 'login';
} else {
  $p = strtolower(trim($_GET['p']));
}
}

$menus = array (
    'home'   => array(
      'url'  => 'index.php',
      'text' => __('Home')
      ),
    'libinfo'  => array(
      'url'  => 'index.php?p=libinfo',
      'text' => __('Library Information')
      ),
    'member'   => array(
      'url'  => 'index.php?p=member',
      'text' => __('Member Area')
      ),
    'librarian'   => array(
      'url'  => 'index.php?p=librarian',
      'text' => __('Librarian')
      ),
    'help'   => array(
      'url'  => 'index.php?p=help',
      'text' => __('Help on Search'),
      'dropdown' => array()
      ),
    'more'   => array(
      'url'  => '#',
      'text' => __('More'),

      'dropdown' => array(
        'news' => array(
          'url'   => 'index.php?p=news',
          'text'  => __('News')
          ),
        'login' => array(
          'url'   => 'index.php?p=login',
          'text'  => __('Librarian LOGIN')
          ),
        'link3' => array(
          'url'   => '#',
          'text'  => 'Link'
          ),
        'link4' => array(
          'url'   => '#',
          'text'  => 'Link'
          )
        )
      )
    );

?>

<nav class="slims-row slims-menus">
	<ul>

  <?php
  $d = '';
  foreach ($menus as $path => $menu) {
    if (isset($menu['dropdown']) && !empty($menu['dropdown'])) {
      $d .= '<li class="dropdown">';
      $d .= '<a class="slims-button slims-button--default dropdown-toggle" id="m-'.$path.'" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">';
      $d .= $menu['text'];
      $d .= '<span class="caret"></span>';
      $d .= '</a>';
      $d .= '<ul class="dropdown-menu slims-dropdown" aria-labelledby="m-'.$path.'">';
      foreach ($menu['dropdown'] as $path2 => $menu2) {
        $d .= '<li><a href="'.$menu2['url'].'">'.$menu2['text'].'</a></li>';
      }
      $d .= '</ul>';
    } else {
      $d .= '<li>';
      if ($p == $path) {
        $c = 'slims-button--blue';
      } else {
        $c = 'slims-button--default';
      }
      $d .= '<a href="'.$menu['url'].'" class="slims-button '.$c.'">'.$menu['text'].'</a>';
      $d .= '</li>';
    }
  }
  echo $d;
  ?>

  </ul>
</nav>