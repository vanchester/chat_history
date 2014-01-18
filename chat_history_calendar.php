<?php

class chat_history_calendar
{
	public static $defaults = array(
		'default_view' => "agendaWeek",
		'timeslots'    => 2,
		'work_start'   => 6,
		'work_end'     => 18,
		'agenda_range' => 60,
		'agenda_sections' => 'smart',
		'event_coloring'  => 0,
		'time_indicator'  => true,
		'allow_invite_shared' => false,
		'date_format'  => "yyyy-MM-dd",
		'date_short'   => "M-d",
		'date_long'    => "MMM d yyyy",
		'date_agenda'  => "ddd MM-dd",
		'time_format'  => "HH:mm",
		'first_day'    => 1,
		'first_hour'   => 6,
		'date_format_sets' => array(
			'yyyy-MM-dd' => array('MMM d yyyy',   'M-d',  'ddd MM-dd'),
			'dd-MM-yyyy' => array('d MMM yyyy',   'd-M',  'ddd dd-MM'),
			'yyyy/MM/dd' => array('MMM d yyyy',   'M/d',  'ddd MM/dd'),
			'MM/dd/yyyy' => array('MMM d yyyy',   'M/d',  'ddd MM/dd'),
			'dd/MM/yyyy' => array('d MMM yyyy',   'd/M',  'ddd dd/MM'),
			'dd.MM.yyyy' => array('dd. MMM yyyy', 'd.M',  'ddd dd.MM.'),
			'd.M.yyyy'   => array('d. MMM yyyy',  'd.M',  'ddd d.MM.'),
		),
	);

	/**
	 * @var chat_history
	 */
	private $plugin;

	/**
	 * @var rcube
	 */
	private $rc;

	public function __construct(chat_history $plugin)
	{
		$this->plugin = $plugin;
		$this->rc = $plugin->getRcube();

		$this->rc->output->set_env('calendar_settings', $this->get_settings());
		$this->plugin->include_script('chathistory.js');

		// include color picker
		$this->plugin->include_script('jquery.miniColors.min.js');
		$this->plugin->include_stylesheet($this->plugin->local_skin_path() . '/jquery.miniColors.css');
		$this->rc->output->set_env('mscolors', $this->get_color_values());
	}

	public static function get_settings()
	{
		return self::$defaults;
	}

	/**
	 * Return a (limited) list of color values to be used for calendar and category coloring
	 * @return mixed List for colors as hex values or false if no presets should be shown
	 */
	public static function get_color_values()
	{
		// selection from http://msdn.microsoft.com/en-us/library/aa358802%28v=VS.85%29.aspx
		return array('000000','006400','2F4F4F','800000','808000','008000',
			'008080','000080','800080','4B0082','191970','8B0000','008B8B',
			'00008B','8B008B','556B2F','8B4513','228B22','6B8E23','2E8B57',
			'B8860B','483D8B','A0522D','0000CD','A52A2A','00CED1','696969',
			'20B2AA','9400D3','B22222','C71585','3CB371','D2691E','DC143C',
			'DAA520','00FA9A','4682B4','7CFC00','9932CC','FF0000','FF4500',
			'FF8C00','FFA500','FFD700','FFFF00','9ACD32','32CD32','00FF00',
			'00FF7F','00FFFF','5F9EA0','00BFFF','0000FF','FF00FF','808080',
			'708090','CD853F','8A2BE2','778899','FF1493','48D1CC','1E90FF',
			'40E0D0','4169E1','6A5ACD','BDB76B','BA55D3','CD5C5C','ADFF2F',
			'66CDAA','FF6347','8FBC8B','DA70D6','BC8F8F','9370DB','DB7093',
			'FF7F50','6495ED','A9A9A9','F4A460','7B68EE','D2B48C','E9967A',
			'DEB887','FF69B4','FA8072','F08080','EE82EE','87CEEB','FFA07A',
			'F0E68C','DDA0DD','90EE90','7FFFD4','C0C0C0','87CEFA','B0C4DE',
			'98FB98','ADD8E6','B0E0E6','D8BFD8','EEE8AA','AFEEEE','D3D3D3',
			'FFDEAD'
		);
	}
}
