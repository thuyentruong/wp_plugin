<?php

$options = get_site_option('canadahtml5map_options');
$option_keys = is_array($options) ? array_keys($options) : array();
$map_id  = (isset($_REQUEST['map_id'])) ? intval($_REQUEST['map_id']) : array_shift($option_keys) ;

$states  = $options[$map_id]['map_data'];
$states  = json_decode($states, true);

$maxRadius = 15;

$dir     = plugins_url('/static/', __FILE__);
$doptions= canada_html5map_plugin_map_defaults();

$pointTypes = array(
    ""  => "Circle",
    
);

foreach (array( 'pointColor', 'pointColorOver',
                'pointBorderColor', 'pointBorderColorOver',
                'pointNameColor', 'pointNameColorOver',
                'pointNameStrokeColor', 'pointNameStrokeColorOver',
                'pointNameFontSize'
                ) as $field) {
    if (!isset($options[$map_id][$field]))
        $options[$map_id][$field] = $doptions[$field];
}


if(isset($_POST['act_type']) && $_POST['act_type'] == 'canada-html5-map-points-save') {
    $points = (isset($_POST['map_points']) and $_POST['map_points']) ?  stripcslashes($_POST['map_points']) : '{}';
    if (($dcd = json_decode($points, true)) !== null AND is_array($dcd)) {
        foreach ($dcd as $pid => &$pointData) {
            if (!empty($pointData['info']))
                $options[$map_id]['state_info'][$pid] = wp_kses_post($pointData['info']);
            else
                unset($options[$map_id]['info'][$pid]);
            unset($pointData['info']);
        }
        unset($pointData);
        $options[$map_id]['points'] = $dcd;
    }
    $options[$map_id]['pointColor'] = $_POST['dPointColor'];
    $options[$map_id]['pointColorOver'] = $_POST['dPointColorOver'];
    $options[$map_id]['pointBorderColor'] = $_POST['dPointBorderColor'];
    $options[$map_id]['pointBorderColorOver'] = $_POST['dPointBorderColorOver'];
    $options[$map_id]['pointNameColor'] = $_POST['dPointNameColor'];
    $options[$map_id]['pointNameColorOver'] = $_POST['dPointNameColorOver'];
    $options[$map_id]['pointNameStrokeColor'] = $_POST['dPointNameStrokeColor'];
    $options[$map_id]['pointNameStrokeColorOver'] = $_POST['dPointNameStrokeColorOver'];
    update_site_option('canadahtml5map_options', $options);
}

$mce_options = array(
    //'media_buttons' => false,
    'editor_height'   => 150,
    'textarea_rows'   => 20,
    'textarea_name'   => 'pointAddInfo',
    'tinymce' => array(
        'add_unload_trigger' => false,
    )
);

echo "<div class=\"wrap\"><h2>" . __('Configuration of Map points', 'canada-html5-map') . "</h2>";
?>
<style>
.tipsy-w {
    z-index: 100500;
}
</style>
<script>
    var imageFieldId = false;
    jQuery(function($){

    $('select[name=map_id],select[name=state_select]').change(function() {

        if ($(this).attr('name')=='map_id') {
        s_id = 0;
        } else {
        s_id = $('select[name=state_select] option:selected').val();
        }

            location.href='admin.php?page=canada-html5-map-points&map_id='+$('select[name=map_id] option:selected').val()+'&s_id='+s_id;
        });

        $('.tipsy-q').tipsy({gravity: 'w'}).css('cursor', 'default');

        $('.color~.colorpicker').each(function(){
            var me = this;

            $(this).farbtastic(function(color){
                var textColor = this.hsl[2] > 0.5 ? '#000' : '#fff';

                $(me).prev().prev().css({
                    background: color,
                    color: textColor
                }).val(color);

                if($(me).next().find('input').attr('checked') == 'checked') {
                    return;
                    var dirClass = $(me).prev().prev().hasClass('colorSimple') ? 'colorSimple' : 'colorOver';

                    $('.'+dirClass).css({
                        background: color,
                        color: textColor
                    }).val(color);
                }
            });

            $.farbtastic(this).setColor($(this).prev().prev().val());

            $($(this).prev().prev()[0]).bind('change', function(){
                $.farbtastic(me).setColor(this.value);
            });

            $(this).hide();
            $(this).prev().prev().bind('focus', function(){
                $(this).next().next().fadeIn();
            });
            $(this).prev().prev().bind('blur', function(){
                $(this).next().next().fadeOut();
            });
        });


        window.send_to_editorArea = window.send_to_editor;

        window.send_to_editor = function(html) {
            if(imageFieldId === false) {
                window.send_to_editorArea(html);
            }
            else {
                var imgurl = $('img',html).attr('src');

                $('#'+imageFieldId).val(imgurl);
                imageFieldId = false;

                tb_remove();
            }

        }

        if (typeof tinyMCE !== 'undefined') tinyMCE.execCommand('mceAddControl', true, 'pointAddInfo')

        $('input[type=submit]').attr('disabled',false);

    });

    function adjustSubmit() {
        jQuery('#map_points').val(map.mapConfig.points ? JSON.stringify(map.mapConfig.points) : '');
    }

</script>
<br />
<form method="POST" class="canada-html5-map main" onsubmit="adjustSubmit()">
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

    <?php canada_html5map_plugin_nav_tabs('points', $map_id); ?>

    <p><?php _e("Double-click to add a point; click and hold to drag; double-click a point to edit it", "canada-html5-map"); ?></p>

    <fieldset>
        <legend><?php echo __('Points Configuration', 'canada-html5-map'); ?></legend>

        <div id="point_info"></div>
        <div>
        <div style="border-top: 1px solid #ddd">
                <label style="position: relative; top: -13px; background: white"><a href="javascript:void(0);" onclick="show_default_options(this.innerHTML.indexOf('+')!==-1)"><?php echo sprintf(__("Points defaults [%s]", "canada-html5-map"), "<span>+</span>") ?></a></label>
                <div id="pointDefOptionsWrapp" style="display: none">
                <table style="width: 100%">
                    <thead>
                    <tr style="font-weight: bold">
                        <td><?php echo __('Option', 'canada-html5-map'); ?></td>
                        <td><?php echo __('Common color', 'canada-html5-map'); ?></td>
                        <td><?php echo __('Hover color', 'canada-html5-map'); ?></td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td><?php echo __('Point color:', 'canada-html5-map'); ?></td>
                        <td><input class="color colorSimple" type="text" name="dPointColor" id="dPointColor" value="<?php echo $options[$map_id]['pointColor'] ?>" style="background-color: <?php echo $options[$map_id]['pointColor'] ?>"  />
                        <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker" style="margin-left: 100px"></div>
                        </td>
                        <td><input class="color colorOver" type="text" name="dPointColorOver" id="dPointColorOver" value="<?php echo $options[$map_id]['pointColorOver'] ?>" style="background-color: <?php echo $options[$map_id]['pointColorOver'] ?>"  />
                        <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point when the mouse cursor is over it.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker" style="margin-left: 100px"></div>
                        </td>
                    </tr>
                    <tr>
                        <td><?php echo __('Border color:', 'canada-html5-map'); ?></td>
                        <td><input class="color colorSimple" type="text" name="dPointBorderColor" id="dPointBorderColor" value="<?php echo $options[$map_id]['pointBorderColor'] ?>" style="background-color: <?php echo $options[$map_id]['pointBorderColor'] ?>"  />
                        <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point border.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker" style="margin-left: 100px"></div></td>
                        <td><input class="color colorOver" type="text" name="dPointBorderColorOver" id="dPointBorderColorOver" value="<?php echo $options[$map_id]['pointBorderColorOver'] ?>" style="background-color: <?php echo $options[$map_id]['pointBorderColorOver'] ?>"  />
                        <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point border when the mouse cursor is over it.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker" style="margin-left: 100px"></div></td>
                    </tr>
                    <tr>
                        <td><?php echo __('Shortname color:', 'canada-html5-map'); ?></td>
                        <td><input class="color colorSimple" type="text" name="dPointNameColor" id="dPointNameColor" value="<?php echo $options[$map_id]['pointNameColor'] ?>" style="background-color: <?php echo $options[$map_id]['pointNameColor'] ?>"  />
                        <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point\'s shortname.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker" style="margin-left: 100px"></div></td>
                        <td><input class="color colorOver" type="text" name="dPointNameColorOver" id="dPointNameColorOver" value="<?php echo $options[$map_id]['pointNameColorOver'] ?>" style="background-color: <?php echo $options[$map_id]['pointNameColorOver'] ?>"  />
                        <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point\'s shortname when the mouse cursor is over it.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker" style="margin-left: 100px"></div></td>
                    </tr>
                    <tr>
                        <td><?php echo __('Shortname stroke color:', 'canada-html5-map'); ?></td>
                        <td><input class="color colorSimple" type="text" name="dPointNameStrokeColor" id="dPointNameStrokeColor" value="<?php echo $options[$map_id]['pointNameStrokeColor'] ?>" style="background-color: <?php echo $options[$map_id]['pointNameStrokeColor'] ?>"  />
                        <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point\'s shortname stroke.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker" style="margin-left: 100px"></div></td>
                        <td><input class="color colorOver" type="text" name="dPointNameStrokeColorOver" id="dPointNameStrokeColorOver" value="<?php echo $options[$map_id]['pointNameStrokeColorOver'] ?>" style="background-color: <?php echo $options[$map_id]['pointNameStrokeColorOver'] ?>"  />
                        <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point\'s shortname stroke when the mouse cursor is over it.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker" style="margin-left: 100px"></div></td>
                    </tr>
                    </tbody>
                </table>
        </div>
            <div style="clear:both"></div>
            <hr>
            <label>Enable zoom: <input type="checkbox" onchange="map.enableZoom(jQuery(this).prop('checked'), true)"></label>
        </div>
        <hr>
        <div id="map_container"></div>
    </fieldset>
    <div style="display: none" id="dialogs">
        <div id="point_cfg">
        <?php
        $w = 32;
        $cp = count($pointTypes) > 1;
        if ($cp) $w = 25;
        if ($cp) {
        ?>
            <div style="float: left; width: <?php echo $w ?>%;">
                <label><span class="title" style="width: 80px"><?php echo __('Point type:', 'canada-html5-map') ?> </span><select name="pointType" id="pointType" >
                <?php foreach ($pointTypes as $pt => $pn) { ?>
                <option value="<?php echo $pt ?>"><?php _e($pn, 'canada-html5-map') ?></option>
                <?php } ?>
                </select></label>
                <span class="tipsy-q" original-title="<?php esc_attr_e('Point type', 'canada-html5-map'); ?>">[?]</span><br />
            </div>
        <?php } ?>
            <div style="float: left; width: <?php echo $w ?>%;">
                <label><span class="title" style="width: 80px"><?php echo __('X position:', 'canada-html5-map') ?> </span><input type="text" name="pointX" id="pointX" value="0" style="width: 50px"/></label>
                <span class="tipsy-q" original-title="<?php esc_attr_e('X position of the point', 'canada-html5-map'); ?>">[?]</span><br />
            </div>
            <div style="float: left; width: <?php echo $w ?>%;">
                <label><span class="title" style="width: 80px"><?php echo __('Y position:', 'canada-html5-map') ?> </span><input type="text" name="pointY" id="pointY" value="0" style="width: 50px"/></label>
                <span class="tipsy-q" original-title="<?php esc_attr_e('Y position of the point', 'canada-html5-map'); ?>">[?]</span><br />
            </div>
            <div style="float: left; width: <?php echo $w ?>%;">
                <label><span class="title" style="width: 80px"><?php echo __('Radius:', 'canada-html5-map') ?> </span><input type="number" name="pointRadius" id="pointRadius" value="4" style="width: 50px" min="1" max="<?php echo $maxRadius ?>"/></label>
                <span class="tipsy-q" original-title="<?php esc_attr_e('Radius of the point', 'canada-html5-map'); ?>">[?]</span><br />
            </div>
            <hr style="clear: both"/>
            <div style="float:left; min-width: 500px">
            <label><span class="title"><?php echo __('Name:', 'canada-html5-map') ?> </span><input type="text" name="pointName" id="pointName" value=""/></label>
            <span class="tipsy-q" original-title="<?php esc_attr_e('This name will be show when mouse will be over this point', 'canada-html5-map'); ?>">[?]</span><br />
            <label><span class="title"><?php echo __('Short name:', 'canada-html5-map') ?> </span><input type="text" name="pointShortname" id="pointShortname" value="" /></label>
            <span class="tipsy-q" original-title="<?php esc_attr_e('This name will be show near point on the map', 'canada-html5-map'); ?>">[?]</span><br />
            </div>
            <div style="float:left; min-width: 500px">
            <label><span class="title"><?php echo __('Text position:', 'canada-html5-map') ?></span><select name="pointTextPos" id="pointTextPos" style=" width: 190px">
                <option value="left-top"><?php echo __('Left Top', 'canada-html5-map') ?></option>
                <option value="left-middle"><?php echo __('Left Middle', 'canada-html5-map') ?></option>
                <option value="left-bottom"><?php echo __('Left Bottom', 'canada-html5-map') ?></option>
                <option value="middle-top"><?php echo __('Center Top', 'canada-html5-map') ?></option>
                <option value="middle-middle"><?php echo __('Center Middle', 'canada-html5-map') ?></option>
                <option value="middle-bottom"><?php echo __('Center Bottom', 'canada-html5-map') ?></option>
                <option value="right-top"><?php echo __('Right Top', 'canada-html5-map') ?></option>
                <option value="right-middle"><?php echo __('Right Middle', 'canada-html5-map') ?></option>
                <option value="right-bottom"><?php echo __('Right Bottom', 'canada-html5-map') ?></option>
            </select></label>
            <span class="tipsy-q" original-title="<?php esc_attr_e('Shortname position relative to the point', 'canada-html5-map'); ?>">[?]</span>
            <br/>
            <label><span class="title"><?php echo __('Font size:', 'canada-html5-map'); ?></span><input type="number" name="pointFS" id="pointFS" min="3" max="20" style="width: 190px"/> px
            <span class="tipsy-q" original-title='<?php echo __('Font size of the shortname displayed near the point.', 'canada-html5-map'); ?>'>[?]</span>&nbsp;&nbsp;&nbsp;</label>
            <label for="pointFSDef"><input name="pointFSDef" id="pointFSDef" type="checkbox" /> <?php echo __('Use default', 'canada-html5-map'); ?></label>
            <br />
            </div>
            <div style="clear: both"></div>

            <div style="border-top: 1px solid #ddd">
                <label style="position: relative; top: -13px; background: white"><a href="javascript:void(0);" onclick="show_comment(this.innerHTML.indexOf('+')!==-1)"><?php echo sprintf(__("Comment [%s]", "canada-html5-map"), "<span>+</span>") ?></a></label>
                <div id="pointCommentWrapp">
                <textarea style="width:100%; height: 150px;" class="" rows="10" cols="45" name="pointComment" id="pointComment"></textarea>
                </div>
            </div>
            <hr/>

            <div style="float:left; min-width: 500px">
            <span class="title"><?php echo __('Point color:', 'canada-html5-map'); ?> </span><input class="color colorSimple" type="text" name="pointColor" id="pointColor" value="" style="background-color: white"  />
            <span class="tipsy-q" original-title='<?php esc_attr_e('The color of a point.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker"></div>
            <label for="colorDef"><input name="colorDef" id="colorDef" class="colorOverCh" type="checkbox" /> <?php echo __('Use default', 'canada-html5-map'); ?></label>
            <br />
            </div>
            <div style="float:left; min-width: 500px">
            <span class="title"><?php echo __('Point hover color:', 'canada-html5-map'); ?> </span><input class="color colorOver" type="text" name="pointColorOver" id="pointColorOver" style="background-color: white"  />
            <span class="tipsy-q" original-title='<?php echo __('The color of a point when the mouse cursor is over it.', 'canada-html5-map'); ?>'>[?]</span><div class="colorpicker"></div>
            <label for="colorOverDef"><input name="colorOverDef" id="colorOverDef" class="colorOverCh" type="checkbox" /> <?php echo __('Use default', 'canada-html5-map'); ?></label>
            <br />
            </div>
            <hr style="clear: both"/>
            <span class="title"><?php echo __('On click action:', 'canada-html5-map'); ?> </span>
            <label><input type="radio" name="clickaction" id="ca-nothing" value="nothing" checked="checked"/> <?php echo __('nothing', 'canada-html5-map') ?></label>&nbsp;&nbsp;&nbsp;
            <label><input type="radio" name="clickaction" id="ca-url"     value="url"  /> <?php echo __('open link', 'canada-html5-map') ?></label>&nbsp;&nbsp;&nbsp;
            <label><input type="radio" name="clickaction" id="ca-info"    value="info" /> <?php echo __('show additional information', 'canada-html5-map') ?></label>&nbsp;&nbsp;&nbsp;
            <br>
            <div id="action-url" style="display: none">
            <hr style="clear: both"/>
                <span class="title"><?php echo __('URL:', 'canada-html5-map'); ?> </span><input style="width: 270px;" class="" type="text" name="pointURL" id="pointURL" value="" />
                <span class="tipsy-q" original-title="<?php esc_attr_e('Open url on click (if specified)', 'canada-html5-map'); ?>">[?]</span></br>
                <span class="title"> </span>
                <label for="pointURLNW"><input name="pointURLNW" id="pointURLNW" class="" type="checkbox" /> <?php echo __('Open url in a new window', 'canada-html5-map'); ?></label>
            </div>
            <div id="action-info" style="display: none">
            <hr style="clear: both"/>
            <span class="title"><?php echo __('Description:', 'canada-html5-map'); ?> <span class="tipsy-q" original-title="<?php esc_attr_e('The description is displayed to the right of the map and contains contacts or some other additional information', 'canada-html5-map'); ?>">[?]</span> </span>
            <?php wp_editor('', 'pointAddInfo', $mce_options); ?>
            </div>
        </div>
    </div>
    <link rel='stylesheet' href='<?php echo $dir ?>css/map.css'>
    <script type='text/javascript' src='<?php echo $dir ?>js/raphael.min.js'></script>
    <script type='text/javascript' src='<?php echo $dir ?>js/map.js'></script>
    <style>
        #map_container .fm-tooltip {
            color: <?php echo $doptions['popupNameColor']; ?>;
            font-size: <?php echo $options[$map_id]['popupNameFontSize'].'px'; ?>
        }
    </style>
<?php
if (isset($options[$map_id]['points'])) foreach ($options[$map_id]['points'] as $pid => &$pointData) {
    if (isset($options[$map_id]['state_info'][$pid]))
        $pointData['info'] = $options[$map_id]['state_info'][$pid];
}
unset($pointData);
if (isset($options[$map_id]['hideSN']) AND $options[$map_id]['hideSN']) {
    $data = json_decode($doptions['map_data'], true);
    $protected_shortnames = array('st6', 'st10');
    foreach ($data as $sid => &$d) {
        if (!in_array($sid, $protected_shortnames)) {
            $d['shortname'] = '';
        }
    }
    $doptions['map_data'] = json_encode($data);
}

?>
    <script>
        var map_cfg = {

        mapWidth        : 0,
        mapHeight       : 0,

        shadowAllow     : false,

        iPhoneLink      : <?php echo $doptions['iPhoneLink']; ?>,

        isNewWindow     : <?php echo $doptions['isNewWindow']; ?>,

        borderColor     : "<?php echo $doptions['borderColor']; ?>",
        borderColorOver     : "<?php echo $doptions['borderColorOver']; ?>",

        nameColor       : "<?php echo $doptions['nameColor']; ?>",
        popupNameColor      : "<?php echo $doptions['popupNameColor']; ?>",
        nameFontSize        : "<?php echo $options[$map_id]['nameFontSize'].'px'; ?>",
        popupNameFontSize   : "<?php echo $options[$map_id]['popupNameFontSize'].'px'; ?>",
        nameFontWeight      : "<?php echo $options[$map_id]['nameFontWeight']; ?>",

        pointColor            : "<?php echo $options[$map_id]['pointColor']?>",
        pointColorOver        : "<?php echo $options[$map_id]['pointColorOver']?>",
        pointBorderColor        : "<?php echo $options[$map_id]['pointBorderColor']?>",
        pointBorderColorOver    : "<?php echo $options[$map_id]['pointBorderColorOver']?>",
        pointNameColor        : "<?php echo $options[$map_id]['pointNameColor']?>",
        pointNameColorOver    : "<?php echo $options[$map_id]['pointNameColorOver']?>",
        pointNameStrokeColor        : "<?php echo $options[$map_id]['pointNameStrokeColor']?>",
        pointNameStrokeColorOver    : "<?php echo $options[$map_id]['pointNameStrokeColorOver']?>",
        pointNameFontSize    : "<?php echo $options[$map_id]['pointNameFontSize']?>",

        overDelay       : <?php echo $doptions['overDelay']; ?>,
        nameStroke      : <?php echo $doptions['nameStroke']?'true':'false'; ?>,
        nameStrokeColor : "<?php echo $doptions['nameStrokeColor']; ?>",
        map_data        : <?php echo $doptions['map_data']; ?>,
        ignoreLinks     : true,
        points          : <?php echo (isset($options[$map_id]['points']) AND $options[$map_id]['points']) ? json_encode($options[$map_id]['points']) : '{}' ?>
        };
        var map = new FlaShopCanadaMap(map_cfg);
        var activePoint = null;
        jQuery(function($){
            var btnAdd = {
                'text': '<?php _e("Add", "canada-html5-map"); ?>',
                'icons': {
                    'primary': 'ui-icon-plus'
                },
                'click' : function() {
                    var x = parseInt(pX.val());
                    var y = parseInt(pY.val());
                    if (isNaN(x)) x = 0;
                    if (isNaN(y)) y = 0;
                    var p = map.addPoint(x, y, pN.val(), null, pT.val());
                    var link, isnw, info = null;
                    var act = $('input[name="clickaction"]:checked').val();
                    if (act == 'url') {
                        link = pU.val();
                        isnw = pUNW.attr('checked') ? true : false;
                    } else if (act == 'info') {
                        link = 'javascript:canadahtml5map_set_state_text<?php echo "_$map_id" ?>("'+p+'");';
                        info = editorGet();
                    } else {
                        link = null;
                    }
                    var attrs = {
                        shortname: pSN.val(),
                        comment: pCmt.val(),
                        textPos: pTP.val(),
                        radius: pR.val() < 1 ? 1 : (pR.val() > <?php echo $maxRadius ?> ? <?php echo $maxRadius ?> : pR.val()),
                        color: uDC.attr('checked') ? null : pC.val(),
                        colorOver: uDCO.attr('checked') ? null : pCO.val(),
                        link: link,
                        info: info,
                        isNewWindow: isnw
                    };
                    if (uDFS.attr('checked')) {
                        attrs.nameFontSize = null;
                    } else {
                        var fs = parseInt(pFS.val());
                        fs = fs < 3 ? 3 : (fs > 20 ? 20 : fs);
                        attrs.nameFontSize = fs+'px';
                    }
                    map.setPointAttr(p, attrs);
                    $(this).dialog('close');
                }
            };
            var btnSave = {
                'text': '<?php _e("Apply", "canada-html5-map"); ?>',
                'icons': {
                    'primary': 'ui-icon-save'
                },
                'click' : function() {
                    var x = parseInt(pX.val());
                    var y = parseInt(pY.val());
                    if (isNaN(x)) x = 0;
                    if (isNaN(y)) y = 0;
                    var link, isnw, info = null;
                    var act = $('input[name="clickaction"]:checked').val();
                    if (act == 'url') {
                        link = pU.val();
                        isnw = pUNW.attr('checked') ? true : false;
                    } else if (act == 'info') {
                        link = 'javascript:canadahtml5map_set_state_text<?php echo "_$map_id" ?>("'+activePoint+'");';
                        info = editorGet();
                    } else {
                        link = null;
                    }
                    var attrs = {
                        x: x,
                        y: y,
                        radius: pR.val() < 1 ? 1 : (pR.val() > <?php echo $maxRadius ?> ? <?php echo $maxRadius ?> : pR.val()),
                        name: pN.val(),
                        shortname: pSN.val(),
                        comment: pCmt.val(),
                        textPos: pTP.val(),
                        color: uDC.attr('checked') ? null : pC.val(),
                        colorOver: uDCO.attr('checked') ? null : pCO.val(),
                        link: link,
                        info: info,
                        isNewWindow: isnw
                    };
                    if (uDFS.attr('checked')) {
                        attrs.nameFontSize = null;
                    } else {
                        var fs = parseInt(pFS.val());
                        fs = fs < 3 ? 3 : (fs > 20 ? 20 : fs);
                        attrs.nameFontSize = fs+'px';
                    }
                    map.setPointAttr(activePoint, attrs);
                    $(this).dialog('close');
                    activePoint = null;
                }
            };
            var btnDelete = {
                'text': '<?php _e("Delete", "canada-html5-map"); ?>',
                'icons': {
                    'primary': 'ui-icon-delete'
                },
                'click' : function() {
                    var name = map.fetchPointAttr(activePoint, 'name');
                    if (confirm('<?php _e("Are you sure you want to delete point", "canada-html5-map") ?> '+name)) {
                        map.deletePoint(activePoint);
                        activePoint = null;
                        $(this).dialog('close');
                    }
                }
            };
            var btnClose = {
                'text': '<?php _e("Cancel", "canada-html5-map"); ?>',
                'icons': {
                    'primary': 'ui-icon-close'
                },
                'click' : function() {
                    $(this).dialog('close');
                }
            };
            map.draw('map_container');
            map.on('dblclick', function(ev, sid, map){
                dlg.dialog('open');
                if (sid && map.mapConfig.points[sid]) {
                    var p = map.mapConfig.points[sid];
                    pX.val(p.x);
                    pY.val(p.y);
                    pN.val(p.name);
                    pSN.val(p.shortname);
                    pR.val(p.radius);
                    pU.val('http://');
                    pT.val(p.pointType).attr('disabled', true);
                    var act = 'nothing';
                    editorSet('');
                    if (p.link && /^javascript:/.test(p.link))
                    {
                        act = 'info';
                        editorSet(p.info?p.info:'');
                    }
                    else if (p.link)
                        act = 'url';
                    $('#ca-'+act).attr('checked', 'checked').click();
                    pU.val(act == 'url' && p.link ? p.link : 'http://');
                    pTP.val(p.textPos ? p.textPos : 'right-middle');
                    pUNW.attr('checked', p.isNewWindow ? 'checked' : false);
                    dlg.dialog('option', {
                        'title': '<?php _e("Edit point: %s", "canada-html5-map") ?>'.replace('%s', p.name),
                        'buttons': [btnSave, btnDelete, btnClose]
                    });
                    pCmt.val(p.comment);
                    show_comment(!!p.comment);
                    if (p.color) {
                        uDC.attr('checked', false);
                        pC.val(p.color).css('backgroundColor', p.color).attr('disabled', false);
                    } else {
                        uDC.attr('checked', 'checked');
                        pC.val(dpC.val()).css('backgroundColor', dpC.val()).attr('disabled', 'disabled');
                    }
                    if (p.colorOver) {
                        uDCO.attr('checked', false);
                        pCO.val(p.colorOver).css('backgroundColor', p.colorOver).attr('disabled', false);
                    } else {
                        uDCO.attr('checked', 'checked');
                        pCO.val(dpCO.val()).css('backgroundColor', dpCO.val()).attr('disabled', 'disabled');
                    }
                    if (p.nameFontSize) {
                        pFS.val(parseInt(p.nameFontSize)).attr('disabled', false);
                        uDFS.attr('checked', false);
                    } else {
                        pFS.val(parseInt(map.mapConfig.pointNameFontSize)).attr('disabled', 'disabled');
                        uDFS.attr('checked', 'checked');
                    }

                    activePoint = sid;
                } else {
                    pX.val(ev.onMapX);
                    pY.val(ev.onMapY);
                    pN.val('');
                    pSN.val('');
                    pU.val('http://');
                    pT.val('').attr('disabled', false);
                    pR.val(4);
                    pTP.val('right-middle');
                    pCmt.val('');
                    show_comment(false);
                    pUNW.attr('checked', false);
                    editorSet('');
                    $('#ca-nothing').attr('checked', 'checked').click();
                    pC.val(dpC.val()).css('backgroundColor', dpC.val()).attr('disabled', 'disabled'); uDC.attr('checked', 'checked');
                    pCO.val(dpCO.val()).css('backgroundColor', dpCO.val()).attr('disabled', 'disabled');  uDCO.attr('checked', 'checked');
                    pFS.val(parseInt(map.mapConfig.pointNameFontSize)).attr('disabled', 'disabled'); uDFS.attr('checked', 'checked');
                    dlg.dialog('option', {
                        'title': '<?php _e("Add new point", "canada-html5-map") ?>',
                        'buttons': [btnAdd, btnClose]
                    });
                    activePoint = null;
                }
            });
            var lastX = 0, lastY = 0, is_moving = false;
            map.on('mousedown', function(ev, sid, map) { if (sid && map.mapConfig.points[sid]) {
                lastX = ev.onMapX;
                lastY = ev.onMapY;
                is_moving = sid;
                ev.stopPropagation();
            } });
            map.on('mouseup', function(ev, sid, map) {
                lastX = 0;
                lastY = 0;
                is_moving = false;
                });
            map.on('mousemove', function(ev, sid, map) {
                if (is_moving) {
                    var dx = ev.onMapX - lastX,
                        dy = ev.onMapY - lastY;
                    map.setPointAttr(is_moving, {
                        x: map.fetchPointAttr(is_moving, 'x')+dx,
                        y: map.fetchPointAttr(is_moving, 'y')+dy
                    });
                    lastX = ev.onMapX;
                    lastY = ev.onMapY;
                    ev.stopPropagation();
                }
            });
            var pX = $('#pointX');
            var pY = $('#pointY');
            var pT = $('#pointType');
            var pN = $('#pointName');
            var pSN = $('#pointShortname');
            var pC  = $('#pointColor');
            var pCO = $('#pointColorOver');
            var dpC  = $('#dPointColor');
            var dpCO = $('#dPointColorOver');
            var pR   = $('#pointRadius');
            var pTP  = $('#pointTextPos');
            var pCmt = $('#pointComment');
            pAI  = $('#pointAddInfo');
            var uDC  = $('#colorDef').change(function() {
                pC.attr('disabled', $(this).attr('checked') ? 'disabled' : null);
            });
            var uDCO = $('#colorOverDef').change(function() {
                pCO.attr('disabled', $(this).attr('checked') ? 'disabled' : null);
            });
            var pFS  = $('#pointFS');
            var uDFS = $('#pointFSDef').change(function() {
                pFS.attr('disabled', $(this).attr('checked') ? 'disabled' : null);
            });
            var pU = $('#pointURL');
            var pUNW = $('#pointURLNW');
            var dlg = $('#point_cfg').dialog({
                'minWidth': 600,
                'width': '80%',
                'autoOpen': false,
                'dialogClass': 'canada-html5-map',
                'buttons': []
            });
            $('input[name="clickaction"]').click(function(){
                $('#action-url, #action-info').hide();
                $('#action-'+$('input[name="clickaction"]:checked').val()).show();
            });
            try{
            if (typeof tinyMCE !== 'undefined') tinyMCE.execCommand('mceAddControl', true, 'pointAddInfo');
            } catch (e) { console.log(e) }
        });
        var pAI;
        function show_comment(show) {
            if (typeof show == 'undefined')
                show = true;
            var w = jQuery('#pointCommentWrapp');
            var p = w.prev().find('span');
            p.html(show ? '-' : '+');
            show ? w.show() : w.hide();
        }
        function show_default_options(show) {
            if (typeof show == 'undefined')
                show = true;
            var w = jQuery('#pointDefOptionsWrapp');
            var p = w.prev().find('span');
            p.html(show ? '-' : '+');
            show ? w.show() : w.hide();
        }
        function editorSet(txt) {
            tinymce.editors.pointAddInfo ?
                tinymce.editors.pointAddInfo.setContent(txt) :
                pAI.val(txt);
        }
        function editorGet() {
            return tinymce.editors.pointAddInfo ?
                tinymce.editors.pointAddInfo.getContent() :
                pAI.val();
        }
    </script>
    <input type="hidden" name="act_type" value="canada-html5-map-points-save" />
    <input type="hidden" name="map_points"  id="map_points"  />
    <input type="hidden" name="points_info" id="points_info" />
    <p class="submit"><input type="submit" value="<?php esc_attr_e('Save Changes', 'canada-html5-map'); ?>" class="button-primary" id="submit" name="submit" disabled></p>
</form>
</div>
