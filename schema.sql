# Table: 'phpbb_topic_prefixes'
CREATE TABLE phpbb_topic_prefixes (
	id mediumint(8) UNSIGNED NOT NULL auto_increment,
	title varchar(255) DEFAULT '' NOT NULL,
	short varchar(255) DEFAULT '' NOT NULL,
	style varchar(255) DEFAULT '' NOT NULL,
	forums varchar(255) DEFAULT '' NOT NULL,
	users varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_topic_prefix_instances'
# For each prefix applied to a topic, this table holds data specifically
# for that prefix instance.
# This mainly allows tokens, such as usernames, timestamps, etc. to be stored
# for display within the prefix template
CREATE TABLE phpbb_topic_prefix_instances (
	id mediumint(8) UNSIGNED NOT NULL auto_increment,
	prefix int(11) UNSIGNED DEFAULT 0 NOT NULL,
	topic int(11) UNSIGNED DEFAULT 0 NOT NULL,
	ordered int(11) UNSIGNED DEFAULT 0 NOT NULL,
	token_data text NOT NULL,
	PRIMARY KEY (id),
	KEY prefix (prefix),
	KEY topic (topic)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;
