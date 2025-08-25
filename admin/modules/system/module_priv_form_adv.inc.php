<?php
/**
 * @Created by          : Waris Agung Widodo (ido.alit@gmail.com)
 * @Date                : 26/09/20 06.33
 * @File name           : module_priv_form_adv.inc.php
 */

defined('INDEX_AUTH') OR die('can not access this file directly');

ob_start();

/**
 * @var $dbs mysqli
 */
$module_query = $dbs->query("SELECT * FROM mst_module");
?>
    <div class="accordion" id="accordionExample">
        <?php $n = 0; while ($module_data = $module_query->fetch_assoc()): ?>
         <?php $bs4AriaComponentName = strtolower(str_replace(' ', '-', $module_data['module_name'])); ?>
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center" id="headingOne">
                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#<?= $bs4AriaComponentName ?>" aria-expanded="true" aria-controls="<?= $bs4AriaComponentName ?>">
                    <?= __(ucwords(str_replace('_', ' ', $module_data['module_name']))) ?>
                </button>

                <?php

                $read_checked = '';
                $write_checked = '';

                if (isset($priv_data[$module_data['module_id']]['r']) AND $priv_data[$module_data['module_id']]['r'] == 1) {
                    $read_checked = 'checked';
                }

                if (isset($priv_data[$module_data['module_id']]['w']) AND $priv_data[$module_data['module_id']]['w'] == 1) {
                    $read_checked = 'checked';
                    $write_checked = 'checked';
                }

                ?>

                <div class="d-flex">
                    <div class="custom-control custom-switch mr-4">
                        <input name="read[]" value="<?= $module_data['module_id'] ?>" <?= $read_checked ?> type="checkbox" class="custom-control-input" id="read-<?= $bs4AriaComponentName ?>">
                        <label class="custom-control-label" for="read-<?= $bs4AriaComponentName ?>"><?= __('Read') ?></label>
                    </div>
                    <div class="custom-control custom-switch">
                        <input name="write[]" value="<?= $module_data['module_id'] ?>" <?= $write_checked ?> type="checkbox" class="custom-control-input" id="write-<?= $bs4AriaComponentName ?>">
                        <label class="custom-control-label" for="write-<?= $bs4AriaComponentName ?>"><?= __('Write') ?></label>
                    </div>
                </div>
            </div>

            <div id="<?= $bs4AriaComponentName ?>" class="collapse <?= $n < 1 ? 'show' : '' ?>" aria-labelledby="headingOne" data-parent="#accordionExample">
                    <?php
                    $menu = [];
                    $_ = '__';
                    $submenu_path = MDLBS . $module_data['module_path'] . '/submenu.php';
                    $for_select_privileges = true;
                    if (file_exists($submenu_path)) include $submenu_path;
                    $menuID = 'prev-'.$bs4AriaComponentName;
                    $submenu = '<ul class="list-group list-group-flush" id="'.$menuID.'">';
                    $submenu .= '<li class="list-group-item text-bold d-flex justify-content-between">';
                    $submenu .= '<span>'.__('Enable or disable submenu').'</span>';
                    $submenu .= <<<HTML
                    <div class="btn-group btn-group-sm" role="group">
                        <button onclick="checkBox('#{$menuID}', 'select')" type="button" class="btn btn-secondary">{$_('Select All')}</button>
                        <button onclick="checkBox('#{$menuID}', 'unselect')" type="button" class="btn btn-secondary">{$_('Unselect All')}</button>
                        <button onclick="checkBox('#{$menuID}', 'invert')" type="button" class="btn btn-secondary">{$_('Select Invert')}</button>
                    </div>
HTML;
                    $submenu .= '</li>';

                    // load submenu from plugins
                    $plugin_menus = \SLiMS\Plugins::getInstance()->getMenus(str_replace(' ', '_', $module_data['module_name']));
                    $menu = array_merge($menu, $plugin_menus);

                    foreach ($menu as $id => $item) {
                        if ($item[0] == 'Header') continue;
                        $id = (!is_numeric($id)) ? $id : md5($item[1]);
                        $menu_checked = in_array($id, $priv_data[$module_data['module_id']]['menus'] ?? []) ? 'checked' : '';
                        $submenu .= <<<HTML
<li class="list-group-item px-4">
    <div class="px-2">
        <div class="custom-control custom-switch">
          <input name="menus[{$module_data['module_id']}][]" value="{$id}" {$menu_checked} type="checkbox" class="custom-control-input" id="menu-{$module_data['module_id']}-{$id}">
          <label class="custom-control-label d-flex justify-content-between" for="menu-{$module_data['module_id']}-{$id}">
            <span>{$item[0]}</span>
            <code>{$id}</code>
          </label>
        </div>
    </div>
</li>
HTML;

                    }
                    $submenu .= '</ul>';
                    echo $submenu;
                    ?>
            </div>
        </div>
        <?php $n++; endwhile; ?>
    </div>
    <script>
        /**
         * Check or uncheck input checkbox method.
         * @param root selector of root
         * @param method select=select all; unselect=unselect all; default=invert
         */
        function checkBox(root, method) {
            $(root).find('input[type=checkbox]').each(function () {
                switch (method) {
                    case 'select':
                        if(!this.checked) $(this).trigger('click')
                        break;
                    case 'unselect':
                        if(this.checked) $(this).trigger('click')
                        break;
                    default:
                        $(this).trigger('click')
                        break;
                }
            })
        }
    </script>
<?php
$priv_table = ob_get_clean();
