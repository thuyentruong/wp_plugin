<?php

$options = get_site_option('canadahtml5map_options');
$option_keys = is_array($options) ? array_keys($options) : array();
$map_id  = (isset($_REQUEST['map_id'])) ? intval($_REQUEST['map_id']) : array_shift($option_keys) ;

if((isset($_POST['act_type']) && $_POST['act_type'] == 'canada-html5-map-main-save') && current_user_can('manage_options')) {

    $_REQUEST['options']['nameStroke']     = (isset($_REQUEST['options']['nameStroke'])) ? 1 : 0;
    $_REQUEST['options']['name']           = stripslashes($_REQUEST['options']['name']);

    if ($_REQUEST['options']['popupCommentColor'] == 'default')
        $_REQUEST['options']['popupCommentColor'] = '';

    foreach (array('borderColor', 'nameColor', 'popupNameColor', 'popupCommentColor') as $field)
        $_REQUEST['options'][$field]    = $_REQUEST['options'][$field] ?
            ($_REQUEST['options'][$field][0] == '#' ? $_REQUEST['options'][$field] : '#' . $_REQUEST['options'][$field]) : '';

    if ( ! empty($_REQUEST['options']['defaultAddInfo']))
        $_REQUEST['options']['defaultAddInfo'] = wp_kses_post(stripslashes($_REQUEST['options']['defaultAddInfo']));
    else
        $_REQUEST['options']['defaultAddInfo'] = '';

    $_REQUEST['options']['zoomEnable']              = (isset($_REQUEST['options']['zoomEnable'])) ? 1 : 0;

    if ($_REQUEST['options']['zoomEnable']) {

        $_REQUEST['options']['zoomEnableControls']      = (isset($_REQUEST['options']['zoomEnableControls'])) ? 1 : 0;
        $_REQUEST['options']['zoomIgnoreMouseScroll']   = (isset($_REQUEST['options']['zoomIgnoreMouseScroll'])) ? 1 : 0;

        $zm = intval($_REQUEST['options']['zoomMax']);
        $_REQUEST['options']['zoomMax'] = $zm = min(5, max(1, $zm));

        if (preg_match('/(\d+[\.,])?\d+/', $_REQUEST['options']['zoomStep'])) {
            $v = str_replace(',','.', $_REQUEST['options']['zoomStep']);
            if ($v > $zm)
                $v = $zm/2;
            elseif ($v < 0)
                $v = 0.2;
            $_REQUEST['options']['zoomStep'] = $v;
        } else {
            $_REQUEST['options']['zoomStep'] = 0.2;
        }
    }
    if (isset($_REQUEST['options']['shadowWidth']))
    {
        if (preg_match('/(\d+[\.,])?\d+/', $_REQUEST['options']['shadowWidth'])) {
            $v = str_replace(',','.', $_REQUEST['options']['shadowWidth']);
            if ($v > 10)
                $v = 10;
            elseif ($v < 0)
                $v = 0.2;
            $_REQUEST['options']['shadowWidth'] = $v;
        } else {
            $_REQUEST['options']['shadowWidth'] = 1.5;
        }
    }

    foreach($_REQUEST['options'] as $key => $value) if ($key != 'defaultAddInfo') { $_REQUEST['options'][$key] = sanitize_text_field($value); }

    if ( ! isset($options[$map_id]['defaultAddInfo']))
        $options[$map_id]['defaultAddInfo'] = '';

    $options[$map_id] = wp_parse_args($_REQUEST['options'],$options[$map_id]);
    if ( ! empty($_REQUEST['options']['hideSN']))
        $options[$map_id]['hideSN'] = true;
    else
        unset($options[$map_id]['hideSN']);

    $options[$map_id]['shadowAllow'] = ( ! empty($_REQUEST['options']['shadowAllow']));

    update_site_option('canadahtml5map_options', $options);

}

if (!isset($options[$map_id]['popupCommentFontSize']))
    $options[$map_id]['popupCommentFontSize'] = '';
if (!isset($options[$map_id]['popupCommentColor']))
    $options[$map_id]['popupCommentColor'] = '';

$mce_options = array(
    //'media_buttons' => false,
    'editor_height'   => 150,
    'textarea_rows'   => 20,
    'textarea_name'   => 'options[defaultAddInfo]',
    'tinymce' => array(
        'add_unload_trigger' => false,
    )
);

echo "<div class=\"wrap\"><h2>" . __('HTML5 Map Config', 'canada-html5-map') . "</h2>";
?>
<script xmlns="http://www.w3.org/1999/html">
    jQuery(function($){
        $('.tipsy-q').tipsy({gravity: 'w'}).css('cursor', 'default');

        $('.color~.colorpicker').each(function(){
            $(this).farbtastic($(this).prev().prev());
            $(this).hide();
            $(this).prev().prev().bind('focus', function(){
                $(this).next().next().fadeIn();
            });
            $(this).prev().prev().bind('blur', function(){
                $(this).next().next().fadeOut();
            });
        });

        $('select[name=map_id]').change(function() {
            location.href='admin.php?page=canada-html5-map-options&map_id='+$(this).val();
        });

        $('input[name*=isResponsive]').change(function() {

            var resp = $('input[name*=isResponsive]:eq(0)').attr('checked')=='checked' ? false : true;
            $('input[name*=maxWidth]').attr('disabled',!resp);
            $('input[name*=mapWidth],input[name*=mapHeight]').attr('disabled',resp);

        });
        $('input[name*=isResponsive]').trigger('change');

        $('input[name*=zoomEnable]').change(function() {

            var resp = $('input[name*=zoomEnable]:eq(0)').attr('checked')=='checked' ? false : true;
            $('input[name*=zoomEnableControls],input[name*=zoomIgnoreMouseScroll],input[name*=zoomMax],input[name*=zoomStep]').attr('disabled',resp);

        });
        $('input[name*=shadowAllow]').change(function() {
            var resp = $('input[name*=shadowAllow]:eq(0)').attr('checked')=='checked' ? false : true;
            $('input[name*=shadowWidth]').attr('disabled',resp);
        });
        $('input[name*=zoomEnable],input[name*=shadowAllow]').trigger('change');

    });
</script>
<br />
<form method="POST" class="canada-html5-map main">
    <span class="title"><?php echo __('Select a map:', 'canada-html5-map'); ?> </span>
    <select name="map_id" style="width: 185px;">
        <?php foreach($options as $id => $map_data) { ?>
            <option value="<?php echo $id; ?>" <?php echo ($id==$map_id)?'selected':'';?>><?php echo $map_data['name']; ?></option>
        <?php } ?>
    </select>
    <span class="tipsy-q" original-title="<?php esc_attr_e('The map', 'canada-html5-map'); ?>">[?]</span>
    <a href="admin.php?page=canada-html5-map-maps" class="page-title-action"><?php
    _e('Maps list', 'canada-html5-map') ?></a>
    <br /><br />

    <?php canada_html5map_plugin_nav_tabs('options', $map_id); ?>

    <p><?php echo __('Specify general settings of the map. To choose a color, click a color box, select the desired color in the color selection dialog and click anywhere outside the dialog to apply the chosen color.', 'canada-html5-map'); ?></p>
    <fieldset>
        <legend><?php echo __('Map Settings', 'canada-html5-map'); ?></legend>

        <span class="title"><?php echo __('Map name:', 'canada-html5-map'); ?> </span><input type="text" name="options[name]" value="<?php echo $options[$map_id]['name']; ?>" />
        <span class="tipsy-q" original-title="<?php esc_attr_e('Name of the map', 'canada-html5-map'); ?>">[?]</span>
        <div class="clear"></div>

        <span class="title"><?php echo __('Layout type:', 'canada-html5-map'); ?> </span>
        <label><?php echo __('Not Responsive:', 'canada-html5-map'); ?> <input type="radio" name="options[isResponsive]" value=0 <?php echo !$options[$map_id]['isResponsive']?'checked':''?> /></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <label><?php echo __('Responsive:', 'canada-html5-map'); ?> <input type="radio" name="options[isResponsive]" value=1 <?php echo $options[$map_id]['isResponsive']?'checked':''?> /></label>
        <span class="tipsy-q" original-title="<?php esc_attr_e('Type of the layout', 'canada-html5-map'); ?>">[?]</span>
        <div class="clear" style="margin-bottom: 10px"></div>

        <span class="title"><?php echo __('Map width:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[mapWidth]" value="<?php echo $options[$map_id]['mapWidth']; ?>" />
        <span class="tipsy-q" original-title="<?php esc_attr_e('The width of the map', 'canada-html5-map'); ?>">[?]</span>
        <div class="clear"></div>

        <span class="title"><?php echo __('Map height:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[mapHeight]" value="<?php echo $options[$map_id]['mapHeight']; ?>" />
        <span class="tipsy-q" original-title="<?php esc_attr_e('The height of the map', 'canada-html5-map'); ?>">[?]</span>
        <div class="clear"></div>

        <span class="title"><?php echo __('Max width:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[maxWidth]" value="<?php echo $options[$map_id]['maxWidth']; ?>" disabled />
        <span class="tipsy-q" original-title="<?php esc_attr_e('The max width of the map', 'canada-html5-map'); ?>">[?]</span>
        <div class="clear"></div>

<!-- @ifdef wp_allow_zoom -->
<hr/>
        <h4 class="title"><?php echo __('Zooming capabilities:', 'canada-html5-map'); ?> </h4><br/>
        <div style="float: left; width: 50%;">
        <label><span class="title"><?php echo __('Allow zoom:', 'canada-html5-map') ?></span> <input type="checkbox" name="options[zoomEnable]" value="right" <?php echo (isset($options[$map_id]['zoomEnable'])&&$options[$map_id]['zoomEnable']) ?'checked':''?> /></label>
        <span class="tipsy-q" original-title="<?php esc_attr_e('Allow map zooming', 'canada-html5-map'); ?>">[?]</span><br />
        <label><span class="title"><?php echo __('Show zoom controls:', 'canada-html5-map') ?></span> <input type="checkbox" name="options[zoomEnableControls]" value="bottom" <?php echo (isset($options[$map_id]['zoomEnableControls'])&&$options[$map_id]['zoomEnableControls']) ?'checked':''?> /></label>
        <span class="tipsy-q" original-title="<?php esc_attr_e('Whether to show or not +/- buttons', 'canada-html5-map'); ?>">[?]</span><br />
        <label><span class="title"><?php echo __('Ignore mouse scroll:', 'canada-html5-map') ?></span> <input type="checkbox" name="options[zoomIgnoreMouseScroll]" value="bottom" <?php echo (isset($options[$map_id]['zoomIgnoreMouseScroll'])&&$options[$map_id]['zoomIgnoreMouseScroll']) ?'checked':''?> /></label>
        <span class="tipsy-q" original-title="<?php esc_attr_e('Do not zoom in/out by mouse scrolling', 'canada-html5-map'); ?>">[?]</span><br />
        </div>
        <div style="float: left; width: 50%;">
        <span class="title"><?php echo __('Max zoom:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[zoomMax]" value="<?php echo (isset($options[$map_id]['zoomMax'])&&intval($options[$map_id]['zoomMax']))? intval($options[$map_id]['zoomMax']) : 2; ?>" />
        <span class="tipsy-q" original-title="<?php esc_attr_e('Maximum zooming level', 'canada-html5-map'); ?>">[?]</span><br />
        <span class="title"><?php echo __('Zoom step:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[zoomStep]" value="<?php echo (isset($options[$map_id]['zoomStep']))? $options[$map_id]['zoomStep'] : 0.2; ?>" />
        <span class="tipsy-q" original-title="<?php esc_attr_e('Zoom step', 'canada-html5-map'); ?>">[?]</span>
        </div>
        <div class="clear"></div>
<!-- @endif -->
<hr>
        <label><span class="title"><?php echo __('Hide shortnames:', 'canada-html5-map'); ?> </span>
        <input type="checkbox" name="options[hideSN]" <?php echo isset($options[$map_id]['hideSN'])?'checked="checked"':''?> /></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <span class="tipsy-q" original-title="<?php esc_attr_e('Do not show shortnames on the map', 'canada-html5-map'); ?>">[?]</span>
        <div class="clear" style="margin-bottom: 10px"></div>

        <div style="float: left; width: 50%;">
        <label><span class="title"><?php echo __('Enable shadows:', 'canada-html5-map'); ?> </span>
        <input type="checkbox" name="options[shadowAllow]" <?php echo (isset($options[$map_id]['shadowAllow']) AND $options[$map_id]['shadowAllow'])?'checked="checked"':''?> /></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <span class="tipsy-q" original-title="<?php esc_attr_e('Enable / disable shadows', 'canada-html5-map'); ?>">[?]</span>
        </div>
        
        <div style="float: left; width: 50%;">
        <span class="title"><?php echo __('Shadow width:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[shadowWidth]" value="<?php echo (isset($options[$map_id]['shadowWidth'])&&(float)($options[$map_id]['shadowWidth']))? (float)($options[$map_id]['shadowWidth']) : 1.5; ?>" />
        <span class="tipsy-q" original-title="<?php esc_attr_e('Shadow width', 'canada-html5-map'); ?>">[?]</span><br />
        </div>
        <div class="clear" style="margin-bottom: 10px"></div>
        
        <span class="title"><?php echo __('Borders Color:', 'canada-html5-map'); ?> </span><input class="color" type="text" name="options[borderColor]" value="<?php echo $options[$map_id]['borderColor']; ?>" style="background-color: #<?php echo $options[$map_id]['borderColor']; ?>" />
        <span class="tipsy-q" original-title="<?php esc_attr_e('The color of borders on the map', 'canada-html5-map'); ?>">[?]</span><div class="colorpicker"></div>
        <div class="clear"></div>
    </fieldset>


    <fieldset>
        <legend><?php echo __('Content Info', 'canada-html5-map'); ?></legend>    
        <span class="title"><?php echo __('Additional Info Area:', 'canada-html5-map'); ?> </span>
        <label><?php echo __('At right:', 'canada-html5-map') ?> <input type="radio" name="options[statesInfoArea]" value="right" <?php echo $options[$map_id]['statesInfoArea'] == 'right'?'checked':''?> /></label>&nbsp;&nbsp;&nbsp;&nbsp;
        <label><?php echo __('At bottom:', 'canada-html5-map') ?> <input type="radio" name="options[statesInfoArea]" value="bottom" <?php echo $options[$map_id]['statesInfoArea'] == 'bottom'?'checked':''?> /></label>
        <span class="tipsy-q" original-title="<?php esc_attr_e('Where to place an additional information about state', 'canada-html5-map'); ?>">[?]</span><br />
        <br/>
        <div id="action-info">
            <span class="title"><?php echo __('Default content:', 'canada-html5-map'); ?> <span class="tipsy-q" original-title="<?php esc_attr_e('Default content that will be shown in area for additional information', 'canada-html5-map'); ?>">[?]</span> </span>
            <br/><br/>
            <?php wp_editor(isset($options[$map_id]['defaultAddInfo']) ? $options[$map_id]['defaultAddInfo'] : '', 'defaultAddInfo', $mce_options); ?>
        </div>
    </fieldset>

    <fieldset class="font-sizes">
        <legend><?php echo __('Font sizes and colors', 'canada-html5-map'); ?></legend>  

        <div class="left-block">
            <h4 class="settings-chapter">
                <?php echo __('Name displayed on the map', 'canada-html5-map'); ?>
            </h4>

            <span class="title"><?php echo __('Font Size:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[nameFontSize]" value="<?php echo $options[$map_id]['nameFontSize']; ?>" />
            <span class="tipsy-q" original-title="<?php esc_attr_e('Font size of names on the map', 'canada-html5-map'); ?>">[?]</span><br />

            <span class="title"><?php echo __('Color:', 'canada-html5-map'); ?> </span><input id='color' class="color" type="text" name="options[nameColor]" value="<?php echo $options[$map_id]['nameColor']; ?>" style="background-color: #<?php echo $options[$map_id]['nameColor']; ?>" />
            <span class="tipsy-q" original-title="<?php esc_attr_e('The color of names on the map', 'canada-html5-map'); ?>">[?]</span><div class="colorpicker"></div><br />

            <span class="title"><?php echo __('Name Stroke:', 'canada-html5-map'); ?> </span><input type="checkbox" name="options[nameStroke]" value=1 <?php echo $options[$map_id]['nameStroke']?'checked':''?> autocomplete="off" />
            <span class="tipsy-q" original-title="<?php esc_attr_e('The stroke on regions names', 'canada-html5-map'); ?>">[?]</span><br />
            <div class="clear" style="margin-bottom: 10px"></div>

            <span class="title"><?php echo __('Color of stroke:', 'canada-html5-map'); ?> </span><input id='color' class="color" type="text" name="options[nameStrokeColor]" value="<?php echo $options[$map_id]['nameStrokeColor']; ?>" style="background-color: #<?php echo $options[$map_id]['nameStrokeColor']; ?>" />
            <span class="tipsy-q" original-title="<?php esc_attr_e('The color of names on the map', 'canada-html5-map'); ?>">[?]</span><div class="colorpicker"></div><br />

        </div>

        <div class="left-block">
            <h4 class="settings-chapter">
                <?php echo __('Tooltip name', 'canada-html5-map'); ?>
            </h4>

            <span class="title"><?php echo __('Font Size:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[popupNameFontSize]" value="<?php echo $options[$map_id]['popupNameFontSize']; ?>" />
            <span class="tipsy-q" original-title="<?php esc_attr_e('Font size of names on the popup', 'canada-html5-map'); ?>">[?]</span><br />

            <span class="title"><?php echo __('Color:', 'canada-html5-map'); ?> </span><input id='pncolor' class="color" type="text" name="options[popupNameColor]" value="<?php echo $options[$map_id]['popupNameColor']; ?>" style="background-color: #<?php echo $options[$map_id]['popupNameColor']; ?>" />
            <span class="tipsy-q" original-title="<?php esc_attr_e('The color of names on the popup', 'canada-html5-map'); ?>">[?]</span><div class="colorpicker"></div><br />

            <h4 class="settings-chapter">
                <?php echo __('Tooltip comment', 'canada-html5-map'); ?>
            </h4>

            <span class="title"><?php echo __('Font Size:', 'canada-html5-map'); ?> </span><input class="span2" type="text" name="options[popupCommentFontSize]" value="<?php echo $options[$map_id]['popupCommentFontSize']; ?>" />
            <span class="tipsy-q" original-title="<?php esc_attr_e('Font size of content in the popup', 'canada-html5-map'); ?>">[?]</span><br />

            <span class="title"><?php echo __('Color:', 'canada-html5-map'); ?> </span><input id='pccolor' class="color" type="text" name="options[popupCommentColor]" value="<?php echo $options[$map_id]['popupCommentColor'] ? $options[$map_id]['popupCommentColor'] : 'default'; ?>" style="background-color: #<?php echo $options[$map_id]['popupCommentColor']; ?>" />
            <span class="tipsy-q" original-title="<?php esc_attr_e('The color of content in the popup', 'canada-html5-map'); ?>">[?]</span><div class="colorpicker"></div><br />
        </div>

    </fieldset>

    <input type="hidden" name="act_type" value="canada-html5-map-main-save" />
    <p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes', 'canada-html5-map'); ?>" class="button-primary" id="submit" name="submit"></p> 

</form>
</div>
