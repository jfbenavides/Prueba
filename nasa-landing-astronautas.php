<?php
/*
Plugin Name: NASA_astronautas
Description: Prueba Gradiweb - Desarrollador web FRONT
Version: 1.0.0
Author: Ing. Jhon Benavides
Author URI: .../nasa-landing-astronautas
shortcode [nasa_landing_astronautas]
*/

register_activation_hook(__FILE__, 'Astronauta_init');

// Activacion de la tabla - se crean las tablas
function Astronauta_init()
{
	global $wpdb;
	$tabla_nasa_astronauta = $wpdb->prefix . 'nasa_astronautas';
	$tabla_nasa_configuracion = $wpdb->prefix . 'nasa_configuracion';
	$charset_collate = $wpdb->get_charset_collate();

	$query = "CREATE TABLE IF NOT EXISTS $tabla_nasa_astronauta (
		astronautaId INT(11) NOT NULL AUTO_INCREMENT,
		nombre VARCHAR(80) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
		edad INT(11) NULL DEFAULT NULL,
		sexo INT(11) NULL DEFAULT NULL,
		correo VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
		motivo VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
		contacto DATE NULL DEFAULT NULL,
		PRIMARY KEY (astronautaId) USING BTREE
    ) $charset_collate";

    $sql = "CREATE TABLE IF NOT EXISTS $tabla_nasa_configuracion (
		configuracionId INT(11) NOT NULL AUTO_INCREMENT,
		introduccion VARCHAR(300) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
		mensaje VARCHAR(300) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
		logo VARCHAR(300) NULL DEFAULT NULL COLLATE 'utf8mb4_general_ci',
		PRIMARY KEY (configuracionId) USING BTREE
    ) $charset_collate";

    include_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($query);
    dbDelta($sql);
}

// Desinstalar Plugin
register_deactivation_hook( __FILE__, 'Astronauta_fin' );

function Astronauta_fin() {

	if (!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN')) exit();

		//eliminar tablas de base de datos
		global $wpdb;

		//Borrar tabla de mysql
		$tabla_nasa_astronauta = $wpdb->prefix . 'nasa_astronautas';
		$tabla_nasa_configuracion = $wpdb->prefix . 'nasa_configuracion';

		$wpdb->query( "DROP TABLE $tabla_nasa_astronauta");
		$wpdb->query( "DROP TABLE $tabla_nasa_configuracion");
		delete_option('imacPrestashop_options');
}

// Shortcode que llama el formulario
add_shortcode('nasa_landing_astronautas', 'nasa_landing_astronautas');


// Crea el shortcode
function nasa_landing_astronautas()
{

	global $wpdb;	

	if (!empty($_POST) AND $_POST['nombre'] != '' AND $_POST['edad'] != '' AND $_POST['sexo'] != '' AND is_email($_POST['correo']) AND $_POST['motivo'] != '' AND $_POST['contacto'] != ''){

		$tabla_nasa_astronauta = $wpdb->prefix . 'nasa_astronautas';

		$nombre = sanitize_text_field($_POST['nombre']);
		$edad = (int)($_POST['edad']);
		$sexo = (int)($_POST['sexo']);
		$correo = sanitize_email($_POST['correo']);
		$motivo = sanitize_text_field($_POST['motivo']);
		$fecha = DateTime::createFromFormat('j/n/Y', $_POST['contacto']);
		$contacto = $fecha->format('Y-m-d');

		$wpdb->insert(
			$tabla_nasa_astronauta, 
			array(
				'nombre' => $nombre,
				'edad' => $edad,
				'sexo' => $sexo,
				'correo' => $correo,
				'motivo' => $motivo,
				'contacto' => $contacto,
			)
		);

		wp_redirect('/nasa-gracias.php');
  		exit();
		
	}

	
	ob_start();
	?>
		
		<form action="<?php get_the_permalink(); ?>" method="post" class="cuestionario">
			<?php wp_nonce_field('graba_astronauta', 'astronauta_nonce'); ?>
			<div class="form-input">
				<label form="Nombre">Nombre Completo</label>
				<input type="text" name="nombre" required="required">
			</div>
			<div class="form-input">
				<label form="Edad">Edad</label>
				<input type="text" name="edad" required="required" maxlength="2">
			</div>
			<div class="form-input">
				<label form="Sexo">Sexo</label>
				<select name="sexo" required="required">
					<option value="0">[ Selecionar ]</option>
				  	<option value="1">Femenino</option>
				  	<option value="2">Masculino</option>
				</select>
			</div>
			<div class="form-input">
				<label form="Correo">Correo Electrónico</label>
				<input type="text" name="correo" required="required">
			</div>
			<div class="form-input">
				<label form="Motivo">Motivo para ir a la luna</label>
				<input type="text" name="motivo" required="required">
			</div>
			<div class="form-input">
				<label form="Contacto">Última vez que tuvo contacto con extraterrestres</label>
				<input type="text" name="contacto" required="required">
			</div>
			<div class="form-input">
				<input type="submit" value="Enviar">
			</div>
		</form>
	
	<?php
	return ob_get_clean();

}


// Shortcode que llama el formulario nasa_gracias
add_shortcode('nasa_gracias', 'nasa_gracias');

function nasa_gracias()
{

	global $wpdb;
	$tabla_nasa_configuracion = $wpdb->prefix . 'nasa_configuracion';
	$configuracion = $wpdb->get_results("SELECT * FROM $tabla_nasa_configuracion ORDER BY configuracionId LIMIT 1 ");


	foreach ($configuracion as $configura) {
		$configura = esc_textarea( $configura->mensaje );
	?>

	<div>
		<p><?php echo $configura; ?></p>
	</div>

	<?php
	}
}

add_action("admin_menu", "Astronauta_menu");

// Agregar el menu del plugin al formulario de wordpress
function Astronauta_menu(){

	add_menu_page("Formulario de Astronatutas", "NASA clientes", "manage_options", "NASA_clientes", "astronauta_admin", "dashicons-feedback", 75);

	add_submenu_page("NASA_clientes","Formulario de Configuración","Configuración","manage_options","NASA_configuracion","configuracion_admin");

	add_submenu_page("NASA_clientes","Reportes","Reporte","manage_options","NASA_reporte","reporte_admin");
}

if (!function_exists("configuracion_admin")) {
	function configuracion_admin(){

		global $wpdb;

		if (!empty($_POST) AND $_POST['introduccion'] != '' AND $_POST['mensaje'] != '' AND $_FILES['imagen'] != ''){

			$tabla_nasa_configuracion = $wpdb->prefix . 'nasa_configuracion';

			$introduccion = sanitize_text_field($_POST['introduccion']);
			$mensaje = sanitize_text_field($_POST['mensaje']);

			$upload_dir_var = wp_upload_dir(); 
		    $upload_dir = $upload_dir_var['path']; 

		    $filename = basename($_FILES['imagen']['name']); 
		    $filename = trim($filename); 		    
		    $filename = preg_replace(" ", "-", $filename);

		    $typefile = $_FILES['imagen']['type'];

		    $uploaddir = realpath($upload_dir); 

		    $uploadfile = $uploaddir.'/'.$filename; 

		    $slugname = preg_replace('/\.[^.]+$/', '', basename($uploadfile)); 

		    if ( file_exists($uploadfile) ) { 
		          $count = "0";
		          while ( file_exists($uploadfile) ) {
		          $count++;
		          if ( $typefile == 'image/jpeg' ) { $exten = 'jpg'; }
		          elseif ( $typefile == 'image/png' ) { $exten = 'png'; }
		          elseif ( $typefile == 'image/gif' ) { $exten = 'gif'; }
		          $uploadfile = $uploaddir.'/'.$slugname.'-'.$count.'.'.$exten;
		          }
		    }

			$wpdb->insert(
				$tabla_nasa_configuracion, 
				array(
					'introduccion' => $introduccion,
					'mensaje' => $mensaje,
					'logo' => $uploadfile,
				)
			);

		}

		//ob_start();
		?>

	        <div class="wrap"><h1>Formulario de Configuración</h1></div>

	        <form action="<?php get_the_permalink(); ?>" method="POST" enctype="multipart/form-data">
	            <?php wp_nonce_field('graba_configuracion', 'configuracion_nonce'); ?>
				<div class='form-input'>
					<label form='introduccion'>Introducción</label>
					<input type='text' name='introduccion' required='required'>
				</div>
				<div class='form-input'>
					<label form='mensaje'>Mensaje</label>
					<input type='text' name='mensaje' required='required'>
				</div>
	            <div class='form-input'>
					<label form='logo'>Logo</label>
					<input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
					<input type="file" name="imagen"  />
				</div>
				<div class='form-input'>
					<input type='submit' value='Guardar'>
				</div>
	        </form>

	    <?php
	   	//return ob_get_clean();
	}
}
function reporte_admin(){

	global $wpdb;
	$tabla_nasa_astronauta = $wpdb->prefix . 'nasa_astronautas';
	$astronautas = $wpdb->get_results("SELECT * FROM $tabla_nasa_astronauta");

	echo '<div class="wrap"><h1>Listado de Posibles Astronautas para el 2022</h1>';
	echo '<table class="wp-list-table widefat fixed striped">';
	echo '<thead><tr><th>Nombre</th><th>Edad</t>';
	echo '<th>Sexo</th><th width="20%">Correo</th><th width="30%">Motivo</th><th>Contacto</th>';
	echo '</tr></thead>';
	echo '<tbody id="the-list">';

	foreach ($astronautas as $astronauta) {

		$nombre = esc_textarea( $astronauta->nombre );
		$edad = (int)( $astronauta->edad );

		if((int)( $astronauta->sexo ) == 1){
			$sexo = 'Femenino';
		}else{
			$sexo = 'Masculino';
		}

		$correo = esc_textarea( $astronauta->correo );
		$motivo = esc_textarea( $astronauta->motivo );
		$fecha = DateTime::createFromFormat('Y-n-j', $astronauta->contacto );
		$contacto = $fecha->format('d/m/Y');

		echo "<td>$nombre</td><td>$edad</td><td>$sexo</td><td>$correo</td><td>$motivo</td><td>$contacto</td></tr>";

	}
}
