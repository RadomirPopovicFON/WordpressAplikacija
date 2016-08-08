<?php
if ( !class_exists('UpiCRMAdminImportExport') ):
class UpiCRMAdminImportExport{
public function Render() {    
             
    switch ($_GET['action']) {
        case 'import_all':
            $this->importAll();
        break;
        case 'excel_output':
            upicrm_excel_output();
        break;
        case 'excel_fromat_output':
            $this->excel_fromat_output();
        break;
        case 'excel_fromat_upload':
            $this->excel_fromat_upload();
        break;
    }  
    ?>

<?php _e('Import data into UpiCRM :<br />
You can easily upload / import data into UpiCRM. In order to upload data into UpiCRM please do the following:','upicrm'); ?><br /><br />

1. <a href="admin.php?page=upicrm_import_export&action=excel_fromat_output"> <?php _e('Download this Excel file','upicrm'); ?></a> <?php _e('and open it in Excel / Google docs','upicrm'); ?><br />

<?php _e('2. Locate the data into the right columns inside the excel file. Usually this should be an easy     
    operation using select / copy on the "other" file, and then "paste" into the UpiCRM Excel   
    file.','upicrm'); ?><br />

<?php _e('3. Do NOT change the structure / format of the excel file, just copy the text data into the right 
    columns, save and exit the file.','upicrm'); ?><br />

<?php _e('4. Upload the file into UpiCRM and you\'re done!','upicrm'); ?><br />

<?php _e('5. Note: any automatic lead management rules you may have – will be processed during the  
    import process. ','upicrm'); ?>

<br /><br /><br />
        <a href="admin.php?page=upicrm_import_export&action=excel_output" class="btn btn-default">
            <i class="glyphicon glyphicon-export"></i> <?php _e('Export all leads data to Excel ','upicrm'); ?>
        </a> 
        <br /><br />
        <a href="admin.php?page=upicrm_import_export&action=import_all" class="btn btn-default">
           <i class="glyphicon glyphicon-import"></i> <?php _e('import all existing forms plugins data into UpiCRM','upicrm'); ?>
        </a> 
        <br /> 
        <?php _e('Note: this will import "old" data from the forms you\'re currently using, if they are configured to keep entries in a database. This will NOT import new data from an external / new source. ','upicrm'); ?>

        <br /><br />
        <?php _e('Import New data into UpiCRM :','upicrm'); ?>
        <a href="admin.php?page=upicrm_import_export&action=excel_fromat_output" class="btn btn-default">
            <i class="glyphicon glyphicon-export"></i> <?php _e('Download UpiCRM Sample Excel File ','upicrm'); ?>
        </a>
        <br />
        <br />
        <form action="admin.php?page=upicrm_import_export&action=excel_fromat_upload" method="post" enctype="multipart/form-data">
            <label for="excel_fromat_upload"><?php _e('Upload & Import','upicrm'); ?></label>
            <input type="file" id="excel_fromat_upload" name="excel_fromat_upload" accept=".csv, .xlsx, xls" />
            <input type="submit" name="submit" id="submit" class="button button-primary" value="<?php _e('Upload','upicrm'); ?>">
        </form>
        <br /><br />
        <?php _e('Example: import data from Excel:','upicrm'); ?><br />
        <img src="<?php echo plugins_url( 'images/upi_import.jpg', dirname(__FILE__) ); ?>" /> 
  <!-- widget grid -->

<?php
    }
    
   
    
    function excel_fromat_output() {
        upicrm_load('excel');
        $UpiCRMLeads = new UpiCRMLeads();
        $UpiCRMUIBuilder = new UpiCRMUIBuilder();
        $UpiCRMFieldsMapping = new UpiCRMFieldsMapping();
        $objPHPExcel = new PHPExcel();
        
        $list_option = $UpiCRMUIBuilder->get_list_option();
        $getLeads = $UpiCRMLeads->get();
        $getNamesMap = $UpiCRMFieldsMapping->get(); 
        $fileName = '/upicrm_format.xlsx';
        $dirName = WP_CONTENT_DIR."/uploads/upicrm"; 
        if (!file_exists($dirName)) {
            mkdir($dirName, 0777, true);
        }
        $t="A";
        foreach ($list_option as $key => $arr) { 
            if ($key == "content") {
                foreach ($arr as $key2 => $value) { 
                    $objPHPExcel->getActiveSheet()->getStyle($t.'1')->getFont()->setBold(true);
                    $objPHPExcel->getActiveSheet()->setCellValue($t.'1', $value);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($t)->setWidth(25);
                    $t++;
                }
            } 
        }
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save($dirName.$fileName);

        echo '<script>window.onload = function (event) { window.location="'.home_url().'/wp-content/uploads/upicrm/upicrm_format.xlsx"; };</script>';
    }
    
    function importAll() {
        $UpiCRMgform = new UpiCRMgform();
        $UpiCRMwpcf7 = new UpiCRMwpcf7();
        $UpiCRMninja = new UpiCRMninja();
        if($UpiCRMgform->is_active()) {
            $UpiCRMgform->import_all();
        }
        if($UpiCRMwpcf7->is_db_active()) {
            $UpiCRMwpcf7->import_all();
        }
        if ($UpiCRMninja->is_active()) {
            $UpiCRMninja->import_all();
        }
    }
    
    function excel_fromat_upload() {
        $UpiCRMLeads = new UpiCRMLeads();
        $fileName = '/import.xlsx';
        $dirName = WP_CONTENT_DIR."/uploads/upicrm"; 
        
        $file_name = key($_FILES);
        if($_FILES[$file_name]['name']){
            if(!$_FILES[$file_name]['error']) {
                move_uploaded_file($_FILES[$file_name]['tmp_name'], $dirName.$fileName);
                
                upicrm_load('excel');
                $objPHPExcel = PHPExcel_IOFactory::load($dirName.$fileName);
                $sheetData = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                $i=0;
                foreach ($sheetData as $sheet) {
                    if ($i == 0) {
                        $field = $sheet;
                    } else {
                        $content = array();
                        foreach ($sheet as $key => $value) {
                            if ($value) {
                                $content[$field[$key]] = $value;
                            }
                        }
                        $UpiCRMLeads->add($content, 4, 0, false);
                    }
                    $i++;
                }
                        ?>
                        <div class="updated">
                            <p>
                            <?php _e('Success!','upicrm'); ?>
                            <?php echo $i-1; ?>
                            <?php _e('new records imported into UpiCRM.','upicrm'); ?>
                            </p>
                        </div>
                        <br /><br />
                        <?php
            }
        }
        else {
?>
                        <div class="error">
                            <p>
                            <?php _e('Error occurred, could not import data','upicrm'); ?>
                            </p>
                        </div>
                        <br /><br />
                        <?php
        }

    }
}



endif;