<?php
if ( !class_exists('UpiCRMAdminExistingFields') ):
    class UpiCRMAdminExistingFields{
        public function Render() {
            $UpiCRMFields = new UpiCRMFields();
            
            switch ($_GET['action']) {
                case 'save_field':
                    $this->saveField();
                    $msg = __('changes saved successfully','upicrm');
                    break;
            }
?>

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
            <form method="post" action="admin.php?page=upicrm_existing_fields&action=save_field">
                <?php _e('Add additional fields and datatypes to UpiCRM:','upicrm'); ?>
                <input type="text" name="field_name" value="" /><br />

                <?php submit_button(__('Add New field','upicrm')); ?>
            </form>
            <br />
            <br />
            <?php 
            foreach ($UpiCRMFields->get_as_array() as $key => $value) { ?>
            <?php echo $value; ?><br />
            <?php } ?>
        </div>
    </div>
</div>
<?php
        }
        
        function saveField() {
            $UpiCRMFields = new UpiCRMFields();
            $UpiCRMFields->add_unique($_POST['field_name']);
        }
    }
endif;
?>