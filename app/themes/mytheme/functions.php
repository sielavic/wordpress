<?php
/**
 * Sage includes
 *
 * The $sage_includes array determines the code library included in your theme.
 * Add or remove files to the array as needed. Supports child theme overrides.
 *
 * Please note that missing files will produce a fatal error.
 *
 * @link https://github.com/roots/sage/pull/1042
 */
$sage_includes = [
  'lib/assets.php',    // Scripts and stylesheets
  'lib/extras.php',    // Custom functions
  'lib/setup.php',     // Theme setup
  'lib/titles.php',    // Page titles
  'lib/wrapper.php',   // Theme wrapper class
  'lib/customizer.php' // Theme customizer
];

foreach ($sage_includes as $file) {
  if (!$filepath = locate_template($file)) {
    trigger_error(sprintf(__('Error locating %s for inclusion', 'sage'), $file), E_USER_ERROR);
  }

  require_once $filepath;
}
unset($file, $filepath);


add_shortcode( 'art_feedback', 'art_feedback' );

function art_feedback() {

	ob_start();
	?>
	
	<div class="container">
	<div class="title title_huge" id="toform">Задать вопрос</div>
					<form class="form mrgn35" id="add_feedback" method="GET">
				    <div class="form_single">
					<div class="form__col form__col_double">
					    <div class="singlerow singlerow_mrgn15">
						<div class="labelwrap labelwrap_single">
				    <input type="text" id="ex_name" class="input2 ex_name required" name="ex_name"  placeholder="Фамилия Имя Отчество*" value="">
				    
				    
				</div>           
				 </div>

					    <div class="dualrow">
						<div class="dualrow">
						    <div class="labelwrap labelwrap_dual">
				    <input type="tel" id="ex_phone" class="input2 ex_phone" name="ex_phone" placeholder="Мобильный телефон*" value="">
				   
				</div>
						    <div class="labelwrap labelwrap_dual">
				    <input type="email" id="ex_email" class="input2 ex_email required" name="ex_email" placeholder="Электронная почта" value="">
				    
				</div>                </div>
						<div class="singlerow">
						    <div class="labelwrap labelwrap_single">
				    <input type="text" id="ex_birthday" class="input2 ex_birthday" name="ex_birthday" placeholder="Дата рождения*" value="">
				    
				</div>                </div>
					    </div>
					</div>
					
					<input type="checkbox" name="art_anticheck" id="art_anticheck" class="art_anticheck" style="display: none !important;" value="true" checked="checked"/>

		<input type="text" name="art_submitted" id="art_submitted" value="" style="display: none !important;"/>
					
					
					<div class="form__col form__col_double">
					    <div class="labelwrap labelwrap_single textarea_large">
				    <textarea name="ex_additional" id="ex_additional" class="textarea textarea_large ex_additional required" placeholder="Любая дополнительная информация"></textarea>
				    
				</div>      
				  </div>

				    </div>


 		




				    <div class="singlerow_privacy ">
				    <div class="form__privacy">
					Отправляя форму я соглашаюсь с <a class="form__privacy_link" href="#">условиями
					    передачи информации</a>
				    </div>
				    <input type="submit" id="submit-feedback" class="submit submit_privacy button" value="Задать вопрос">
				</div>
				</form>
	</div>
	
	<?php

	return ob_get_clean();
}




























add_action( 'wp_enqueue_scripts', 'art_feedback_scripts' );

function art_feedback_scripts() {

	
	wp_enqueue_script( 'jquery-form' );

	
	wp_enqueue_script(
		'feedback',
		get_stylesheet_directory_uri() . '/feedback.js',
		array( 'jquery' ),
		1.0,
		true
	);

	
	wp_localize_script(
		'feedback',
		'feedback_object',
		array(
			'url'   => admin_url( 'admin-ajax.php' ),
			'nonce' => wp_create_nonce( 'feedback-nonce' ),
		)
	);

}









add_action( 'wp_ajax_feedback_action', 'ajax_action_callback' );
add_action( 'wp_ajax_nopriv_feedback_action', 'ajax_action_callback' );

function ajax_action_callback() {

	// Массив ошибок
	$err_message = array();

	// Проверяем nonce. Если проверкане прошла, то блокируем отправку
	if ( ! wp_verify_nonce( $_POST['nonce'], 'feedback-nonce' ) ) {
		wp_die( 'Данные отправлены с левого адреса' );
	}

	// Проверяем на спам. Если скрытое поле заполнено или снят чек, то блокируем отправку
	if ( false === $_POST['art_anticheck'] || ! empty( $_POST['art_submitted'] ) ) {
		wp_die( 'Ожидайте пожалуйста(c)' );
	}

	// Проверяем полей имени, если пустое, то пишем сообщение в массив ошибок
	if ( empty( $_POST['ex_name'] ) || ! isset( $_POST['ex_name'] ) ) {
		$err_message['name'] = 'Пожалуйста, введите ваше имя.';
	} else {
		$ex_name = sanitize_text_field( $_POST['ex_name'] );
	}

	// Проверяем полей емайла, если пустое, то пишем сообщение в массив ошибок
	if ( empty( $_POST['ex_email'] ) || ! isset( $_POST['ex_email'] ) ) {
		$err_message['email'] = 'Пожалуйста, введите адрес вашей электронной почты.';
	} elseif ( ! preg_match( '/^[[:alnum:]][a-z0-9_.-]*@[a-z0-9.-]+\.[a-z]{2,4}$/i', $_POST['ex_email'] ) ) {
		$err_message['email'] = 'Адрес электронной почты некорректный.';
	} else {
		$ex_email = sanitize_email( $_POST['ex_email'] );

	}
	// Проверяем полей темы письма, если пустое, то пишем сообщение по умолчанию
	if ( empty( $_POST['ex_phone'] ) || ! isset( $_POST['ex_phone'] ) ) {
		$err_message['phone'] = 'Пожалуйста, введите ваше сообщение.';
	} else {
		$ex_phone = sanitize_text_field( $_POST['ex_phone'] );
	}

	// Проверяем полей сообщения, если пустое, то пишем сообщение в массив ошибок
	if ( empty( $_POST['ex_birthday'] ) || ! isset( $_POST['ex_birthday'] ) ) {
		$err_message['birthday'] = 'Пожалуйста, введите дату рождения.';
	} else {
		$ex_birthday = sanitize_textarea_field( $_POST['ex_birthday'] );
	}

        if ( empty( $_POST['ex_additional'] ) || ! isset( $_POST['ex_additional'] ) ) {
		$err_message['additional'] = 'Пожалуйста, что-нибудь напишите.';
	} else {
		$ex_additional = sanitize_textarea_field( $_POST['ex_additional'] );
	}


	// Проверяем массив ошибок, если не пустой, то передаем сообщение. Иначе отправляем письмо
	if ( $err_message ) {

		wp_send_json_error( $err_message );

	} else {

		// Указываем адресата
		$email_to = '';

		// Если адресат не указан, то берем данные из настроек сайта
		if ( ! $email_to ) {
			$email_to = get_option( 'admin_email' );
		}

		$body    = "Имя: $ex_name \nEmail: $ex_email \nEmail: $ex_phone \nEmail: $ex_birthday \n\nСообщение: $ex_additional ";
		$headers = 'From: ' . $ex_name . ' <' . $email_to . '>' . "\r\n" . 'Reply-To: ' . $email_to;

		// Отправляем письмо
		wp_mail( $email_to, $ex_additional, $body, $headers );

		// Отправляем сообщение об успешной отправке
		$message_success = 'Собщение отправлено. В ближайшее время я свяжусь с вами.';
		wp_send_json_success( $message_success );
	}

	// На всякий случай убиваем еще раз процесс ajax
	wp_die();

}













