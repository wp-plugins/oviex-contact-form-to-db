<?php
//load all the required js and css

add_action('admin_enqueue_scripts', 'ocfdb_style_admin_data');
function ocfdb_style_admin_data(){
		if(isset($_GET['page']) && $_GET['page'] == 'entries'){
			wp_enqueue_script('datatable', trailingslashit(plugin_dir_url(__FILE__)).'DataTables/media/js/jquery.dataTables.js', array('jquery'));
			wp_enqueue_script('tabletools', trailingslashit(plugin_dir_url(__FILE__)).'DataTables/extensions/TableTools/js/dataTables.tableTools.js', array('jquery'));
			wp_enqueue_script('shCore', trailingslashit(plugin_dir_url(__FILE__)).'DataTables/syntax/shCore.js', array('jquery'));
			wp_enqueue_script('demo', trailingslashit(plugin_dir_url(__FILE__)).'DataTables/js/demo.js', array('jquery'));
			
			//Load Data table style;
			wp_enqueue_style( 'datatable_css', trailingslashit(plugin_dir_url(__FILE__)).'DataTables/media/css/jquery.dataTables.css'); 
			wp_enqueue_style( 'tabletools_css', trailingslashit(plugin_dir_url(__FILE__)).'DataTables/extensions/TableTools/css/dataTables.tableTools.css'); 
			wp_enqueue_style( 'shCore_css', trailingslashit(plugin_dir_url(__FILE__)).'DataTables/syntax/shCore.css'); 
			wp_enqueue_style( 'demo_css', trailingslashit(plugin_dir_url(__FILE__)).'DataTables/css/demo.css'); 		
		}	
}


// function for inserting submitted data into database
	
add_action('save_ocf','ocfdb_insert',5,1);
function ocfdb_insert($post_data)
{		
	if(isset($post_data)):
		global $wpdb;
		$entries_table = $wpdb->prefix . 'oviex_contact_form';
		$data = array();
		$data['first_name'] = sanitize_text_field($post_data['name']);
		$data['last_name'] =  sanitize_text_field($post_data['last_name']);
		$data['phone'] = intval($post_data['phone']);
		$data['email'] = sanitize_email($post_data['email']);
		$data['date_submitted'] = time();
		if($post_data['ip'] === filter_var($post_data['ip'], FILTER_VALIDATE_IP))
				{
					$data['ip'] = $post_data['ip'];
				}
		$data['comment'] = esc_attr($post_data['comment']);
		$result = $wpdb->query($wpdb->prepare( 
	"INSERT INTO $entries_table	( first_name, last_name, phone, email, date_submitted, ip, comment ) VALUES ( %s, %s, %d, %s,%d,%s,%s )", 
        $data['first_name'], 
	$data['last_name'], 
	$data['phone'],
	$data['email'],
	$data['date_submitted'],
	$data['ip'],
	$data['comment']));
		if($result){
			return true;
		}
		else{
			return false;
		}
	endif;
}




//Show submitted entries from Oviex contact form in admin panel
function ocfdb_show_submitted_entries(){
		global $wpdb;
		?>
		<style type="text/css" class="init">
			tfoot input
			{
				width: 100%;
				padding: 3px;
				box-sizing: border-box;
			}
			h1 a{
				color:#2C2C2C;
				text-decoration:none;
			}
			h1 a:hover{
				text-decoration:underline;
			}
			a{
				color: #069FDF;
				cursor:pointer;
			}
			.success{
				color:#009900;
				font-weight:bold;
			}
			.error{
				color:#F33C21;
				font-weight:bold;
			}
			
		</style>
		<script type="text/javascript" language="javascript" class="init">
			jQuery(document).ready(function() 
			{
				// Setup - add a text input to each footer cell
				
					jQuery('#example1 tfoot th').each( function () 
					{
						var title = jQuery('#example1 thead th').eq( jQuery(this).index() ).text();
						jQuery(this).html( '<input type="text" placeholder="Search '+title+'" />' );
					} );
					
				// DataTable
				
					var table = jQuery('#example1').DataTable(
					{
						"order": [[ 0, "desc" ]],
						dom: 'T<"clear">lfrtip',
						tableTools: 
						{
								"sSwfPath": "<?php echo trailingslashit(plugin_dir_url(__FILE__)); ?>swf/copy_csv_xls_pdf.swf",
								"aButtons": [{ "sExtends": "csv", "mColumns": "visible", "oSelectorOpts": { page: "current" } }]
						}    
					});
					
				// Apply the search
				
					table.columns().eq( 0 ).each( function ( colIdx ) 
					{
						jQuery( 'input', table.column( colIdx ).footer() ).on( 'keyup change', function () 
						{
							table
								.column( colIdx )
								.search( this.value )
								.draw();
						} );
					} );
					
			} );
		</script>
			<div class="head">
				<h2>Oviex Contact Form Entries</h2>
			</div>
				<?php
					$entries_table = $wpdb->prefix.'oviex_contact_form';
					$entries = $wpdb->get_results("SELECT * FROM $entries_table");
				?>
			<table id="example1" class="display" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th>ID</th> 
						<th>Name</th> 
						<th>Last Name</th> 
						<th>Phone/Skype</th>
						<th>Email</th>
						<th>Comment</th>
						<th>Date Submitted(Y-m-d)</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th>ID</th> 
						<th>Name</th> 
						<th>Last Name</th> 
						<th>Phone/Skype</th>
						<th>Email</th>
						<th>comment</th>
						<th>Date Submitted(Y-m-d)</th>
					</tr>
				</tfoot>
				<tbody>
					<?php 		
						if($entries):
							foreach($entries as $lead)
							{
								$id = $lead->id;
								$name = $lead->first_name;
								$last_name = $lead->last_name;
								$phone = $lead->phone;
								$email = $lead->email;
								$comment = $lead->comment;
								
								$ip = $lead->ip;
								$date = date('Y-m-d',$lead->date_submitted);
								?>
									<tr>
										<th><?php echo $id;?></th> 
										<th><?php echo $name;?></th> 
										<th><?php echo $last_name;?></th> 
										<th><?php echo $phone;?></th>
										<th><?php echo $email;?></th>
										<th><?php echo $comment;?></th>
										<th><?php echo $date;?></th>
									</tr>
								<?php 
							}
						 else:				 
							echo 'No entries Found.';				 
						 endif; 
					?>
				</tbody>
			</table>            
		<?php 
}
//add sub menu page
add_action( 'admin_menu', 'ocfdb_submenu_page' );	
function ocfdb_submenu_page() {
add_submenu_page('Oviex-contact-us-form', 'Entries - Oviex Contact Form', 'Entries', 'administrator', 'entries', 'ocfdb_show_submitted_entries'); 
}?>