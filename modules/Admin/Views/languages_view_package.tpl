<h3>Viewing Package: {{ package->package_name }}</h3>

<table class="table table-striped">
	<tr>
		<th>Key</th>
		<th width="65%">Value</th>
		<th width="5%"></th>
	</tr>
{% for string in strings %}
	<tr>
		<td>
			{{ string->string_key }}
		</td>
		<td>
			{{ string->string_value }}
		</td>
		<td>
			<a href="#" class="btn">Edit</a>
		</td>
	</tr>
{% endfor %}
</table>

<h3>Add a String</h3>

<form class="form-horizontal">
	<div class="control-group">
		<label class="control-label">Key</label>

		<div class="controls">
			<input type="text" name="string_key" id="string_key" />
		</div>
	</div>
	<div class="control-group">
		<label class="control-label">Value</label>

		<div class="controls">
			<textarea name="string_value" id="string_value"></textarea>
		</div>
	</div>
	<div class="control-group">
		<label class="control-label"></label>

		<div class="controls">
			<button class="btn btn-success">Save</button>
		</div>
	</div>
</form>
