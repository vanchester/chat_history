<?php

/**
 * ejabberd chat history Plugin
 *
 * @author vanchester <admin@vanchester.ru>
 * @licence GNU AGPL
 *
 * Configuration (see config.inc.php.dist)
 */

class chat_history extends rcube_plugin
{
	const TABLE_ARCHIVE = 'archive_collections';
	const TABLE_MESSAGES = 'archive_messages';
	const TABLE_ROSTERUSERS = 'rosterusers';

	/**
	 * @var rcube
	 */
	private $rc;

	/**
	 * @var rcube_db
	 */
	private $_db;

	/**
	 * @var chat_history_calendar
	 */
	private $calendar;

	public function init()
	{
		$this->rc = rcube::get_instance();

		$this->add_texts('localization/', false);

		// register task
		$this->register_task('chat_history');
		$this->register_action('index', array($this, 'index'));
		$this->register_action('show_chat_history', array($this, 'action_show_history'));

		// add taskbar button
		$this->add_button(array(
			'command'    => 'chat_history',
			'class'      => 'button-chat_history',
			'classsel'   => 'button-chat_history button-selected',
			'innerclass' => 'button-inner',
			'label'      => 'chat_history.chat_history',
		), 'taskbar');

		$this->include_stylesheet($this->local_skin_path()."/chat_history.css");

		if ($this->rc->task == 'chat_history') {
			$this->rc->output->add_handlers(array(
				'chathistorycontactlist' => array($this, 'contactlist'),
				'chathistorymessages' => array($this, 'messages'),
			));

			if (!$this->rc->output->ajax_call && !$this->rc->output->env['framed']) {
				require_once($this->home . '/chat_history_calendar.php');
				$this->calendar = new chat_history_calendar($this);
			}

			$this->include_script('chathistory.js');
		}
	}

	/**
	 * @return rcube
	 */
	public function getRcube()
	{
		return $this->rc;
	}

	function index()
	{
		$this->rc->output->set_pagetitle($this->gettext('chat_history.chat_history'));
		$this->rc->output->send('chat_history.chat_history');
	}

	public function contactlist()
	{
		$contactsQuery = $this->get_dbh()->query(
			"SELECT a.jid, r.nick FROM
				(SELECT DISTINCT(CONCAT(with_user, '@', with_server)) jid
				FROM " . self::TABLE_ARCHIVE . "
				WHERE us = ? AND with_user <> '') a
			LEFT JOIN " . self::TABLE_ROSTERUSERS . " r ON r.jid = a.jid
			ORDER BY jid",
			$this->rc->get_user_email()
		);

		$out = "<ul id='mailboxlist' class='treelist listing'>\n";
		$out .= "<li class='selected'><a href='#' onclick='return rcmail.command(\"show_history\",\"\",this)'>All</a></li>\n";
		while ($contact = $this->get_dbh()->fetch_assoc($contactsQuery)) {
			if (!$contact['nick']) {
				$contact['nick'] = $contact['jid'];
			}
			$out .= "<li id='{$contact['jid']}'>
						<a href='#' title='{$contact['jid']}' onclick='return rcmail.command(\"show_history\",\"{$contact['jid']}\",this)'>{$contact['nick']}</a>
					</li>\n";
		}
		$out .= "</ul>\n";

		return $out;
	}

	private function get_dbh()
	{
		if (!$this->_db) {
			$this->load_config();
			$config = $this->rc->config;
			$this->_db = rcube_db::factory($config->get('chathistory_db_dsnw'), $config->get('chathistory_db_dsnr'), $config->get('chathistory_db_persistent'));
			$this->_db->set_debug((bool)$config->get('chathistory_sql_debug'));
		}

		return $this->_db;
	}

	public function action_show_history()
	{
		$jid = isset($_POST['_jid']) ? $_POST['_jid'] : null;
		$date = isset($_POST['_dt']) ? date('Y-m-d', strtotime($_POST['_dt'].' 00:00:00')) : null;

		$out = $this->messages($date, $jid);
		$this->rc->output->command('plugin.update_history', array('content' => $out));
	}

	public function messages($date = null, $jid = null)
	{
		if (!$date || is_array($date)) {
			$date = date('Y-m-d');
		}

		if ($jid) {
			$jid = preg_replace('/[^\w\d\._\-\@]/i', '', $jid);
		}

		$attrib = array(
			'name' => 'chat_history_messages',
			'id' => 'chathistorymessages',
			'class' => 'records-table messagelist sortheader fixedheader fixedcopy'
		);

		$table = new html_table($attrib);

		$columns = array(
			'direction' => array(
				'className' => '',
				'html' => '',
			),
			'date' => array(
				'className' => '',
				'html' => $this->gettext('chat_history.time'),
			),
			'user' => array(
				'className' => '',
				'html' => $this->gettext('chat_history.user'),
			),
			'message' => array(
				'className' => '',
				'html' => $this->gettext('chat_history.message'),
			)
		);

		if ($jid) {
			unset($columns['user']);
		}

		foreach ($columns as $cell) {
			$table->add_header(array('class' => $cell['className'], 'id' => $cell['id']), $cell['html']);
		}

		$prefs = $this->rc->user->get_prefs();
		$messagesQuery = $this->get_dbh()->query(
			"SELECT
				r.id, r.direction, r.utc, r.utc2, CONCAT(u.nick, ' ', r.jid) name, r.body
			FROM (
				SELECT
					m.id, m.dir direction,
					CONVERT_TZ(m.utc, 'GMT', ?) utc, m.utc utc2, CONCAT(a.with_user, '@', a.with_server) jid, m.body
				FROM " . self::TABLE_MESSAGES . " m
				JOIN " . self::TABLE_ARCHIVE . " a ON a.id = m.coll_id
				WHERE a.us = ?
				ORDER BY m.utc DESC
			) r
			LEFT JOIN " . self::TABLE_ROSTERUSERS . " u ON u.jid = r.jid
			WHERE ((r.utc >= ? AND r.utc < ?) OR (r.utc IS NULL AND r.utc2 >= ? AND r.utc2 < ?))" .
			($jid ? "AND u.jid = '{$jid}'" : ''),

			isset($prefs['timezone']) && $prefs['timezone'] != 'auto' ? $prefs['timezone'] : date('e'),
			$this->rc->get_user_email(),
			$date, date('Y-m-d', strtotime($date.' 00:00:00') + 60*60*24),
			$date, date('Y-m-d', strtotime($date.' 00:00:00') + 60*60*24)
		);

		while ($message = $this->get_dbh()->fetch_assoc($messagesQuery)) {
			$table->add_row(array('id' => $message['id']));
			unset($message['id']);

			$message['utc'] = date('H:i:s', strtotime($message['utc'] ?: $message['utc2']));
			unset($message['utc2']);
			$message['body'] = nl2br(htmlentities($message['body']));
			if ($jid) {
				unset($message['name']);
			}

			foreach ($message as $name => $value) {
				if ($name == 'direction') {
					$table->add("{$name}-{$value}", null);
				} else {
					$table->add($name, $value);
				}
			}
		}

		$out = $table->show();
		return $out;
	}
}
