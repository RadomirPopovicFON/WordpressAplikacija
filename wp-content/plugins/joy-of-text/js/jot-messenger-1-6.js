(function ($) {
    
        // Initiate Multiselect plugin in Messages tab
	$(document).ready(function(){
	    jQuery("#jot-plugin-messages\\[jot-message-grouplist\\]").multiselect({   		
	    	height:"300",
		noneSelectedText:jot_strings.selectrecip
	    });
	    jQuery('#jot-sendstatus-div').hide(); 
	});
	
	
        // Open tabs
	$(document).ready(function(){
            
	    //$('div[id^=tab]').hide();
	    //$('#tabgroupdetails').show();
	    $('.jot-subtab').click(function (event) {
		event.preventDefault();
		var tab_id = $(this).attr('href');
		$('.jot-subtab').removeClass('nav-tab-active')
		$(this).addClass('nav-tab-active');
		$('div[id^=jottab]').hide();
		$(tab_id).show(); 
	    });
	});
	
		
	// Add a new member on admin screen
	$(document).ready(function(){
	   
	    $('[id^=jot-mem-new]').click(function(event) {
		event.preventDefault();
		jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\"></div>");
		
				// jot-mem-add-<groupid>
		var valarr = $(this).attr('id').split('-');
		var formdata =  {   'jot_grpid' : valarr[3],
		                    'jot_grpmemname' : $('#jot-mem-add-name').val(),
				    'jot_grpmemnum' : $('#jot-mem-add-num').val(),
				    'jot_namefield_id' : $('#jot-mem-add-name').attr('id'),
				    'jot_numfield_id'  : $('#jot-mem-add-num').attr('id')					
		};
		var data = {
		        'action': 'process_addmem',
		        'formdata':  formdata
		};
		
		$.post(ajax_object.ajax_url, data, function(response) {				    
		    var resp = JSON.parse(response);
			  
		    if (resp.errorcode != '0'){			
			$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			 $("#" + resp.errorfield).focus();		    
		    } else {
			// Clear old input values
			$('#jot-mem-add-name').val("");
			$('#jot-mem-add-num').val("");
			
			$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			var row = "";
			var addid = "jot-added-" + formdata['jot_grpid'];
			row += "<tr class='jot-member-list' id='" + addid +  "'>";
			row += "<td class='jot-td-l'>";
			rownameid = "jot-mem-upd-name-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownameid + "' name='" + rownameid + "' maxlength='40' size='40' type='text' value='" + formdata['jot_grpmemname'] + "'/>"
			row += "</td>";
			row += "<td class='jot-td-r'>";
			rownumid = "jot-mem-upd-num-" + formdata['jot_grpid'] + "-" + resp.lastid;
			row += "<input id='" + rownumid + "' name='" + rownumid + "' maxlength='40' size='40' type='text' value='" + formdata['jot_grpmemnum'].replace(/\s/g, '') + "'/>"
			row += "</td>";                 
			row += "<td class='jot-td-l'><div class='divider'></div><a href='#' id='jot-mem-save-" + formdata['jot_grpid'] + '-' + resp.lastid + "'><img src='" + jot_images.saveimg +  "' title='Save'></a><div class='divider'></div><a href='#' id='jot-mem-delete-" + formdata['jot_grpid']  +  '-' + resp.lastid + "'><img src='" + jot_images.delimg + "' title='Delete'></a><div class='divider'></div></td>";          
			row += "</tr>\n";
			
			$('.jot-member-add').closest('tr').after(row);
			$("#" + addid).hide().fadeIn('slow');			
		    }
		});
		    
		
	    });
	});
	
	// Save existing member's details on admin screen
	$(document).ready(function(){
	    $( "#jot-groupmem-tab" ).on( "click", "a[id^=jot-mem-save]", function( event ) {
	    	event.preventDefault();
		jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\"></div>");
				
		    // jot-mem-upd-<type>-<groupid>-<groupmemid>
		       
		    var valarr = $(this).attr('id').split('-');
		    
		    var formdata =  {   'jot_grpid' : valarr[3],
					'jot_grpmemid' : valarr[4],
					'jot_grpmemname' : $('#jot-mem-upd-name-' + valarr[3] + '-' + valarr[4]).val(),
					'jot_grpmemnum'  : $('#jot-mem-upd-num-'  + valarr[3] + '-' + valarr[4]).val(),
					'jot_namefield_id' : $('#jot-mem-upd-name-' + valarr[3] + '-' + valarr[4]).attr('id'),
					'jot_numfield_id'  : $('#jot-mem-upd-num-' + valarr[3] + '-' + valarr[4]).attr('id')
		    };
		    
		    var data = {
		        'action': 'process_savemem',
		        'formdata':  formdata
		    };
		   
		    jQuery.post(ajax_object.ajax_url, data, function(response) {				    
			var resp = JSON.parse(response);
			  
			if (resp.errorcode != '0'){			
			    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			    $("#" + resp.errorfield).focus();			    
			} else {
			    $("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
			}
		    });
		    
		
	    });
	});
	
	// Delete a member from admin screen
	$(document).ready(function(){
	    $( "#jot-groupmem-tab" ).on( "click", "a[id^=jot-mem-delete]", function( event ) {
	    //$('[id^=jot-mem-delete-]').click(function(event) {
		event.preventDefault();
		jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\"></div>");
			
		    var $tr = $(this).closest('tr');
	
		    // jot-mem-delete-<groupid>-<groupmemid>
		    var valarr = $(this).attr('id').split('-');
		    var formdata =  {   'jot_grpid' : valarr[3],
					'jot_grpmemid' : valarr[4]					
		    };
		    var data = {
		        'action': 'process_deletemem',
		        'formdata':  formdata
		    };
		    if (confirm('Are you sure you want to delete this member?')) {
			jQuery.post(ajax_object.ajax_url, data, function(response) {				    
			    var resp = JSON.parse(response);
			      
			    if (resp.errorcode != '0'){			
				$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );			   		    
			    } else {
				$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg + " " + resp.sqlerr + " </div>" );
				$tr.find('td').fadeOut('slow',function(){ 
				   $tr.remove();                    
			        }); 
			    }
			});	
		    }
	    });
	});
	
	// Open group details
	$(document).ready(function(){
	    $('#jot-group-list-tab tr').click(function(){
		var joturl = wp_vars.wp_admin_url + 'options-general.php?page=jot-plugin&tab=group-list&lastid=' + $(this).attr('id') + '&grppage=' + $('#jot_grppage').val();
		$(location).attr('href',joturl);		
	    });
	});
       
       // Save invite form on intial load
       $(document).ready(function(){
	    if ( $("#jot-group-invite-form").length > 0 ) {
				
		var data = {
		    'action': 'process_forms',
		    'formdata': $("#jot-group-invite-form").serialize()     
		};
		
		jQuery.post(ajax_object.ajax_url, data, function(response) {				    
		    //
		});	
		
	    };
	});
       
       // Save invite form
       $(document).ready(function(){
	    $("#jot-saveinvite").click(function(){
		jQuery("#jot-invite-message").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4>" + jot_strings.saveinv + "</h4></div>");
		var data = {
		    'action': 'process_forms',
		    'formdata': $("#jot-group-invite-form").serialize()     
		};
		
		
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		    var resp = JSON.parse(response);
			      
		    if (resp.errorcode != '0'){			
			$("#jot-invite-message").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + resp.errormsg + " " + resp.sqlerr + "</h4></div>" );			   		    
		    } else {
			$("#jot-invite-message").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\"<h4>" + resp.errormsg + " " + resp.sqlerr + "</h4></div>" );
		    }			   
		});	
		
	    });
	});
       
        // Save group details form
       $(document).ready(function(){
	    $("#jot-savegrpdetails").click(function(){
		jQuery("#jot-grpdetails-message").html("<h4>" + jot_strings.savegrp + "</h4>");
		var origname = $('#jot-plugin-group-list\\[jot_groupnameupd\\]').val();
		var origdesc = $('#jot-plugin-group-list\\[jot_groupdescupd\\]').val();
		var grpid = $('#jot_grpid').val();
		
		var data = {
		    'action': 'process_forms',
		    'formdata': $("#jot-group-details-form").serialize()     
		};
		
		
		// We can also pass the url value separately from ajaxurl for front end AJAX implementations
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		    var resp = JSON.parse(response);
			      
		    if (resp.errorcode != '0'){			
			$("#jot-grpdetails-message").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + resp.errormsg + " " + resp.sqlerr + "</h4></div>" );			   		    
		    } else {
			$("#jot-grpdetails-message").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\"<h4>" + resp.errormsg + " " + resp.sqlerr + "</h4></div>" );
		        // Change name/desc on Group Manager panel
			$('#' + grpid).find('td').eq(0).hide().html(origname).fadeIn('slow') ;
			$('#' + grpid).find('td').eq(1).hide().html(origdesc).fadeIn('slow') ;
			$('#jot_grptitle').hide().html(origname).fadeIn('slow') ;			
		    }		    
		});	
		
	    });
	});
       
       // Subscribe to a group
       $(document).ready(function(){
	    $("[id^=jot-subscribegroup]").click(function(){		
		
		var parentform = $(this).parents('form:first');
		jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html(jot_strings.grpsub);
		
		var data = {
		    'action': 'process_forms',
		    'formdata': $(this).parents('form:first').serialize()     
		};
			
		jQuery.post(ajax_object.ajax_url, data, function(response) {
		   
		    var resp = JSON.parse(response);
			      
		    if (resp.errorcode != '0'){			
			jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html("<div id=\"jot-messagestatus\" class=\"jot-messagered\">" + resp.errormsg + " " + resp.sqlerr + "</div>" );			   		    
		    } else {
			jQuery($(parentform).find('div[id^=jot-subscribemessage]')).html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\">" + resp.errormsg + " " + resp.sqlerr + "</div>" );
		    }		
		    
		});
		
	    });
	});
       
       // Send a message
       $(document).ready(function(){
	    $("#jot-sendmessage").click(function(event){
		event.preventDefault();
		$('#jot-sendstatus-div').hide();
		
		var selected_numbers = $("#jot-plugin-messages\\[jot-message-grouplist\\]").val();
		var scheduled = false;
				
		if ($("#jot-scheddate").length > 0) {			
			if ($("#jot-scheddate").datepicker( "getDate" ) == null) {
				scheduled = false;
			} else {
				scheduled = true;
			}
		}
		if (selected_numbers) {
			if (scheduled == true) {
				// Queue messages
				$('#jot-sendstatus-div').html(tabhtml);
		    		jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4><img src='" + jot_images.spinner +  "'><div class='divider'></div>" + jot_strings.queuemsg  +"</h4></div>");
		   
				var allform = $("#jot-message-field-form").serialize();
				var formdata =  {   'jot-message-grouplist' :  JSON.stringify(selected_numbers),
						    'jot-allform' : allform
						};
				var data = {
				       'action': 'queue_message',
				       'formdata' : formdata
				       
				};				
			       
				jQuery.post(ajax_object.ajax_url, data, function(response) {
				    var resp = JSON.parse(response);
						  
				    if (resp.errorcode != '0'){
						var errhtml = "";
						$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + resp.errormsg + "</h4></div>");			  						    		   		    
				    } else {
						scheddate = $('#jot-scheddate').val();
						schedtime = $('#jot-schedtime').val();
						$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagegreen\"><h4>" + jot_strings.scheduled + " (" + scheddate + " @ " + schedtime + ")" + "</h4></div>");
				    }
				});
				
			} else {
				// Send messages
				    var tabhtml = "<table id=\"jot-sendstatustab\">";
				    tabhtml += "<tr><th class=\"jot-td-c\">" + jot_strings.number + "</th><th class=\"jot-td-c\">" + jot_strings.status + "</th></tr>";
				    tabhtml += "</table>";
				    $('#jot-sendstatus-div').html(tabhtml);
				    var counter = 1;
				    
				    for(var i = 0; i < selected_numbers.length; i++){
					
					jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4>" + jot_strings.sendmsg  +"</h4></div>");
				    
					var data = {
					    'action': 'send_message',
					    'jotmemid': selected_numbers[i],
					    'formdata': $("#jot-message-field-form").serialize()     
					};
					
					
					// We can also pass the url value separately from ajaxurl for front end AJAX implementations
					jQuery.post(ajax_object.ajax_url, data, function(response) {
							    
					    // For todays date;
					    Date.prototype.today = function () { 
						return ((this.getDate() < 10)?"0":"") + this.getDate() +"/"+(((this.getMonth()+1) < 10)?"0":"") + (this.getMonth()+1) +"/"+ this.getFullYear();
					    }
					    // For the time now
					    Date.prototype.timeNow = function () {
						 return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
					    }
					    
					    
					    var newDate = new Date();
					    var datetime = newDate.today() + " @ " + newDate.timeNow();
					    var resp = JSON.parse(response);
						      
					    if (resp.errorcode != '0'){
						var errhtml = "";
						$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + resp.errormsg + "</h4><p>");			   
						if (resp.send_errors) {
						    $('#jot-sendstatus-div').show();
						    $('#jot-sendstatustab tr:last').after("<tr class=\"jot-messagered\"><td class=\"jot-td-c\">" + resp.send_errors[0]['send_message_number'] + "</td><td> " + resp.send_errors[0]['send_message_msg'] + " (Error code : " + resp.send_errors[0]['send_message_errorcode'] + ") </td></tr>").fadeIn('slow');	
						}			    		   		    
					    } else {
						$("#jot-messagestatus").html("");
						if (resp.send_errors) {
						    $('#jot-sendstatus-div').show();
						    $('#jot-sendstatustab tr:last').after("<tr class=\"jot-messagegreen\"><td class=\"jot-td-c\">" + resp.send_errors[0]['send_message_number'] + "</td><td> " + resp.send_errors[0]['send_message_msg'] + " " + datetime + "</td></tr>").fadeIn('slow');
						}
						if (counter < selected_numbers.length) {				
						   jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4>" + jot_strings.sendmsg + " " + counter + "/" + selected_numbers.length + "</h4></div>");
						} else {
						   jQuery("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messageblack\"><h4>" + jot_strings.proccomplete + "</h4></div>");
						}
					    }
					    
					    counter++;
					});
				    }
			} 
		} else {
			$("#jot-messagestatus").html("<div id=\"jot-messagestatus\" class=\"jot-messagered\"><h4>" + jot_strings.selectrecip + "</h4><p>");
		}
	    });
	});
	
		
	// Write subscribe form on Invite Panel
	$(document).ready(function() {
	    	    
	    function writeSubscribeForm() {
		
		var subhtml = '<div>\n';
		subhtml += '<form id="jot-subscriber-form-' + jot_lastgroup.id + '" action="" method="post">\n';
		subhtml += '<input type="hidden"  name="jot-group-id" value="' + jot_lastgroup.id + '">\n';
		subhtml += '<input type="hidden"  name="jot_form_id" value="jot-subscriber-form">';
		subhtml += '<table>\n';
		subhtml += '<tr><th colspan=2 class="jot-td-c">' + $('#jot-plugin-group-list\\[jot_grpinvdesc\\]').val()  + '</th></tr>';
		subhtml += '<tr><th>' + $('#jot-plugin-group-list\\[jot_grpinvnametxt\\]').val() + '</th><td><input id="jot-subscribe-name" name="jot-subscribe-name" maxlength="40" size="40" type="text"/></td></tr>\n';
		subhtml += '<tr><th>' + $('#jot-plugin-group-list\\[jot_grpinvnumtxt\\]').val() + '</th><td><input id="jot-subscribe-num" name="jot-subscribe-num" maxlength="40" size="40" type="text"/></td></tr>\n';
		subhtml += '<tr><td><input type="button" id="jot-subscribegroup-' + jot_lastgroup.id + '" class="button" value="Subscribe"/></td>';
		subhtml += '<td><div id="jot-subscribemessage"></div></td></tr>';
		subhtml += '</table>';
		subhtml += '</form>';
		
		subhtml += '</div>';
		
		$("#jot-plugin-group-list\\[jot_grpinvformtxt\\]").val(subhtml);
	    };
	    $(document).on("change keyup", "#jot-plugin-group-list\\[jot_grpinvdesc\\]", writeSubscribeForm);
	    $(document).on("change keyup", "#jot-plugin-group-list\\[jot_grpinvnametxt\\]", writeSubscribeForm);
	    $(document).on("change keyup", "#jot-plugin-group-list\\[jot_grpinvnumtxt\\]", writeSubscribeForm);
	    
	    
	    if($("#jot-plugin-group-list\\[jot_grpinvformtxt\\]").length > 0){
		writeSubscribeForm();
	    }
	});
	
	function get_messagecount () {
		var cs = $('#jot-plugin-messages\\[jot-message\\]').val().length + $('#jot-plugin-messages\\[jot-message-suffix\\]').val().length;
		
		var messlen = 160; // also change below
		if (cs == 0) {
		    var currmessage = messlen;
		} else {
		    var currmessage = messlen - ((cs % messlen == 0) ? messlen : (cs % messlen));
		}
		var messcnt = ((cs==0) ? 1 : Math.ceil(cs/messlen));
			
		$('#jot-message-count').text(currmessage + "/" + messcnt );
	};
	
	$(document).ready(function(){		   
	    $(document).on('keyup', '#jot-plugin-messages\\[jot-message\\], #jot-plugin-messages\\[jot-message-suffix\\]', get_messagecount);
	});
	
	
	// Populate message character count on intial load
       $(document).ready(function(){
	    if ( $("#jot-message-count").length > 0 ) {
		get_messagecount();		
	    };
	});
	
	
	
	// Submit SMS provider form on select change 
	$(document).ready(function(){
	    $('#jot-plugin-smsprovider\\[jot-smsproviders\\]').change(function(){
		  var joturl = wp_vars.wp_admin_url + 'options-general.php?page=jot-plugin&tab=smsprovider&smsprovider=' + $(this).val();
		  $(location).attr('href',joturl);
		
	    });
	});
	
	
	// Disable Welcome message if checkbox isn't checked
        $(document).ready(function(){
	        if ($("#jot-plugin-group-list\\[jot_grpinvretchk\\]").length > 0 ) {
			if ($("#jot-plugin-group-list\\[jot_grpinvretchk\\]").is(':checked')) {
	                  // Ignore
			} else {
			   $("#jot-plugin-group-list\\[jot_grpinvrettxt\\]").attr('disabled', 'disabled');
			}
	        }
	})
	
	// Enable Welcome message if checkbox clicked.
	$(document).ready(function(){
	    $("#jot-plugin-group-list\\[jot_grpinvretchk\\]").click(function(){
		$("#jot-plugin-group-list\\[jot_grpinvrettxt\\]").attr('disabled', !$(this).attr('checked'));
		$("#jot-plugin-group-list\\[jot_grpinvrettxt\\]").focus();	
	    });
	});
	
	
	// Radio button for voice options
	$(document).ready(function(){
	     $("#container-jot-plugin-smsprovider\\[jot-voice-gender\\] input[type='radio']").click(function(){
		var voice = $(this).val();
				
		var formdata =  {  'jot_voice_gender' : voice			   
					}
		var data = {
			'action': 'process_refresh_languages',		    
			'formdata': formdata    
			};
		//$("#jot-plugin-smsprovider\\[jot-voice-accent\\]").hide();
		jQuery.post(ajax_object.ajax_url, data, function(response) {		   
		    var resp = JSON.parse(response);			      
		    		    
		    html = '<select id="jot-plugin-smsprovider[jot-voice-accent]" name="jot-plugin-smsprovider[jot-voice-accent]">';
		    
		    $.each(resp, function(index, row) {			
			html += '<option value="' + index  + '">' +  row + '</option>';
		    });
		    html += '</select>';
		    $("#jot-plugin-smsprovider\\[jot-voice-accent\\]").html(html);
		    $('#jot-plugin-smsprovider\\[jot-voice-accent\\] option[value="en-GB"]').prop('selected', true);
		});
		
	    });
	});
	
	

	
}(jQuery));