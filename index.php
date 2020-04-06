<html>
<head>
<title>Hello PHP!</title>
<script src="<?php echo $_ENV['ELASTICIO_WIDGET_URL'] ?>"></script>
</head>
<body>
<h1>PHP Demo of elastic.io contact importer</h1>

<button onclick="elasticio.contacts()"><strong>Click me!</strong></button>

<p>This demo project demonstrate how you can connect your App to Xing,
 LinkedIn, Google Contacts, Salesforce and many other APIs to import
 (and synchronise) contact data.
 See more on <a href="http://www.elastic.io/contact_importer">here</a></p>
 
<h2>Imported contacts:</h2>
<ul id="contacts"></ul>
<script>
	// Optional identification step
	// see here for more information http://www.elastic.io/docs#identifying-widget-users
	elasticio.identify({
		id : 'my-superuser-id',
		email : 'chuck@norris.com',
		name : 'Chuck Norris'
	});
	// Registering event listener that will get the data
	elasticio.on('contact', function(contact) {
		// Now contact data is stored in 'contact' property
		// exact format of the contact data see here - http://www.elastic.io/docs#canonical-contact-model
		var para = document.createElement("li");
		var node = document.createTextNode(contact.name.firstName + ' ' + contact.name.lastName);
		para.appendChild(node);

		var element = document.getElementById("contacts");
		element.appendChild(para);
	});
</script>
</body>
</html>
