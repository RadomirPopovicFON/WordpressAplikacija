<?php

class EmailitSidebarWidget extends WP_Widget {

    function EmailitSidebarWidget() {
        $widget_ops = array('classname' => 'EmailitWidget', 'description' => __("The most flexible sharing tools, including E-MAILiT's global mobile-optimized sharing menu, Twitter, Facebook, WhatsApp, SMS & many more.",'e-mailit'));
        $this->WP_Widget('EmailitWidget', __('E-MAILiT Share','e-mailit'), $widget_ops);
    }

    function form($instance) {
        $defaults = array('title' => __('Share','e-mailit'), 'toolbar_type' => 'large');
        $instance = wp_parse_args((array) $instance, $defaults);
        $title = esc_attr($instance['title']);
        $button_id = esc_attr($instance['button_id']);
        $toolbar_type = esc_attr($instance['toolbar_type']);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title','e-mailit') ?>:
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" />
            </label>
        </p>
        <p>
            <label class="label"><?php _e('STYLE','e-mailit') ?></label>
            <ul class="radio">
                <li>
                    <label>
                        <input type="radio" name="<?php echo $this->get_field_name('toolbar_type'); ?>" value="square" <?php checked($toolbar_type, 'square'); ?>/>
                        <?php _e('Square','e-mailit') ?>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="<?php echo $this->get_field_name('toolbar_type'); ?>" value="wide" <?php checked($toolbar_type, 'wide'); ?>/>
                        <?php _e('Wide','e-mailit') ?>
                    </label>
                </li>
                <li>
                    <label>
                        <input type="radio" name="<?php echo $this->get_field_name('toolbar_type'); ?>" value="circular" <?php checked($toolbar_type, 'circular'); ?>/>
                        <?php _e('Circle','e-mailit') ?>
                    </label>
                </li>                
                <li>
                    <label>
                        <input type="radio" name="<?php echo $this->get_field_name('toolbar_type'); ?>" value="native" <?php checked($toolbar_type, 'native'); ?>/>
                        <?php _e('Native (original 3rd party share buttons)','e-mailit') ?>
                    </label>
                </li>
            </ul>
        </p>
        <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['button_id'] = strip_tags($new_instance['button_id']);
        $instance['toolbar_type'] = strip_tags($new_instance['toolbar_type']);
        return $instance;
    }

    function widget($args, $instance) {
        extract($args, EXTR_SKIP);

        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);

        if (!empty($title)) {
            echo $title;
        };

        // WIDGET CODE GOES HERE
        $toolbar_type = isset($instance['toolbar_type']) ? $instance['toolbar_type'] : '';

        $emailit_options = get_option('emailit_options');
        $emailit_options['toolbar_type'] = $toolbar_type;
        $outputValue = emailit_createButton($emailit_options);

        echo $outputValue;
    }

}
?>