<h3>Languages</h3>

<table class="table table-striped">
	<tr>
		<th width="5%">ID</th>
		<th width="65%">Name</th>
		<th>Locale</th>
		<th width="5%"></th>
	</tr>
{% for language in languages %}
	<tr>
		<td>{{ language->id_language }}</td>
		<td>{{ language->language_name }}</td>
		<td>{{ language->language_code }}</td>
		<td>
			<a href="#" class="btn">Edit</a>
		</td>
	</tr>
{% endfor %}
</table>

<h3>Packages</h3>

<table class="table table-striped">
	<tr>
		<th width="5%">ID</th>
		<th width="65%">Name</th>
		<th>Type</th>
		<th width="5%"></th>
	</tr>
{% for package in packages %}
	<tr>
		<td>{{ package->id_package }}</td>
		<td>{{ package->package_name }}</td>
		<td>{{ package->package_type }}</td>
		<td>
			<a href="#" class="btn">Edit</a>
		</td>
	</tr>
{% endfor %}
</table>