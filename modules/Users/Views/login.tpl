		<h3 class="header">{{ lang('users.titles.login') }}</h3>

		<form action="{{ scripturl }}/login/submit/" method="post" class="form-vertical">
{% if error is not null %}
			<div class="alert alert-error">
				{{ error }}
			</div>
{% endif %}
			<div class="well">
				<div class="control-group">
					<label class="control-label">
						{{ lang('users.login.username') }}:
					</label>
					<div class="controls">
						<input type="text" name="username" size="30" value="{{ username }}" />
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">
						{{ lang('users.login.password') }}:
					</label>
					<div class="controls">
						<input type="password" name="password" id="password" size="30" />
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">
						{{ lang('users.login.time') }}:
					</label>
					<div class="controls">
						<select name="cookie_time">
							<option value="3600"{{ cookie_time == 3600 ? ' selected="selected"' : '' }}>1 Hour</option>
							<option value="86400"{{ cookie_time == 86400 ? ' selected="selected"' : '' }}>1 Day</option>
							<option value="604800"{{ cookie_time == 604800 ? ' selected="selected"' : '' }}>1 Week</option>
							<option value="2592000"{{ cookie_time == 2592000 ? ' selected="selected"' : '' }}>1 Month</option>
							<option value="189216000"{{ cookie_time == 189216000 ? ' selected="selected"' : '' }}>Forever</option>
						</select>
					</div>
				</div>

				<script type="text/javascript" src="{{ default_theme_url }}/scripts/hash.js?{{ reload_counter }}"></script>
				<script type="text/javascript">
					$("#login_form").bind('submit', function()
					{
						// Pre-hash the password, and remove the plaintext version so it doesn't get passed along.
						$('<input type="hidden" name="password_hashed" id="password_hashed" />').attr('value', Sha256.hash($("#password").val())).appendTo("#login_form");
						$("#password").val("");
					});
				</script>
			</div>

			<div class="well alternate center">
				<input class="btn btn-primary" type="submit" name="submit" value="{{ lang('users.log_in') }}" />
			</div>
		</form>