# Table: 'phpbb_topic_prefixes'
CREATE TABLE phpbb_topic_prefixes (
	id mediumint(8) UNSIGNED NOT NULL auto_increment,
	title varchar(255) DEFAULT '' NOT NULL,
	short varchar(255) DEFAULT '' NOT NULL,
	color varchar(6) DEFAULT '000000' NOT NULL,
	forums varchar(255) DEFAULT '' NOT NULL,
	users varchar(255) DEFAULT '' NOT NULL,
	PRIMARY KEY (id)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;


# Table: 'phpbb_topic_prefixes_used'
# This table holds individual usages of the prefixes
# stored on the phpbb_topic_prefixes table. This allows
# for tokens to be used
CREATE TABLE phpbb_topic_prefixes_used (
	id mediumint(8) UNSIGNED NOT NULL auto_increment,
	prefix int(11) UNSIGNED DEFAULT '0' NOT NULL,
	topic int(11) UNSIGNED DEFAULT '0' NOT NULL,
	token_data text NOT NULL, # Serialized array of data for tokens
	applied_time int(11) UNSIGNED DEFAULT 0 NOT NULL,
	PRIMARY KEY (id),
	KEY prefix (prefix_id),
	KEY topic (topic_id),
	KEY applied_time (applied_time)
) CHARACTER SET `utf8` COLLATE `utf8_bin`;
