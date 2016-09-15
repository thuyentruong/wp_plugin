<?php

$update   = false;
$options  = get_site_option('canadahtml5map_options');

if (isset($_REQUEST['action'])) {
    switch ($_REQUEST['action']) {
        case 'map_import':
            canada_html5map_plugin_import();
            break;
        case 'new':
            $name      = sanitize_text_field($_REQUEST['name']);
            $options[] = canada_html5map_plugin_map_defaults($name);
            $update    = true;
            break;
        case 'delete':
            unset($options[intval($_REQUEST['map_id'])]);
            $update = true;
            break;
    }
}

if ($update) update_site_option('canadahtml5map_options',$options);

class Map_List_Table extends WP_List_Table {

    public function prepare_items()
    {
        $columns  = $this->get_columns();
        $hidden   = $this->get_hidden_columns();
        $sortable = $this->get_sortable_columns();

        $data     = $this->table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        $this->_column_headers = array($columns, $hidden, $sortable);
        $this->items = $data;
    }

    public function get_columns()
    {
        $columns = array(
            'checkbox'  => '<input type="checkbox" class="maps_toggle" autocomplete="off" />',
            'name'      => __('Name', 'canada-html5-map'),
            'shortcode' => __('ShortCode', 'canada-html5-map'),
            'edit'      => __('Edit', 'canada-html5-map'),
        );

        return $columns;
    }

    public function get_hidden_columns()
    {
        return array();
    }

    public function get_sortable_columns()
    {
        return array('name' => array('name', false));
    }

    private function table_data()
    {

        $data      = array();
        $options   = get_site_option('canadahtml5map_options');

        foreach ($options as $map_id => $map_data) {
            $data[] = array(
                            'id'        => $map_id,
                            'name'      => $map_data['name'],
                            'shortcode' => '[canadahtml5map id="'.$map_id.'"]',
                            'edit'      => '<a href="admin.php?page=canada-html5-map-options&map_id='.$map_id.'">'.__('General settings', 'canada-html5-map').'</a><br />
                                            <a href="admin.php?page=canada-html5-map-states&map_id='.$map_id.'">'.__('Detailed settings', 'canada-html5-map').'</a><br />'.
                                            '<a href="admin.php?page=canada-html5-map-points&map_id='.$map_id.'">'.__('Points settings', 'canada-html5-map').'</a><br />'.
                                            '<a href="admin.php?page=canada-html5-map-view&map_id='.$map_id.'">'.__('Preview', 'canada-html5-map').'</a><br /><br />
                                            <a href="admin.php?page=canada-html5-map-maps&action=delete&map_id='.$map_id.'" class="delete" style="color:#FF0000">'.__('Delete', 'canada-html5-map').'</a><br />
                                            ',
                            );
        }

        return $data;
    }

    public function column_default( $item, $column_name )
    {

        switch( $column_name ) {
            case 'checkbox':
                echo '&nbsp;<input type="checkbox" value="'.$item['id'].'" class="map_checkbox" autocomplete="off" />';
                break;
            case 'name':
            case 'shortcode':
            case 'edit':
                return $item[ $column_name ];
        }
    }

    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'name';
        $order   = 'asc';

        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }

        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }

        $result = strcmp( $a[$orderby], $b[$orderby] );

        if($order === 'asc')
        {
            return $result;
        }

        return -$result;
    }

    public function get_table_classes()
    {
        $list = parent::get_table_classes();
        $list[] ='canada-html5-map';
        return $list;
    }

}


$listtable = new Map_List_Table();
$listtable->prepare_items();

?>

    <div class="wrap">
        <div id="icon-users" class="icon32"></div>
        <h2><?php echo __('HTML5 Maps', 'canada-html5-map'); ?></h2>
        <?php $listtable->display(); ?>
    </div>

    <form name="action_form" action="" method="POST" enctype="multipart/form-data" class="canada-html5-map full">
        <input type="hidden" name="action" value="new" />
        <input type="hidden" name="maps" value="" />

        <fieldset>
            <legend><?php echo __('Map Settings', 'canada-html5-map'); ?></legend>
            <span><?php echo __('New map name:', 'canada-html5-map'); ?></span>
            <input type="text" name="name" value="<?php echo __('New map', 'canada-html5-map'); ?>" />
            <input type="submit" class="button button-primary" value="<?php echo __('Add new map', 'canada-html5-map'); ?>" />
        </fieldset>

        <fieldset>
            <legend><? echo __('Export/import', 'canada-html5-map') ?></legend>
            <p><?php echo __('To export please select a checkbox of one or more maps, and press Export button', 'canada-html5-map'); ?></p>
            <input type="button" class="button button-secondary export" value="<?php echo __('Export', 'canada-html5-map'); ?>" />
            <input type="button" class="button button-secondary import" value="<?php echo __('Import', 'canada-html5-map'); ?>" />
        </fieldset>

        <div style="visibility: hidden">
            <input type="file" name="import_file" />
        </div>

    </form>

    <script type="text/javascript">
        jQuery(document).ready(function() {

            jQuery('a.delete').click(function() {
                if (confirm('<?php echo __('Remove the map?\nAttention! All settings for the map will be deleted permanently!', 'canada-html5-map'); ?>')) {
                    return true;
                } else {
                    return false;
                }
            });

            jQuery('.maps_toggle').click(function() {
                jQuery('.map_checkbox,.maps_toggle').not(jQuery(this)).each(function() {
                    jQuery(this).prop('checked', !(jQuery(this).is(':checked')));
                });
            });

            jQuery('input.export').click(function() {
                jQuery('input[name=action]').val('canada-html5-map-export');

                var maps = '';
                jQuery('.map_checkbox:checked').each(function() {
                    if (maps!='') maps+=',';
                    maps+=jQuery(this).val();
                });

                jQuery('input[name=maps]').val(maps);

                jQuery('form[name=action_form]').submit();
                return false; 
            });

            jQuery('input.import').click(function() {
                jQuery('input[name=import_file]').click();
                return false;
            });

            jQuery('input[name=import_file]').change(function() {
                jQuery('input[name=action]').val('map_import');
                jQuery('form[name=action_form]').submit();
            });

        });
    </script>

<?php

?>