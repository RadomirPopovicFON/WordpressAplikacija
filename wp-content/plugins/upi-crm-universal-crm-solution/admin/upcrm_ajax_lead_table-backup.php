<?php 
global $SourceTypeName;
    
$UpiCRMLeads = new UpiCRMLeads();
$getLeads = $UpiCRMLeads->get();
print_r($_POST); 

?>

<!-- NEW WIDGET START -->
      <article class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        
        <!-- Widget ID (each widget will need unique ID)-->
        <div class="jarviswidget jarviswidget-color-blueDark" id="wid-id-1" data-widget-editbutton="false">
          <!-- widget options:
usage: 
<div class="jarviswidget" id="wid-id-0" data-widget-editbutton="false">

data-widget-colorbutton="false"
data-widget-editbutton="false"
data-widget-togglebutton="false"
data-widget-deletebutton="false"
data-widget-fullscreenbutton="false"
data-widget-custombutton="false"
data-widget-collapsed="true"
data-widget-sortable="false"

-->
                      <header>
                        <span class="widget-icon">
                          
                          <i class="fa fa-table">
                          </i>
                          
                        </span>
                        <h2>
                          Column Filters 
                        </h2>
                        
                      </header>
                      
                      <!-- widget div-->
                      <div>
                        
                        <!-- widget edit box -->
                        <div class="jarviswidget-editbox">
                          <!-- This area used as dropdown edit box -->
                          
                        </div>
                        <!-- end widget edit box -->
                        
                        <!-- widget content -->
                        <div class="widget-body no-padding">
                          
                          <table id="datatable_fixed_column" class="table table-striped table-bordered" width="100%">
                            
                            <thead>
                              <tr>
                                <th class="hasinput" style="width:17%">
                                  <input type="text" class="form-control" placeholder="Filter ID" />
                                </th>
                                <th class="hasinput" style="width:17%">
                                  <input type="text" class="form-control" placeholder="content" />
                                </th>
                                <th class="hasinput" style="width:16%">
                                  <input type="text" class="form-control" placeholder="Filter Office" />
                                </th>
                                <th class="hasinput" style="width:17%">
                                  <input type="text" class="form-control" placeholder="Filter Age" />
                                </th>
                                <th class="hasinput icon-addon">
                                  <input id="dateselect_filter" type="text" placeholder="Filter Date" class="form-control datepicker" data-dateformat="yy/mm/dd">
                                  <label for="dateselect_filter" class="glyphicon glyphicon-calendar no-margin padding-top-15" rel="tooltip" title="" data-original-title="Filter Date">
                                  </label>
                                </th>
                                <th class="hasinput" style="width:16%">
                                  <input type="text" class="form-control" placeholder="Filter Salary" />
                                </th>
                              </tr>
                              <tr>
                                <th data-class="expand">
                                  ID
                                </th>
                                <th>
                                  Content
                                </th>
                                <th data-hide="phone">
                                  Source Name
                                </th>
                                <th data-hide="phone,tablet">
                                  User IP
                                </th>
                                <th data-hide="phone,tablet">
                                  User Agent
                                </th>
                                <th data-hide="phone,tablet">
                                  User Referer
                                </th>
                                <th data-hide="phone,tablet">
                                  Time
                                </th>
                              </tr>
                            </thead>
                            
                            <tbody>
                                <?php
                                foreach ($getLeads as $obj) {
                                    ?>
                                <tr>
                                    <td><?php echo $obj->lead_id;?></td>
                                    <td><?php 
                                        $lead_content_arr = (array)json_decode($obj->lead_content);
                                        foreach ($lead_content_arr as $input) {
                                            $input = (array)$input;
                                            foreach ($input as $name => $value) {
                                                echo "<strong>{$name}:</strong> {$value}<br />";
                                            }
                                            
                                       }
                                    ?></td>
                                    <td><?php echo $UpiCRMLeads->get_source_form_name($obj->source_id,$obj->source_type);?></td>
                                    <td><?php echo $obj->user_ip;?></td>
                                    <td><?php echo $obj->user_agent;?></td>
                                    <td><?php echo $obj->user_referer;?></td>
                                    <td><?php echo $obj->time;?></td>
                                      
                                </tr>
                                    <?php
                                }
                                ?>
                              
                            </tbody>
							
                          </table>
                          
                        </div>
                        <!-- end widget content -->
                        
                      </div>
                      <!-- end widget div -->
                      
                  </div>
                  <!-- end widget -->
                  
              </article>
              <!-- WIDGET END -->