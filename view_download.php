<?= _get_template('_view_head', array('page_title' => $page_title)); ?>

<h2>Quote database</h2>
<p>This is the entire quote database.</p>

<p>It is a <a href="https://www.postgresql.org/">PostgreSQL</a> database dump.</p>

<p><a href="<?= htmlspecialchars($db_file); ?>">Download the database dump</a>
(Last updated <?= htmlspecialchars($db_mtime); ?>)</p>


<h2>Images</h2>
<p>Quotes can have images. Only the image filenames/paths are in the database.</p>

<p><a href="<?= htmlspecialchars($images_file); ?>">Download the images</a>
(Last updated <?= htmlspecialchars($images_mtime); ?>)</p>


<h2>Quote bot</h2>

<p>The quote bot IRC script is <a
href="https://github.com/horgh/irssi-scripts/blob/master/sqlquote.pl">here</a>.
It's an Irssi Perl script.</p>


<h2>Quote site</h2>

<p>The website itself is <a href="https://github.com/horgh/quote-site">here</a>.
It is a PHP application.</p>


<h2>Notification bot</h2>
<p>The bot that notifies IRC when a quote gets added is <a
href="https://github.com/horgh/irc/tree/master/ircnotify">this</a>.</p>
