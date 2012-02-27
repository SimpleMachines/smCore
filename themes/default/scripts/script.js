var Core = {};

$(function()
{
	$("#js_warning").hide();
});

function check_all(parent, reverse)
{
	$("#" + parent + " input[type='checkbox']").attr("checked", !reverse);
}

var write_all_day = function()
{
	var which = this.id.replace("_allday", "");
	var timebox = $("#" + which + "_timebox");
	var endbox = $("#end_date_box");
	var justtoday = $("#justtoday");

	if ($(timebox).css("display") == "none")
		$(timebox).show();
	else
	{
		$(timebox).hide();

		if (which == "end")
		{
			$("#end_time").val("5:00");
			$("#end_ampm").val("PM");
		}
	}

	if (which == "start")
	{
		if ($(justtoday).css("display") == "none")
		{
			$(justtoday).show().attr("checked", "unchecked");

			$("#start_time").val("8:00");
			$("#start_ampm").val("AM");
		} 
		else
		{
			$(justtoday).hide();

			if ($(endbox).css("display") == "none")
				$(endbox).show();
		}
	}
}

var write_validate = function()
{
	error = "";

	if ($("#users_table " + (editing ? "input[type=radio]:checked" : "input[type=checkbox]:checked")).length < 1)
		error = "Please select at least one user.";
	else
	{
		start_month = $("#start_month").val();
		start_day = $("#start_day").val();
		start_year = $("#start_year").val();
		start_time = $("#start_time").val();
		start_ampm = $("#start_ampm").val();

		end_month = $("#end_month").val();
		end_day = $("#end_day").val();
		end_year = $("#end_year").val();
		end_time = $("#end_time").val();
		end_ampm = $("#end_ampm").val();

		if (end_year < start_year)
			error = "The end year can\'t be before the start year!";
		else if (end_year == start_year)
		{
			if (end_month < start_month)
				error = "The end month can\'t be before the start month!";
			else if (end_month == start_month)
			{
				if (end_day < start_day)
					error = "The end day can\'t be before the start day!";
				else if (end_day == start_day)
				{
					// here\'s where it gets tricky...
					if (end_ampm == "AM" && start_ampm == "PM")
						error = "The end time can\'t be before the start time! (AM vs. PM)";
					else if (end_ampm == start_ampm)
					{
						start_broken = start_time.split(":");
						end_broken = end_time.split(":");

						if (typeof(start_broken[1]) == "undefined")
							start_broken[1] = 0;

						if (start_broken[0] == 12)
							start_broken[0] = 0;

						if (typeof(end_broken[1]) == "undefined")
							end_broken[1] = 0;

						if (end_broken[0] == 12)
							end_broken[0] = 0;

						end_broken[0] = Math.floor(end_broken[0]);
						end_broken[1] = Math.floor(end_broken[1]);
						start_broken[0] = Math.floor(start_broken[0]);
						start_broken[1] = Math.floor(start_broken[1]);

						if (end_broken[0] == start_broken[0])
						{
							if (start_broken[1] == end_broken[1])
								error = "The end time can\'t be equal to the start time!";
							else if (end_broken[1] < start_broken[1])
								error = "The end time can\'t be before the start time! (Minutes)";
						}
						else if (end_broken[0] < start_broken[0])
							error = "The end time can\'t be before the start time! (Hours)";

						if ((end_broken[0] > 12 && end_broken[0] < 25) || (start_broken[0] > 12 && start_broken[0] < 25))
							error = "We\'re not in the military, bub!";
						else if (end_broken[1] > 60 || end_broken[1] < 0 || start_broken[1] > 60 || start_broken[1] < 0)
							error = "We count minutes like car acceleration.";			
					}
				}
			}
		}
	}

	if (error != "")
	{
		$("#error_text").html(error);
		$("#warning_box").fadeIn();
	}
	else
		$("#warning_box").fadeOut();
}

var write_just_today = function()
{
	if ($("#end_date_box").css("display") == "none")
		$("#end_date_box").show();
	else
	{
		$("#end_time").val("5:00");
		$("#end_ampm").val("PM");

		$("#end_date_box").hide();
	}
}

// Show a dialog (just using this for shorthand)
function show_dialog(dTitle, dText, dButtons, dWidth)
{
	if (dTitle == null)
		dTitle = "Error";
	if (dText == null)
		dText = "An unknown error occurred.";

	$("#dialog").find("p").html(dText).end().attr("title", dTitle).dialog({modal: true, width: dWidth, buttons: dButtons});
}

function clock_in(event)
{
	// jQuery UI isn't releasing this, probably something I'm doing wrong
	$(this).toggleClass("ui-state-focus", false);

	// If they're offsite, prompt them for a reason
	if (offsite)
	{
		show_dialog("Offsite Clockin", $("#offsite_message").html(), {
			"Clock In": function()
			{
				$(this).dialog("close");

				$.post(scripturl + "/time/clockin/", $("#dialog form").serialize(), function(data, status)
					{
						if (status != "success")
						{
							show_dialog("Error", "There was an error processing your request: " + status + ".");
							return;
						}

						if ("error" in data)
						{
							show_dialog("Error", data.error);
							return;
						}

						$("#header_clock_in").unbind("click").click(clock_out).val("Clock Out");

						show_dialog("Clocked In", data.message);
					}, "json"
				);
			},
			"Cancel": function()
			{
				$(this).dialog("close");
			}
		}, "400px");
	}
	else
	{
		// They're at VMG or Westridge, so just pass them through
		$.post(scripturl + "/time/clockin/", {"is_ajax": "yes"}, function(data, status)
			{
				if (status != "success")
				{
					show_dialog("Error", "There was an error processing your request: " + status + ".");
					return;
				}

				if ("error" in data)
				{
					show_dialog("Error", data.error);
					return;
				}

				$("#header_clock_in").unbind("click").click(clock_out).val("Clock Out");

				show_dialog("Clocked In", data.message);
			}, "json"
		);
	}

	event.preventDefault();
	return false;
}

function clock_out(event)
{
	// jQuery UI isn't releasing this, probably something I'm doing wrong
	$(this).toggleClass("ui-state-focus", false);

	// If they're offsite, prompt them for a reason
	if (offsite)
	{
		show_dialog("Offsite Clockin", $("#offsite_message").html(), {
			"Clock In": function()
			{
				$(this).dialog("close");

				$.post(scripturl + "/time/clockout/", $("#dialog form").serialize(), function(data, status)
					{
						if (status != "success")
						{
							show_dialog("Error", "There was an error processing your request: " + status + ".");
							return;
						}

						if ("error" in data)
						{
							show_dialog("Error", data.error);
							return;
						}

						$("#header_clock_in").unbind("click").click(clock_in).val("Clock In");

						show_dialog("Clocked Out", data.message);
					}, "json"
				);
			},
			"Cancel": function()
			{
				$(this).dialog("close");
			}
		}, "400px");
	}
	else
	{
		// They're at VMG or Westridge, so just pass them through
		$.post(scripturl + "/time/clockout/", {"is_ajax": "yes"}, function(data, status)
			{
				if (status != "success")
				{
					show_dialog("Error", "There was an error processing your request: " + status + ".");
					return;
				}

				if ("error" in data)
				{
					show_dialog("Error", data.error);
					return;
				}

				$("#header_clock_in").unbind("click").click(clock_in).val("Clock In");

				show_dialog("Clocked Out", data.message);
			}, "json"
		);
	}

	event.preventDefault();
	return false;
}

(function($)
{
	// Requires a source URL and a namespace. No hand-holding.
	$.fn.coreUserPicker = function(options)
	{
		// Requires jQuery UI's autocomplete
		if (typeof($.fn.autocomplete) !== "function")
			return this;

		var settings = {
			'source': Core.scripturl + '/api/core/user-search/',
			'limit': 1,
			'users': [],
			'input': $(this),
			'name': $(this).attr('name')
		};

		if (options)
			$.extend(settings, options);

		if (settings.source.length < 1)
			return this;

		var methods = {
			'add': function(id, username)
			{
				if (id == 0 || $('#' + settings.name + '_user_' + id).length > 0)
					return;

				var insertHTML = '<button id="' + settings.name + '_user_' + id + '" data-user-id="' + id + '" class="user_picker_button delete"><span>' + username + '</span></button>' + 
					'<input type="hidden" class="' + settings.name + '_values" name="' + settings.name + '_value' + (settings.limit == 1 ? '' : '[]') + '" value="' + id + '" />';

				settings.users_box.append(insertHTML);

				$('#' + settings.name + '_user_' + id).bind('click', methods.remove);

				settings.input.toggle(settings.limit == 0 || $('input.' + settings.name + '_values').length < settings.limit);
			},
			'remove': function(event)
			{
				$(this).next().remove();
				$(this).remove();

				settings.input.toggle(settings.limit == 0 || $('input.' + settings.name + '_values').length < settings.limit);

				event.preventDefault();
				return false;
			}
		};

		// Set a base name for all of the elements we may insert
		settings.name = $(this).attr("name");

		$(this).after('<div id="' + settings.name + '_users"></div>');
		settings.users_box = $('#' + settings.name + '_users');

		$(this).data("userpicker.settings", settings).autocomplete({
			source: function(request, response)
			{
				$.get(
					settings.source,
					{
						term: request.term
					},
					function(data)
					{
						if (data.length > 0)
						{
							unused = [];

							for (var user in data)
								if ($('#' + settings.name + '_user_' + data[user].id).length < 1)
									unused[unused.length] = data[user];

							if (unused.length == 1)
							{
								methods.add(unused[0].id, unused[0].value);
								settings.input.val('');
								return false;
							}
							else
								response(unused);
						}
					},
					"json"
				);
			},
			minLength: 2,
			select: function(event, ui)
			{
				if (ui.item)
					methods.add(ui.item.id, ui.item.value);

				return false;
			},
			close: function(event, ui)
			{
				$(this).val("");
			}
		});

		if (settings.users.length > 0)
			for (var user in settings.users)
				methods.add(settings.users[user][0], settings.users[user][1]);

		return this;
	};

	$.fn.coreColorPicker = function(options)
	{
		if (!this.length)
		{
			return this;
		}

		var defaults = {
			"colors": [
				['800000', '808000', '008000', '008080', '000080', '800080', '7f7f7f'],
				['804000', '408000', '008040', '004080', '400080', '800040', '666666'],
				['ff0000', 'ffff00', '00ff00', '00ffff', '0000ff', 'ff00ff', '4c4c4c'],
				['ff8000', '80ff00', '00ff80', '0080ff', '8000ff', 'ff0080', '333333'],
				['ff6666', 'ffff66', '66ff66', '66ffff', '6666ff', 'ff66ff', '191919'],
				['ffcc66', 'ccff66', '66ffcc', '66ccff', 'cc66ff', 'ff6fcf', '000000']
			],
			"defaultColor": "000000"
		};

		var options = $.extend({}, defaults, options);

		var container = $('<div class="ui-colorpicker ui-widget ui-widget-content ui-helper-clearfix ui-corner-all"></div>');

		colorTable = '<table class="ui-colorpicker-table">';

		// Create the cells
		for (var i = 0; i < options.colors.length; i++)
		{
			colorTable += '<tr>';

			for (var j = 0; j < options.colors[i].length; j++)
			{
				colorTable += '<td class="ui-colorpicker-td" title="' + options.colors[i][j] + '" style="background-color: #' + options.colors[i][j] + ';"></td>';
			}

			colorTable += '</tr>';
		}

		container.insertAfter($("body")).html(colorTable + "</table>").hide().find(".ui-colorpicker-td").bind('click', function(event)
		{
			color = $(this).attr('title');

			container.data('input').val(color).css('backgroundColor', '#' + color);
		});

		return this.each(function(index, elem)
		{
			var input = $(elem);

			// Set this CSS here because we only want it when JS is enabled
			$(elem).addClass('ui-colorpicker-input').css({
				'color': 'transparent',
				'textIndent': '5000px',
				'width': '50px',
				'backgroundColor': '#' + (elem.value || options.defaultColor)
			}).bind('click', function(event)
			{
				container.data('input', input).css({
					"left": input.offset().left,
					"top": input.offset().top + input.height() + 3,
				}).show();

				// Bind the cancel button to hide the container
				$(window).bind('click', function(event)
				{
					container.hide();
				});

				// Stop the hiding function from immediately triggering
				event.stopPropagation();
			});
		});
	};
})(jQuery);