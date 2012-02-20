<?php

// Email class
// for mail-handler v0.1

	if (!class_exists('Logger')) {
		class Logger {
			function write($str) { echo trim($str) . "\n"; }
		}
	}

	class Email {
		var $subject, $body, $to, $from, $cc, $bcc, $tplfile;
		var $attachments;
		var $send_mime = false;
		var $vars = array();

		function set_file($tplfile) {
			if (!file_exists($tplfile)) {
				Logger::write("Specified file doesn't exist: $tplfile", "Email::set_file");
				return false;
			}
			$this->tplfile = $tplfile;
		}

		function assign($key, $val = false) {
			$this->vars['%'.$key.'%'] = $val;
		}

		function attach($file, $type, $name = null) {
			if ($name === null) {
				$name = basename($file);
			}
			if (!file_exists($file)) {
				return false;
			}
			if (!is_readable($file)) {
				return false;
			}
			$this->attachments[] = array($file, $type, $name);
			$this->send_mime = true;
		}

		function send() {
			if (!$this->to) {
				Logger::write("Please specify a recipient", "Email::send");
			}
			$body = file_get_contents($this->tplfile);
			$lines = explode("\n", $body);
			if (preg_match('/^subject: (.*)$/i', $lines[0], $matches)) {
				$this->subject = trim($matches[1]);
				unset($lines[0]);
				$body = implode("\n", $lines);
			}
			if (!isset($this->subject)) {
				Logger::write("Please provide a subject (or empty string)", "Email::send");
				return false;
			}
			if (!is_array($this->to)) $this->to = array($this->to);
			$success = true;
			foreach ($this->to as $recipient) {
				$result = $this->do_send($recipient, $body);
				#$result = mail($recipient, $subject, $body, $headers);
				#echo "<!-- mailed to $recipient: $body -->\n";
				$success = $success && $result;
			}
			return $success;
		}

		function do_send($to, $body) {
			$this->assign('to', $to);
			$search = array_keys($this->vars);
			$replace = $this->vars;
			$body = str_replace($search, $replace, $body);
			$subject = str_replace($search, $replace, $this->subject);
			if ($this->from) {
				$headers = "From: $this->from";
			} else {
				$headers = '';
			}
			if ($this->cc) {
				if ($headers) $headers .= "\n";
				$headers .= "Cc: $this->cc";
			}
			if ($this->bcc) {
				if ($headers) $headers .= "\n";
				$headers .= "Bcc: $this->bcc";
			}
			if ($this->send_mime) {
				$rnd = md5(time());
				$bnd = "==Multipart_Boundary_x${rnd}x";
				$headers .= "\nMIME-Version: 1.0";
				$headers .= "\nContent-Type: multipart/mixed;";
				$headers .= " boundary=\"${bnd}\"";
				$message = "This is a multi-part message in MIME format.\n\n" . "--${bnd}\n";
				$message .= "Content-Type: text/plain; charset=\"utf-8\"\n";
				$message .= "Content-Transfer-Encoding: 7bit\n\n";
				$message .= $body . "\n\n";
				if (count($this->attachments)) foreach ($this->attachments as $attach_info) {
					if (!is_array($attach_info) || empty($attach_info[0])) continue;
					list($file, $type, $name) = $attach_info;
					$fp = fopen($file, 'rb');
					$data = fread($fp, filesize($file));
					fclose($fp);
					$data = chunk_split(base64_encode($data));
					$message .= "--${bnd}\n";
					$message .= "Content-Type: $type;\n";
					$message .= " name=\"$name\"\n";
					$message .= "Content-Disposition: attachment;\n";
					$message .= " filename=\"$name\"\n";
					$message .= "Content-Transfer-Encoding: base64\n\n";
					$message .= $data . "\n\n";
				}
				$message .= "--${bnd}--\n";
				$result = mail($to, $subject, $message, $headers);
			} else {
				$result = mail($to, $subject, $body, $headers);
			}
			return $result;
		}
	}

?>
