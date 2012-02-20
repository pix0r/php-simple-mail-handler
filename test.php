<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<title>Email Form</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>

<h1>Sample Form</h1>
<?php if (is_array($_GET['err'])) { ?>
	<fieldset id="fs_error">
		<legend>Error</legend>
		<?php foreach ($_GET['err'] as $err) { ?>
			<p><?= htmlentities($err) ?></p>
		<?php } ?>
	</fieldset>
<?php } ?>

<form action="mail-handler.php" method="post" enctype="multipart/form-data">
<fieldset>
	<legend>Feedback Form</legend>
	<label>Your Name: <input name="name" /></label><br />
	<label>Your Email: <input name="from" /></label><br />
	<label>File 1: <input type="file" name="file1" /></label><br />
	<label>File 2: <input type="file" name="file2" /></label><br />
	<label>Message: <textarea name="message"></textarea></label><br />
</fieldset>

<p><input type="submit" name="action" id="action" value="Send Form" /></p>

</form>

</body>
</html>
