<?php
if ( !class_exists('UpiCRMAdminExistingStatuses') ):
    class UpiCRMAdminExistingStatuses{
        public function Render() {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            
            switch ($_GET['action']) {
                case 'save_status':
                    $this->saveStatus();
                    $msg = __('changes saved successfully','upicrm');
                    break;
            }
?>
<script type="text/javascript">
    $j(document).ready(function () {
        
        $j("*[data-callback='edit']").click(function() {
            var id = $j(this).attr("data-status_id");
            $j(".status_show[data-status_id="+id+"]").hide();
            $j(".status_edit[data-status_id="+id+"]").show();
        });
        
        $j("*[data-callback='save']").click(function() {
            var id = $j(this).attr("data-status_id");
            var val = $j("input[data-callback='edit_input'][data-status_id="+id+"]").val();
                    var data = {
                        'action': 'change_status_name',
                        'lead_status_id': id,
                        'lead_status_name': val,
                    };
                    $j.post(ajaxurl, data , function(response) {
                        if (response == 1) {
                            $j(".status_edit[data-status_id="+id+"]").hide();
                            $j(".status_show[data-status_id="+id+"]").show();
                            $j(".status_show[data-status_id="+id+"] .text").text(val);
                        }
                        else {
                            alert("Oh no! Error!");
                            console.log(response);
                        }
                    });
            
        });
        
        $j("*[data-callback='cancel']").click(function() {
            var id = $j(this).attr("data-status_id");
            $j(".status_edit[data-status_id="+id+"]").hide();
            $j(".status_show[data-status_id="+id+"]").show();
        });
        
    })
</script>

<style type="text/css">
    .glyphicon-edit,.glyphicon-floppy-save,glyphicon-floppy-remove {
        margin-left: 5px;
    }
</style>

    <?php
            if (isset($msg)) {
    ?>
    <div class="updated">
        <p><?php echo $msg; ?></p>
    </div>
    <?php
            }
    ?>
    <div class="row">
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-8">
            <form method="post" action="admin.php?page=upicrm_existing_statuses&action=save_status">
                <?php _e('Add additional status to UpiCRM:','upicrm'); ?>
                <input type="text" name="status_name" value="" /><br />

                <?php submit_button(__('Add New Status','upicrm')); ?>
            </form>
            <br />
            <br />
            <?php 
            foreach ($UpiCRMLeadsStatus->get_as_array() as $key => $value) { ?>
                <div class="status_edit" style="display: none;" data-status_id="<?php echo $key; ?>">
                    <input type="text" value="<?php echo $value; ?>" data-callback="edit_input" data-status_id="<?php echo $key; ?>" />
                    <span class="glyphicon glyphicon-floppy-save pointer-click" data-callback="save" data-status_id="<?php echo $key; ?>" title="save"></span>
                    <span class="glyphicon glyphicon-floppy-remove pointer-click" data-callback="cancel" data-status_id="<?php echo $key; ?>" title="cancel"></span>

                </div>
                <div class="status_show" data-status_id="<?php echo $key; ?>">
                    <span class="text"><?php echo $value; ?></span> 
                    <span class="glyphicon glyphicon-edit pointer-click" data-callback="edit" data-status_id="<?php echo $key; ?>" title="edit"></span>
                </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php
        }
        
        function saveStatus() {
            $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
            $UpiCRMLeadsStatus->add_unique($_POST['status_name']);
        }
        
    function wp_ajax_change_status_name_callback() {
        $UpiCRMLeadsStatus = new UpiCRMLeadsStatus();
        $updateArr = array("lead_status_name" => $_POST['lead_status_name']);
        $UpiCRMLeadsStatus->update($updateArr,$_POST['lead_status_id']);
        echo 1;
        die();
    }
}

add_action( 'wp_ajax_change_status_name', array(new UpiCRMAdminExistingStatuses,'wp_ajax_change_status_name_callback'));
    
endif;
?>