<h3>Recently Registered</h3>

<table class="table table-striped" style="width: 100%">
	<tr>
		<th>ID</th>
		<th>Username</th>
		<th>Display Name</th>
		<th>Email</th>
		<th>Registered</th>
		<th width="1%"></th>
	</tr>
{% for user in users %}
	<tr>
		<td>{{ user->getId() }}</td>
		<td>{{ user->getUsername() }}</td>
		<td>{{ user->getDisplayName() }}</td>
		<td>{{ user->getEmail() }}</td>
		<td>{{ user->getRegistered() |date }}</td>
		<td>
			<a href="#" class="btn">More&nbsp;Info</a>
		</td>
	</tr>
{% endfor %}
</table>