		<h3 class="header">{{ lang('users.register.join_now') }}</h3>

		<form action="{{ scripturl }}/register/?step=finish" method="post" class="form-vertical">
			<div class="well">
				<div class="control-group">
					<label class="control-label">
						{{ lang('users.login.username') }}:
					</label>
					<div class="controls">
						<input type="text" name="new_account_username" id="new_account_username" size="30" />
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">
						{{ lang('users.login.email') }}:
					</label>
					<div class="controls">
						<input type="text" name="new_account_email" id="new_account_email" size="30" />
					</div>
				</div>

				<div class="control-group">
					<label class="control-label">
						{{ lang('users.login.password') }}:
					</label>
					<div class="controls">
						<input type="password" name="new_account_password" id="new_account_password" size="30" />
					</div>
				</div>

				<p class="help-block">
					{{ lang('users.register.disclaimer', scripturl) |raw }}
				</p>
			</div>

			<div class="well alternate center">
				<input class="btn btn-primary" type="submit" name="submit" value="{{ lang('users.register.create_account') }}" />
				<input type="hidden" name="step" value="1" />
			</div>
		</form>

		<script type="text/javascript" src="{{ default_theme_url }}/scripts/hash.js?{{ reload_counter }}"></script>
		<script type="text/javascript">
			$("#register_form").bind('submit', function()
			{
				// Pre-hash the password, and remove the plaintext version so it doesn't get passed along.
				$('<input type="hidden" name="password_hashed" id="password_hashed" />').attr('value', Sha256.hash($("#new_account_password").val())).appendTo("#register_form");
				$("#new_account_password").val("");
			});
		</script>