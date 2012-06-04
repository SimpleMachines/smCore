		<div class="well">
			<form action="{$context.scripturl}/register/resend/" method="post">
				{{ lang('users.register.resend_message') }}
				<input type="text" name="activate_email" size="25" /><br />
				<br />
				<input class="btn btn-success" type="submit" name="submit" value="{{ lang('users.register.resend') }}" />
			</form>
		</div>