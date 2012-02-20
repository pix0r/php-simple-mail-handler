<?php

// mail-handler.php
// form handler and mailer
// version: 0.1

// CONFIGURATION

// List of data with types (email, string, text, int, file)
$formvars = array(
	'from'		=>	'email',
	'name'		=>	'string',
	'message'	=>	'text',
	'file1'		=>	'file',
	'file2'		=>	'file',
	);

// Required fields
// Will trigger an error if these are not present (and formatted properly)
$required = array(
	'from',
	'name',
	);

// Information for sending email (required: template, to, from)
// Text replacements will apply inside most of these
$email = array(
	// Email template file. Text with replacement vars. First line
	//   should be: "Subject: your subject here"
	'template'	=>	'message.tpl',
	'to'		=>	'form_submission@yourdomain.com',
	'from'		=>	'%name% <%from%>',
	'cc'		=>	'someone_else@yourdomain.com',
	'bcc'		=>	'',
	// Array of file variables
	'attach'	=>	array('file1', 'file2'),
	// Page to redirect to on success
	'success'	=>	'success.html',
	// Page to redirect to on error (with messages attached as err[])
	// Default is HTTP_REFERER
	'error'		=>	false,
	#'error'		=>	'error.html',
	);

require_once('email.class.php');

$err_arr = array();
if (!empty($_REQUEST)) {
	$data = array();
	foreach ($formvars as $name => $type) {
		$val = isset($_REQUEST[$name]) ? $_REQUEST[$name] : null;
		switch ($type) {
			case 'string':
			default:
				$val = str_replace(array("\r\n", "\n"), array('',''), $val);
				break;
			case 'text':
				// Nothing
				break;
			case 'email':
				if (preg_match('/^[a-z0-9\._\-=]+@(?:(?:[a-z0-9\-]+\.)+[a-z]{2,4}|(?:[0-9]{1,3}\.){3}[0-9]{1,3})$/i', $val)) {
				} else {
					$val = null;
					$err_arr[$name] = "Invalid email for field: $name";
				}
				break;
			case 'int':
				$val = ($val === null) ? null : intval($val);
				break;
			case 'file':
				if (isset($_FILES[$name])) {
					$val = $_FILES[$name]['name'];
				} else {
					$val = null;
				}
				break;
		}
		if (empty($val) && in_array($name, $required) && !isset($err_arr[$name])) {
			$err_arr[$name] = "Missing required field: $name";
		} else {
			$data[$name] = $val;
		}
	}
	if (!count($err_arr)) {
		$search = array();
		$replace = array();
		$msg = new Email();
		$other_data = array(
			'remote_ip'		=>	$_SERVER['REMOTE_ADDR'],
			'date'				=>	date("Y-m-d H:i:s"),
		);
		foreach (array_merge($other_data, $data) as $k => $v) {
			$search[] = "%$k%";
			$replace[] = $v;
			$msg->assign($k, $v);
		}
		$msg->to = str_replace($search, $replace, $email['to']);
		$msg->from = str_replace($search, $replace, $email['from']);
		$msg->cc = str_replace($search, $replace, $email['cc']);
		$msg->bcc = str_replace($search, $replace, $email['bcc']);
		if (isset($email['attach'])) {
			if (!is_array($email['attach'])) $email['attach'] = array($email['attach']);
			foreach ($email['attach'] as $name) {
				if (!isset($_FILES[$name]) || !$_FILES[$name]['size']) continue;
				$msg->attach($_FILES[$name]['tmp_name'], $_FILES[$name]['type'], $_FILES[$name]['name']);
				$msg->attachments[] = $_FILES[$name];
			}
		}
		$msg->set_file($email['template']);
		$msg->send();
		if ($email['success']) {
			header("Location: " . $email['success']);
		} else {
			echo "Successfully sent email";
		}
	} else {
		$err_qs = '';
		foreach ($err_arr as $err) {
			if ($err_qs) $err_qs .= "&";
			$err_qs .= 'err[]='.urlencode($err);
		}
		if (isset($email['error']) && $email['error']) {
			$redir = $email['error'];
		} else {
			$redir = $_SERVER['HTTP_REFERER'];
			// Strip off err[]..
			$redir = preg_replace('/[\?&]?err\[\].*/', '', $redir);
		}
		if (strpos($redir, '?') === false) {
			$redir .= "?$err_qs";
		} else {
			$redir .= "&$err_qs";
		}
		header("Location: $redir");
	}
}

?>
