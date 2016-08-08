<?php
if ( !class_exists('UpiCRMAdminWarp') ):
    class UpiCRMAdminWarp{
        public function header($upi_setting) {
            global $wp_version;
            ?>
<script type="text/javascript">
    $j(document).ready(function () {
        pageSetUp();
    })
</script>
        <?php
       if ($wp_version >= 5) {
        ?>
            <div class="alert alert-warning fade in">
                <i class="fa-fw fa fa-warning"></i>
                 <strong>Warning</strong> Your UpiCRM version is not compatible with WordPress 5.X . <a href="http://www.upicrm.com/?utm_source=upicrmvf">Please upgrade your UpiCRM WordPress CRM solution here</a>.
            </div>
       <?php } ?>
<div id="upicrm_warp">
    <div id="content">
        <div class="row">
            <div class="col-xs-12 col-sm-7 col-md-7 col-lg-4">
                <h1 class="page-title txt-color-blueDark">
                    <i class="fa fa-<?php echo $upi_setting['logo'] ?>"></i>
                    UpiCRM
                    <?php if ($upi_setting['title']) { ?>
                        <span>
                            > 
                            <b><?php echo $upi_setting['title']; ?> </b>
                        </span>
                    <?php } ?>
                </h1>
            </div>
        </div>
            <?php
        }
        
        function footer() {
            ?>

                </div> <!-- #content close -->
            </div> <!-- #upicrm close -->
            <?php
        }
    }  
    
endif;

