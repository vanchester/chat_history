if (window.rcmail) {
	var settings = $.extend(rcmail.env.calendar_settings, rcmail.env.libcal_settings);

	var datepicker_settings = {
		dateFormat: settings['date_format'].replace(/M/g, 'm').replace(/mmmmm/, 'MM').replace(/mmm/, 'M').replace(/dddd/, 'DD').replace(/ddd/, 'D').replace(/yy/g, 'y'),
		firstDay : settings['first_day'],
		dayNamesMin: settings['days_short'],
		monthNames: settings['months'],
		monthNamesShort: settings['months'],
		changeMonth: false,
		showOtherMonths: true,
		selectOtherMonths: true
	};

	rcmail.addEventListener('init', function(evt) {
		if (rcmail.env.task != 'chat_history') {
			return;
		}

		$li = $('#contactlist-content li');
		$li.on('click', function () {
			show_chat_history({
				'jid': $(this).attr('id'),
				'date': $('#datepicker').datepicker({ dateFormat: 'yyyy-mm-dd' }).val()
			});
			$li.filter('.selected').removeClass('selected');
			$(this).addClass('selected');
			$('#searchreset').trigger('click');
		});
	});

	rcmail.addEventListener('plugin.show_chat_history', show_chat_history);
	rcmail.addEventListener('plugin.update_history', update_history);
}

$(document).ready(function () {
	// initialize small calendar widget using jQuery UI datepicker
	var minical = $('#datepicker').datepicker($.extend(datepicker_settings, {
		inline: true,
		showWeek: false,
		changeMonth: true,
		changeYear: true,
		onSelect: function(dateText, inst) {
			show_chat_history({
				'jid': $('#contactlist-content li.selected').attr('id'),
				'date': $('#datepicker').datepicker({ dateFormat: 'yyyy-mm-dd' }).val()
			});
			$('#searchreset').trigger('click');
		}
	})) // set event handler for clicks on calendar week cell of the datepicker widget
	.click(function(e) {
		var cell = $(e.target);
		if (e.target.tagName == 'TD' && cell.hasClass('ui-datepicker-week-col')) {
			var base_date = minical.datepicker('getDate');
			if (minical.data('month'))
				base_date.setMonth(minical.data('month')-1);
			if (minical.data('year'))
				base_date.setYear(minical.data('year'));
			base_date.setHours(12);
			base_date.setDate(base_date.getDate() - ((base_date.getDay() + 6) % 7) + datepicker_settings.firstDay);
			var day_off = base_date.getDay() - datepicker_settings.firstDay;
			var base_kw = iso8601Week(base_date);
			var target_kw = parseInt(cell.html());
			var diff = (target_kw - base_kw) * 7 * DAY_MS;
			// select monday of the chosen calendar week
			var date = new Date(base_date.getTime() - day_off * DAY_MS + diff);
			minical.datepicker('setDate', date);
		}
	});

	$('#searchreset').click(reset_quicksearch);

	$('#qicksearchform').submit(quicksearch);
});

function update_history(params) {
	$('.messagelistcontainer').html(params.content);
}

function show_chat_history(params)
{
	var query = (typeof params.search != 'undefined')
			? '_q=' + params.search + '&_jid=' + (params.jid ? params.jid : '')
			: '_act=show_chat_history&_jid=' + (params.jid ? params.jid : '') + '&_dt=' + params.date;

	lock = rcmail.set_busy(true, 'searching');
	rcmail.http_post('show_chat_history', query, lock);
}

function quicksearch()
{
	show_chat_history({
		'search': $('#quicksearchbox').val(),
		'jid': $('#contactlist-content li.selected').attr('id')
	});
	return false;
}

function reset_quicksearch()
{
	$('#quicksearchbox').val('');
	return false;
}
