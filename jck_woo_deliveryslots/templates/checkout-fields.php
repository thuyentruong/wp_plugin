<?php if( !empty( $fields ) ) { ?>

    <div id="jckwds-fields" <?php if( !$active ) echo 'class="jckwds-fields-inactive"'; ?>>

        <h3><?php _e('Delivery Details', 'jckwds'); ?></h3>

        <?php foreach( $fields as $field_name => $field_data ) { ?>

            <div id="<?php echo $field_name; ?>-wrapper">

                <?php woocommerce_form_field( $field_name, $field_data['field_args'], $field_data['value'] ); ?>

            </div>

        <?php } ?>

    </div>

<?php } ?>