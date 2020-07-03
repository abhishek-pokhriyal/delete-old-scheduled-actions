<!DOCTYPE html>
<html>
<head>
	<title>Delete old actions</title>
	<script type="text/javascript" src="<?php echo esc_url( includes_url( 'js/jquery/jquery.js' ) ); ?>"></script>
	<script type="text/javascript" src="<?php echo esc_url( plugins_url( 'assets/dosa.js' , __FILE__ ) ); ?>"></script>
</head>
<body>
	<input type="hidden" name="ajax_url" value="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
	<form>
		<div class="form-group">
			<label for="initialLimit">Limit</label>
			<input type="number" id="initialLimit" name="limit">
		</div>

		<div class="form-group">
			<label for="stopProcess">Stop</label>
			<input type="checkbox" name="stopped" id="stopProcess">
		</div>

		<input type="button" value="Initiate delete" id="initiateDelete">
	</form>

	<div id="deletionStatus">
	</div>
</body>
</html>
