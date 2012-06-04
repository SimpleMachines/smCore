<table class="table table-striped">
	<tr>
		<th>Name</th>
		<th>Identifier</th>
		<th>Description</th>
		<th></th>
	</tr>
{% for module in modules|sort %}
	<tr>
		<td>{{ module.name }}</td>
		<td>{{ module.identifier }}</td>
		<td>{{ module.description }}</td>
		<td>
{% if module.routes.admin is defined %}
			<a href="{{ scripturl }}/{{ module.routes.admin.match }}" class="btn">Administrate</a>
{% endif %}
		</td>
	</tr>
{% endfor %}
</table>