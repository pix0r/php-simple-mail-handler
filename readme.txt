mail-handler 0.1

This is a simple formmail tool that is fairly customizable and works with attachments.  Instructions:

Create a form and point it at mail-handler.php.  

Edit mail-handler.php to call out field names and types (string, text, email, file), specify which fields are required, and configure the message.  $formvars should call out all fields being sent from the form, and their types.  $required should contain an array of fields required before the message will be sent.  $email contains the actual email configuration, including template filename, To:, From:, Cc:, Bcc:, which files to attach, and where to direct the browser on success or failure.

Create a message template.  mail-handler.php will automatically replace text such as %name% with a form variable called 'name' if it is specified in the configuration.  It also includes %remote_ip% and %date% by default.

Cross your fingers and try it out.
