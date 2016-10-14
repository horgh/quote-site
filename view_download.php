<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<h2>Quote database</h2>
<p>This is the entire quote database.</p>

<p>It is a <a href="https://www.postgresql.org/">PostgreSQL</a> database dump.</p>

<p><a href="<?= htmlspecialchars($db_file); ?>">Download the database dump</a>
(Last updated <?= htmlspecialchars($db_mtime); ?>) </p>

<h2>Quote bot</h2>

<p>The quote bot script is <a
href="https://github.com/horgh/irssi-scripts/blob/master/sqlquote.pl">here</a>.
It's an Irssi Perl script.</p>

<h2>Quote site</h2>

<p>Here is an archive of the quote site. It is a PHP application.</p>

<p><a href="<?= htmlspecialchars($site_file); ?>">Download the quote site</a>
(Last updated <?= htmlspecialchars($site_file_mtime); ?>)</p>

<p>The bot that notifies when a quote gets added is <a
href="https://github.com/horgh/irc/tree/master/ircnotify">this</a>.</p>
