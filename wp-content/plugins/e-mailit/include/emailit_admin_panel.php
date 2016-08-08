<?php
defined('ABSPATH') or die('No direct access permitted');

add_filter('admin_menu', 'emailit_admin_menu');

function emailit_admin_menu() {
    add_options_page('E-MAILiT Settings', 'E-MAILiT Share', 'manage_options', basename(__FILE__), 'emailit_settings_page');
}

function emailit_settings_page() {
    ?>
    <div id="emailit_admin_panel">
        <h1 class="header">E-MAILiT <span><?php _e('Share Settings', 'e-mailit') ?></span></h1>
        <form onsubmit="return validate()" id="emailit_options" action="options.php" method="post">
            <?php
            settings_fields('emailit_options');
            $emailit_options = get_option('emailit_options');
            ?>
            <script type="text/javascript">
                function validate() {
                    jQuery("textarea[name='emailit_options[floating_services]']").val(jQuery("textarea[name='emailit_options[floating_services]']").val().replace(/\s/gi, ""));
                    jQuery("textarea[name='emailit_options[mobile_services]']").val(jQuery("textarea[name='emailit_options[mobile_services]']").val().replace(/\s/gi, ""));

                    var e_mailit_default_servises = jQuery.map(jQuery('#social_services li'), function (element) {
                        return jQuery(element).attr('class').replace(/E_mailit_/gi, '').replace(/ ui-sortable-handle/gi, '');
                    }).join(',');

                    jQuery('#servicess input.ui-helper-hidden-accessible').attr("disabled", "disabled");
                    jQuery('#default_buttons').val(e_mailit_default_servises);

                    var follow_services = {};
                    jQuery("#social_services_follow .follow-field").each(function () {
                        if (jQuery(this).val() !== "") {
                            var name = jQuery(this).attr('name').replace(/follow_/gi, '');
                            follow_services[name] = jQuery(this).val();
                        }
                    });
                    jQuery("#follow_services").val(JSON.stringify(follow_services));
                    jQuery('#social_services_follow .follow-field').attr("disabled", "disabled");

                    if (!jQuery('#emailit_showonhome').is(':checked') && !jQuery('#emailit_showonarchives').is(':checked')
                            && !jQuery('#emailit_showonsearch').is(':checked') && !jQuery('#emailit_showoncats').is(':checked') && !jQuery('#emailit_showonpages').is(':checked') && !jQuery('#emailit_showonexcerpts').is(':checked') && !jQuery('#emailit_showonposts').is(':checked'))
                        alert("Select a placement option to display the button.");

                    return true;
                }
                jQuery(function () {
                    jQuery("#tabs").tabs();

                    jQuery("input[type='checkbox']").bootstrapSwitch();
                    jQuery('.colorpicker_widget input').colorPicker();

                    e_mailit_config = {mobile_bar: false};
                    jQuery.getScript("//www.e-mailit.com/widget/menu3x/js/button.js", function () {
    <?php
    if (isset($emailit_options["default_buttons"]) && $emailit_options["default_buttons"] !== "") {
        echo "var default_buttons ='" . $emailit_options["default_buttons"] . "';" . PHP_EOL;
    } else {
        echo "var default_buttons ='Facebook,Twitter,Google_Plus,Pinterest,LinkedIn,Gmail';" . PHP_EOL;
    }
    ?>
                        var share = e_mailit.services.split(","); // Get buttons
                        for (var key in share) {
                            var services = share[key];
                            var name = services.replace(/_/gi, " ");
                            if (name === "Google Plus")
                                name = "Google+";
                            if (name === "Facebook Like and Share")
                                name = "Facebook Like & Share";
                            var sharelinkInput = jQuery("<input type=\"checkbox\" id=\"checkbox" + services + "\" name=\"" + services + "\" />");
                            var sharelinkLabel = jQuery("<label for=\"checkbox" + services + "\" class='services_list' id=\"" + services + "\" ><div class=\"E_mailit_" + services + "\"> </div> <span class='services_list_name'>" + name + "</span></label>");
                            sharelinkInput.appendTo('#servicess');
                            sharelinkLabel.appendTo('#servicess');
                        }

                        jQuery("#servicess input[type=checkbox]").click(function () {
                            if (jQuery(this).is(':checked')) {
                                var class_name = this.name.replace(/_/gi, " ");
                                if (class_name === "Google Plus")
                                    class_name = "Google+";
                                if (class_name === "Facebook Like and Share")
                                    class_name = "Facebook Like & Share";

                                jQuery('#social_services').append('<li title="' + class_name + '" class="E_mailit_' + this.name + '"></li>');
                                jQuery("#E_mailit_" + this.name + "").effect("transfer", {
                                    to: "#social_services ." + this.name
                                }, 500);
                            } else {
                                jQuery("#social_services .E_mailit_" + this.name).effect("transfer", {
                                    to: "#" + this.name + ""
                                }, 500).delay(500).remove();
                            }
                        });

                        var new_share = default_buttons.split(","); // Get buttons
                        addButtons(new_share);

                        jQuery("#social_services").sortable({
                            revert: true,
                            opacity: 0.8
                        });
                        jQuery("ul#social_services, #social_services li").disableSelection();
                        jQuery("#check").button();
                        jQuery("#servicess").buttonset();
                        jQuery(".uncheck_all_btn").click(function () {
                            jQuery("#servicess input[type=checkbox]").attr('checked', false);
                            jQuery("#servicess input[type=checkbox]").button("refresh");
                            jQuery("#social_services").empty();
                            jQuery("#servicess input:not(:checked)").button("option", "disabled", false);
                            jQuery(".message_good").show("fast");
                        });

                        jQuery(".social_services_default_btn").click(function () {
                            jQuery(".uncheck_all_btn").click();
                            addButtons(new_share);
                            jQuery("#servises_customize_btn").show('fast');
                            jQuery("#social_services #custom,#servicess,.filterinput,.social_services_default_btn,.message_good,.message_bad,.uncheck_all_btn").hide('fast');
                            styleChanged();
                        });

                        jQuery("#servises_customize_btn").click(function () {
                            jQuery("#servises_customize_btn").hide('fast');
                            jQuery("#social_services #custom,#servicess,.filterinput,.message_good,.social_services_default_btn,.uncheck_all_btn").show('fast');
                        });

                        jQuery.expr[':'].Contains = function (a, i, m) {//boitheia gia to search me ta grammata tis :contains
                            return (a.textContent || a.innerText || "").toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
                        };
                        jQuery('#filter-form-input-text').keyup(function (event) {
                            var filter = jQuery('#filter-form-input-text').val();
                            if (filter == '' || filter.length < 1) {
                                jQuery(".services_list").show();
                            } else {
                                jQuery(".services_list").find(".services_list_name:not(:Contains(" + filter + "))").parent().parent().hide();
                                jQuery(".services_list").find(".services_list_name:Contains(" + filter + ")").parent().parent().show();
                            }
                            var value = jQuery("input[name='emailit_options[toolbar_type]']:checked").val();
                            var nativeServices = ["Facebook", "Facebook_Like", "Facebook_Like_and_Share", "Facebook_Send", "Twitter", "Google_Plus", "LinkedIn", "Pinterest", "VKontakte", "Odnoklassniki"];
                            if (value === "native") {
                                jQuery("#servicess label").each(function () {
                                    if (jQuery.inArray(jQuery(this).attr('id'), nativeServices) < 0) {
                                        jQuery(this).hide();
                                    }
                                });
                            } else {
                                jQuery("#servicess label#Facebook_Like, #servicess label#Facebook_Like_and_Share").hide();
                            }
                            jQuery("#servicess").buttonset("refresh");
                        });

                        var follow_values = JSON.parse('<?php echo $emailit_options["follow_services"] ?>');
                        for (var key in e_mailit.follows_links) {
                            var link_with_input = e_mailit.follows_links[key].replace(/{FOLLOW}/gi, '<input class="follow-field" name="follow_' + key + '" type="text">');
                            jQuery("#social_services_follow").append('<li><i class="E_mailit_' + key + '"></i>' + link_with_input + '</li>');
                            if (follow_values && follow_values[key]) {
                                jQuery("#social_services_follow .follow-field[name='follow_" + key + "']").val(follow_values[key]);
                            }
                        }

                        jQuery("input[name='emailit_options[toolbar_type]']").click(function () {
                            styleChanged();
                        });
                        function styleChanged() {
                            jQuery('#filter-form-input-text').val("");
                            var nativeServices = ["Facebook", "Facebook_Like", "Facebook_Like_and_Share", "Facebook_Send", "Twitter", "Google_Plus", "LinkedIn", "Pinterest", "VKontakte", "Odnoklassniki"];
                            var value = jQuery("input[name='emailit_options[toolbar_type]']:checked").val();
                            if (value === "native") {
                                jQuery(".icon_size").hide();
                                jQuery("#emailit_rounded").hide();
                                jQuery("#emailit_text_display").show();
                                jQuery("#emailit_back_color").hide();
                                jQuery("#emailit_text_color").hide();
                                jQuery("#emailit_global_text_color").show();
                                jQuery("#servicess label").show();
                                jQuery("#servicess label").each(function () {
                                    if (jQuery.inArray(jQuery(this).attr('id'), nativeServices) < 0) {
                                        jQuery(this).hide();
                                        jQuery("#social_services li.E_mailit_" + jQuery(this).attr('id')).remove();
                                        jQuery("#servicess input#checkbox" + jQuery(this).attr('id')).prop('checked', false);
                                    }
                                });
                            } else {
                                jQuery("#emailit_back_color").show();
                                jQuery("#emailit_rounded").show();
                                if (value === "square") {
                                    jQuery("#emailit_text_color").hide();
                                    jQuery("#emailit_global_text_color").hide();
                                    jQuery("#emailit_text_display").hide();
                                } else if (value === "wide") {
                                    jQuery("#emailit_text_color").show();
                                    jQuery("#emailit_global_text_color").show();
                                    jQuery("#emailit_text_display").show();
                                } else {
                                    jQuery("#emailit_text_color").hide();
                                    jQuery("#emailit_global_text_color").hide();
                                    jQuery("#emailit_rounded").hide();
                                    jQuery("#emailit_text_display").hide();
                                }
                                jQuery(".icon_size").show();
                                jQuery("#emailit_circular").show();
                                jQuery("#servicess label").show();
                                jQuery("#servicess label#Facebook_Like, #servicess label#Facebook_Like_and_Share").hide();
                                jQuery("#social_services li.E_mailit_Facebook_Like, #social_services li.E_mailit_Facebook_Like_and_Share").remove();
                                jQuery("#servicess input#checkboxFacebook_Like, #servicess input#checkboxFacebook_Like_and_Share").prop('checked', false);
                            }
                            jQuery("#servicess").buttonset("refresh");
                        }

                        //floating
                        jQuery("input[name='emailit_options[floating_type]']").click(function () {
                            floatingStyleChanged();
                        });
                        function floatingStyleChanged() {
                            var value = jQuery("input[name='emailit_options[floating_type]']:checked").val();
                            if (value === "native") {
                                jQuery(".floating_icon_size").hide();
                                jQuery("#emailit_floating_rounded").hide();
                                jQuery("#emailit_floating_back_color").hide();
                                jQuery("#emailit_floating_text_color").hide();
                            } else {
                                jQuery("#emailit_floating_back_color").show();
                                jQuery("#emailit_floating_rounded").show();
                                if (value === "square") {
                                    jQuery("#emailit_floating_text_color").hide();
                                } else if (value === "wide") {
                                    jQuery("#emailit_floating_text_color").show();
                                } else {
                                    jQuery("#emailit_floating_text_color").hide();
                                    jQuery("#emailit_floating_rounded").hide();
                                }
                                jQuery(".floating_icon_size").show();
                            }
                        }
                        var valMap = [16, 20, 24, 32, 40, 48];
                        jQuery("#slider-range").slider({
                            min: 0,
                            max: valMap.length - 1,
                            value: jQuery.inArray(<?php echo substr($emailit_options["icon_size"], 0, strrpos($emailit_options["icon_size"], " ")) ?>, valMap),
                            slide: function (event, ui) {
                                jQuery("#icon_size").val(valMap[ui.value] + " px");
                            }
                        });
                        jQuery("#floating-slider-range").slider({
                            min: 0,
                            max: valMap.length - 1,
                            value: jQuery.inArray(<?php echo substr($emailit_options["floating_icon_size"], 0, strrpos($emailit_options["floating_icon_size"], " ")) ?>, valMap),
                            slide: function (event, ui) {
                                jQuery("#floating_icon_size").val(valMap[ui.value] + " px");
                            }
                        });
                        styleChanged();
                        floatingStyleChanged();

                        function mobServicesChanged() {
                            if (jQuery("input[name='emailit_options[mob_button_set]']:checked").val() === "mob_custom")
                                jQuery("#mob_services").show();
                            else
                                jQuery("#mob_services").hide();
                        }
                        jQuery("input[name='emailit_options[mob_button_set]']").click(function () {
                            mobServicesChanged();
                        });
                        mobServicesChanged();
                    });

                    function floatingServicesChanged() {
                        if (jQuery("input[name='emailit_options[floating_button_set]']:checked").val() === "floating_custom")
                            jQuery("#floating_services").show();
                        else
                            jQuery("#floating_services").hide();
                    }
                    jQuery("input[name='emailit_options[floating_button_set]']").click(function () {
                        floatingServicesChanged();
                    });
                    floatingServicesChanged();
                });
                function addButtons(new_share) {
                    for (var key in new_share) {
                        var service = new_share[key];
                        jQuery('#servicess #checkbox' + service).click();
                    }
                }
            </script>
            <div id="tabs">
                <ul>
                    <li><a href="#tabs-share_buttons"><?php _e('SHARE BUTTONS', 'e-mailit') ?></a></li>
                    <li><a href="#tabs-advanced"><?php _e('ADVANCED OPTIONS', 'e-mailit') ?></a></li>
                </ul>
                <div id="tabs-share_buttons">
                    <div class="emailit_admin_panel_section">
                        <h3><?php _e('Content buttons', 'e-mailit') ?></h3>
                        <label class="label"><?php _e('STYLE', 'e-mailit') ?></label>
                        <ul class="radio">
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[toolbar_type]" value="square" <?php echo ($emailit_options["toolbar_type"] == "square" ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Square', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[toolbar_type]" value="wide" <?php echo ($emailit_options["toolbar_type"] == "wide" ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Wide', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[toolbar_type]" value="circular" <?php echo ($emailit_options["toolbar_type"] == "circular" ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Circle', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>                                
                                <label>
                                    <input type="radio" name="emailit_options[toolbar_type]" value="native" <?php echo ($emailit_options["toolbar_type"] == "native" ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Native (original 3rd party share buttons - note that the code for these plugins are native to the social network and thus have limited options for modification)', 'e-mailit') ?>
                                </label>
                            </li>
                        </ul>
                        <label class="label icon_size">SIZE</label>
                        <div id="slider-range" class="icon_size"></div>
                        <input type="text" id="icon_size" class="icon_size" name="emailit_options[icon_size]" value="<?php echo $emailit_options["icon_size"] ?>" readonly />						
                        <ul class="fields">
                            <li id="emailit_rounded">
                                <label><?php _e('ROUNDED', 'e-mailit') ?></label>
                                <input type="checkbox" name="emailit_options[rounded]" value="true" <?php echo ($emailit_options['rounded'] == true ? 'checked="checked"' : ''); ?>/>
                            </li>
                            <li id="emailit_back_color">                                
                                <label><?php _e('BACKGROUND COLOR (leave it blank for default style)', 'e-mailit') ?></label>
                                <div class="colorpicker_widget">
                                    <input type="text" name="emailit_options[back_color]" maxlength="7" size="7" id="colorpickerField2" value="<?php echo $emailit_options["back_color"] ?>" />
                                </div>
                            </li>  
                            <li id="emailit_text_color">
                                <label><?php _e('TEXT COLOR (leave it blank for default style)', 'e-mailit') ?></label>
                                <div class="colorpicker_widget">
                                    <input type="text" name="emailit_options[text_color]" maxlength="7" size="7" id="colorpickerField3" value="<?php echo $emailit_options["text_color"] ?>" />
                                </div>                                
                            </li>							
                        </ul>  
                        <label><?php _e('SERVICES', 'e-mailit') ?></label>
                        <div class="out-of-the-box">
                            <ul id="social_services" class="large"></ul>
                            <div class="services_buttons">
                                <a style="display:none;" class="social_services_default_btn"><?php _e('Restore settings', 'e-mailit') ?></a>
                                <a style="display:none;" class="uncheck_all_btn"><?php _e('Clear all', 'e-mailit') ?></a>
                                <a id="servises_customize_btn"><?php _e('Customize...', 'e-mailit') ?></a> 
                            </div>                            
                            <div class="message_good" style="display:none"><?php _e('Select your buttons', 'e-mailit') ?></div>
                            <div class="filterinput">
                                <input placeholder="Search for services" data-type="search" id="filter-form-input-text">
                            </div>                        
                            <div id="servicess" class="large">
                            </div>
                            <input id="default_buttons" name="emailit_options[default_buttons]" value="<?php echo $emailit_options['default_buttons']; ?>" type="hidden"/>
                        </div>
                        <div>
                            <label><?php _e('PLACEMENT', 'e-mailit') ?></label>
                            <ul class="radio">
                                <li>
                                    <label>                          
                                        <input type="radio" name="emailit_options[button_position]"  value="both" <?php echo ($emailit_options['button_position'] == 'both' ? 'checked="checked"' : ''); ?>/>
                                        <?php _e('Both', 'e-mailit') ?></label>
                                </li>
                                <li>
                                    <label>                            
                                        <input type="radio" name="emailit_options[button_position]" value="bottom" <?php echo ($emailit_options['button_position'] == 'bottom' ? 'checked="checked"' : ''); ?>/>
                                        <?php _e('Below content', 'e-mailit') ?></label>
                                </li>
                                <li>
                                    <label>                          
                                        <input type="radio" name="emailit_options[button_position]"  value="top" <?php echo ($emailit_options['button_position'] == 'top' ? 'checked="checked"' : ''); ?>/>
                                        <?php _e('Above content', 'e-mailit') ?></label>
                                </li>
                            </ul>  
                            <label><?php _e('LOCATIONS', 'e-mailit') ?></label>
                            <label class="above"><?php _e('Homepage', 'e-mailit') ?><br />
                                <input id="emailit_showonhome" type="checkbox" name="emailit_options[emailit_showonhome]" value="true" <?php echo ($emailit_options['emailit_showonhome'] == true ? 'checked="checked"' : ''); ?>/>
                            </label>
                            <label class="above"><?php _e('Posts', 'e-mailit') ?><br />
                                <input id="emailit_showonposts" type="checkbox" name="emailit_options[emailit_showonposts]" value="true" <?php echo ($emailit_options['emailit_showonposts'] == true ? 'checked="checked"' : ''); ?>/>
                            </label>
                            <label class="above"><?php _e('Pages', 'e-mailit') ?><br />
                                <input id="emailit_showonpages" type="checkbox" name="emailit_options[emailit_showonpages]" value="true" <?php echo ($emailit_options['emailit_showonpages'] == true ? 'checked="checked"' : ''); ?>/>
                            </label>
                            <label class="above"><?php _e('Excerpts', 'e-mailit') ?><br />
                                <input id="emailit_showonexcerpts" type="checkbox" name="emailit_options[emailit_showonexcerpts]" value="true" <?php echo ($emailit_options['emailit_showonexcerpts'] == true ? 'checked="checked"' : ''); ?>/>  
                            </label>                    
                            <label class="above"><?php _e('Archives', 'e-mailit') ?><br />
                                <input id="emailit_showonarchives" type="checkbox" name="emailit_options[emailit_showonarchives]" value="true" <?php echo ($emailit_options['emailit_showonarchives'] == true ? 'checked="checked"' : ''); ?>/>
                            </label>
                            <label class="above"><?php _e('Categories', 'e-mailit') ?><br />
                                <input id="emailit_showoncats" type="checkbox" name="emailit_options[emailit_showoncats]" value="true" <?php echo ($emailit_options['emailit_showoncats'] == true ? 'checked="checked"' : ''); ?>/>
                            </label>
                            <label class="above"><?php _e('Search results', 'e-mailit') ?><br />
                                <input id="emailit_showonsearch" type="checkbox" name="emailit_options[emailit_showonsearch]" value="true" <?php echo ($emailit_options['emailit_showonsearch'] == true ? 'checked="checked"' : ''); ?>/>
                            </label>                            
                            <p>
                                <label><strong>Insert buttons anywhere in content</strong></label>
                                To insert buttons in a specific place, add {e-mailit} shortcode anywhere you want in your content.
                            </p>                        
                        </div>

                    </div>
                    <div class="emailit_admin_panel_section">
                        <h3><?php _e('Global button (more sharing options)', 'e-mailit') ?></h3>
                        <label><?php _e('ORDER', 'e-mailit') ?></label>
                        <ul class="radio">
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[global_button]" value="last" <?php echo ($emailit_options['global_button'] == 'last' ? 'checked="checked"' : ''); ?>/>                               
                                    <?php _e('Show last in sharing toolbar', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[global_button]" value="first" <?php echo ($emailit_options['global_button'] == 'first' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Show first in sharing toolbar', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[global_button]" value="disabled" <?php echo ($emailit_options['global_button'] == 'disabled' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Deactivate', 'e-mailit') ?>
                                </label>
                            </li>
                        </ul>
                        <label><?php _e('OPEN GLOBAL SHARING MENU ON', 'e-mailit') ?></label>
                        <ul class="radio">
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[open_on]" value="onclick" <?php echo ($emailit_options['open_on'] == 'onclick' ? 'checked="checked"' : ''); ?>/>  
                                    <?php _e('Click', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[open_on]" value="onmouseover" <?php echo ($emailit_options['open_on'] == 'onmouseover' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Hover', 'e-mailit') ?>
                                </label>
                            </li>
                        </ul>
                        <ul class="fields">
                            <li id="emailit_text_display">
                                <label><?php _e('SHARE TEXT', 'e-mailit') ?></label>
                                <input type="text" name="emailit_options[text_display]" value="<?php
                                if ($emailit_options['text_display'])
                                    echo $emailit_options['text_display'];
                                else
                                    echo "Share";
                                ?>"/>                                
                            </li>
                            <li id="emailit_global_back_color" style="display: list-item;">
                                <label>BACKGROUND COLOR (leave it blank for default style)</label>
                                <div class="colorpicker_widget">
                                    <input type="text" name="emailit_options[global_back_color]" maxlength="7" size="7" id="colorpickerField5" value="<?php echo $emailit_options["global_back_color"] ?>">
                                </div>
                            </li>								
                            <li id="emailit_global_text_color" style="display: list-item;">
                                <label>TEXT COLOR (leave it blank for default style)</label>
                                <div class="colorpicker_widget">
                                    <input type="text" name="emailit_options[global_text_color]" maxlength="7" size="7" id="colorpickerField6" value="<?php echo $emailit_options["global_text_color"] ?>">
                                </div>
                            </li>							
                            <li>
                                <label><?php _e('AUTO SHOW SHARE OVERLAY AFTER', 'e-mailit') ?></label>					
                                <input min="0" max="1000" type="number" name="emailit_options[auto_popup]" value="<?php
                                if ($emailit_options['auto_popup'])
                                    echo $emailit_options['auto_popup'];
                                else
                                    echo '0';
                                ?>"/> <?php _e('sec', 'e-mailit') ?>
                            </li>                             
                        </ul>
                    </div>
                    <div class="emailit_admin_panel_section">
                        <h3><?php _e('Floating', 'e-mailit') ?></h3>
                        <label><?php _e('SHARE SIDEBAR', 'e-mailit') ?></label>
                        <ul class="radio">                            
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[floating_bar]" value="disabled" <?php echo ($emailit_options['floating_bar'] == 'disabled' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Deactivate', 'e-mailit') ?></label>
                            </li>
                            <li>
                                <label>                        
                                    <input type="radio" name="emailit_options[floating_bar]"  value="left" <?php echo ($emailit_options['floating_bar'] == 'left' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Left', 'e-mailit') ?></label>
                            </li>
                            <li>
                                <label>                           
                                    <input type="radio" name="emailit_options[floating_bar]"  value="right" <?php echo ($emailit_options['floating_bar'] == 'right' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Right', 'e-mailit') ?></label>
                            </li>
                        </ul>
                        <label class="label"><?php _e('STYLE', 'e-mailit') ?></label>
                        <ul class="radio">
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[floating_type]" value="square" <?php echo ($emailit_options["floating_type"] == "square" ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Square', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[floating_type]" value="wide" <?php echo ($emailit_options["floating_type"] == "wide" ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Wide', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[floating_type]" value="circular" <?php echo ($emailit_options["floating_type"] == "circular" ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Circle', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>                                
                                <label>
                                    <input type="radio" name="emailit_options[floating_type]" value="native" <?php echo ($emailit_options["floating_type"] == "native" ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Native (original 3rd party share buttons - note that the code for these plugins are native to the social network and thus have limited options for modification)', 'e-mailit') ?>
                                </label>
                            </li>
                        </ul>
                        <label class="label floating_icon_size">SIZE</label>
                        <div id="floating-slider-range" class="floating_icon_size"></div>
                        <input type="text" class="floating_icon_size" id="floating_icon_size" name="emailit_options[floating_icon_size]" value="<?php echo $emailit_options["floating_icon_size"] ?>" readonly />						
                        <ul class="fields">
                            <li id="emailit_floating_rounded">
                                <label><?php _e('ROUNDED', 'e-mailit') ?></label>
                                <input type="checkbox" name="emailit_options[floating_rounded]" value="true" <?php echo ($emailit_options['floating_rounded'] == true ? 'checked="checked"' : ''); ?>/>
                            </li>
                            <li id="emailit_floating_back_color">                                
                                <label><?php _e('BACKGROUND COLOR (leave it blank for default style)', 'e-mailit') ?></label>
                                <div class="colorpicker_widget">
                                    <input type="text" name="emailit_options[floating_back_color]" maxlength="7" size="7" id="colorpickerField7" value="<?php echo $emailit_options["floating_back_color"] ?>" />
                                </div>
                            </li>  
                            <li id="emailit_floating_text_color">
                                <label><?php _e('TEXT COLOR (leave it blank for default style)', 'e-mailit') ?></label>
                                <div class="colorpicker_widget">
                                    <input type="text" name="emailit_options[floating_text_color]" maxlength="7" size="7" id="colorpickerField8" value="<?php echo $emailit_options["floating_text_color"] ?>" />
                                </div>                                
                            </li>							
                        </ul>  
                        <label><?php _e('SERVICES', 'e-mailit') ?></label>
                        <ul class="radio">
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[floating_button_set]" value="floating_same" <?php echo ($emailit_options['floating_button_set'] == 'floating_same' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Same as Content (Standalone)', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[floating_button_set]" value="floating_custom" <?php echo ($emailit_options['floating_button_set'] == 'floating_custom' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Select your own services', 'e-mailit') ?> [ <a href="https://www.e-mailit.com/services" target="_blank"><strong><?php _e('Service Codes', 'e-mailit') ?></strong></a> ]
                                </label>                                                                
                            </li>
                            <li id="floating_services">
                                <?php _e('Separate multiple services correctly (case sensitive) by comma', 'e-mailit') ?>
                                <textarea name="emailit_options[floating_services]" placeholder="Facebook,Twitter,WhatsApp"><?php echo $emailit_options['floating_services'] ?></textarea>
                            </li>                                                             
                        </ul>  
                    </div>
                    <div class="emailit_admin_panel_section">
                        <h3><?php _e('Mobile Sharing', 'e-mailit') ?></h3>
                        <ul class="fields">
                            <li>
                                <label><?php _e('MOBILE SHARE BAR', 'e-mailit') ?> (<a href="http://blog.e-mailit.com/2016/02/amazing-free-mobile-share-buttons.html" target="_blank">what's this?</a>)</label>
                                <input id="mobile_bar" type="checkbox" name="emailit_options[mobile_bar]" value="true" <?php echo ($emailit_options['mobile_bar'] == true ? 'checked="checked"' : ''); ?>/>
                            </li>
                            <li>                                
                                <label><?php _e('BACKGROUND COLOR (leave it blank for default style)', 'e-mailit') ?></label>
                                <div class="colorpicker_widget">
                                    <input type="text" name="emailit_options[mobile_back_color]" maxlength="7" size="7" id="colorpickerField4" value="<?php echo $emailit_options["mobile_back_color"] ?>" />
                                </div>
                            </li>
                        </ul>
                        <label><?php _e('SERVICES', 'e-mailit') ?></label>
                        <ul class="radio">
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[mob_button_set]" value="mob_default" <?php echo (!$emailit_options['mob_button_set'] || $emailit_options['mob_button_set'] == 'mob_default' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Default for Mobile', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[mob_button_set]" value="mob_same" <?php echo ($emailit_options['mob_button_set'] == 'mob_same' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Same as Content (Standalone)', 'e-mailit') ?>
                                </label>
                            </li>
                            <li>
                                <label>
                                    <input type="radio" name="emailit_options[mob_button_set]" value="mob_custom" <?php echo ($emailit_options['mob_button_set'] == 'mob_custom' ? 'checked="checked"' : ''); ?>/>
                                    <?php _e('Select your own services', 'e-mailit') ?> [ <a href="https://www.e-mailit.com/services" target="_blank"><strong><?php _e('Service Codes', 'e-mailit') ?></strong></a> ]
                                </label>                                                                
                            </li>
                            <li id="mob_services">
                                <?php _e('Separate multiple services correctly (case sensitive) by comma', 'e-mailit') ?>
                                <textarea name="emailit_options[mobile_services]" placeholder="Facebook,Twitter,WhatsApp"><?php echo $emailit_options['mobile_services'] ?></textarea>
                            </li>                                                             
                        </ul>                                                            
                    </div>					
                    <div>
                        <h3><?php _e('Extra Features', 'e-mailit') ?></h3>
                        <ul class="fields">
                            <li>
                                <label><?php _e('COUNTERS', 'e-mailit') ?></label>
                                <input type="checkbox" name="emailit_options[display_counter]" value="true" <?php echo ($emailit_options['display_counter'] == true ? 'checked="checked"' : ''); ?>/>
                            </li>                              
                            <li>
                                <label><?php _e('TWEET VIA (your Twitter username)', 'e-mailit') ?></label>
                                <input type="text" name="emailit_options[TwitterID]" value="<?php echo $emailit_options['TwitterID']; ?>"/>
                            </li>
                            <li>
                                <label><?php _e('PINTEREST SHAREABLE IMAGES', 'e-mailit') ?></label>
                                <input id="hover_pinit" type="checkbox" name="emailit_options[hover_pinit]" value="true" <?php echo ($emailit_options['hover_pinit'] == true ? 'checked="checked"' : ''); ?>/>
                            </li>
                        </ul>
                    </div>
                </div>                      
                <div id="tabs-advanced">
                    <ul class="fields">     
                        <li>
                            <label><?php _e('ADD YOUR LOGO', 'e-mailit') ?> (<a href="http://blog.e-mailit.com/2015/12/new-feature-update-add-your-logo.html" target="_blank">what's this?</a>)</label>
                            <label><?php _e('Insert your Image Unit location', 'e-mailit') ?></label>
                            <input placeholder="http://" name="emailit_options[logo]" type="text" value="<?php echo $emailit_options["logo"] ?>">                            
                        </li>
                        <li>
                            <label><?php _e('AFTER SHARE PROMO', 'e-mailit') ?> (<a href="http://blog.e-mailit.com/2015/12/monetize-your-content-marketing.html" target="_blank">what's this?</a>)</label>
                            <input id="after_share_dialog" type="checkbox" name="emailit_options[after_share_dialog]" value="true" <?php echo ($emailit_options['after_share_dialog'] == true ? 'checked="checked"' : ''); ?>/>
                        </li>                       
                        <li>
                            <label><?php _e('AFTER SHARE PROMO HEADING', 'e-mailit') ?></label>
                            <input placeholder="Thanks for sharing! Like our content? Follow us!" name="emailit_options[thanks_message]" type="text" value="<?php echo $emailit_options["thanks_message"] ?>">
                        </li>
                    </ul>
                    <div class="follow_services">
                        <label><?php _e('FOLLOW SERVICES (show on After Share Promo)', 'e-mailit') ?></label>
                        <ul id="social_services_follow" class="large">

                        </ul>
                        <input id="follow_services" name="emailit_options[follow_services]" value="<?php echo $emailit_options['follow_services']; ?>" type="hidden"/>
                    </div> 
                    <ul class="fields">                            
                        <li>
                            <label><?php _e('DISPLAY ADVERTS', 'e-mailit') ?></label>					
                            <input id="display_ads" type="checkbox" name="emailit_options[display_ads]" value="true" <?php echo ($emailit_options['display_ads'] == true ? 'checked="checked"' : ''); ?>/>
                        </li>
                        <li>
                            <label><?php _e('MONETIZE (show on After Share Promo)', 'e-mailit') ?></label>
                            <label><?php _e('Insert your Ad Unit (or Promo) location', 'e-mailit') ?> (<a href="https://www.e-mailit.com/sample/ad-unit.html" target="_blank">Download/Save &amp; edit the sample file</a>)</label>
                            <input placeholder="http://" name="emailit_options[ad_url]" type="text" value="<?php echo $emailit_options["ad_url"] ?>">
                        </li>
                        <li>
                            <label><?php _e('E-MAILiT branding', 'e-mailit') ?></label>
                            <input type="checkbox" name="emailit_options[emailit_branding]" value="true" <?php echo ($emailit_options['emailit_branding'] == true ? 'checked="checked"' : ''); ?>/>
                        </li>						
                    </ul>
                </div>
                <p>
                    <input id="submit" name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
                <p/>
            </div>
            <p>
                <?php _e('<a target="_blank" href="https://www.e-mailit.com/sharer?url=https://wordpress.org/plugins/e-mailit&title=&UA_ID=" title="Share">Share</a> this plugin (demo)', 'e-mailit') ?>
            </p>
            <p>
                <a href="https://twitter.com/emailit" class="twitter-follow-button" data-show-count="true">Follow @emailit</a>
                <script>!function (d, s, id) {
                        var js, fjs = d.getElementsByTagName(s)[0], p = /^http:/.test(d.location) ? 'http' : 'https';
                        if (!d.getElementById(id)) {
                            js = d.createElement(s);
                            js.id = id;
                            js.src = p + '://platform.twitter.com/widgets.js';
                            fjs.parentNode.insertBefore(js, fjs);
                        }
                    }(document, 'script', 'twitter-wjs');</script>
            </p>
        </form>
    </div>

    <?php
}
