
/*
 * VARIABLES
 * Description: All Global Vars
 */
var $j = jQuery;

// Impacts the responce rate of some of the responsive elements (lower value affects CPU but improves speed)
$j.throttle_delay = 350;

// The rate at which the menu expands revealing child elements on click
$j.menu_speed = 235;

// Note: You will also need to change this variable in the "variable.less" file.
$j.navbar_height = 49;

/*
 * APP DOM REFERENCES
 * Description: Obj DOM reference, please try to avoid changing these
 */
$j.root_ = $j('body');
$j.left_panel = $j('#left-panel');
$j.shortcut_dropdown = $j('#shortcut');
$j.bread_crumb = $j('#ribbon ol.breadcrumb');

// desktop or mobile
$j.device = null;

/*
 * APP CONFIGURATION
 * Description: Enable / disable certain theme features here
 */
$j.navAsAjax = false; // Your left nav in your app will no longer fire ajax calls

// Please make sure you have included "jarvis.widget.js" for this below feature to work
$j.enableJarvisWidgets = true;

// Warning: Enabling mobile widgets could potentially crash your webApp if you have too many 
// 			widgets running at once (must have $j.enableJarvisWidgets = true)
$j.enableMobileWidgets = false;


/*
 * DETECT MOBILE DEVICES
 * Description: Detects mobile device - if any of the listed device is detected
 * a class is inserted to $j.root_ and the variable $j.device is decleard. 
 */

/* so far this is covering most hand held devices */
var ismobile = (/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()));

if (!ismobile) {
    // Desktop
    $j.root_.addClass("desktop-detected");
    $j.device = "desktop";
} else {
    // Mobile
    $j.root_.addClass("mobile-detected");
    $j.device = "mobile";

    // Removes the tap delay in idevices
    // dependency: js/plugin/fastclick/fastclick.js 
    //FastClick.attach(document.body);
}

/* ~ END: CHECK MOBILE DEVICE */

/*
 * DOCUMENT LOADED EVENT
 * Description: Fire when DOM is ready
 */

$j(document).ready(function () {
    /*
	 * Fire tooltips
	 */
    if ($j("[rel=tooltip]").length) {
        $j("[rel=tooltip]").tooltip();
    }

    //TODO: was moved from window.load due to IE not firing consist
    nav_page_height()

    // INITIALIZE LEFT NAV
    if (!null) {
        $j('nav ul').jarvismenu({
            accordion: true,
            speed: $j.menu_speed,
            closedSign: '<em class="fa fa-expand-o"></em>',
            openedSign: '<em class="fa fa-collapse-o"></em>'
        });
    } else {
        alert("Error - menu anchor does not exist");
    }

    // COLLAPSE LEFT NAV
    $j('.minifyme').click(function (e) {
        $j('body').toggleClass("minified");
        $j(this).effect("highlight", {}, 500);
        e.preventDefault();
    });

    // HIDE MENU
    $j('#hide-menu >:first-child > a').click(function (e) {
        $j('body').toggleClass("hidden-menu");
        e.preventDefault();
    });

    $j('#show-shortcut').click(function (e) {
        if ($j.shortcut_dropdown.is(":visible")) {
            shortcut_buttons_hide();
        } else {
            shortcut_buttons_show();
        }
        e.preventDefault();
    });

    // SHOW & HIDE MOBILE SEARCH FIELD
    $j('#search-mobile').click(function () {
        $j.root_.addClass('search-mobile');
    });

    $j('#cancel-search-js').click(function () {
        $j.root_.removeClass('search-mobile');
    });

    // ACTIVITY
    // ajax drop
    $j('#activity').click(function (e) {
        var $jthis = $j(this);

        if ($jthis.find('.badge').hasClass('bg-color-red')) {
            $jthis.find('.badge').removeClassPrefix('bg-color-');
            $jthis.find('.badge').text("0");
            // console.log("Ajax call for activity")
        }

        if (!$jthis.next('.ajax-dropdown').is(':visible')) {
            $jthis.next('.ajax-dropdown').fadeIn(150);
            $jthis.addClass('active');
        } else {
            $jthis.next('.ajax-dropdown').fadeOut(150);
            $jthis.removeClass('active')
        }

        var mytest = $jthis.next('.ajax-dropdown').find('.btn-group > .active > input').attr('id');
        //console.log(mytest)

        e.preventDefault();
    });

    $j('input[name="activity"]').change(function () {
        //alert($j(this).val())
        var $jthis = $j(this);

        url = $jthis.attr('id');
        container = $j('.ajax-notifications');

        loadURL(url, container);

    });

    $j(document).mouseup(function (e) {
        if (!$j('.ajax-dropdown').is(e.target)// if the target of the click isn't the container...
		&& $j('.ajax-dropdown').has(e.target).length === 0) {
            $j('.ajax-dropdown').fadeOut(150);
            $j('.ajax-dropdown').prev().removeClass("active")
        }
    });

    $j('button[data-loading-text]').on('click', function () {
        var btn = $j(this)
        btn.button('loading')
        setTimeout(function () {
            btn.button('reset')
        }, 3000)
    });

    // NOTIFICATION IS PRESENT

    function notification_check() {
        $jthis = $j('#activity > .badge');

        if (parseInt($jthis.text()) > 0) {
            $jthis.addClass("bg-color-red bounceIn animated")
        }
    }

    notification_check();

    // RESET WIDGETS
    $j('#refresh').click(function (e) {
        $j.SmartMessageBox({
            title: "<i class='fa fa-refresh' style='color:green'></i> Clear Local Storage",
            content: "Would you like to RESET all your saved widgets and clear LocalStorage?",
            buttons: '[No][Yes]'
        }, function (ButtonPressed) {
            if (ButtonPressed == "Yes" && localStorage) {
                localStorage.clear();
                location.reload();
            }

        });
        e.preventDefault();
    });

    // LOGOUT BUTTON
    $j('#logout a').click(function (e) {
        //get the link
        var $jthis = $j(this);
        $j.loginURL = $jthis.attr('href');
        $j.logoutMSG = $jthis.data('logout-msg');

        // ask verification
        $j.SmartMessageBox({
            title: "<i class='fa fa-sign-out txt-color-orangeDark'></i> Logout <span class='txt-color-orangeDark'><strong>" + $j('#show-shortcut').text() + "</strong></span> ?",
            content: $j.logoutMSG || "You can improve your security further after logging out by closing this opened browser",
            buttons: '[No][Yes]'

        }, function (ButtonPressed) {
            if (ButtonPressed == "Yes") {
                $j.root_.addClass('animated fadeOutUp');
                setTimeout(logout, 1000)
            }

        });
        e.preventDefault();
    });

    /*
	 * LOGOUT ACTION
	 */

    function logout() {
        window.location = $j.loginURL;
    }

    /*
	* SHORTCUTS
	*/

    // SHORT CUT (buttons that appear when clicked on user name)
    $j.shortcut_dropdown.find('a').click(function (e) {

        e.preventDefault();

        window.location = $j(this).attr('href');
        setTimeout(shortcut_buttons_hide, 300);

    });

    // SHORTCUT buttons goes away if mouse is clicked outside of the area
    $j(document).mouseup(function (e) {
        if (!$j.shortcut_dropdown.is(e.target)// if the target of the click isn't the container...
		&& $j.shortcut_dropdown.has(e.target).length === 0) {
            shortcut_buttons_hide()
        }
    });

    // SHORTCUT ANIMATE HIDE
    function shortcut_buttons_hide() {
        $j.shortcut_dropdown.animate({
            height: "hide"
        }, 300, "easeOutCirc");
        $j.root_.removeClass('shortcut-on');

    }

    // SHORTCUT ANIMATE SHOW
    function shortcut_buttons_show() {
        $j.shortcut_dropdown.animate({
            height: "show"
        }, 200, "easeOutCirc")
        $j.root_.addClass('shortcut-on');
    }

});

/*
 * RESIZER WITH THROTTLE
 * Source: http://benalman.com/code/projects/jquery-resize/examples/resize/
 */

(function ($j, window, undefined) {

    var elems = $j([]), jq_resize = $j.resize = $j.extend($j.resize, {}), timeout_id, str_setTimeout = 'setTimeout', str_resize = 'resize', str_data = str_resize + '-special-event', str_delay = 'delay', str_throttle = 'throttleWindow';

    jq_resize[str_delay] = $j.throttle_delay;

    jq_resize[str_throttle] = true;

    $j.event.special[str_resize] = {

        setup: function () {
            if (!jq_resize[str_throttle] && this[str_setTimeout]) {
                return false;
            }

            var elem = $j(this);
            elems = elems.add(elem);
            $j.data(this, str_data, {
                w: elem.width(),
                h: elem.height()
            });
            if (elems.length === 1) {
                loopy();
            }
        },
        teardown: function () {
            if (!jq_resize[str_throttle] && this[str_setTimeout]) {
                return false;
            }

            var elem = $j(this);
            elems = elems.not(elem);
            elem.removeData(str_data);
            if (!elems.length) {
                clearTimeout(timeout_id);
            }
        },

        add: function (handleObj) {
            if (!jq_resize[str_throttle] && this[str_setTimeout]) {
                return false;
            }
            var old_handler;

            function new_handler(e, w, h) {
                var elem = $j(this), data = $j.data(this, str_data);
                data.w = w !== undefined ? w : elem.width();
                data.h = h !== undefined ? h : elem.height();

                old_handler.apply(this, arguments);
            };
            if ($j.isFunction(handleObj)) {
                old_handler = handleObj;
                return new_handler;
            } else {
                old_handler = handleObj.handler;
                handleObj.handler = new_handler;
            }
        }
    };

    function loopy() {
        timeout_id = window[str_setTimeout](function () {
            elems.each(function () {
                var elem = $j(this), width = elem.width(), height = elem.height(), data = $j.data(this, str_data);
                if (width !== data.w || height !== data.h) {
                    elem.trigger(str_resize, [data.w = width, data.h = height]);
                }

            });
            loopy();

        }, jq_resize[str_delay]);

    };

})(jQuery, this);

/*
* NAV OR #LEFT-BAR RESIZE DETECT
* Description: changes the page min-width of #CONTENT and NAV when navigation is resized.
* This is to counter bugs for min page width on many desktop and mobile devices.
* Note: This script uses JSthrottle technique so don't worry about memory/CPU usage
*/

// Fix page and nav height
function nav_page_height() {
    var setHeight = $j('#main').height();
    //menuHeight = $j.left_panel.height();

    var windowHeight = $j(window).height() - $j.navbar_height;
    //set height

    if (setHeight > windowHeight) {// if content height exceedes actual window height and menuHeight
        $j.left_panel.css('min-height', setHeight + 'px');
        $j.root_.css('min-height', setHeight + $j.navbar_height + 'px');

    } else {
        $j.left_panel.css('min-height', windowHeight + 'px');
        $j.root_.css('min-height', windowHeight + 'px');
    }
}

$j('#main').resize(function () {
    nav_page_height();
    check_if_mobile_width();
})

$j('nav').resize(function () {
    nav_page_height();
})

function check_if_mobile_width() {
    if ($j(window).width() < 979) {
        $j.root_.addClass('mobile-view-activated')
    } else if ($j.root_.hasClass('mobile-view-activated')) {
        $j.root_.removeClass('mobile-view-activated');
    }
}

/* ~ END: NAV OR #LEFT-BAR RESIZE DETECT */

/*
 * DETECT IE VERSION
 * Description: A short snippet for detecting versions of IE in JavaScript
 * without resorting to user-agent sniffing
 * RETURNS:
 * If you're not in IE (or IE version is less than 5) then:
 * //ie === undefined
 *
 * If you're in IE (>=5) then you can determine which version:
 * // ie === 7; // IE7
 *
 * Thus, to detect IE:
 * // if (ie) {}
 *
 * And to detect the version:
 * ie === 6 // IE6
 * ie > 7 // IE8, IE9 ...
 * ie < 9 // Anything less than IE9
 */

// TODO: delete this function later on - no longer needed (?)
var ie = (function () {

    var undef, v = 3, div = document.createElement('div'), all = div.getElementsByTagName('i');

    while (div.innerHTML = '<!--[if gt IE ' + (++v) + ']><i></i><![endif]-->', all[0]);

    return v > 4 ? v : undef;

}()); // do we need this? 

/* ~ END: DETECT IE VERSION */

/*
 * CUSTOM MENU PLUGIN
 */

$j.fn.extend({

    //pass the options variable to the function
    jarvismenu: function (options) {

        var defaults = {
            accordion: 'true',
            speed: 200,
            closedSign: '[+]',
            openedSign: '[-]'
        };

        // Extend our default options with those provided.
        var opts = $j.extend(defaults, options);
        //Assign current element to variable, in this case is UL element
        var $jthis = $j(this);

        //add a mark [+] to a multilevel menu
        $jthis.find("li").each(function () {
            if ($j(this).find("ul").size() != 0) {
                //add the multilevel sign next to the link
                $j(this).find("a:first").append("<b class='collapse-sign'>" + opts.closedSign + "</b>");

                //avoid jumping to the top of the page when the href is an #
                if ($j(this).find("a:first").attr('href') == "#") {
                    $j(this).find("a:first").click(function () {
                        return false;
                    });
                }
            }
        });

        //open active level
        $jthis.find("li.active").each(function () {
            $j(this).parents("ul").slideDown(opts.speed);
            $j(this).parents("ul").parent("li").find("b:first").html(opts.openedSign);
            $j(this).parents("ul").parent("li").addClass("open")
        });

        $jthis.find("li a").click(function () {

            if ($j(this).parent().find("ul").size() != 0) {

                if (opts.accordion) {
                    //Do nothing when the list is open
                    if (!$j(this).parent().find("ul").is(':visible')) {
                        parents = $j(this).parent().parents("ul");
                        visible = $jthis.find("ul:visible");
                        visible.each(function (visibleIndex) {
                            var close = true;
                            parents.each(function (parentIndex) {
                                if (parents[parentIndex] == visible[visibleIndex]) {
                                    close = false;
                                    return false;
                                }
                            });
                            if (close) {
                                if ($j(this).parent().find("ul") != visible[visibleIndex]) {
                                    $j(visible[visibleIndex]).slideUp(opts.speed, function () {
                                        $j(this).parent("li").find("b:first").html(opts.closedSign);
                                        $j(this).parent("li").removeClass("open");
                                    });

                                }
                            }
                        });
                    }
                }// end if
                if ($j(this).parent().find("ul:first").is(":visible") && !$j(this).parent().find("ul:first").hasClass("active")) {
                    $j(this).parent().find("ul:first").slideUp(opts.speed, function () {
                        $j(this).parent("li").removeClass("open");
                        $j(this).parent("li").find("b:first").delay(opts.speed).html(opts.closedSign);
                    });

                } else {
                    $j(this).parent().find("ul:first").slideDown(opts.speed, function () {
                        /*$j(this).effect("highlight", {color : '#616161'}, 500); - disabled due to CPU clocking on phones*/
                        $j(this).parent("li").addClass("open");
                        $j(this).parent("li").find("b:first").delay(opts.speed).html(opts.openedSign);
                    });
                } // end else
            } // end if
        });
    } // end function
});

/* ~ END: CUSTOM MENU PLUGIN */

/*
 * ELEMENT EXIST OR NOT
 * Description: returns true or false
 * Usage: $j('#myDiv').doesExist();
 */

jQuery.fn.doesExist = function () {
    return jQuery(this).length > 0;
};

/* ~ END: ELEMENT EXIST OR NOT */

/*
 * FULL SCREEN FUNCTION
 */

// Find the right method, call on correct element
function launchFullscreen(element) {

    if (!$j.root_.hasClass("full-screen")) {

        $j.root_.addClass("full-screen");

        if (element.requestFullscreen) {
            element.requestFullscreen();
        } else if (element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
        } else if (element.webkitRequestFullscreen) {
            element.webkitRequestFullscreen();
        } else if (element.msRequestFullscreen) {
            element.msRequestFullscreen();
        }

    } else {

        $j.root_.removeClass("full-screen");

        if (document.exitFullscreen) {
            document.exitFullscreen();
        } else if (document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
        } else if (document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
        }

    }

}

/*
 * ~ END: FULL SCREEN FUNCTION
 */

/*
 * INITIALIZE FORMS
 * Description: Select2, Masking, Datepicker, Autocomplete
 */

function runAllForms() {

    /*
	 * BOOTSTRAP SLIDER PLUGIN
	 * Usage:
	 * Dependency: js/plugin/bootstrap-slider
	 */
    if ($j.fn.slider) {
        $j('.slider').slider();
    }

    /*
	 * SELECT2 PLUGIN
	 * Usage:
	 * Dependency: js/plugin/select2/
	 */
    if ($j.fn.select2) {
        $j('.select2').each(function () {
            var $jthis = $j(this);
            var width = $jthis.attr('data-select-width') || '100%';
            //, _showSearchInput = $jthis.attr('data-select-search') === 'true';
            $jthis.select2({
                //showSearchInput : _showSearchInput,
                allowClear: true,
                width: width
            })
        })
    }

    /*
	 * MASKING
	 * Dependency: js/plugin/masked-input/
	 */
    if ($j.fn.mask) {
        $j('[data-mask]').each(function () {

            var $jthis = $j(this);
            var mask = $jthis.attr('data-mask') || 'error...', mask_placeholder = $jthis.attr('data-mask-placeholder') || 'X';

            $jthis.mask(mask, {
                placeholder: mask_placeholder
            });
        })
    }

    /*
	 * Autocomplete
	 * Dependency: js/jqui
	 */
    if ($j.fn.autocomplete) {
        $j('[data-autocomplete]').each(function () {

            var $jthis = $j(this);
            var availableTags = $jthis.data('autocomplete') || ["The", "Quick", "Brown", "Fox", "Jumps", "Over", "Three", "Lazy", "Dogs"];

            $jthis.autocomplete({
                source: availableTags
            });
        })
    }

    /*
	 * JQUERY UI DATE
	 * Dependency: js/libs/jquery-ui-1.10.3.min.js
	 * Usage:
	 */
    if ($j.fn.datepicker) {
        $j('.datepicker').each(function () {

            var $jthis = $j(this);
            var dataDateFormat = $jthis.attr('data-dateformat') || 'dd.mm.yy';

            $jthis.datepicker({
                dateFormat: dataDateFormat,
                prevText: '<i class="fa fa-chevron-left"></i>',
                nextText: '<i class="fa fa-chevron-right"></i>'
            });
        })
    }

    /*
	 * AJAX BUTTON LOADING TEXT
	 * Usage: <button type="button" data-loading-text="Loading..." class="btn btn-xs btn-default ajax-refresh"> .. </button>
	 */
    $j('button[data-loading-text]').on('click', function () {
        var btn = $j(this)
        btn.button('loading')
        setTimeout(function () {
            btn.button('reset')
        }, 3000)
    });

}

/* ~ END: INITIALIZE FORMS */

/*
 * INITIALIZE CHARTS
 * Description: Sparklines, PieCharts
 */

function runAllCharts() {
    /*
	 * SPARKLINES
	 * DEPENDENCY: js/plugins/sparkline/jquery.sparkline.min.js
	 * See usage example below...
	 */

    /* Usage:
	 * 		<div class="sparkline-line txt-color-blue" data-fill-color="transparent" data-sparkline-height="26px">
	 *			5,6,7,9,9,5,9,6,5,6,6,7,7,6,7,8,9,7
	 *		</div>
	 */

    if ($j.fn.sparkline) {

        $j('.sparkline').each(function () {
            var $jthis = $j(this);
            var sparklineType = $jthis.data('sparkline-type') || 'bar';

            // BAR CHART
            if (sparklineType == 'bar') {

                var barColor = $jthis.data('sparkline-bar-color') || $jthis.css('color') || '#0000f0', sparklineHeight = $jthis.data('sparkline-height') || '26px', sparklineBarWidth = $jthis.data('sparkline-barwidth') || 5, sparklineBarSpacing = $jthis.data('sparkline-barspacing') || 2, sparklineNegBarColor = $jthis.data('sparkline-negbar-color') || '#A90329', sparklineStackedColor = $jthis.data('sparkline-barstacked-color') || ["#A90329", "#0099c6", "#98AA56", "#da532c", "#4490B1", "#6E9461", "#990099", "#B4CAD3"];

                $jthis.sparkline('html', {
                    type: 'bar',
                    barColor: barColor,
                    type: sparklineType,
                    height: sparklineHeight,
                    barWidth: sparklineBarWidth,
                    barSpacing: sparklineBarSpacing,
                    stackedBarColor: sparklineStackedColor,
                    negBarColor: sparklineNegBarColor,
                    zeroAxis: 'false'
                });

            }

            //LINE CHART
            if (sparklineType == 'line') {

                var sparklineHeight = $jthis.data('sparkline-height') || '20px', sparklineWidth = $jthis.data('sparkline-width') || '90px', thisLineColor = $jthis.data('sparkline-line-color') || $jthis.css('color') || '#0000f0', thisLineWidth = $jthis.data('sparkline-line-width') || 1, thisFill = $jthis.data('fill-color') || '#c0d0f0', thisSpotColor = $jthis.data('sparkline-spot-color') || '#f08000', thisMinSpotColor = $jthis.data('sparkline-minspot-color') || '#ed1c24', thisMaxSpotColor = $jthis.data('sparkline-maxspot-color') || '#f08000', thishighlightSpotColor = $jthis.data('sparkline-highlightspot-color') || '#50f050', thisHighlightLineColor = $jthis.data('sparkline-highlightline-color') || 'f02020', thisSpotRadius = $jthis.data('sparkline-spotradius') || 1.5;
                thisChartMinYRange = $jthis.data('sparkline-min-y') || 'undefined', thisChartMaxYRange = $jthis.data('sparkline-max-y') || 'undefined', thisChartMinXRange = $jthis.data('sparkline-min-x') || 'undefined', thisChartMaxXRange = $jthis.data('sparkline-max-x') || 'undefined', thisMinNormValue = $jthis.data('min-val') || 'undefined', thisMaxNormValue = $jthis.data('max-val') || 'undefined', thisNormColor = $jthis.data('norm-color') || '#c0c0c0', thisDrawNormalOnTop = $jthis.data('draw-normal') || false;

                $jthis.sparkline('html', {
                    type: 'line',
                    width: sparklineWidth,
                    height: sparklineHeight,
                    lineWidth: thisLineWidth,
                    lineColor: thisLineColor,
                    fillColor: thisFill,
                    spotColor: thisSpotColor,
                    minSpotColor: thisMinSpotColor,
                    maxSpotColor: thisMaxSpotColor,
                    highlightSpotColor: thishighlightSpotColor,
                    highlightLineColor: thisHighlightLineColor,
                    spotRadius: thisSpotRadius,
                    chartRangeMin: thisChartMinYRange,
                    chartRangeMax: thisChartMaxYRange,
                    chartRangeMinX: thisChartMinXRange,
                    chartRangeMaxX: thisChartMaxXRange,
                    normalRangeMin: thisMinNormValue,
                    normalRangeMax: thisMaxNormValue,
                    normalRangeColor: thisNormColor,
                    drawNormalOnTop: thisDrawNormalOnTop

                });

            }

            //PIE CHART
            if (sparklineType == 'pie') {

                var pieColors = $jthis.data('sparkline-piecolor') || ["#B4CAD3", "#4490B1", "#98AA56", "#da532c", "#6E9461", "#0099c6", "#990099", "#717D8A"], pieWidthHeight = $jthis.data('sparkline-piesize') || 90, pieBorderColor = $jthis.data('border-color') || '#45494C', pieOffset = $jthis.data('sparkline-offset') || 0;

                $jthis.sparkline('html', {
                    type: 'pie',
                    width: pieWidthHeight,
                    height: pieWidthHeight,
                    tooltipFormat: '<span style="color: {{color}}">&#9679;</span> ({{percent.1}}%)',
                    sliceColors: pieColors,
                    offset: 0,
                    borderWidth: 1,
                    offset: pieOffset,
                    borderColor: pieBorderColor
                });

            }

            //BOX PLOT
            if (sparklineType == 'box') {

                var thisBoxWidth = $jthis.data('sparkline-width') || 'auto', thisBoxHeight = $jthis.data('sparkline-height') || 'auto', thisBoxRaw = $jthis.data('sparkline-boxraw') || false, thisBoxTarget = $jthis.data('sparkline-targetval') || 'undefined', thisBoxMin = $jthis.data('sparkline-min') || 'undefined', thisBoxMax = $jthis.data('sparkline-max') || 'undefined', thisShowOutlier = $jthis.data('sparkline-showoutlier') || true, thisIQR = $jthis.data('sparkline-outlier-iqr') || 1.5, thisBoxSpotRadius = $jthis.data('sparkline-spotradius') || 1.5, thisBoxLineColor = $jthis.css('color') || '#000000', thisBoxFillColor = $jthis.data('fill-color') || '#c0d0f0', thisBoxWhisColor = $jthis.data('sparkline-whis-color') || '#000000', thisBoxOutlineColor = $jthis.data('sparkline-outline-color') || '#303030', thisBoxOutlineFill = $jthis.data('sparkline-outlinefill-color') || '#f0f0f0', thisBoxMedianColor = $jthis.data('sparkline-outlinemedian-color') || '#f00000', thisBoxTargetColor = $jthis.data('sparkline-outlinetarget-color') || '#40a020';

                $jthis.sparkline('html', {
                    type: 'box',
                    width: thisBoxWidth,
                    height: thisBoxHeight,
                    raw: thisBoxRaw,
                    target: thisBoxTarget,
                    minValue: thisBoxMin,
                    maxValue: thisBoxMax,
                    showOutliers: thisShowOutlier,
                    outlierIQR: thisIQR,
                    spotRadius: thisBoxSpotRadius,
                    boxLineColor: thisBoxLineColor,
                    boxFillColor: thisBoxFillColor,
                    whiskerColor: thisBoxWhisColor,
                    outlierLineColor: thisBoxOutlineColor,
                    outlierFillColor: thisBoxOutlineFill,
                    medianColor: thisBoxMedianColor,
                    targetColor: thisBoxTargetColor

                })

            }

            //BULLET
            if (sparklineType == 'bullet') {

                var thisBulletHeight = $jthis.data('sparkline-height') || 'auto', thisBulletWidth = $jthis.data('sparkline-width') || 2, thisBulletColor = $jthis.data('sparkline-bullet-color') || '#ed1c24', thisBulletPerformanceColor = $jthis.data('sparkline-performance-color') || '#3030f0', thisBulletRangeColors = $jthis.data('sparkline-bulletrange-color') || ["#d3dafe", "#a8b6ff", "#7f94ff"]

                $jthis.sparkline('html', {

                    type: 'bullet',
                    height: thisBulletHeight,
                    targetWidth: thisBulletWidth,
                    targetColor: thisBulletColor,
                    performanceColor: thisBulletPerformanceColor,
                    rangeColors: thisBulletRangeColors

                })

            }

            //DISCRETE
            if (sparklineType == 'discrete') {

                var thisDiscreteHeight = $jthis.data('sparkline-height') || 26, thisDiscreteWidth = $jthis.data('sparkline-width') || 50, thisDiscreteLineColor = $jthis.css('color'), thisDiscreteLineHeight = $jthis.data('sparkline-line-height') || 5, thisDiscreteThrushold = $jthis.data('sparkline-threshold') || 'undefined', thisDiscreteThrusholdColor = $jthis.data('sparkline-threshold-color') || '#ed1c24';

                $jthis.sparkline('html', {

                    type: 'discrete',
                    width: thisDiscreteWidth,
                    height: thisDiscreteHeight,
                    lineColor: thisDiscreteLineColor,
                    lineHeight: thisDiscreteLineHeight,
                    thresholdValue: thisDiscreteThrushold,
                    thresholdColor: thisDiscreteThrusholdColor

                })

            }

            //TRISTATE
            if (sparklineType == 'tristate') {

                var thisTristateHeight = $jthis.data('sparkline-height') || 26, thisTristatePosBarColor = $jthis.data('sparkline-posbar-color') || '#60f060', thisTristateNegBarColor = $jthis.data('sparkline-negbar-color') || '#f04040', thisTristateZeroBarColor = $jthis.data('sparkline-zerobar-color') || '#909090', thisTristateBarWidth = $jthis.data('sparkline-barwidth') || 5, thisTristateBarSpacing = $jthis.data('sparkline-barspacing') || 2, thisZeroAxis = $jthis.data('sparkline-zeroaxis') || false;

                $jthis.sparkline('html', {

                    type: 'tristate',
                    height: thisTristateHeight,
                    posBarColor: thisBarColor,
                    negBarColor: thisTristateNegBarColor,
                    zeroBarColor: thisTristateZeroBarColor,
                    barWidth: thisTristateBarWidth,
                    barSpacing: thisTristateBarSpacing,
                    zeroAxis: thisZeroAxis

                })

            }

            //COMPOSITE: BAR
            if (sparklineType == 'compositebar') {

                var sparklineHeight = $jthis.data('sparkline-height') || '20px', sparklineWidth = $jthis.data('sparkline-width') || '100%', sparklineBarWidth = $jthis.data('sparkline-barwidth') || 3, thisLineWidth = $jthis.data('sparkline-line-width') || 1, thisLineColor = $jthis.data('sparkline-color-top') || '#ed1c24', thisBarColor = $jthis.data('sparkline-color-bottom') || '#333333'

                $jthis.sparkline($jthis.data('sparkline-bar-val'), {

                    type: 'bar',
                    width: sparklineWidth,
                    height: sparklineHeight,
                    barColor: thisBarColor,
                    barWidth: sparklineBarWidth
                    //barSpacing: 5

                })

                $jthis.sparkline($jthis.data('sparkline-line-val'), {

                    width: sparklineWidth,
                    height: sparklineHeight,
                    lineColor: thisLineColor,
                    lineWidth: thisLineWidth,
                    composite: true,
                    fillColor: false

                })

            }

            //COMPOSITE: LINE
            if (sparklineType == 'compositeline') {

                var sparklineHeight = $jthis.data('sparkline-height') || '20px', sparklineWidth = $jthis.data('sparkline-width') || '90px', sparklineValue = $jthis.data('sparkline-bar-val'), sparklineValueSpots1 = $jthis.data('sparkline-bar-val-spots-top') || null, sparklineValueSpots2 = $jthis.data('sparkline-bar-val-spots-bottom') || null, thisLineWidth1 = $jthis.data('sparkline-line-width-top') || 1, thisLineWidth2 = $jthis.data('sparkline-line-width-bottom') || 1, thisLineColor1 = $jthis.data('sparkline-color-top') || '#333333', thisLineColor2 = $jthis.data('sparkline-color-bottom') || '#ed1c24', thisSpotRadius1 = $jthis.data('sparkline-spotradius-top') || 1.5, thisSpotRadius2 = $jthis.data('sparkline-spotradius-bottom') || thisSpotRadius1, thisSpotColor = $jthis.data('sparkline-spot-color') || '#f08000', thisMinSpotColor1 = $jthis.data('sparkline-minspot-color-top') || '#ed1c24', thisMaxSpotColor1 = $jthis.data('sparkline-maxspot-color-top') || '#f08000', thisMinSpotColor2 = $jthis.data('sparkline-minspot-color-bottom') || thisMinSpotColor1, thisMaxSpotColor2 = $jthis.data('sparkline-maxspot-color-bottom') || thisMaxSpotColor1, thishighlightSpotColor1 = $jthis.data('sparkline-highlightspot-color-top') || '#50f050', thisHighlightLineColor1 = $jthis.data('sparkline-highlightline-color-top') || '#f02020', thishighlightSpotColor2 = $jthis.data('sparkline-highlightspot-color-bottom') || thishighlightSpotColor1, thisHighlightLineColor2 = $jthis.data('sparkline-highlightline-color-bottom') || thisHighlightLineColor1, thisFillColor1 = $jthis.data('sparkline-fillcolor-top') || 'transparent', thisFillColor2 = $jthis.data('sparkline-fillcolor-bottom') || 'transparent';

                $jthis.sparkline(sparklineValue, {

                    type: 'line',
                    spotRadius: thisSpotRadius1,

                    spotColor: thisSpotColor,
                    minSpotColor: thisMinSpotColor1,
                    maxSpotColor: thisMaxSpotColor1,
                    highlightSpotColor: thishighlightSpotColor1,
                    highlightLineColor: thisHighlightLineColor1,

                    valueSpots: sparklineValueSpots1,

                    lineWidth: thisLineWidth1,
                    width: sparklineWidth,
                    height: sparklineHeight,
                    lineColor: thisLineColor1,
                    fillColor: thisFillColor1

                })

                $jthis.sparkline($jthis.data('sparkline-line-val'), {

                    type: 'line',
                    spotRadius: thisSpotRadius2,

                    spotColor: thisSpotColor,
                    minSpotColor: thisMinSpotColor2,
                    maxSpotColor: thisMaxSpotColor2,
                    highlightSpotColor: thishighlightSpotColor2,
                    highlightLineColor: thisHighlightLineColor2,

                    valueSpots: sparklineValueSpots2,

                    lineWidth: thisLineWidth2,
                    width: sparklineWidth,
                    height: sparklineHeight,
                    lineColor: thisLineColor2,
                    composite: true,
                    fillColor: thisFillColor2

                })

            }

        });

    }// end if

    /*
	 * EASY PIE CHARTS
	 * DEPENDENCY: js/plugins/easy-pie-chart/jquery.easy-pie-chart.min.js
	 * Usage: <div class="easy-pie-chart txt-color-orangeDark" data-pie-percent="33" data-pie-size="72" data-size="72">
	 *			<span class="percent percent-sign">35</span>
	 * 	  	  </div>
	 */

    if ($j.fn.easyPieChart) {

        $j('.easy-pie-chart').each(function () {
            var $jthis = $j(this);
            var barColor = $jthis.css('color') || $jthis.data('pie-color'), trackColor = $jthis.data('pie-track-color') || '#eeeeee', size = parseInt($jthis.data('pie-size')) || 25;
            $jthis.easyPieChart({
                barColor: barColor,
                trackColor: trackColor,
                scaleColor: false,
                lineCap: 'butt',
                lineWidth: parseInt(size / 8.5),
                animate: 1500,
                rotate: -90,
                size: size,
                onStep: function (value) {
                    this.$jel.find('span').text(~~value);
                }
            });
        });

    } // end if

}

/* ~ END: INITIALIZE CHARTS */

/*
 * INITIALIZE JARVIS WIDGETS
 */

// Setup Desktop Widgets
function setup_widgets_desktop() {

    if ($j.fn.jarvisWidgets && $j.enableJarvisWidgets) {

        $j('#widget-leads-grid').jarvisWidgets({

            grid: 'article',
            widgets: '.jarviswidget',
            localStorage: true,
            deleteSettingsKey: '#deletesettingskey-options',
            settingsKeyLabel: 'Reset settings?',
            deletePositionKey: '#deletepositionkey-options',
            positionKeyLabel: 'Reset position?',
            sortable: true,
            buttonsHidden: false,
            // toggle button
            toggleButton: true,
            toggleClass: 'fa fa-minus | fa fa-plus',
            toggleSpeed: 200,
            onToggle: function () {
            },
            // delete btn
            deleteButton: false,
            deleteClass: 'fa fa-times',
            deleteSpeed: 200,
            onDelete: function () {
            },
            // edit btn
            editButton: true,
            editPlaceholder: '.jarviswidget-editbox',
            editClass: 'fa fa-cog | fa fa-save',
            editSpeed: 200,
            onEdit: function () {
            },
            // color button
            colorButton: true,
            // full screen
            fullscreenButton: true,
            fullscreenClass: 'fa fa-resize-full | fa fa-resize-small',
            fullscreenDiff: 3,
            onFullscreen: function () {
            },
            // custom btn
            customButton: false,
            customClass: 'folder-10 | next-10',
            customStart: function () {
                alert('Hello you, this is a custom button...')
            },
            customEnd: function () {
                alert('bye, till next time...')
            },
            // order
            buttonOrder: '%refresh% %custom% %edit% %toggle% %fullscreen% %delete%',
            opacity: 1.0,
            dragHandle: '> header',
            placeholderClass: 'jarviswidget-placeholder',
            indicator: true,
            indicatorTime: 600,
            ajax: true,
            timestampPlaceholder: '.jarviswidget-timestamp',
            timestampFormat: 'Last update: %m%/%d%/%y% %h%:%i%:%s%',
            refreshButton: true,
            refreshButtonClass: 'fa fa-refresh',
            labelError: 'Sorry but there was a error:',
            labelUpdated: 'Last Update:',
            labelRefresh: 'Refresh',
            labelDelete: 'Delete widget:',
            afterLoad: function () {
            },
            rtl: false, // best not to toggle this!
            onChange: function () {

            },
            onSave: function () {

            },
            ajaxnav: $j.navAsAjax // declears how the localstorage should be saved

        });



    }

}

// Setup Desktop Widgets
function setup_widgets_mobile() {

    if ($j.enableMobileWidgets && $j.enableJarvisWidgets) {
        setup_widgets_desktop();
    }

}

/* ~ END: INITIALIZE JARVIS WIDGETS */

/*
 * GOOGLE MAPS
 * description: Append google maps to head dynamically
 */

var gMapsLoaded = false;
window.gMapsCallback = function () {
    gMapsLoaded = true;
    $j(window).trigger('gMapsLoaded');
}
window.loadGoogleMaps = function () {
    if (gMapsLoaded)
        return window.gMapsCallback();
    var script_tag = document.createElement('script');
    script_tag.setAttribute("type", "text/javascript");
    script_tag.setAttribute("src", "http://maps.google.com/maps/api/js?sensor=false&callback=gMapsCallback");
    (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
}
/* ~ END: GOOGLE MAPS */

/*
 * LOAD SCRIPTS
 * Usage:
 * Define function = myPrettyCode ()...
 * loadScript("js/my_lovely_script.js", myPrettyCode);
 */

var jsArray = {};

function loadScript(scriptName, callback) {

    if (!jsArray[scriptName]) {
        jsArray[scriptName] = true;

        // adding the script tag to the head as suggested before
        var body = document.getElementsByTagName('body')[0];
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = scriptName;

        // then bind the event to the callback function
        // there are several events for cross browser compatibility
        //script.onreadystatechange = callback;
        script.onload = callback;

        // fire the loading
        body.appendChild(script);

    } else if (callback) {// changed else to else if(callback)
        //console.log("JS file already added!");
        //execute function
        callback();
    }

}

/* ~ END: LOAD SCRIPTS */

/*
* APP AJAX REQUEST SETUP
* Description: Executes and fetches all ajax requests also
* updates naivgation elements to active
*/
if ($j.navAsAjax) {
    // fire this on page load if nav exists
    if ($j('nav').length) {
        checkURL();
    };

    $j(document).on('click', 'nav a[href!="#"]', function (e) {
        e.preventDefault();
        var $jthis = $j(e.currentTarget);

        // if parent is not active then get hash, or else page is assumed to be loaded
        if (!$jthis.parent().hasClass("active") && !$jthis.attr('target')) {

            // update window with hash
            // you could also do here:  $j.device === "mobile" - and save a little more memory

            if ($j.root_.hasClass('mobile-view-activated')) {
                $j.root_.removeClass('hidden-menu');
                window.setTimeout(function () {
                    if (window.location.search) {
                        window.location.href =
							window.location.href.replace(window.location.search, '')
								.replace(window.location.hash, '') + '#' + $jthis.attr('href');
                    } else {
                        window.location.hash = $jthis.attr('href')
                    }
                }, 150);
                // it may not need this delay...
            } else {
                if (window.location.search) {
                    window.location.href =
						window.location.href.replace(window.location.search, '')
							.replace(window.location.hash, '') + '#' + $jthis.attr('href');
                } else {
                    window.location.hash = $jthis.attr('href');
                }
            }
        }

    });

    // fire links with targets on different window
    $j(document).on('click', 'nav a[target="_blank"]', function (e) {
        e.preventDefault();
        var $jthis = $j(e.currentTarget);

        window.open($jthis.attr('href'));
    });

    // fire links with targets on same window
    $j(document).on('click', 'nav a[target="_top"]', function (e) {
        e.preventDefault();
        var $jthis = $j(e.currentTarget);

        window.location = ($jthis.attr('href'));
    });

    // all links with hash tags are ignored
    $j(document).on('click', 'nav a[href="#"]', function (e) {
        e.preventDefault();
    });

    // DO on hash change
    $j(window).on('hashchange', function () {
        checkURL();
    });
}

// CHECK TO SEE IF URL EXISTS
function checkURL() {

    //get the url by removing the hash
    var url = location.hash.replace(/^#/, '');

    container = $j('#content');
    // Do this if url exists (for page refresh, etc...)
    if (url) {
        // remove all active class
        $j('nav li.active').removeClass("active");
        // match the url and add the active class
        $j('nav li:has(a[href="' + url + '"])').addClass("active");
        var title = ($j('nav a[href="' + url + '"]').attr('title'))

        // change page title from global var
        document.title = (title || document.title);
        //console.log("page title: " + document.title);

        // parse url to jquery
        loadURL(url + location.search, container);
    } else {

        // grab the first URL from nav
        var $jthis = $j('nav > ul > li:first-child > a[href!="#"]');

        //update hash
        window.location.hash = $jthis.attr('href');

    }

}

// LOAD AJAX PAGES

function loadURL(url, container) {
    //console.log(container)

    $j.ajax({
        type: "GET",
        url: url,
        dataType: 'html',
        cache: true, // (warning: this will cause a timestamp and will call the request twice)
        beforeSend: function () {
            // cog placed
            container.html('<h1><i class="fa fa-cog fa-spin"></i> Loading...</h1>');

            // Only draw breadcrumb if it is main content material
            // TODO: see the framerate for the animation in touch devices

            if (container[0] == $j("#content")[0]) {
                drawBreadCrumb();
                // scroll up
                $j("html").animate({
                    scrollTop: 0
                }, "fast");
            }
        },
        /*complete: function(){
	    	// Handle the complete event
	    	// alert("complete")
		},*/
        success: function (data) {
            // cog replaced here...
            // alert("success")

            container.css({
                opacity: '0.0'
            }).html(data).delay(50).animate({
                opacity: '1.0'
            }, 300);


        },
        error: function (xhr, ajaxOptions, thrownError) {
            container.html('<h4 style="margin-top:10px; display:block; text-align:left"><i class="fa fa-warning txt-color-orangeDark"></i> Error 404! Page not found.</h4>');
        },
        async: false
    });

    //console.log("ajax request sent");
}

// UPDATE BREADCRUMB
function drawBreadCrumb() {
    var nav_elems = $j('nav li.active > a'), count = nav_elems.length;

    //console.log("breadcrumb")
    $j.bread_crumb.empty();
    $j.bread_crumb.append($j("<li>Home</li>"));
    nav_elems.each(function () {
        $j.bread_crumb.append($j("<li></li>").html($j.trim($j(this).clone().children(".badge").remove().end().text())));
        // update title when breadcrumb is finished...
        if (!--count) document.title = $j.bread_crumb.find("li:last-child").text();
    });

}

/* ~ END: APP AJAX REQUEST SETUP */

/*
 * PAGE SETUP
 * Description: fire certain scripts that run through the page
 * to check for form elements, tooltip activation, popovers, etc...
 */
function pageSetUp() {

    if ($j.device === "desktop") {
        // is desktop

        $j("#tabs").tabs().addClass("ui-tabs-vertical ui-helper-clearfix").show();

        // activate tooltips
        if ($j.fn.tooltip) $j("[rel=tooltip]").tooltip();

        // activate popovers
        if ($j.fn.popover) {
            $j("[rel=popover]").popover();

            // activate popovers with hover states
            $j("[rel=popover-hover]").popover({
                trigger: "hover"
            });
        }

        // activate inline charts
        runAllCharts();

        // setup widgets
        setup_widgets_desktop();

        //setup nav height (dynamic)
        nav_page_height();

        // run form elements
        runAllForms();

    } else {

        // is mobile

        // activate popovers
        $j("[rel=popover]").popover();

        // activate popovers with hover states
        $j("[rel=popover-hover]").popover({
            trigger: "hover"
        });

        // activate inline charts
        runAllCharts();

        // setup widgets
        setup_widgets_mobile();

        //setup nav height (dynamic)
        nav_page_height();

        // run form elements
        runAllForms();

    }

}

// Keep only 1 active popover per trigger - also check and hide active popover if user clicks on document
$j('body').on('click', function (e) {
    $j('[rel="popover"]').each(function () {
        //the 'is' for buttons that trigger popups
        //the 'has' for icons within a button that triggers a popup
        if (!$j(this).is(e.target) && $j(this).has(e.target).length === 0 && $j('.popover').has(e.target).length === 0) {
            $j(this).popover('hide');
        }
    });
});
/*! SmartAdmin - v1.5 - 2014-09-27 */function SmartUnLoading() { $j(".divMessageBox").fadeOut(300, function () { $j(this).remove() }), $j(".LoadingBoxContainer").fadeOut(300, function () { $j(this).remove() }) } function getInternetExplorerVersion() { var a = -1; if ("Microsoft Internet Explorer" == navigator.appName) { var b = navigator.userAgent, c = new RegExp("MSIE ([0-9]{1,}[.0-9]{0,})"); null != c.exec(b) && (a = parseFloat(RegExp.$j1)) } return a } function checkVersion() { var a = "You're not using Windows Internet Explorer.", b = getInternetExplorerVersion(); b > -1 && (a = b >= 8 ? "You're using a recent copy of Windows Internet Explorer." : "You should upgrade your copy of Windows Internet Explorer."), alert(a) } function isIE8orlower() { var a = "0", b = getInternetExplorerVersion(); return b > -1 && (a = b >= 9 ? 0 : 1), a } jQuery(document).ready(function () { $j("body").append("<div id='divSmallBoxes'></div>"), $j("body").append("<div id='divMiniIcons'></div><div id='divbigBoxes'></div>") }); var ExistMsg = 0, SmartMSGboxCount = 0, PrevTop = 0; $j.SmartMessageBox = function (a, b) { var c, d; a = $j.extend({ "title": "", "content": "", "NormalButton": void 0, "ActiveButton": void 0, "buttons": void 0, "input": void 0, "inputValue": void 0, "placeholder": "", "options": void 0 }, a); var e = 0; if (e = 1, 0 == isIE8orlower() && $j.sound_on) { var f = document.createElement("audio"); f.setAttribute("src", $j.sound_path + "messagebox.mp3"), f.addEventListener("load", function () { f.play() }, !0), f.pause(), f.play() } SmartMSGboxCount += 1, 0 == ExistMsg && (ExistMsg = 1, c = "<div class='divMessageBox animated fadeIn fast' id='MsgBoxBack'></div>", $j("body").append(c), 1 == isIE8orlower() && $j("#MsgBoxBack").addClass("MessageIE")); var g = "", h = 0; if (void 0 != a.input) switch (h = 1, a.input = a.input.toLowerCase(), a.input) { case "text": a.inputValue = "string" === $j.type(a.inputValue) ? a.inputValue.replace(/'/g, "&#x27;") : a.inputValue, g = "<input class='form-control' type='" + a.input + "' id='txt" + SmartMSGboxCount + "' placeholder='" + a.placeholder + "' value='" + a.inputValue + "'/><br/><br/>"; break; case "password": g = "<input class='form-control' type='" + a.input + "' id='txt" + SmartMSGboxCount + "' placeholder='" + a.placeholder + "'/><br/><br/>"; break; case "select": if (void 0 == a.options) alert("For this type of input, the options parameter is required."); else { g = "<select class='form-control' id='txt" + SmartMSGboxCount + "'>"; for (var i = 0; i <= a.options.length - 1; i++) "[" == a.options[i] ? j = "" : "]" == a.options[i] ? (k += 1, j = "<option>" + j + "</option>", g += j) : j += a.options[i]; g += "</select>" } break; default: alert("That type of input is not handled yet") } d = "<div class='MessageBoxContainer animated fadeIn fast' id='Msg" + SmartMSGboxCount + "'>", d += "<div class='MessageBoxMiddle'>", d += "<span class='MsgTitle'>" + a.title + "</span class='MsgTitle'>", d += "<p class='pText'>" + a.content + "</p>", d += g, d += "<div class='MessageBoxButtonSection'>", void 0 == a.buttons && (a.buttons = "[Accept]"), a.buttons = $j.trim(a.buttons), a.buttons = a.buttons.split(""); var j = "", k = 0; void 0 == a.NormalButton && (a.NormalButton = "#232323"), void 0 == a.ActiveButton && (a.ActiveButton = "#ed145b"); for (var i = 0; i <= a.buttons.length - 1; i++) "[" == a.buttons[i] ? j = "" : "]" == a.buttons[i] ? (k += 1, j = "<button id='bot" + k + "-Msg" + SmartMSGboxCount + "' class='btn btn-default btn-sm botTempo'> " + j + "</button>", d += j) : j += a.buttons[i]; d += "</div>", d += "</div>", d += "</div>", SmartMSGboxCount > 1 && ($j(".MessageBoxContainer").hide(), $j(".MessageBoxContainer").css("z-index", 99999)), $j(".divMessageBox").append(d), 1 == h && $j("#txt" + SmartMSGboxCount).focus(), $j(".botTempo").hover(function () { $j(this).attr("id") }, function () { $j(this).attr("id") }), $j(".botTempo").click(function () { var a = $j(this).attr("id"), c = a.substr(a.indexOf("-") + 1), d = $j.trim($j(this).text()); if (1 == h) { if ("function" == typeof b) { var e = c.replace("Msg", ""), f = $j("#txt" + e).val(); b && b(d, f) } } else "function" == typeof b && b && b(d); $j("#" + c).addClass("animated fadeOut fast"), SmartMSGboxCount -= 1, 0 == SmartMSGboxCount && $j("#MsgBoxBack").removeClass("fadeIn").addClass("fadeOut").delay(300).queue(function () { ExistMsg = 0, $j(this).remove() }) }) }; var BigBoxes = 0; $j.bigBox = function (a, b) { var c; if (a = $j.extend({ "title": "", "content": "", "icon": void 0, "number": void 0, "color": void 0, "sound": $j.sound_on, "sound_file": "bigbox", "timeout": void 0, "colortime": 1500, "colors": void 0 }, a), a.sound && 0 == isIE8orlower()) { var d = document.createElement("audio"); navigator.userAgent.match("Firefox/") ? d.setAttribute("src", $j.sound_path + a.sound_file + ".ogg") : d.setAttribute("src", $j.sound_path + a.sound_file + ".mp3"), d.addEventListener("load", function () { d.play() }, !0), d.pause(), d.play() } BigBoxes += 1, c = "<div id='bigBox" + BigBoxes + "' class='bigBox animated fadeIn fast'><div id='bigBoxColor" + BigBoxes + "'><i class='botClose fa fa-times' id='botClose" + BigBoxes + "'></i>", c += "<span>" + a.title + "</span>", c += "<p>" + a.content + "</p>", c += "<div class='bigboxicon'>", void 0 == a.icon && (a.icon = "fa fa-cloud"), c += "<i class='" + a.icon + "'></i>", c += "</div>", c += "<div class='bigboxnumber'>", void 0 != a.number && (c += a.number), c += "</div></div>", c += "</div>", $j("#divbigBoxes").append(c), void 0 == a.color && (a.color = "#004d60"), $j("#bigBox" + BigBoxes).css("background-color", a.color), $j("#divMiniIcons").append("<div id='miniIcon" + BigBoxes + "' class='cajita animated fadeIn' style='background-color: " + a.color + ";'><i class='" + a.icon + "'/></i></div>"), $j("#miniIcon" + BigBoxes).bind("click", function () { var a = $j(this).attr("id"), b = a.replace("miniIcon", "bigBox"), c = a.replace("miniIcon", "bigBoxColor"); $j(".cajita").each(function () { var a = $j(this).attr("id"), b = a.replace("miniIcon", "bigBox"); $j("#" + b).css("z-index", 9998) }), $j("#" + b).css("z-index", 9999), $j("#" + c).removeClass("animated fadeIn").delay(1).queue(function () { $j(this).show(), $j(this).addClass("animated fadeIn"), $j(this).clearQueue() }) }); var e, f = $j("#botClose" + BigBoxes), g = $j("#bigBox" + BigBoxes), h = $j("#miniIcon" + BigBoxes); if (void 0 != a.colors && a.colors.length > 0 && (f.attr("colorcount", "0"), e = setInterval(function () { var b = f.attr("colorcount"); f.animate({ "backgroundColor": a.colors[b].color }), g.animate({ "backgroundColor": a.colors[b].color }), h.animate({ "backgroundColor": a.colors[b].color }), b < a.colors.length - 1 ? f.attr("colorcount", 1 * b + 1) : f.attr("colorcount", 0) }, a.colortime)), f.bind("click", function () { clearInterval(e), "function" == typeof b && b && b(); var a = $j(this).attr("id"), c = a.replace("botClose", "bigBox"), d = a.replace("botClose", "miniIcon"); $j("#" + c).removeClass("fadeIn fast"), $j("#" + c).addClass("fadeOut fast").delay(300).queue(function () { $j(this).clearQueue(), $j(this).remove() }), $j("#" + d).removeClass("fadeIn fast"), $j("#" + d).addClass("fadeOut fast").delay(300).queue(function () { $j(this).clearQueue(), $j(this).remove() }) }), void 0 != a.timeout) { var i = BigBoxes; setTimeout(function () { clearInterval(e), $j("#bigBox" + i).removeClass("fadeIn fast"), $j("#bigBox" + i).addClass("fadeOut fast").delay(300).queue(function () { $j(this).clearQueue(), $j(this).remove() }), $j("#miniIcon" + i).removeClass("fadeIn fast"), $j("#miniIcon" + i).addClass("fadeOut fast").delay(300).queue(function () { $j(this).clearQueue(), $j(this).remove() }) }, a.timeout) } }; var SmallBoxes = 0, SmallCount = 0, SmallBoxesAnchos = 0; $j.smallBox = function (a, b) { var c; if (a = $j.extend({ "title": "", "content": "", "icon": void 0, "iconSmall": void 0, "sound": $j.sound_on, "sound_file": "smallbox", "color": void 0, "timeout": void 0, "colortime": 1500, "colors": void 0 }, a), a.sound && 0 == isIE8orlower()) { var d = document.createElement("audio"); navigator.userAgent.match("Firefox/") ? d.setAttribute("src", $j.sound_path + a.sound_file + ".ogg") : d.setAttribute("src", $j.sound_path + a.sound_file + ".mp3"), d.addEventListener("load", function () { d.play() }, !0), d.pause(), d.play() } SmallBoxes += 1, c = ""; var e = "", f = "smallbox" + SmallBoxes; if (e = void 0 == a.iconSmall ? "<div class='miniIcono'></div>" : "<div class='miniIcono'><i class='miniPic " + a.iconSmall + "'></i></div>", c = void 0 == a.icon ? "<div id='smallbox" + SmallBoxes + "' class='SmallBox animated fadeInRight fast'><div class='textoFull'><span>" + a.title + "</span><p>" + a.content + "</p></div>" + e + "</div>" : "<div id='smallbox" + SmallBoxes + "' class='SmallBox animated fadeInRight fast'><div class='foto'><i class='" + a.icon + "'></i></div><div class='textoFoto'><span>" + a.title + "</span><p>" + a.content + "</p></div>" + e + "</div>", 1 == SmallBoxes) $j("#divSmallBoxes").append(c), SmallBoxesAnchos = $j("#smallbox" + SmallBoxes).height() + 40; else { var g = $j(".SmallBox").size(); 0 == g ? ($j("#divSmallBoxes").append(c), SmallBoxesAnchos = $j("#smallbox" + SmallBoxes).height() + 40) : ($j("#divSmallBoxes").append(c), $j("#smallbox" + SmallBoxes).css("top", SmallBoxesAnchos), SmallBoxesAnchos = SmallBoxesAnchos + $j("#smallbox" + SmallBoxes).height() + 20, $j(".SmallBox").each(function (a) { 0 == a ? ($j(this).css("top", 20), heightPrev = $j(this).height() + 40, SmallBoxesAnchos = $j(this).height() + 40) : ($j(this).css("top", heightPrev), heightPrev = heightPrev + $j(this).height() + 20, SmallBoxesAnchos = SmallBoxesAnchos + $j(this).height() + 20) })) } var h = $j("#smallbox" + SmallBoxes); void 0 == a.color ? h.css("background-color", "#004d60") : h.css("background-color", a.color); var i; void 0 != a.colors && a.colors.length > 0 && (h.attr("colorcount", "0"), i = setInterval(function () { var b = h.attr("colorcount"); h.animate({ "backgroundColor": a.colors[b].color }), b < a.colors.length - 1 ? h.attr("colorcount", 1 * b + 1) : h.attr("colorcount", 0) }, a.colortime)), void 0 != a.timeout && setTimeout(function () { clearInterval(i); { var a = $j(this).height() + 20; $j("#" + f).css("top") } 0 != $j("#" + f + ":hover").length ? $j("#" + f).on("mouseleave", function () { SmallBoxesAnchos -= a, $j("#" + f).remove(), "function" == typeof b && b && b(); var c = 0; $j(".SmallBox").each(function (a) { 0 == a ? ($j(this).animate({ "top": 20 }, 300), c = $j(this).height() + 40, SmallBoxesAnchos = $j(this).height() + 40) : ($j(this).animate({ "top": c }, 350), c = c + $j(this).height() + 20, SmallBoxesAnchos = SmallBoxesAnchos + $j(this).height() + 20) }) }) : (clearInterval(i), SmallBoxesAnchos -= a, "function" == typeof b && b && b(), $j("#" + f).removeClass().addClass("SmallBox").animate({ "opacity": 0 }, 300, function () { $j(this).remove(); var a = 0; $j(".SmallBox").each(function (b) { 0 == b ? ($j(this).animate({ "top": 20 }, 300), a = $j(this).height() + 40, SmallBoxesAnchos = $j(this).height() + 40) : ($j(this).animate({ "top": a }), a = a + $j(this).height() + 20, SmallBoxesAnchos = SmallBoxesAnchos + $j(this).height() + 20) }) })) }, a.timeout), $j("#smallbox" + SmallBoxes).bind("click", function () { clearInterval(i), "function" == typeof b && b && b(); { var a = $j(this).height() + 20; $j(this).attr("id"), $j(this).css("top") } SmallBoxesAnchos -= a, $j(this).removeClass().addClass("SmallBox").animate({ "opacity": 0 }, 300, function () { $j(this).remove(); var a = 0; $j(".SmallBox").each(function (b) { 0 == b ? ($j(this).animate({ "top": 20 }, 300), a = $j(this).height() + 40, SmallBoxesAnchos = $j(this).height() + 40) : ($j(this).animate({ "top": a }, 350), a = a + $j(this).height() + 20, SmallBoxesAnchos = SmallBoxesAnchos + $j(this).height() + 20) }) }) }) };